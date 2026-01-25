<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Layouts;

/**
 * Two column layout with sidebar on the right.
 */
class SidebarRightLayout extends AbstractLayout
{
    public function getSlug(): string
    {
        return 'sidebar-right';
    }

    public function getName(): string
    {
        return __('content::layouts.sidebar_right.name');
    }

    /**
     * @return array<string>
     */
    public function getZones(): array
    {
        return ['main', 'sidebar'];
    }
}
