<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Registries;

use AichaDigital\LaraContent\Blocks\Contracts\BlockContract;
use AichaDigital\LaraContent\Exceptions\BlockNotFoundException;
use AichaDigital\LaraContent\Exceptions\DuplicateBlockSlugException;

/**
 * Registry for managing available blocks.
 *
 * Singleton pattern - registered in the service provider.
 */
class BlockRegistry
{
    /**
     * @var array<string, BlockContract>
     */
    protected array $blocks = [];

    /**
     * Register a block.
     *
     * @throws DuplicateBlockSlugException
     */
    public function register(BlockContract $block): self
    {
        $slug = $block->getSlug();

        if (isset($this->blocks[$slug])) {
            throw new DuplicateBlockSlugException(
                "Block with slug '{$slug}' is already registered."
            );
        }

        $this->blocks[$slug] = $block;

        return $this;
    }

    /**
     * Get a block by slug.
     *
     * @throws BlockNotFoundException
     */
    public function get(string $slug): BlockContract
    {
        if (! isset($this->blocks[$slug])) {
            throw new BlockNotFoundException(
                "Block with slug '{$slug}' not found."
            );
        }

        return $this->blocks[$slug];
    }

    /**
     * Check if a block exists.
     */
    public function has(string $slug): bool
    {
        return isset($this->blocks[$slug]);
    }

    /**
     * Get all registered blocks.
     *
     * @return array<string, BlockContract>
     */
    public function all(): array
    {
        return $this->blocks;
    }

    /**
     * Get all block slugs.
     *
     * @return array<string>
     */
    public function slugs(): array
    {
        return array_keys($this->blocks);
    }

    /**
     * Get blocks available for a specific zone.
     *
     * @return array<string, BlockContract>
     */
    public function forZone(string $zone): array
    {
        return array_filter($this->blocks, function (BlockContract $block) use ($zone) {
            $allowedZones = $block->getAllowedZones();

            return in_array('*', $allowedZones, true) || in_array($zone, $allowedZones, true);
        });
    }

    /**
     * Get blocks as options for select inputs.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        $options = [];

        foreach ($this->blocks as $slug => $block) {
            $options[$slug] = $block->getName();
        }

        return $options;
    }

    /**
     * Get interactive blocks only.
     *
     * @return array<string, BlockContract>
     */
    public function interactive(): array
    {
        return array_filter($this->blocks, fn (BlockContract $block) => $block->isInteractive());
    }

    /**
     * Get static (non-interactive) blocks only.
     *
     * @return array<string, BlockContract>
     */
    public function static(): array
    {
        return array_filter($this->blocks, fn (BlockContract $block) => ! $block->isInteractive());
    }

    /**
     * Remove a block from the registry.
     */
    public function forget(string $slug): self
    {
        unset($this->blocks[$slug]);

        return $this;
    }

    /**
     * Clear all registered blocks.
     */
    public function flush(): self
    {
        $this->blocks = [];

        return $this;
    }
}
