<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Blocks;

use AichaDigital\LaraContent\Services\MenuBuilder;
use Illuminate\Contracts\View\View;

/**
 * Menu block.
 *
 * Renders a navigation menu by slug.
 */
class MenuBlock extends AbstractBlock
{
    public function __construct(
        protected MenuBuilder $menuBuilder
    ) {}

    public function getSlug(): string
    {
        return 'menu';
    }

    public function getName(): string
    {
        return __('content::blocks.menu.name');
    }

    /**
     * @return array<string, array{type: string, required?: bool, default?: mixed}>
     */
    public function getConfigSchema(): array
    {
        return [
            'menu_slug' => [
                'type' => 'string',
                'required' => true,
                'default' => '',
            ],
            'style' => [
                'type' => 'string',
                'required' => false,
                'default' => 'vertical',
            ],
            'show_icons' => [
                'type' => 'boolean',
                'required' => false,
                'default' => false,
            ],
        ];
    }

    public function render(BlockContext $context): View|string
    {
        $menuSlug = $this->getConfigWithDefault($context, 'menu_slug');
        $style = $this->getConfigWithDefault($context, 'style');
        $showIcons = $this->getConfigWithDefault($context, 'show_icons');

        if (empty($menuSlug)) {
            return '';
        }

        $menu = $this->menuBuilder->build($menuSlug);

        if (! $menu) {
            return '';
        }

        return view($this->getViewName(), [
            'context' => $context,
            'menu' => $menu,
            'items' => $menu->getTree(),
            'style' => $style,
            'showIcons' => $showIcons,
            'block' => $this,
        ]);
    }
}
