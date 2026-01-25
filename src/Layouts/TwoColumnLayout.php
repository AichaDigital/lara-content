<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Layouts;

/**
 * Two equal column layout.
 */
class TwoColumnLayout extends AbstractLayout
{
    public function getSlug(): string
    {
        return 'two-column';
    }

    public function getName(): string
    {
        return __('content::layouts.two_column.name');
    }

    /**
     * @return array<string>
     */
    public function getZones(): array
    {
        return ['left', 'right'];
    }
}
