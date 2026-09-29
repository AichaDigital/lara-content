<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Enums\ContentType;
use AichaDigital\LaraContent\Services\ContentSanitizer;

beforeEach(function () {
    $this->sanitizer = new ContentSanitizer;
});

test('sanitizes html content', function () {
    $html = '<p>Hello <strong>World</strong></p>';

    $result = $this->sanitizer->sanitizeHtml($html);

    expect($result)->toContain('<p>')
        ->and($result)->toContain('<strong>');
});

test('removes script tags', function () {
    $html = '<p>Hello</p><script>alert("XSS")</script>';

    $result = $this->sanitizer->sanitizeHtml($html);

    expect($result)->not->toContain('<script>');
});

test('converts markdown to html', function () {
    $markdown = '# Title

This is a **paragraph**.';

    $result = $this->sanitizer->sanitizeMarkdown($markdown);

    expect($result)->toContain('<h1>')
        ->and($result)->toContain('<strong>');
});

test('converts GFM tables to html (the GFM extension must be active)', function () {
    // Regression guard: CommonMarkConverter silently discards a custom
    // environment passed as its second constructor argument, which used to
    // deactivate the GFM extension (tables) in this sanitizer.
    $markdown = <<<'MD'
        | A | B |
        | --- | --- |
        | 1 | 2 |
        MD;

    $result = $this->sanitizer->sanitizeMarkdown($markdown);

    expect($result)->toContain('<table>')
        ->and($result)->toContain('<th>A</th>')
        ->and($result)->toContain('<td>1</td>');
});

test('sanitize method handles content type', function () {
    $html = '<p>Test</p>';
    $markdown = '**Bold**';

    $htmlResult = $this->sanitizer->sanitize($html, ContentType::HTML);
    $markdownResult = $this->sanitizer->sanitize($markdown, ContentType::MARKDOWN);

    expect($htmlResult)->toContain('<p>')
        ->and($markdownResult)->toContain('<strong>');
});

test('strips tags from content', function () {
    $html = '<p>Hello <strong>World</strong></p>';

    $result = $this->sanitizer->stripTags($html);

    expect($result)->toBe('Hello World');
});

test('truncates content', function () {
    $content = '<p>This is a long paragraph that should be truncated.</p>';

    $result = $this->sanitizer->truncate($content, 20);

    expect(strlen($result))->toBeLessThanOrEqual(23); // 20 + 3 for '...'
});

test('handles empty content', function () {
    expect($this->sanitizer->sanitizeHtml(''))->toBe('')
        ->and($this->sanitizer->sanitizeMarkdown(''))->toBe('');
});
