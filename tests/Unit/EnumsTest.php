<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Enums\ContentType;
use AichaDigital\LaraContent\Enums\MenuItemType;

test('content type has correct values', function () {
    expect(ContentType::MARKDOWN->value)->toBe(1)
        ->and(ContentType::HTML->value)->toBe(2);
});

test('content type provides options', function () {
    $options = ContentType::options();

    expect($options)->toHaveCount(2)
        ->and($options)->toHaveKey(1)
        ->and($options)->toHaveKey(2);
});

test('content type identifies type correctly', function () {
    expect(ContentType::MARKDOWN->isMarkdown())->toBeTrue()
        ->and(ContentType::MARKDOWN->isHtml())->toBeFalse()
        ->and(ContentType::HTML->isHtml())->toBeTrue()
        ->and(ContentType::HTML->isMarkdown())->toBeFalse();
});

test('menu item type has correct values', function () {
    expect(MenuItemType::PAGE->value)->toBe(1)
        ->and(MenuItemType::POST->value)->toBe(2)
        ->and(MenuItemType::URL->value)->toBe(3)
        ->and(MenuItemType::ROUTE->value)->toBe(4);
});

test('menu item type identifies internal types', function () {
    expect(MenuItemType::PAGE->isInternal())->toBeTrue()
        ->and(MenuItemType::POST->isInternal())->toBeTrue()
        ->and(MenuItemType::URL->isInternal())->toBeFalse()
        ->and(MenuItemType::ROUTE->isInternal())->toBeFalse();
});

test('menu item type identifies url type', function () {
    expect(MenuItemType::URL->isUrl())->toBeTrue()
        ->and(MenuItemType::PAGE->isUrl())->toBeFalse();
});

test('menu item type identifies route type', function () {
    expect(MenuItemType::ROUTE->isRoute())->toBeTrue()
        ->and(MenuItemType::PAGE->isRoute())->toBeFalse();
});
