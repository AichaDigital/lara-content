<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Enums;

/**
 * Content type enumeration.
 *
 * Defines the format of content storage (Markdown vs HTML).
 */
enum ContentType: int
{
    case MARKDOWN = 1;
    case HTML = 2;

    /**
     * Get the human-readable label for this type.
     */
    public function label(): string
    {
        return match ($this) {
            self::MARKDOWN => __('content::enums.content_type.markdown'),
            self::HTML => __('content::enums.content_type.html'),
        };
    }

    /**
     * Get all types as an array for select inputs.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $type) => $type->label(), self::cases())
        );
    }

    /**
     * Get the file extension associated with this type.
     */
    public function extension(): string
    {
        return match ($this) {
            self::MARKDOWN => 'md',
            self::HTML => 'html',
        };
    }

    /**
     * Determine if this type is Markdown.
     */
    public function isMarkdown(): bool
    {
        return $this === self::MARKDOWN;
    }

    /**
     * Determine if this type is HTML.
     */
    public function isHtml(): bool
    {
        return $this === self::HTML;
    }
}
