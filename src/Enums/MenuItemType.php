<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Enums;

/**
 * Menu item type enumeration.
 *
 * Defines the different types of links a menu item can represent.
 */
enum MenuItemType: int
{
    case PAGE = 1;
    case POST = 2;
    case URL = 3;
    case ROUTE = 4;

    /**
     * Get the human-readable label for this type.
     */
    public function label(): string
    {
        return match ($this) {
            self::PAGE => __('content::enums.menu_item_type.page'),
            self::POST => __('content::enums.menu_item_type.post'),
            self::URL => __('content::enums.menu_item_type.url'),
            self::ROUTE => __('content::enums.menu_item_type.route'),
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
     * Determine if this type references an internal model.
     */
    public function isInternal(): bool
    {
        return in_array($this, [self::PAGE, self::POST], true);
    }

    /**
     * Determine if this type uses a URL string.
     */
    public function isUrl(): bool
    {
        return $this === self::URL;
    }

    /**
     * Determine if this type uses a route name.
     */
    public function isRoute(): bool
    {
        return $this === self::ROUTE;
    }
}
