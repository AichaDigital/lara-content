<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Enums\ContentType;
use AichaDigital\LaraContent\Models\Post;

test('reading time estimates minutes from the translated content word count', function () {
    $post = Post::create([
        'slug' => 'reading-time',
        'title' => ['en' => 'Reading time'],
        'content' => ['en' => '<p>'.str_repeat('word ', 400).'</p>'],
        'content_type' => ContentType::HTML,
        'is_published' => true,
    ]);

    // 400 words at 200 wpm => 2 minutes, HTML tags stripped before counting.
    expect($post->reading_time)->toBe(2);
});

test('reading time is at least one minute for empty content', function () {
    $post = Post::create([
        'slug' => 'empty-content',
        'title' => ['en' => 'Empty'],
        'content' => ['en' => ''],
        'content_type' => ContentType::HTML,
        'is_published' => true,
    ]);

    expect($post->reading_time)->toBe(1);
});
