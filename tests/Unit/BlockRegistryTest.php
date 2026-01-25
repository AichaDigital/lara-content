<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Blocks\HtmlBlock;
use AichaDigital\LaraContent\Exceptions\BlockNotFoundException;
use AichaDigital\LaraContent\Exceptions\DuplicateBlockSlugException;
use AichaDigital\LaraContent\Registries\BlockRegistry;
use AichaDigital\LaraContent\Services\ContentSanitizer;

beforeEach(function () {
    $this->registry = new BlockRegistry;
    $this->sanitizer = new ContentSanitizer;
});

test('can register a block', function () {
    $block = new HtmlBlock($this->sanitizer);

    $this->registry->register($block);

    expect($this->registry->has('html'))->toBeTrue();
});

test('can get a registered block', function () {
    $block = new HtmlBlock($this->sanitizer);
    $this->registry->register($block);

    $retrieved = $this->registry->get('html');

    expect($retrieved)->toBe($block);
});

test('throws exception when getting non-existent block', function () {
    $this->registry->get('non-existent');
})->throws(BlockNotFoundException::class);

test('throws exception when registering duplicate slug', function () {
    $block = new HtmlBlock($this->sanitizer);
    $this->registry->register($block);
    $this->registry->register($block);
})->throws(DuplicateBlockSlugException::class);

test('can get blocks for a specific zone', function () {
    $block = new HtmlBlock($this->sanitizer);
    $this->registry->register($block);

    // HtmlBlock allows all zones (*)
    $forMain = $this->registry->forZone('main');

    expect($forMain)->toHaveCount(1);
});

test('can get all registered blocks', function () {
    $block = new HtmlBlock($this->sanitizer);
    $this->registry->register($block);

    $all = $this->registry->all();

    expect($all)->toHaveCount(1)
        ->and($all['html'])->toBe($block);
});
