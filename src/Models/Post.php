<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Models;

use AichaDigital\LaraContent\Concerns\HasTranslatableContent;
use AichaDigital\LaraContent\Concerns\HasUuid;
use AichaDigital\LaraContent\Contracts\ContentAuthorContract;
use AichaDigital\LaraContent\Enums\ContentType;
use AichaDigital\LaraContent\Enums\PublishStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Post model for blog posts and articles.
 *
 * @property string $id
 * @property string $slug
 * @property array $title
 * @property array|null $excerpt
 * @property array|null $content
 * @property array<string, string>|null $meta_title
 * @property array<string, string>|null $meta_description
 * @property string|null $featured_image
 * @property array<string, string>|null $featured_image_alt
 * @property string|null $focus_keyword
 * @property array<int, string>|null $secondary_keywords
 * @property array<int, string>|null $internal_notes
 * @property string|null $author_id
 * @property ContentType $content_type
 * @property PublishStatus $publish_status
 * @property Carbon|null $published_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read ContentAuthorContract|null $author
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, Tag> $tags
 * @property-read int $reading_time
 */
class Post extends Model
{
    use HasTranslatableContent;
    use HasUuid;
    use SoftDeletes;

    protected $table = 'content_posts';

    protected $attributes = [
        'publish_status' => 'draft',
    ];

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'featured_image',
        'featured_image_alt',
        'focus_keyword',
        'secondary_keywords',
        'internal_notes',
        'author_id',
        'content_type',
        'publish_status',
        'published_at',
    ];

    /**
     * Attributes that carry editorial-only data. They must never be rendered
     * or exposed by any public API surface (corpus rule: notas_internas is
     * never rendered).
     *
     * @var array<int, string>
     */
    public const INTERNAL_ATTRIBUTES = [
        'focus_keyword',
        'secondary_keywords',
        'internal_notes',
    ];

    /**
     * Translatable attributes.
     *
     * @var array<string>
     */
    public array $translatable = [
        'title',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'featured_image_alt',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content_type' => ContentType::class,
            'publish_status' => PublishStatus::class,
            'secondary_keywords' => 'array',
            'internal_notes' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the attributes that are safe for public rendering/API exposure.
     *
     * @return array<int, string>
     */
    public function publicAttributes(): array
    {
        return array_values(array_diff($this->getFillable(), self::INTERNAL_ATTRIBUTES));
    }

    /**
     * Get the author of this post.
     *
     * @return BelongsTo<Model&ContentAuthorContract, $this>
     */
    public function author(): BelongsTo
    {
        $authorModel = config('content.author_model', 'App\\Models\\User');

        return $this->belongsTo($authorModel, 'author_id');
    }

    /**
     * Scope to only published posts.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished($query)
    {
        return $query->where('publish_status', PublishStatus::PUBLISHED->value)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Get the categories attached to this post.
     *
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        /** @var class-string<Category> $categoryModel */
        $categoryModel = config('content.models.category', Category::class);

        return $this->belongsToMany(
            $categoryModel,
            'content_post_categories',
            'post_id',
            'category_id'
        )->orderBy('slug');
    }

    /**
     * Get the tags attached to this post.
     *
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        /** @var class-string<Tag> $tagModel */
        $tagModel = config('content.models.tag', Tag::class);

        return $this->belongsToMany(
            $tagModel,
            'content_post_tags',
            'post_id',
            'tag_id'
        )->orderBy('slug');
    }

    /**
     * Scope to order by most recent.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeRecent($query)
    {
        return $query->orderByDesc('published_at')->orderByDesc('created_at');
    }

    /**
     * Scope to find by slug.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Get the route key name for model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Check if the post has a featured image.
     */
    public function hasFeaturedImage(): bool
    {
        return ! empty($this->featured_image);
    }

    /**
     * Get the reading time estimate in minutes.
     *
     * @return Attribute<int, never>
     */
    protected function readingTime(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                $content = $this->getTranslatedContent('content') ?? '';
                $wordCount = str_word_count(strip_tags($content));
                $wordsPerMinute = 200;

                return max(1, (int) ceil($wordCount / $wordsPerMinute));
            },
        );
    }
}
