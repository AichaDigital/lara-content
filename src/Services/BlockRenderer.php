<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Services;

use AichaDigital\LaraContent\Blocks\BlockContext;
use AichaDigital\LaraContent\Blocks\Contracts\BlockContract;
use AichaDigital\LaraContent\Models\Page;
use AichaDigital\LaraContent\Models\PageBlock;
use AichaDigital\LaraContent\Registries\BlockRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

/**
 * Block rendering service.
 *
 * Handles rendering of individual blocks with caching support.
 */
class BlockRenderer
{
    public function __construct(
        protected BlockRegistry $blockRegistry
    ) {}

    /**
     * Render a page block.
     */
    public function render(PageBlock $pageBlock, ?Page $page = null): string
    {
        $block = $this->blockRegistry->get($pageBlock->block_type_slug);

        $context = new BlockContext(
            config: $pageBlock->config ?? [],
            page: $page ?? $pageBlock->page,
            pageBlock: $pageBlock,
            zone: $pageBlock->zone,
            position: $pageBlock->position,
            locale: app()->getLocale(),
        );

        // Interactive blocks are never cached
        if ($block->isInteractive()) {
            return $this->renderBlock($block, $context);
        }

        // Check if caching is enabled for this block
        if (! $pageBlock->isCacheEnabled()) {
            return $this->renderBlock($block, $context);
        }

        $cacheKey = $this->getCacheKey($context);
        $ttl = $pageBlock->getEffectiveCacheTtl();

        return Cache::remember($cacheKey, $ttl, fn () => $this->renderBlock($block, $context));
    }

    /**
     * Render a standalone block (not associated with a page).
     *
     * @param  array<string, mixed>  $config
     */
    public function renderStandalone(string $blockSlug, array $config = [], string $zone = 'main'): string
    {
        $block = $this->blockRegistry->get($blockSlug);

        $context = new BlockContext(
            config: $config,
            zone: $zone,
            locale: app()->getLocale(),
        );

        return $this->renderBlock($block, $context);
    }

    /**
     * Render a block with context.
     */
    protected function renderBlock(BlockContract $block, BlockContext $context): string
    {
        $result = $block->render($context);

        if ($result instanceof View) {
            return $result->render();
        }

        return (string) $result;
    }

    /**
     * Get the cache key for a block context.
     */
    protected function getCacheKey(BlockContext $context): string
    {
        $prefix = config('content.cache.prefix', 'content');

        return $prefix.':'.$context->getCacheKey();
    }

    /**
     * Clear cache for a specific page block.
     */
    public function clearCache(PageBlock $pageBlock): void
    {
        $context = new BlockContext(
            config: $pageBlock->config ?? [],
            pageBlock: $pageBlock,
            zone: $pageBlock->zone,
            position: $pageBlock->position,
        );

        // Clear for all locales
        $locales = config('app.available_locales', [config('app.locale')]);

        foreach ($locales as $locale) {
            $localizedContext = $context->withLocale($locale);
            $cacheKey = $this->getCacheKey($localizedContext);
            Cache::forget($cacheKey);
        }
    }

    /**
     * Clear all block cache for a page.
     */
    public function clearPageCache(Page $page): void
    {
        foreach ($page->blocks as $pageBlock) {
            $this->clearCache($pageBlock);
        }
    }
}
