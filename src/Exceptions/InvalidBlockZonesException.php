<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Exceptions;

use Exception;

class InvalidBlockZonesException extends Exception
{
    /**
     * @param  array<string>  $allowedZones
     */
    public function __construct(
        string $blockSlug,
        string $requestedZone,
        public readonly array $allowedZones
    ) {
        $allowed = implode(', ', $allowedZones);
        $message = "Block '{$blockSlug}' cannot be placed in zone '{$requestedZone}'. Allowed zones: {$allowed}";
        parent::__construct($message);
    }
}
