<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Models;

use AichaDigital\LaraContent\Concerns\HasTranslatableContent;
use AichaDigital\LaraContent\Concerns\HasUuid;
use AichaDigital\LaraContent\Enums\ContentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Page model for static content pages.
 *
 * @property string $id
 * @property string $slug
 * @property array $title
 * @property array|null $meta_description
 * @property string $layout_slug
 * @property ContentType $content_type
 * @property bool $is_published
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<PageBlock> $blocks
 */
class Page extends Model
{
    use HasTranslatableContent;
    use HasUuid;
    use SoftDeletes;

    protected $table = 'content_pages';

    protected $fillable = [
        'slug',
        'title',
        'meta_description',
        'layout_slug',
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
        'meta_description',
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
     * Get the blocks associated with this page.
     *
     * @return HasMany<PageBlock, $this>
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('zone')->orderBy('position');
    }

    /**
     * Get blocks for a specific zone.
     *
     * @return HasMany<PageBlock, $this>
     */
    public function blocksInZone(string $zone): HasMany
    {
        return $this->blocks()->where('zone', $zone);
    }

    /**
     * Scope to only published pages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Page>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Page>
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
     * Scope to find by slug.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Page>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Page>
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
}
