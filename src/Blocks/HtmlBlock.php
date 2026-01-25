<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Blocks;

use AichaDigital\LaraContent\Services\ContentSanitizer;
use Illuminate\Contracts\View\View;

/**
 * HTML content block.
 *
 * Renders sanitized HTML content.
 */
class HtmlBlock extends AbstractBlock
{
    public function __construct(
        protected ContentSanitizer $sanitizer
    ) {}

    public function getSlug(): string
    {
        return 'html';
    }

    public function getName(): string
    {
        return __('content::blocks.html.name');
    }

    /**
     * @return array<string, array{type: string, required?: bool, default?: mixed}>
     */
    public function getConfigSchema(): array
    {
        return [
            'content' => [
                'type' => 'string',
                'required' => true,
                'default' => '',
            ],
        ];
    }

    public function render(BlockContext $context): View|string
    {
        $content = $this->getConfigWithDefault($context, 'content');
        $sanitized = $this->sanitizer->sanitizeHtml($content ?? '');

        return view($this->getViewName(), [
            'context' => $context,
            'content' => $sanitized,
            'block' => $this,
        ]);
    }
}
