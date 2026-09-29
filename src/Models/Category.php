<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Models;

use AichaDigital\LaraContent\Concerns\HasTranslatableContent;
use AichaDigital\LaraContent\Concerns\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Category model for post taxonomy.
 *
 * @property string $id
 * @property string $slug
 * @property array<string, string> $name
 * @property array<string, string>|null $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Post> $posts
 */
class Category extends Model
{
    use HasTranslatableContent;
    use HasUuid;

    protected $table = 'content_categories';

    protected $fillable = [
        'slug',
        'name',
        'description',
    ];

    /**
     * Translatable attributes.
     *
     * @var array<string>
     */
    public array $translatable = [
        'name',
        'description',
    ];

    /**
     * Get the posts that belong to this category.
     *
     * @return BelongsToMany<Post, $this>
     */
    public function posts(): BelongsToMany
    {
        /** @var class-string<Post> $postModel */
        $postModel = config('content.models.post', Post::class);

        return $this->belongsToMany(
            $postModel,
            'content_post_categories',
            'category_id',
            'post_id'
        );
    }

    /**
     * Get the route key name for model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
