<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Enums\ContentType;
use AichaDigital\LaraContent\Models\Page;

test('can create a page', function () {
    $page = Page::create([
        'slug' => 'test-page',
        'title' => ['en' => 'Test Page', 'es' => 'Pagina de prueba'],
        'layout_slug' => 'single',
        'content_type' => ContentType::HTML,
        'is_published' => true,
    ]);

    expect($page)->toBeInstanceOf(Page::class)
        ->and($page->slug)->toBe('test-page')
        ->and($page->id)->not->toBeNull();
});

test('page has uuid primary key', function () {
    $page = Page::create([
        'slug' => 'uuid-test',
        'title' => ['en' => 'UUID Test'],
        'layout_slug' => 'single',
    ]);

    expect($page->id)->toBeString()
        ->and(strlen($page->id))->toBe(36);
});

test('page has translatable title', function () {
    $page = Page::create([
        'slug' => 'translatable-test',
        'title' => ['en' => 'English Title', 'es' => 'Titulo en espanol'],
        'layout_slug' => 'single',
    ]);

    app()->setLocale('en');
    expect($page->title)->toBe('English Title');

    app()->setLocale('es');
    expect($page->title)->toBe('Titulo en espanol');
});

test('published scope filters correctly', function () {
    Page::create([
        'slug' => 'published',
        'title' => ['en' => 'Published'],
        'layout_slug' => 'single',
        'is_published' => true,
    ]);

    Page::create([
        'slug' => 'draft',
        'title' => ['en' => 'Draft'],
        'layout_slug' => 'single',
        'is_published' => false,
    ]);

    $published = Page::published()->get();

    expect($published)->toHaveCount(1)
        ->and($published->first()->slug)->toBe('published');
});

test('by slug scope finds page', function () {
    Page::create([
        'slug' => 'find-me',
        'title' => ['en' => 'Find Me'],
        'layout_slug' => 'single',
    ]);

    $page = Page::bySlug('find-me')->first();

    expect($page)->not->toBeNull()
        ->and($page->slug)->toBe('find-me');
});

test('page uses slug for route binding', function () {
    $page = new Page;

    expect($page->getRouteKeyName())->toBe('slug');
});
