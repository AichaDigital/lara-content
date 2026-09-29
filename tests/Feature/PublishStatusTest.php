<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Enums\ContentType;
use AichaDigital\LaraContent\Enums\PublishStatus;
use AichaDigital\LaraContent\Models\Post;

test('posts default to draft status', function () {
    $post = Post::create([
        'slug' => 'default-status',
        'title' => ['en' => 'Default status'],
        'content_type' => ContentType::HTML,
    ]);

    expect($post->publish_status)->toBe(PublishStatus::DRAFT);
});

test('publish status is cast to the enum', function () {
    $post = Post::create([
        'slug' => 'cast-status',
        'title' => ['en' => 'Cast status'],
        'content_type' => ContentType::HTML,
        'publish_status' => 'ready',
    ]);

    expect($post->publish_status)->toBeInstanceOf(PublishStatus::class)
        ->and($post->publish_status)->toBe(PublishStatus::READY);
});

test('published scope returns only published posts', function () {
    Post::create([
        'slug' => 'published-post',
        'title' => ['en' => 'Published'],
        'content_type' => ContentType::HTML,
        'publish_status' => PublishStatus::PUBLISHED,
    ]);

    foreach (['draft', 'review', 'ready', 'archived'] as $status) {
        Post::create([
            'slug' => "post-$status",
            'title' => ['en' => ucfirst($status)],
            'content_type' => ContentType::HTML,
            'publish_status' => $status,
        ]);
    }

    $published = Post::published()->get();

    expect($published)->toHaveCount(1)
        ->and($published->first()->slug)->toBe('published-post');
});

test('published scope excludes scheduled posts with a future publish date', function () {
    Post::create([
        'slug' => 'scheduled-post',
        'title' => ['en' => 'Scheduled'],
        'content_type' => ContentType::HTML,
        'publish_status' => PublishStatus::PUBLISHED,
        'published_at' => now()->addDay(),
    ]);

    Post::create([
        'slug' => 'live-post',
        'title' => ['en' => 'Live'],
        'content_type' => ContentType::HTML,
        'publish_status' => PublishStatus::PUBLISHED,
        'published_at' => now()->subDay(),
    ]);

    Post::create([
        'slug' => 'immediate-post',
        'title' => ['en' => 'Immediate'],
        'content_type' => ContentType::HTML,
        'publish_status' => PublishStatus::PUBLISHED,
    ]);

    $published = Post::published()->get();

    expect($published->pluck('slug')->sort()->values()->all())
        ->toBe(['immediate-post', 'live-post']);
});
