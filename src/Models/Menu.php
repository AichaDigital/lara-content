<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Models;

use AichaDigital\LaraContent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Menu model for navigation menus.
 *
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<MenuItem> $items
 * @property-read \Illuminate\Database\Eloquent\Collection<MenuItem> $rootItems
 */
class Menu extends Model
{
    use HasUuid;

    protected $table = 'content_menus';

    protected $fillable = [
        'slug',
        'name',
    ];

    /**
     * Get all menu items.
     *
     * @return HasMany<MenuItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('position');
    }

    /**
     * Get root-level menu items (no parent).
     *
     * @return HasMany<MenuItem, $this>
     */
    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id');
    }

    /**
     * Scope to find by slug.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Menu>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Menu>
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
     * Build the menu tree structure.
     *
     * @return \Illuminate\Support\Collection<int, MenuItem>
     */
    public function getTree()
    {
        return $this->rootItems()
            ->with('children')
            ->where('is_active', true)
            ->get();
    }
}
