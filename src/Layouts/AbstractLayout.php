<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Layouts;

use AichaDigital\LaraContent\Layouts\Contracts\LayoutContract;
use Illuminate\Contracts\View\View;

/**
 * Abstract base class for layouts.
 *
 * Provides common functionality for all layout implementations.
 */
abstract class AbstractLayout implements LayoutContract
{
    /**
     * Get the unique slug identifier for this layout.
     */
    abstract public function getSlug(): string;

    /**
     * Get the human-readable name of this layout.
     */
    abstract public function getName(): string;

    /**
     * Get the available zones in this layout.
     *
     * @return array<string>
     */
    abstract public function getZones(): array;

    /**
     * Get the view name for this layout.
     */
    public function getViewName(): string
    {
        return 'content::layouts.'.$this->getSlug();
    }

    /**
     * Render the layout with the given zone contents.
     *
     * @param  array<string, string>  $zoneContents
     */
    public function render(array $zoneContents): View
    {
        return view($this->getViewName(), [
            'zones' => $zoneContents,
            'layout' => $this,
        ]);
    }

    /**
     * Check if a zone exists in this layout.
     */
    public function hasZone(string $zone): bool
    {
        return in_array($zone, $this->getZones(), true);
    }

    /**
     * Get the default zone for this layout.
     */
    public function getDefaultZone(): string
    {
        $zones = $this->getZones();

        return $zones[0] ?? 'main';
    }
}
