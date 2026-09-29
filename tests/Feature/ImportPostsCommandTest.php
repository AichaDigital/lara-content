<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Enums\ContentType;
use AichaDigital\LaraContent\Enums\PublishStatus;
use AichaDigital\LaraContent\Models\Category;
use AichaDigital\LaraContent\Models\Post;
use AichaDigital\LaraContent\Models\Tag;
use Illuminate\Support\Facades\Artisan;

function importFixture(string $dir, string $filename, string $contents): string
{
    file_put_contents($dir."/{$filename}", $contents);

    return $dir;
}

beforeEach(function () {
    // The consumer pins the corpus language in its own config; the package
    // default falls back to app.locale.
    config(['content.import.locale' => 'es']);

    $this->dir = sys_get_temp_dir().'/lara-content-import-'.uniqid();
    mkdir($this->dir);
});

afterEach(function () {
    array_map('unlink', glob($this->dir.'/*.md') ?: []);
    @rmdir($this->dir);
});

test('imports a markdown post with converted html and mapped fields', function () {
    importFixture($this->dir, 'sample-post.md', <<<'MD'
---
titulo: "Sample post"
slug: sample-post
meta_title: "A short meta title"
meta_description: "A meta description within limits"
extracto: "Short intro"
categoria: "Tips"
etiquetas: ["grammar", "vocabulary"]
estado: "revisado"
fecha_publicacion: "2024-12-25"
notas_internas:
  - "Internal review note"
---

# Sample post

This is the **body** with a table.

| A | B |
|---|---|
| 1 | 2 |
MD);

    Artisan::call('content:import-posts', ['path' => $this->dir]);

    $post = Post::query()->where('slug', 'sample-post')->firstOrFail();

    expect($post->content_type)->toBe(ContentType::HTML)
        ->and($post->publish_status)->toBe(PublishStatus::READY)
        ->and($post->published_at->toDateString())->toBe('2024-12-25')
        ->and($post->getTranslations('title'))->toBe(['es' => 'Sample post'])
        ->and($post->getTranslations('meta_title')['es'])->toBe('A short meta title')
        ->and($post->internal_notes)->toBe(['Internal review note'])
        ->and($post->getTranslations('content')['es'])->toContain('<table>')
        ->and($post->getTranslations('content')['es'])->toContain('<strong>body</strong>')
        ->and($post->getTranslations('content')['es'])->not->toContain('# Sample post')
        ->and($post->categories()->count())->toBe(1)
        ->and($post->categories()->first()->slug)->toBe('tips')
        ->and($post->tags()->pluck('slug')->sort()->values()->all())->toBe(['grammar', 'vocabulary']);
});

test('import is idempotent: running twice does not duplicate posts or taxonomy', function () {
    importFixture($this->dir, 'idempotent.md', <<<'MD'
---
titulo: "Idempotent"
slug: idempotent
estado: "listo"
---

Body of the post.
MD);

    Artisan::call('content:import-posts', ['path' => $this->dir]);
    Artisan::call('content:import-posts', ['path' => $this->dir]);

    expect(Post::query()->where('slug', 'idempotent')->count())->toBe(1)
        ->and(Tag::count())->toBe(0)
        ->and(Category::count())->toBe(0);
});

test('files without a slug frontmatter key are skipped', function () {
    importFixture($this->dir, 'not-a-post.md', <<<'MD'
---
nombre: "Email sequence"
disparador: "kit download"
---

Email content.
MD);

    $exit = Artisan::call('content:import-posts', ['path' => $this->dir]);

    expect($exit)->toBe(0)
        ->and(Post::count())->toBe(0);
});

test('frontmatter over the meta limits fails the import of that file', function () {
    importFixture($this->dir, 'too-long.md', <<<'MD'
---
titulo: "Too long"
slug: too-long
meta_title: "This meta title is definitely much longer than sixty characters limit"
estado: "listo"
---

Body.
MD);

    $exit = Artisan::call('content:import-posts', ['path' => $this->dir]);

    expect($exit)->toBe(1)
        ->and(Post::count())->toBe(0);
});

test('dry run validates without writing anything', function () {
    importFixture($this->dir, 'dry.md', <<<'MD'
---
titulo: "Dry run"
slug: dry-run
estado: "listo"
categoria: "Culture"
---

Body.
MD);

    $exit = Artisan::call('content:import-posts', ['path' => $this->dir, '--dry-run' => true]);

    expect($exit)->toBe(0)
        ->and(Post::count())->toBe(0)
        ->and(Category::count())->toBe(0);
});

test('unknown editorial states fail loudly', function () {
    importFixture($this->dir, 'weird-state.md', <<<'MD'
---
titulo: "Weird"
slug: weird-state
estado: "misterioso"
---

Body.
MD);

    $exit = Artisan::call('content:import-posts', ['path' => $this->dir]);
    $output = Artisan::output();

    expect($exit)->toBe(1)
        ->and($output)->toContain('misterioso')
        ->and(Post::count())->toBe(0);
});

test('author option is assigned to imported posts', function () {
    importFixture($this->dir, 'authored.md', <<<'MD'
---
titulo: "Authored"
slug: authored
estado: "listo"
---

Body.
MD);

    $authorId = '00000000-0000-7000-8000-000000000001';
    Artisan::call('content:import-posts', ['path' => $this->dir, '--author' => $authorId]);

    expect(Post::query()->where('slug', 'authored')->firstOrFail()->author_id)->toBe($authorId);
});

test('a missing directory fails with a clear message', function () {
    $exit = Artisan::call('content:import-posts', ['path' => '/nonexistent/dir']);

    expect($exit)->toBe(1)
        ->and(Artisan::output())->toContain('not found');
});
