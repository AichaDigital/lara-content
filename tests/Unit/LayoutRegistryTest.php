<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Exceptions\DuplicateLayoutSlugException;
use AichaDigital\LaraContent\Exceptions\LayoutNotFoundException;
use AichaDigital\LaraContent\Layouts\SingleLayout;
use AichaDigital\LaraContent\Registries\LayoutRegistry;

beforeEach(function () {
    $this->registry = new LayoutRegistry;
});

test('can register a layout', function () {
    $layout = new SingleLayout;

    $this->registry->register($layout);

    expect($this->registry->has('single'))->toBeTrue();
});

test('can get a registered layout', function () {
    $layout = new SingleLayout;
    $this->registry->register($layout);

    $retrieved = $this->registry->get('single');

    expect($retrieved)->toBe($layout);
});

test('throws exception when getting non-existent layout', function () {
    $this->registry->get('non-existent');
})->throws(LayoutNotFoundException::class);

test('throws exception when registering duplicate slug', function () {
    $layout = new SingleLayout;
    $this->registry->register($layout);
    $this->registry->register($layout);
})->throws(DuplicateLayoutSlugException::class);

test('can get all registered layouts', function () {
    $layout = new SingleLayout;
    $this->registry->register($layout);

    $all = $this->registry->all();

    expect($all)->toHaveCount(1)
        ->and($all['single'])->toBe($layout);
});

test('can get layout options', function () {
    $layout = new SingleLayout;
    $this->registry->register($layout);

    $options = $this->registry->options();

    expect($options)->toHaveKey('single');
});

test('can forget a layout', function () {
    $layout = new SingleLayout;
    $this->registry->register($layout);

    $this->registry->forget('single');

    expect($this->registry->has('single'))->toBeFalse();
});
