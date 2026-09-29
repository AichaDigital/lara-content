<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Console\Commands;

use AichaDigital\LaraContent\Enums\ContentType;
use AichaDigital\LaraContent\Models\Category;
use AichaDigital\LaraContent\Models\Post;
use AichaDigital\LaraContent\Models\Tag;
use AichaDigital\LaraContent\Services\ContentSanitizer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

/**
 * Imports markdown posts with YAML frontmatter into content_posts.
 *
 * The command is consumer-agnostic: frontmatter keys are mapped to post
 * attributes through config('content.import.field_map'), editorial states
 * through config('content.import.status_map'). Markdown bodies are converted
 * to sanitized HTML at import time (content_type = html). Upsert is
 * idempotent by slug; files without a `slug` frontmatter key are skipped.
 */
class ImportPostsCommand extends Command
{
    protected $signature = 'content:import-posts
        {path : Directory containing the markdown files}
        {--author= : Author UUID assigned to every imported post}
        {--dry-run : Validate and report without writing anything}';

    protected $description = 'Import markdown posts with YAML frontmatter (idempotent upsert by slug)';

    public function __construct(protected ContentSanitizer $sanitizer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $directory = realpath($this->argument('path'));

        if ($directory === false || ! is_dir($directory)) {
            $this->error("Import directory not found: {$this->argument('path')}");

            return self::FAILURE;
        }

        $fieldMap = (array) config('content.import.field_map', []);
        $statusMap = (array) config('content.import.status_map', []);
        $locale = (string) (config('content.import.locale') ?? config('app.locale', 'en'));
        $dryRun = (bool) $this->option('dry-run');

        $created = $updated = $skipped = 0;
        /** @var array<int, string> $errors */
        $errors = [];

        foreach (glob($directory.'/*.md') ?: [] as $file) {
            $frontmatter = $this->parseFrontmatter((string) file_get_contents($file));

            if ($frontmatter === null || ! isset($frontmatter[$fieldMap['slug'] ?? 'slug'])) {
                $skipped++;
                $this->line("skip (no slug): {$file}");

                continue;
            }

            $attributes = $this->mapFrontmatter($frontmatter, $fieldMap, $statusMap, $locale, $file, $errors);

            if ($attributes === null) {
                continue;
            }

            $attributes = $this->convertContent($attributes, $errors, $file);

            if ($dryRun) {
                $created++;
                $this->line("ok (dry-run): {$attributes['slug']}");

                continue;
            }

            [$post, $isNew] = $this->upsert($attributes);
            $isNew ? $created++ : $updated++;
            $this->attachTaxonomy($post, $attributes, $locale);
        }

        $this->table(
            ['created', 'updated', 'skipped', 'errors'],
            [[$created, $updated, $skipped, count($errors)]]
        );

        foreach ($errors as $error) {
            $this->error($error);
        }

        return $errors === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Extract and parse the YAML frontmatter block, or null when absent.
     *
     * @return array<string, mixed>|null
     */
    protected function parseFrontmatter(string $contents): ?array
    {
        if (! preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $contents, $matches)) {
            return null;
        }

        try {
            $parsed = Yaml::parse($matches[1]);
        } catch (\Throwable) {
            return null;
        }

        return is_array($parsed) ? $parsed : null;
    }

