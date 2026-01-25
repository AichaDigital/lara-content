<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Layouts;

/**
 * Three column layout.
 */
class ThreeColumnLayout extends AbstractLayout
{
    public function getSlug(): string
    {
        return 'three-column';
    }

    public function getName(): string
    {
        return __('content::layouts.three_column.name');
    }

    /**
     * @return array<string>
     */
    public function getZones(): array
    {
        return ['left', 'center', 'right'];
    }
}
