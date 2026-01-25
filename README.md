# Lara Content

> **ALPHA VERSION**: This package is in early development. The API may change without notice. Not recommended for production use yet.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aichadigital/lara-content.svg?style=flat-square)](https://packagist.org/packages/aichadigital/lara-content)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/aichadigital/lara-content/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/aichadigital/lara-content/actions?query=workflow%3ACI+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/aichadigital/lara-content.svg?style=flat-square)](https://packagist.org/packages/aichadigital/lara-content)

Content management package for Laravel with pages, posts, blocks and menus. Supports Blade templates with optional Livewire components for interactivity. Includes multilingual support via Spatie Translatable.


## Features

- **Pages**: Flexible page system with customizable layouts and block zones
- **Posts**: Blog/news posts with author attribution and publishing workflow
- **Menus**: Hierarchical menu system with nested items
- **Blocks**: Modular content blocks (HTML, Recent Posts, Menu, Contact Form)
- **Layouts**: Pre-built page layouts (Single, Sidebar Left/Right, Two/Three Column)
- **Multilingual**: Full translation support via spatie/laravel-translatable
- **Extensible**: Register custom layouts and blocks via registries
- **Secure**: HTML sanitization with configurable allowed tags


## Requirements

- PHP 8.3+
- Laravel 12+
- Livewire 3+ (optional, for interactive blocks)


## Installation

Install via composer:


```bash
composer require aichadigital/lara-content
```


Publish and run migrations:


```bash
php artisan vendor:publish --tag="lara-content-migrations"
php artisan migrate
```


Publish the config file:


```bash
php artisan vendor:publish --tag="lara-content-config"
```


Optionally publish views for customization:


```bash
php artisan vendor:publish --tag="lara-content-views"
```


## Configuration

Key configuration options in `config/content.php`:


```php
return [
    // User ID type: 'auto', 'int', 'uuid', 'ulid'
    'user_id_type' => env('CONTENT_USER_ID_TYPE', 'auto'),

    // Author model for posts
    'author_model' => env('CONTENT_AUTHOR_MODEL', 'App\\Models\\User'),

    // Cache settings
    'cache' => [
        'enabled' => env('CONTENT_CACHE_ENABLED', true),
        'default_ttl' => env('CONTENT_CACHE_TTL', 3600),
    ],

    // Security: allowed HTML tags and attributes
    'security' => [
        'allowed_tags' => ['p', 'br', 'strong', 'em', 'a', 'img', ...],
        'allowed_attributes' => [...],
    ],
];
```


## Usage

### Pages

Pages support flexible layouts with multiple content zones:


```php
use AichaDigital\LaraContent\Models\Page;

// Create a page
$page = Page::create([
    'title' => 'About Us',
    'slug' => 'about-us',
    'layout' => 'sidebar-right',
    'status' => 'published',
]);

// Add blocks to zones
$page->blocks()->create([
    'zone' => 'main',
    'block_type' => 'html',
    'content' => ['html' => '<p>Welcome to our company...</p>'],
    'order' => 1,
]);
```


### Posts

Blog posts with author attribution:


```php
use AichaDigital\LaraContent\Models\Post;

$post = Post::create([
    'title' => 'Getting Started',
    'slug' => 'getting-started',
    'content' => '# Introduction...',
    'author_id' => auth()->id(),
    'status' => 'published',
    'published_at' => now(),
]);
```


### Menus

Hierarchical menus with nested items:


```php
use AichaDigital\LaraContent\Models\Menu;

$menu = Menu::create([
    'name' => 'Main Navigation',
    'slug' => 'main-nav',
]);

$menu->items()->create([
    'title' => 'Home',
    'url' => '/',
    'order' => 1,
]);
```


### Blocks

Render blocks in your views:


```blade
@foreach($page->blocks as $block)
    {!! app(BlockRenderer::class)->render($block) !!}
@endforeach
```


## Available Layouts

| Slug | Name | Zones |
|------|------|-------|
| `single` | Single Column | main |
| `sidebar-left` | Sidebar Left | main, sidebar |
| `sidebar-right` | Sidebar Right | main, sidebar |
| `two-column` | Two Column | left, right |
| `three-column` | Three Column | left, center, right |


## Available Blocks

| Slug | Name | Interactive | Description |
|------|------|-------------|-------------|
| `html` | HTML Block | No | Raw HTML content |
| `recent-posts` | Recent Posts | No | List of recent posts |
| `menu` | Menu Block | No | Render a menu |
| `contact-form` | Contact Form | Yes | Livewire contact form |


## Extending

### Custom Layouts

Register custom layouts in your service provider:


```php
use AichaDigital\LaraContent\Registries\LayoutRegistry;
use App\Content\Layouts\CustomLayout;

public function boot(): void
{
    app(LayoutRegistry::class)->register(new CustomLayout());
}
```


### Custom Blocks

Register custom blocks:


```php
use AichaDigital\LaraContent\Registries\BlockRegistry;
use App\Content\Blocks\CustomBlock;

public function boot(): void
{
    app(BlockRegistry::class)->register(new CustomBlock());
}
```


## Testing


```bash
composer test
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.


## License

AGPL-3.0-or-later. See [License File](LICENSE.md) for details.

---

# Lara Content (Español)

> **VERSION ALPHA**: Este paquete está en desarrollo inicial. La API puede cambiar sin previo aviso. No recomendado para producción todavía.

Paquete de gestión de contenido para Laravel con páginas, posts, bloques y menús. Soporta plantillas Blade con componentes Livewire opcionales para interactividad. Incluye soporte multilingüe via Spatie Translatable.


## Características

- **Páginas**: Sistema flexible de páginas con layouts personalizables y zonas de bloques
- **Posts**: Posts de blog/noticias con atribución de autor y flujo de publicación
- **Menús**: Sistema jerárquico de menús con elementos anidados
- **Bloques**: Bloques de contenido modulares (HTML, Posts Recientes, Menú, Formulario de Contacto)
- **Layouts**: Layouts predefinidos (Una Columna, Sidebar Izquierda/Derecha, Dos/Tres Columnas)
- **Multilingüe**: Soporte completo de traducciones via spatie/laravel-translatable
- **Extensible**: Registra layouts y bloques personalizados via registries
- **Seguro**: Sanitización HTML con tags permitidos configurables


## Requisitos

- PHP 8.3+
- Laravel 12+
- Livewire 3+ (opcional, para bloques interactivos)


## Instalación

Instalar via composer:


```bash
composer require aichadigital/lara-content
```


Publicar y ejecutar migraciones:


```bash
php artisan vendor:publish --tag="lara-content-migrations"
php artisan migrate
```


Publicar archivo de configuración:


```bash
php artisan vendor:publish --tag="lara-content-config"
```


Opcionalmente publicar vistas para personalización:


```bash
php artisan vendor:publish --tag="lara-content-views"
```


## Uso

### Páginas


```php
use AichaDigital\LaraContent\Models\Page;

// Crear una página
$page = Page::create([
    'title' => 'Sobre Nosotros',
    'slug' => 'sobre-nosotros',
    'layout' => 'sidebar-right',
    'status' => 'published',
]);

// Añadir bloques a zonas
$page->blocks()->create([
    'zone' => 'main',
    'block_type' => 'html',
    'content' => ['html' => '<p>Bienvenido a nuestra empresa...</p>'],
    'order' => 1,
]);
```


### Posts


```php
use AichaDigital\LaraContent\Models\Post;

$post = Post::create([
    'title' => 'Primeros Pasos',
    'slug' => 'primeros-pasos',
    'content' => '# Introducción...',
    'author_id' => auth()->id(),
    'status' => 'published',
    'published_at' => now(),
]);
```


### Menús


```php
use AichaDigital\LaraContent\Models\Menu;

$menu = Menu::create([
    'name' => 'Navegación Principal',
    'slug' => 'nav-principal',
]);

$menu->items()->create([
    'title' => 'Inicio',
    'url' => '/',
    'order' => 1,
]);
```


## Layouts Disponibles

| Slug | Nombre | Zonas |
|------|--------|-------|
| `single` | Una Columna | main |
| `sidebar-left` | Sidebar Izquierda | main, sidebar |
| `sidebar-right` | Sidebar Derecha | main, sidebar |
| `two-column` | Dos Columnas | left, right |
| `three-column` | Tres Columnas | left, center, right |


## Bloques Disponibles

| Slug | Nombre | Interactivo | Descripción |
|------|--------|-------------|-------------|
| `html` | Bloque HTML | No | Contenido HTML |
| `recent-posts` | Posts Recientes | No | Lista de posts recientes |
| `menu` | Bloque Menú | No | Renderiza un menú |
| `contact-form` | Formulario Contacto | Sí | Formulario Livewire |


## Extensión

### Layouts Personalizados


```php
use AichaDigital\LaraContent\Registries\LayoutRegistry;
use App\Content\Layouts\MiLayout;

public function boot(): void
{
    app(LayoutRegistry::class)->register(new MiLayout());
}
```


### Bloques Personalizados


```php
use AichaDigital\LaraContent\Registries\BlockRegistry;
use App\Content\Blocks\MiBloque;

public function boot(): void
{
    app(BlockRegistry::class)->register(new MiBloque());
}
```


## Tests


```bash
composer test
```


## Licencia

AGPL-3.0-or-later. Ver [archivo de licencia](LICENSE.md) para detalles.
