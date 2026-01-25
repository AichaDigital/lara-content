<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent;

use AichaDigital\LaraContent\Blocks\ContactFormBlock;
use AichaDigital\LaraContent\Blocks\HtmlBlock;
use AichaDigital\LaraContent\Blocks\MenuBlock;
use AichaDigital\LaraContent\Blocks\RecentPostsBlock;
use AichaDigital\LaraContent\Layouts\SidebarLeftLayout;
use AichaDigital\LaraContent\Layouts\SidebarRightLayout;
use AichaDigital\LaraContent\Layouts\SingleLayout;
use AichaDigital\LaraContent\Layouts\ThreeColumnLayout;
use AichaDigital\LaraContent\Layouts\TwoColumnLayout;
use AichaDigital\LaraContent\Livewire\ContactForm;
use AichaDigital\LaraContent\Registries\BlockRegistry;
use AichaDigital\LaraContent\Registries\LayoutRegistry;
use AichaDigital\LaraContent\Services\BlockRenderer;
use AichaDigital\LaraContent\Services\ContentSanitizer;
use AichaDigital\LaraContent\Services\MenuBuilder;
use AichaDigital\LaraContent\Services\PageRenderer;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ContentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lara-content')
            ->hasConfigFile('content')
            ->hasViews('content')
            ->hasTranslations()
            ->hasMigrations([
                '2025_01_01_000001_create_content_pages_table',
                '2025_01_01_000002_create_content_page_blocks_table',
                '2025_01_01_000003_create_content_posts_table',
                '2025_01_01_000004_create_content_menus_table',
                '2025_01_01_000005_create_content_menu_items_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->registerSingletons();
        $this->registerServices();
    }

    public function packageBooted(): void
    {
        $this->registerCoreLayouts();
        $this->registerCoreBlocks();
        $this->registerLivewireComponents();
    }

    /**
     * Register singleton services.
     */
    protected function registerSingletons(): void
    {
        $this->app->singleton(LayoutRegistry::class, fn () => new LayoutRegistry);
        $this->app->singleton(BlockRegistry::class, fn () => new BlockRegistry);
        $this->app->singleton(ContentSanitizer::class, fn () => new ContentSanitizer);
        $this->app->singleton(MenuBuilder::class, fn () => new MenuBuilder);
    }

    /**
     * Register services.
     */
    protected function registerServices(): void
    {
        $this->app->singleton(BlockRenderer::class, function ($app) {
            return new BlockRenderer(
                $app->make(BlockRegistry::class)
            );
        });

        $this->app->singleton(PageRenderer::class, function ($app) {
            return new PageRenderer(
                $app->make(LayoutRegistry::class),
                $app->make(BlockRenderer::class)
            );
        });
    }

    /**
     * Register core layouts.
     */
    protected function registerCoreLayouts(): void
    {
        /** @var LayoutRegistry $registry */
        $registry = $this->app->make(LayoutRegistry::class);

        $registry->register(new SingleLayout);
        $registry->register(new SidebarLeftLayout);
        $registry->register(new SidebarRightLayout);
        $registry->register(new TwoColumnLayout);
        $registry->register(new ThreeColumnLayout);
    }

    /**
     * Register core blocks.
     */
    protected function registerCoreBlocks(): void
    {
        /** @var BlockRegistry $registry */
        $registry = $this->app->make(BlockRegistry::class);

        $registry->register($this->app->make(HtmlBlock::class));
        $registry->register(new RecentPostsBlock);
        $registry->register($this->app->make(MenuBlock::class));
        $registry->register(new ContactFormBlock);
    }

    /**
     * Register Livewire components if Livewire is available.
     */
    protected function registerLivewireComponents(): void
    {
        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }

        \Livewire\Livewire::component('content-contact-form', ContactForm::class);
    }
}
