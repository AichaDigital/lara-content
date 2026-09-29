<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Enums\ContentType;
use AichaDigital\LaraContent\Models\Post;

test('seo and internal fields persist with translatable casts', function () {
    $post = Post::create([
        'slug' => 'seo-fields',
        'title' => ['en' => 'SEO fields'],
        'content_type' => ContentType::HTML,
        'meta_title' => ['es' => 'Titulo SEO', 'en' => 'SEO title'],
        'meta_description' => ['es' => 'Descripcion SEO corta'],
        'featured_image' => 'https://example.test/cover.png',
        'featured_image_alt' => ['es' => 'Texto alternativo de la imagen'],
        'focus_keyword' => 'aprender arabe',
        'secondary_keywords' => ['alfabeto arabe', 'curso de arabe'],
        'internal_notes' => ['Review note one', 'Review note two'],
    ]);

    $post = $post->fresh();

    expect($post->getTranslations('meta_title'))->toBe(['es' => 'Titulo SEO', 'en' => 'SEO title'])
        ->and($post->getTranslations('meta_description')['es'])->toBe('Descripcion SEO corta')
        ->and($post->getTranslations('featured_image_alt')['es'])->toBe('Texto alternativo de la imagen')
        ->and($post->focus_keyword)->toBe('aprender arabe')
        ->and($post->secondary_keywords)->toBe(['alfabeto arabe', 'curso de arabe'])
        ->and($post->internal_notes)->toBe(['Review note one', 'Review note two']);
});

test('seo fields are translatable', function () {
    $post = Post::create([
        'slug' => 'seo-translatable',
        'title' => ['en' => 'SEO translatable'],
        'content_type' => ContentType::HTML,
        'meta_title' => ['es' => 'Titulo SEO', 'en' => 'SEO title'],
        'meta_description' => ['es' => 'Descripcion', 'en' => 'Description'],
        'featured_image_alt' => ['es' => 'Alt ES', 'en' => 'Alt EN'],
    ]);

    app()->setLocale('es');
    expect($post->meta_title)->toBe('Titulo SEO')
        ->and($post->meta_description)->toBe('Descripcion')
        ->and($post->featured_image_alt)->toBe('Alt ES');

    app()->setLocale('en');
    expect($post->meta_title)->toBe('SEO title')
        ->and($post->featured_image_alt)->toBe('Alt EN');
});

test('internal attributes are declared and excluded from the public surface', function () {
    expect(Post::INTERNAL_ATTRIBUTES)->toBe([
        'focus_keyword',
        'secondary_keywords',
        'internal_notes',
    ]);

    $public = (new Post)->publicAttributes();

    foreach (Post::INTERNAL_ATTRIBUTES as $internal) {
        expect($public)->not->toContain($internal);
    }

    // The public surface still carries everything a listing needs.
    expect($public)->toContain('slug')
        ->and($public)->toContain('title')
        ->and($public)->toContain('excerpt')
        ->and($public)->toContain('meta_title')
        ->and($public)->toContain('meta_description');
});
