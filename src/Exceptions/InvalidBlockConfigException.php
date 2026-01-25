<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Exceptions;

use Exception;

class InvalidBlockConfigException extends Exception
{
    /**
     * @param  array<string, string>  $errors
     */
    public function __construct(
        string $blockSlug,
        public readonly array $errors
    ) {
        $message = "Invalid configuration for block '{$blockSlug}': ".implode(', ', $errors);
        parent::__construct($message);
    }
}
