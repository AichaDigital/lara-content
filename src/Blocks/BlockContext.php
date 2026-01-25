<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Blocks;

use AichaDigital\LaraContent\Models\Page;
use AichaDigital\LaraContent\Models\PageBlock;

/**
 * Value Object for block rendering context.
 *
 * Contains all the information a block needs to render itself.
 */
final readonly class BlockContext
{
    /**
     * @param  array<string, mixed>  $config  Block configuration from PageBlock
     * @param  Page|null  $page  The page containing this block (if any)
     * @param  PageBlock|null  $pageBlock  The page block instance (if any)
     * @param  string  $zone  The zone where this block is being rendered
     * @param  int  $position  The position within the zone
     * @param  string  $locale  The current rendering locale
     */
    public function __construct(
        public array $config = [],
        public ?Page $page = null,
        public ?PageBlock $pageBlock = null,
        public string $zone = 'main',
        public int $position = 0,
        public string $locale = 'es',
    ) {}

    /**
     * Get a configuration value with optional default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Check if a configuration key exists.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * Get the cache key for this block context.
     */
    public function getCacheKey(): string
    {
        $parts = [
            'block',
            $this->pageBlock?->id ?? 'standalone',
            $this->zone,
            $this->position,
            $this->locale,
            md5(serialize($this->config)),
        ];

        return implode(':', $parts);
    }

    /**
     * Create a new context with modified config.
     *
     * @param  array<string, mixed>  $config
     */
    public function withConfig(array $config): self
    {
        return new self(
            config: array_merge($this->config, $config),
            page: $this->page,
            pageBlock: $this->pageBlock,
            zone: $this->zone,
            position: $this->position,
            locale: $this->locale,
        );
    }

    /**
     * Create a new context with a different locale.
     */
    public function withLocale(string $locale): self
    {
        return new self(
            config: $this->config,
            page: $this->page,
            pageBlock: $this->pageBlock,
            zone: $this->zone,
            position: $this->position,
            locale: $locale,
        );
    }
}
