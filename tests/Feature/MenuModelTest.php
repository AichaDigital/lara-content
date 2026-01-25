<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Enums\MenuItemType;
use AichaDigital\LaraContent\Models\Menu;
use AichaDigital\LaraContent\Models\MenuItem;

test('can create a menu', function () {
    $menu = Menu::create([
        'slug' => 'main-menu',
        'name' => 'Main Menu',
    ]);

    expect($menu)->toBeInstanceOf(Menu::class)
        ->and($menu->slug)->toBe('main-menu')
        ->and($menu->id)->not->toBeNull();
});

test('menu has uuid primary key', function () {
    $menu = Menu::create([
        'slug' => 'uuid-test',
        'name' => 'UUID Test',
    ]);

    expect($menu->id)->toBeString()
        ->and(strlen($menu->id))->toBe(36);
});

test('can create menu items', function () {
    $menu = Menu::create([
        'slug' => 'test-menu',
        'name' => 'Test Menu',
    ]);

    $item = MenuItem::create([
        'menu_id' => $menu->id,
        'label' => ['en' => 'Home', 'es' => 'Inicio'],
        'type' => MenuItemType::URL,
        'reference' => '/',
        'position' => 0,
    ]);

    expect($item)->toBeInstanceOf(MenuItem::class)
        ->and($item->menu_id)->toBe($menu->id);
});

test('menu has items relationship', function () {
    $menu = Menu::create([
        'slug' => 'with-items',
        'name' => 'Menu with Items',
    ]);

    MenuItem::create([
        'menu_id' => $menu->id,
        'label' => ['en' => 'Item 1'],
        'type' => MenuItemType::URL,
        'reference' => '/item1',
        'position' => 0,
    ]);

    MenuItem::create([
        'menu_id' => $menu->id,
        'label' => ['en' => 'Item 2'],
        'type' => MenuItemType::URL,
        'reference' => '/item2',
        'position' => 1,
    ]);

    expect($menu->items)->toHaveCount(2);
});

test('menu item has translatable label', function () {
    $menu = Menu::create([
        'slug' => 'translatable',
        'name' => 'Translatable',
    ]);

    $item = MenuItem::create([
        'menu_id' => $menu->id,
        'label' => ['en' => 'English', 'es' => 'Espanol'],
        'type' => MenuItemType::URL,
        'reference' => '/',
        'position' => 0,
    ]);

    app()->setLocale('en');
    expect($item->label)->toBe('English');

    app()->setLocale('es');
    expect($item->label)->toBe('Espanol');
});

test('menu item can have children', function () {
    $menu = Menu::create([
        'slug' => 'nested',
        'name' => 'Nested Menu',
    ]);

    $parent = MenuItem::create([
        'menu_id' => $menu->id,
        'label' => ['en' => 'Parent'],
        'type' => MenuItemType::URL,
        'reference' => '/parent',
        'position' => 0,
    ]);

    $child = MenuItem::create([
        'menu_id' => $menu->id,
        'parent_id' => $parent->id,
        'label' => ['en' => 'Child'],
        'type' => MenuItemType::URL,
        'reference' => '/child',
        'position' => 0,
    ]);

    expect($parent->children)->toHaveCount(1)
        ->and($child->parent->id)->toBe($parent->id);
});
