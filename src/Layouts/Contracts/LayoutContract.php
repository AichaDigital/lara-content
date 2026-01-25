<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Layouts\Contracts;

use Illuminate\Contracts\View\View;

/**
 * Contract for content layouts.
 *
 * Layouts define the structural arrangement of zones where blocks can be placed.
 */
interface LayoutContract
{
    /**
     * Get the unique slug identifier for this layout.
     */
    public function getSlug(): string;

    /**
     * Get the human-readable name of this layout.
     */
    public function getName(): string;

    /**
     * Get the available zones in this layout.
     *
     * @return array<string> Zone identifiers (e.g., 'main', 'sidebar', 'header')
     */
    public function getZones(): array;

    /**
     * Render the layout with the given zone contents.
     *
     * @param  array<string, string>  $zoneContents  Rendered HTML for each zone
     */
    public function render(array $zoneContents): View;

    /**
     * Get the view name for this layout.
     */
    public function getViewName(): string;
}
