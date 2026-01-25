<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Layouts;

/**
 * Two column layout with sidebar on the left.
 */
class SidebarLeftLayout extends AbstractLayout
{
    public function getSlug(): string
    {
        return 'sidebar-left';
    }

    public function getName(): string
    {
        return __('content::layouts.sidebar_left.name');
    }

    /**
     * @return array<string>
     */
    public function getZones(): array
    {
        return ['sidebar', 'main'];
    }
}
