<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Services;

use AichaDigital\LaraContent\Enums\ContentType;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use Stevebauman\Purify\Facades\Purify;

/**
 * Content sanitization service.
 *
 * Handles HTML sanitization and Markdown conversion.
 */
class ContentSanitizer
{
    protected ?CommonMarkConverter $markdownConverter = null;

    /**
     * Sanitize HTML content.
     */
    public function sanitizeHtml(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        return Purify::config($this->getPurifyConfig())->clean($content);
    }

    /**
     * Convert Markdown to sanitized HTML.
     */
    public function sanitizeMarkdown(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        $html = $this->getMarkdownConverter()->convert($content)->getContent();

        return $this->sanitizeHtml($html);
    }

    /**
     * Sanitize content based on type.
     */
    public function sanitize(string $content, ContentType $type): string
    {
        return match ($type) {
            ContentType::MARKDOWN => $this->sanitizeMarkdown($content),
            ContentType::HTML => $this->sanitizeHtml($content),
        };
    }

    /**
     * Get the Markdown converter instance.
     */
    protected function getMarkdownConverter(): CommonMarkConverter
    {
        if ($this->markdownConverter === null) {
            $environment = new Environment($this->getMarkdownConfig());
            $environment->addExtension(new CommonMarkCoreExtension);
            $environment->addExtension(new GithubFlavoredMarkdownExtension);

            $this->markdownConverter = new CommonMarkConverter([], $environment);
        }

        return $this->markdownConverter;
    }

    /**
     * Get Purify configuration.
     *
     * @return array<string, mixed>
     */
    protected function getPurifyConfig(): array
    {
        $allowedTags = config('content.security.allowed_tags', [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li',
            'a', 'img',
            'blockquote', 'pre', 'code',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'div', 'span',
            'hr',
        ]);

        $allowedAttributes = config('content.security.allowed_attributes', [
            'a' => ['href', 'title', 'target', 'rel'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            'th' => ['colspan', 'rowspan'],
            'td' => ['colspan', 'rowspan'],
            '*' => ['class', 'id'],
        ]);

        return [
            'HTML.Allowed' => $this->buildAllowedHtmlString($allowedTags, $allowedAttributes),
            'HTML.SafeIframe' => true,
            'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
            'Attr.AllowedFrameTargets' => ['_blank'],
            'HTML.Nofollow' => true,
        ];
    }

    /**
     * Build the HTML.Allowed string for HTMLPurifier.
     *
     * @param  array<string>  $tags
     * @param  array<string, array<string>>  $attributes
     */
    protected function buildAllowedHtmlString(array $tags, array $attributes): string
    {
        $parts = [];

        foreach ($tags as $tag) {
            $tagAttrs = $attributes[$tag] ?? [];
            $globalAttrs = $attributes['*'] ?? [];
            $allAttrs = array_unique(array_merge($tagAttrs, $globalAttrs));

            if (empty($allAttrs)) {
                $parts[] = $tag;
            } else {
                $parts[] = $tag.'['.implode('|', $allAttrs).']';
            }
        }

        return implode(',', $parts);
    }

    /**
     * Get Markdown converter configuration.
     *
     * @return array<string, mixed>
     */
    protected function getMarkdownConfig(): array
    {
        return config('content.markdown', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 10,
        ]);
    }

    /**
     * Strip all HTML tags from content.
     */
    public function stripTags(string $content): string
    {
        return strip_tags($content);
    }

    /**
     * Truncate content to a specified length.
     */
    public function truncate(string $content, int $length = 200, string $suffix = '...'): string
    {
        $stripped = $this->stripTags($content);

        if (mb_strlen($stripped) <= $length) {
            return $stripped;
        }

        return mb_substr($stripped, 0, $length).$suffix;
    }
}
