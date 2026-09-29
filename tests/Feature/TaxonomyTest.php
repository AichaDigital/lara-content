<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Enums\ContentType;
use AichaDigital\LaraContent\Models\Category;
use AichaDigital\LaraContent\Models\Post;
use AichaDigital\LaraContent\Models\Tag;
use Illuminate\Database\UniqueConstraintViolationException;

test('can create a category with a translatable name', function () {
    $category = Category::create([
        'slug' => 'basic-lessons',
        'name' => ['es' => 'Clases básicas para principiantes', 'en' => 'Basic lessons for beginners'],
        'description' => ['es' => 'Lecciones de introducción al árabe'],
    ]);

    expect($category->id)->toBeString()
        ->and(strlen($category->id))->toBe(36);

    app()->setLocale('es');
    expect($category->name)->toBe('Clases básicas para principiantes');

    app()->setLocale('en');
    expect($category->name)->toBe('Basic lessons for beginners');
});

test('can create a tag with a translatable name', function () {
    $tag = Tag::create([
        'slug' => 'alfabeto',
        'name' => ['es' => 'Alfabeto', 'en' => 'Alphabet'],
    ]);

    expect($tag->id)->toBeString()
        ->and(strlen($tag->id))->toBe(36);

    app()->setLocale('es');
    expect($tag->name)->toBe('Alfabeto');
});

test('a post can belong to categories and tags', function () {
    $post = Post::create([
        'slug' => 'categorized-post',
        'title' => ['en' => 'Categorized'],
        'content_type' => ContentType::HTML,
        'publish_status' => 'ready',
    ]);

    $category = Category::create([
        'slug' => 'tips',
        'name' => ['es' => 'Consejos para aprender árabe'],
    ]);

    $tags = collect([
        ['slug' => 'vocabulario', 'name' => ['es' => 'Vocabulario']],
        ['slug' => 'pronunciacion', 'name' => ['es' => 'Pronunciación']],
    ])->map(fn (array $attributes) => Tag::create($attributes));

    $post->categories()->attach($category);
    $post->tags()->attach($tags->pluck('id'));

    expect($post->categories()->count())->toBe(1)
        ->and($post->categories()->first()->slug)->toBe('tips')
        ->and($post->tags()->count())->toBe(2)
        ->and($post->tags()->pluck('slug')->all())->toBe(['pronunciacion', 'vocabulario'])
        ->and($category->posts()->count())->toBe(1)
        ->and($category->posts()->first()->slug)->toBe('categorized-post');
});

test('the composite primary key prevents duplicate taxonomy attachments', function () {
    $post = Post::create([
        'slug' => 'pivoted-post',
        'title' => ['en' => 'Pivoted'],
        'content_type' => ContentType::HTML,
    ]);

    $category = Category::create([
        'slug' => 'culture',
        'name' => ['es' => 'Cultura árabe'],
    ]);

    $post->categories()->attach($category);

    // Raw double-attach violates the composite PK; the idempotent API is
    // syncWithoutDetaching.
    $post->categories()->attach($category);
})->throws(UniqueConstraintViolationException::class);

test('syncWithoutDetaching is idempotent', function () {
    $post = Post::create([
        'slug' => 'synced-post',
        'title' => ['en' => 'Synced'],
        'content_type' => ContentType::HTML,
    ]);

    $category = Category::create([
        'slug' => 'culture',
        'name' => ['es' => 'Cultura árabe'],
    ]);

    $post->categories()->syncWithoutDetaching([$category->id]);
    $post->categories()->syncWithoutDetaching([$category->id]);

    expect($post->categories()->count())->toBe(1);
});

test('detaching taxonomy clears the relation without touching the post', function () {
    $post = Post::create([
        'slug' => 'detached-post',
        'title' => ['en' => 'Detached'],
        'content_type' => ContentType::HTML,
    ]);

    $tag = Tag::create([
        'slug' => 'gramatica',
        'name' => ['es' => 'Gramática'],
    ]);

    $post->tags()->attach($tag);
    $post->tags()->detach($tag);

    expect($post->tags()->count())->toBe(0)
        ->and($post->fresh()->slug)->toBe('detached-post')
        ->and(Tag::count())->toBe(1);
});

test('soft delete keeps taxonomy pivots; force delete cascades them', function () {
    $post = Post::create([
        'slug' => 'doomed-post',
        'title' => ['en' => 'Doomed'],
        'content_type' => ContentType::HTML,
    ]);

    $category = Category::create([
        'slug' => 'comunicacion',
        'name' => ['es' => 'Comunicación básica'],
    ]);

    $post->categories()->attach($category);

    // Soft delete keeps the pivot: the post remains recoverable.
    $post->delete();
    expect(app('db')->table('content_post_categories')->count())->toBe(1)
        ->and(Category::count())->toBe(1);

    // Hard delete fires the FK cascade but leaves taxonomy rows intact.
    $post->forceDelete();
    expect(app('db')->table('content_post_categories')->count())->toBe(0)
        ->and(Category::count())->toBe(1);
});
