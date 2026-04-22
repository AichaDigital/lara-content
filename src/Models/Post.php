<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Models;

use AichaDigital\LaraContent\Concerns\HasTranslatableContent;
use AichaDigital\LaraContent\Concerns\HasUuid;
use AichaDigital\LaraContent\Contracts\ContentAuthorContract;
use AichaDigital\LaraContent\Enums\ContentType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Post model for blog posts and articles.
 *
 * @property string $id
 * @property string $slug
 * @property array $title
 * @property array|null $excerpt
 * @property array|null $content
 * @property string|null $featured_image
 * @property int|string|null $author_id
 * @property ContentType $content_type
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read ContentAuthorContract|null $author
 */
class Post extends Model
{
    use HasTranslatableContent;
    use HasUuid;
    use SoftDeletes;

    protected $table = 'content_posts';

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'featured_image',
        'author_id',
        'content_type',
        'is_published',
        'published_at',
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
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content_type' => ContentType::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
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
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
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
     */
    public function getReadingTimeAttribute(): int
    {
        $content = $this->getTranslatedContent('content') ?? '';
        $wordCount = str_word_count(strip_tags($content));
        $wordsPerMinute = 200;

        return max(1, (int) ceil($wordCount / $wordsPerMinute));
    }
}
