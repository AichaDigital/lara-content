<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Models;

use AichaDigital\LaraContent\Concerns\HasTranslatableContent;
use AichaDigital\LaraContent\Concerns\HasUuid;
use AichaDigital\LaraContent\Enums\MenuItemType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MenuItem model for individual menu entries.
 *
 * @property string $id
 * @property string $menu_id
 * @property string|null $parent_id
 * @property array $label
 * @property MenuItemType $type
 * @property string|null $reference
 * @property int $position
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Menu $menu
 * @property-read MenuItem|null $parent
 * @property-read Collection<MenuItem> $children
 * @property-read Collection<int, MenuItem> $childrenRecursive
 */
class MenuItem extends Model
{
    use HasTranslatableContent;
    use HasUuid;

    protected $table = 'content_menu_items';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'type',
        'reference',
        'position',
        'is_active',
    ];

    /**
     * Translatable attributes.
     *
     * @var array<string>
     */
    public array $translatable = [
        'label',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MenuItemType::class,
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the menu this item belongs to.
     *
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent menu item.
     *
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get child menu items.
     *
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('position');
    }

    /**
     * Get the whole active child subtree in a single eager-loadable relation.
     *
     * Eager-load this on the tree root to render a menu of arbitrary depth
     * without triggering a query per level.
     *
     * @return HasMany<MenuItem, $this>
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Check if this item has active children.
     *
     * Prefers the eager-loaded subtree to stay N+1-safe when rendering a tree.
     */
    public function hasChildren(): bool
    {
        if ($this->relationLoaded('childrenRecursive')) {
            return $this->childrenRecursive->isNotEmpty();
        }

        return $this->children()->exists();
    }

    /**
     * Check if this item points to the current request URL.
     */
    public function isActive(): bool
    {
        return request()->url() === $this->getUrl();
    }

    /**
     * Get the URL for this menu item.
     */
    public function getUrl(): string
    {
        return match ($this->type) {
            MenuItemType::PAGE => $this->getPageUrl(),
            MenuItemType::POST => $this->getPostUrl(),
            MenuItemType::URL => $this->reference ?? '#',
            MenuItemType::ROUTE => $this->getRouteUrl(),
        };
    }

    /**
     * Get URL for a page reference.
     */
    protected function getPageUrl(): string
    {
        if (empty($this->reference)) {
            return '#';
        }

        return route('content.page', ['page' => $this->reference], false);
    }

    /**
     * Get URL for a post reference.
     */
    protected function getPostUrl(): string
    {
        if (empty($this->reference)) {
            return '#';
        }

        return route('content.post', ['post' => $this->reference], false);
    }

    /**
     * Get URL for a route reference.
     */
    protected function getRouteUrl(): string
    {
        if (empty($this->reference) || ! \Route::has($this->reference)) {
            return '#';
        }

        return route($this->reference, [], false);
    }

    /**
     * Scope to filter active items.
     *
     * @param  Builder<MenuItem>  $query
     * @return Builder<MenuItem>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get root items only.
     *
     * @param  Builder<MenuItem>  $query
     * @return Builder<MenuItem>
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to order by position.
     *
     * @param  Builder<MenuItem>  $query
     * @return Builder<MenuItem>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
