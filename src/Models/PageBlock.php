<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Models;

use AichaDigital\LaraContent\Concerns\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PageBlock model for block instances within pages.
 *
 * @property string $id
 * @property string $page_id
 * @property string $block_type_slug
 * @property string $zone
 * @property int $position
 * @property array $config
 * @property int|null $cache_ttl
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Page $page
 */
class PageBlock extends Model
{
    use HasUuid;

    protected $table = 'content_page_blocks';

    protected $fillable = [
        'page_id',
        'block_type_slug',
        'zone',
        'position',
        'config',
        'cache_ttl',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'position' => 'integer',
            'cache_ttl' => 'integer',
        ];
    }

    /**
     * Get the page this block belongs to.
     *
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the effective cache TTL (block-specific or default).
     */
    public function getEffectiveCacheTtl(): int
    {
        return $this->cache_ttl ?? config('content.cache.default_ttl', 3600);
    }

    /**
     * Check if caching is enabled for this block.
     */
    public function isCacheEnabled(): bool
    {
        return config('content.cache.enabled', true) && $this->cache_ttl !== 0;
    }

    /**
     * Get a config value with default.
     */
    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Scope to filter by zone.
     *
     * @param  Builder<PageBlock>  $query
     * @return Builder<PageBlock>
     */
    public function scopeInZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }

    /**
     * Scope to order by position.
     *
     * @param  Builder<PageBlock>  $query
     * @return Builder<PageBlock>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
