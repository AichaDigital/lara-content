<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Layouts;

/**
 * Single column layout with one main zone.
 */
class SingleLayout extends AbstractLayout
{
    public function getSlug(): string
    {
        return 'single';
    }

    public function getName(): string
    {
        return __('content::layouts.single.name');
    }

    /**
     * @return array<string>
     */
    public function getZones(): array
    {
        return ['main'];
    }
}
