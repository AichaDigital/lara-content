<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Enums;

/**
 * Publishing status enumeration.
 *
 * Defines the editorial lifecycle of posts and pages. `published_at` acts as
 * the scheduling timestamp: a PUBLISHED item with a future `published_at`
 * is scheduled and not yet publicly visible.
 */
enum PublishStatus: string
{
    case DRAFT = 'draft';
    case REVIEW = 'review';
    case READY = 'ready';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    /**
     * Get the human-readable label for this status.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => __('content::enums.publish_status.draft'),
            self::REVIEW => __('content::enums.publish_status.review'),
            self::READY => __('content::enums.publish_status.ready'),
            self::PUBLISHED => __('content::enums.publish_status.published'),
            self::ARCHIVED => __('content::enums.publish_status.archived'),
        };
    }

    /**
     * Get all statuses as an array for select inputs.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $status) => $status->label(), self::cases())
        );
    }

    /**
     * Determine if this status is publicly visible.
     */
    public function isPublished(): bool
    {
        return $this === self::PUBLISHED;
    }

    /**
     * Determine if this status is part of the pre-publication workflow.
     */
    public function isWorkflow(): bool
    {
        return in_array($this, [self::DRAFT, self::REVIEW, self::READY], true);
    }
}
