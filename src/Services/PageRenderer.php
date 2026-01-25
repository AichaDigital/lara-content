<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Services;

use AichaDigital\LaraContent\Models\Page;
use AichaDigital\LaraContent\Registries\LayoutRegistry;
use Illuminate\Contracts\View\View;

/**
 * Page rendering service.
 *
 * Orchestrates the complete rendering of a page with its layout and blocks.
 */
class PageRenderer
{
    public function __construct(
        protected LayoutRegistry $layoutRegistry,
        protected BlockRenderer $blockRenderer
    ) {}

    /**
     * Render a complete page.
     */
    public function render(Page $page): View
    {
        $layout = $this->layoutRegistry->get($page->layout_slug);
        $zones = $layout->getZones();

        $zoneContents = [];

        foreach ($zones as $zone) {
            $zoneContents[$zone] = $this->renderZone($page, $zone);
        }

        return $layout->render($zoneContents);
    }

    /**
     * Render all blocks in a specific zone.
     */
    public function renderZone(Page $page, string $zone): string
    {
        $blocks = $page->blocksInZone($zone)->orderBy('position')->get();

        $rendered = [];

        foreach ($blocks as $pageBlock) {
            $rendered[] = $this->blockRenderer->render($pageBlock, $page);
        }

        return implode("\n", $rendered);
    }

    /**
     * Get page metadata for SEO.
     *
     * @return array<string, string|null>
     */
    public function getMetadata(Page $page): array
    {
        return [
            'title' => $page->getTranslatedContent('title'),
            'description' => $page->getTranslatedContent('meta_description'),
        ];
    }

    /**
     * Render a page by slug.
     */
    public function renderBySlug(string $slug): ?View
    {
        $page = Page::bySlug($slug)->published()->first();

        if (! $page) {
            return null;
        }

        return $this->render($page);
    }

    /**
     * Check if a page exists and is published.
     */
    public function exists(string $slug): bool
    {
        return Page::bySlug($slug)->published()->exists();
    }

    /**
     * Get the layout for a page.
     */
    public function getLayout(Page $page): \AichaDigital\LaraContent\Layouts\Contracts\LayoutContract
    {
        return $this->layoutRegistry->get($page->layout_slug);
    }

    /**
     * Get available zones for a page's layout.
     *
     * @return array<string>
     */
    public function getAvailableZones(Page $page): array
    {
        return $this->getLayout($page)->getZones();
    }
}
