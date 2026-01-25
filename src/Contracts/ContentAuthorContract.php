<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Contracts;

/**
 * Contract for content authors.
 *
 * Implemented by the User model to provide author information for posts.
 */
interface ContentAuthorContract
{
    /**
     * Get the author's unique identifier.
     */
    public function getId(): int|string;

    /**
     * Get the author's display name.
     */
    public function getName(): string;

    /**
     * Get the URL to the author's avatar image.
     */
    public function getAvatarUrl(): ?string;
}