    /**
     * Map frontmatter keys to post attributes following the field map.
     *
     * @param  array<string, mixed>  $frontmatter
     * @param  array<string, string>  $fieldMap
     * @param  array<string, string>  $statusMap
     * @param  array<int, string>  $errors
     * @return array<string, mixed>|null
     */
    protected function mapFrontmatter(
        array $frontmatter,
        array $fieldMap,
        array $statusMap,
        string $locale,
        string $file,
        array &$errors
    ): ?array {
        $attributes = [];
        $maxTitle = (int) config('content.import.max_meta_title', 60);
        $maxDescription = (int) config('content.import.max_meta_description', 155);

        foreach ($fieldMap as $sourceKey => $attribute) {
            if (! array_key_exists($sourceKey, $frontmatter) || $frontmatter[$sourceKey] === '' || $frontmatter[$sourceKey] === null) {
                continue;
            }

            $value = $frontmatter[$sourceKey];

            if (in_array($attribute, ['categories', 'tags'], true)) {
                $attributes[$attribute] = is_array($value) ? $value : [$value];

                continue;
            }

            if ($attribute === 'publish_status') {
                $status = $statusMap[(string) $value] ?? null;

                if ($status === null) {
                    $errors[] = "unknown estado '{$value}' in {$file}";

                    return null;
                }

                $attributes[$attribute] = $status;

                continue;
            }

            if ($attribute === 'published_at') {
                $attributes[$attribute] = Carbon::parse((string) $value);

                continue;
            }

            // Translatable attributes store single-language frontmatter
            // values keyed by the import locale.
            if (in_array($attribute, ['title', 'excerpt', 'meta_title', 'meta_description', 'featured_image_alt'], true)) {
                $text = (string) $value;

                if ($attribute === 'meta_title' && mb_strlen($text) > $maxTitle) {
                    $errors[] = "meta_title longer than {$maxTitle} chars in {$file}";

                    return null;
                }

                if ($attribute === 'meta_description' && mb_strlen($text) > $maxDescription) {
                    $errors[] = "meta_description longer than {$maxDescription} chars in {$file}";

                    return null;
                }

                $attributes[$attribute] = [$locale => $text];

                continue;
            }

            $attributes[$attribute] = $value;
        }

        return $attributes;
    }

    /**
     * Convert the markdown body to sanitized HTML (content_type = html).
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<int, string>  $errors
     * @return array<string, mixed>
     */
    protected function convertContent(array $attributes, array &$errors, string $file): array
    {
        $body = preg_replace('/^---\s*\n(.*?)\n---\s*\n/s', '', (string) file_get_contents($file), 1) ?? '';

        // Consumers render headings from their own templates; the body's
        // leading H1 duplicates the title and is stripped at import.
        $body = preg_replace('/^#\s+.+\n/', '', ltrim($body), 1) ?? $body;

        $attributes['content'] = [$this->importLocale() => $this->sanitizer->sanitizeMarkdown($body)];
        $attributes['content_type'] = ContentType::HTML->value;

        return $attributes;
    }

    protected function importLocale(): string
    {
        return (string) (config('content.import.locale') ?? config('app.locale', 'en'));
    }

    /**
     * Idempotent upsert by slug (includes trashed rows).
     *
     * @param  array<string, mixed>  $attributes
     * @return array{0: Post, 1: bool}
     */
    protected function upsert(array $attributes): array
    {
        /** @var Post $post */
        $post = Post::withTrashed()->firstOrNew(['slug' => $attributes['slug']]);
        $isNew = ! $post->exists;

        $post->fill($attributes);

        if ($author = $this->option('author')) {
            $post->author_id = $author;
        }

        $post->save();

        return [$post, $isNew];
    }

    /**
     * Find-or-create categories/tags by slug and sync them on the post.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function attachTaxonomy(Post $post, array $attributes, string $locale): void
    {
        if (isset($attributes['categories'])) {
            $post->categories()->sync($this->resolveTaxonomy(
                $attributes['categories'],
                (string) config('content.models.category'),
                $locale
            ));
        }

        if (isset($attributes['tags'])) {
            $post->tags()->sync($this->resolveTaxonomy(
                $attributes['tags'],
                (string) config('content.models.tag'),
                $locale
            ));
        }
    }

    /**
     * @param  array<int, string>  $names
     * @param  class-string<Category|Tag>  $model
     * @return array<int, string>
     */
    protected function resolveTaxonomy(array $names, string $model, string $locale): array
    {
        $ids = [];

        foreach ($names as $name) {
            $slug = Str::slug((string) $name);

            /** @var Category|Tag $row */
            $row = $model::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => [$locale => (string) $name]]
            );

            $ids[] = $row->id;
        }

        return $ids;
    }
}
