<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Blocks;

use AichaDigital\LaraContent\Models\Post;
use Illuminate\Contracts\View\View;

/**
 * Recent posts block.
 *
 * Displays a list of recent blog posts.
 */
class RecentPostsBlock extends AbstractBlock
{
    public function getSlug(): string
    {
        return 'recent-posts';
    }

    public function getName(): string
    {
        return __('content::blocks.recent_posts.name');
    }

    /**
     * @return array<string>
     */
    public function getAllowedZones(): array
    {
        return ['sidebar', 'main'];
    }

    /**
     * @return array<string, array{type: string, required?: bool, default?: mixed}>
     */
    public function getConfigSchema(): array
    {
        return [
            'count' => [
                'type' => 'integer',
                'required' => false,
                'default' => 5,
            ],
            'show_excerpt' => [
                'type' => 'boolean',
                'required' => false,
                'default' => true,
            ],
            'show_date' => [
                'type' => 'boolean',
                'required' => false,
                'default' => true,
            ],
            'show_author' => [
                'type' => 'boolean',
                'required' => false,
                'default' => false,
            ],
        ];
    }

    public function render(BlockContext $context): View|string
    {
        $count = $this->getConfigWithDefault($context, 'count');
        $showExcerpt = $this->getConfigWithDefault($context, 'show_excerpt');
        $showDate = $this->getConfigWithDefault($context, 'show_date');
        $showAuthor = $this->getConfigWithDefault($context, 'show_author');

        $posts = Post::published()
            ->recent()
            ->limit($count)
            ->get();

        return view($this->getViewName(), [
            'context' => $context,
            'posts' => $posts,
            'showExcerpt' => $showExcerpt,
            'showDate' => $showDate,
            'showAuthor' => $showAuthor,
            'block' => $this,
        ]);
    }
}
