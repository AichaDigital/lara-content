<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Registries;

use AichaDigital\LaraContent\Exceptions\DuplicateLayoutSlugException;
use AichaDigital\LaraContent\Exceptions\LayoutNotFoundException;
use AichaDigital\LaraContent\Layouts\Contracts\LayoutContract;

/**
 * Registry for managing available layouts.
 *
 * Singleton pattern - registered in the service provider.
 */
class LayoutRegistry
{
    /**
     * @var array<string, LayoutContract>
     */
    protected array $layouts = [];

    /**
     * Register a layout.
     *
     * @throws DuplicateLayoutSlugException
     */
    public function register(LayoutContract $layout): self
    {
        $slug = $layout->getSlug();

        if (isset($this->layouts[$slug])) {
            throw new DuplicateLayoutSlugException(
                "Layout with slug '{$slug}' is already registered."
            );
        }

        $this->layouts[$slug] = $layout;

        return $this;
    }

    /**
     * Get a layout by slug.
     *
     * @throws LayoutNotFoundException
     */
    public function get(string $slug): LayoutContract
    {
        if (! isset($this->layouts[$slug])) {
            throw new LayoutNotFoundException(
                "Layout with slug '{$slug}' not found."
            );
        }

        return $this->layouts[$slug];
    }

    /**
     * Check if a layout exists.
     */
    public function has(string $slug): bool
    {
        return isset($this->layouts[$slug]);
    }

    /**
     * Get all registered layouts.
     *
     * @return array<string, LayoutContract>
     */
    public function all(): array
    {
        return $this->layouts;
    }

    /**
     * Get all layout slugs.
     *
     * @return array<string>
     */
    public function slugs(): array
    {
        return array_keys($this->layouts);
    }

    /**
     * Get layouts as options for select inputs.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        $options = [];

        foreach ($this->layouts as $slug => $layout) {
            $options[$slug] = $layout->getName();
        }

        return $options;
    }

    /**
     * Remove a layout from the registry.
     */
    public function forget(string $slug): self
    {
        unset($this->layouts[$slug]);

        return $this;
    }

    /**
     * Clear all registered layouts.
     */
    public function flush(): self
    {
        $this->layouts = [];

        return $this;
    }
}
