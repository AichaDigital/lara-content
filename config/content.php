<?php

declare(strict_types=1);
use AichaDigital\LaraContent\Models\Menu;
use AichaDigital\LaraContent\Models\MenuItem;
use AichaDigital\LaraContent\Models\Page;
use AichaDigital\LaraContent\Models\PageBlock;
use AichaDigital\LaraContent\Models\Post;

return [
    /*
    |--------------------------------------------------------------------------
    | User ID Type Configuration
    |--------------------------------------------------------------------------
    |
    | Defines the type of primary key used in the users table.
    | This affects how foreign keys (like author_id) are created.
    |
    | Supported: 'auto', 'int', 'uuid', 'ulid'
    |
    | 'auto' - Automatically detect from the users table
    | 'int'  - unsignedBigInteger (Laravel default)
    | 'uuid' - char(36) UUID v7 (recommended)
    | 'ulid' - char(26) ULID
    |
    */
    'user_id_type' => env('CONTENT_USER_ID_TYPE', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Author Model
    |--------------------------------------------------------------------------
    |
    | The fully qualified class name of the User model used as the author
    | for posts. This model should implement ContentAuthorContract.
    |
    */
    'author_model' => env('CONTENT_AUTHOR_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for blocks and menus.
    |
    */
    'cache' => [
        'enabled' => env('CONTENT_CACHE_ENABLED', true),
        'default_ttl' => env('CONTENT_CACHE_TTL', 3600), // seconds
        'prefix' => 'content',
        'debug_headers' => env('CONTENT_CACHE_DEBUG', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure HTML sanitization settings.
    |
    */
    'security' => [
        'allowed_tags' => [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li',
            'a', 'img',
            'blockquote', 'pre', 'code',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'div', 'span',
            'hr',
            'figure', 'figcaption',
        ],
        'allowed_attributes' => [
            'a' => ['href', 'title', 'target', 'rel'],
            'img' => ['src', 'alt', 'title', 'width', 'height'],
            'th' => ['colspan', 'rowspan', 'scope'],
            'td' => ['colspan', 'rowspan'],
            '*' => ['class', 'id'],
        ],
        'allowed_image_hosts' => [
            'localhost',
            // Add your CDN domains here
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the CommonMark Markdown parser.
    |
    */
    'markdown' => [
        'html_input' => 'strip', // strip, escape, or allow
        'allow_unsafe_links' => false,
        'max_nesting_level' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Classes
    |--------------------------------------------------------------------------
    |
    | Override the default model classes if you need to extend them.
    |
    */
    'models' => [
        'page' => Page::class,
        'page_block' => PageBlock::class,
        'post' => Post::class,
        'menu' => Menu::class,
        'menu_item' => MenuItem::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Configure route prefixes and middleware for content routes.
    |
    */
    'routes' => [
        'enabled' => true,
        'prefix' => '',
        'middleware' => ['web'],
        'page_route_name' => 'content.page',
        'post_route_name' => 'content.post',
    ],
];
