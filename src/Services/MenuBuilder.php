<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Services;

use AichaDigital\LaraContent\Models\Menu;
use Illuminate\Support\Facades\Cache;

/**
 * Menu building service.
 *
 * Handles menu retrieval and caching.
 */
class MenuBuilder
{
    /**
     * Build/retrieve a menu by slug.
     */
    public function build(string $slug): ?Menu
    {
        if (! config('content.cache.enabled', true)) {
            return $this->loadMenu($slug);
        }

        $cacheKey = $this->getCacheKey($slug);
        $ttl = config('content.cache.default_ttl', 3600);

        return Cache::remember($cacheKey, $ttl, fn () => $this->loadMenu($slug));
    }

    /**
     * Load a menu from the database.
     */
    protected function loadMenu(string $slug): ?Menu
    {
        return Menu::bySlug($slug)
            ->with(['rootItems' => function ($query): void {
                $query->where('is_active', true)
                    ->orderBy('position')
                    ->with(['children' => function ($q): void {
                        $q->where('is_active', true)->orderBy('position');
                    }]);
            }])
            ->first();
    }

    /**
     * Get the cache key for a menu.
     */
    protected function getCacheKey(string $slug): string
    {
        $prefix = config('content.cache.prefix', 'content');
        $locale = app()->getLocale();

        return "{$prefix}:menu:{$slug}:{$locale}";
    }

    /**
     * Clear cache for a specific menu.
     */
    public function clearCache(string $slug): void
    {
        $locales = config('app.available_locales', [config('app.locale')]);
        $prefix = config('content.cache.prefix', 'content');

        foreach ($locales as $locale) {
            $cacheKey = "{$prefix}:menu:{$slug}:{$locale}";
            Cache::forget($cacheKey);
        }
    }

    /**
     * Clear cache for a menu model.
     */
    public function clearCacheForMenu(Menu $menu): void
    {
        $this->clearCache($menu->slug);
    }

    /**
     * Clear all menu caches.
     */
    public function clearAllCache(): void
    {
        $menus = Menu::pluck('slug');

        foreach ($menus as $slug) {
            $this->clearCache($slug);
        }
    }

    /**
     * Get all available menus.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Menu>
     */
    public function all()
    {
        return Menu::all();
    }

    /**
     * Get menus as options for select inputs.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        return Menu::pluck('name', 'slug')->toArray();
    }
}
