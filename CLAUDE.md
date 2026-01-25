# Lara Content - Instrucciones de Proyecto

## Descripcion del Paquete

Paquete de gestion de contenido para Laravel: paginas, posts, bloques y menus. Blade + Livewire opcional, soporte multilingue.


## Stack Tecnologico

- **PHP**: 8.3+
- **Laravel**: 12+
- **Dependencias**:
  - spatie/laravel-package-tools
  - spatie/laravel-translatable
  - stevebauman/purify (sanitizacion HTML)
  - league/commonmark (Markdown)
- **Livewire**: Opcional (para bloques interactivos)


## Arquitectura

### Registries (Singleton)

- `LayoutRegistry`: Gestiona layouts disponibles
- `BlockRegistry`: Gestiona bloques disponibles
- Patron: registrar en `packageBooted()` del ServiceProvider

### Contracts

- `LayoutContract`: Define estructura de layouts
- `BlockContract`: Define estructura de bloques
- `ContentAuthorContract`: Define el modelo de autor

### Traits

- `HasUuid`: UUID v7 para modelos
- `HasSlug`: Generacion automatica de slugs
- `HasStatus`: Estados (draft, published, archived)

### MigrationHelper

- `MigrationHelper::userIdColumn()`: FK a users segun configuracion
- Soporta: auto, int, uuid, ulid


## Estructura de Directorios


```
src/
  Blocks/           # Bloques de contenido
    Contracts/      # BlockContract
  Concerns/         # Traits (HasUuid, HasSlug, etc.)
  Contracts/        # ContentAuthorContract
  Enums/            # ContentStatus
  Exceptions/       # Excepciones custom
  Layouts/          # Layouts de pagina
    Contracts/      # LayoutContract
  Livewire/         # Componentes Livewire
  Models/           # Page, Post, Menu, MenuItem, PageBlock
  Registries/       # LayoutRegistry, BlockRegistry
  Services/         # BlockRenderer, PageRenderer, ContentSanitizer
  Support/          # MigrationHelper
config/
  content.php       # Configuracion
database/
  factories/        # Factories para testing
  migrations/       # Migraciones
resources/
  views/            # Vistas Blade
tests/
```


## Reglas de Desarrollo

### Migraciones

- Usar `MigrationHelper::userIdColumn()` para FK a users
- Prefijo de tablas: `content_` (pages, posts, menus, etc.)
- NUNCA usar tipo ENUM en MySQL

### Enums

- Usar PHP Enum + unsignedTinyInteger en BD
- Ejemplo: `ContentStatus` con valores backed (int)

### Testing

- Ejecutar tests EN el directorio del paquete
- SQLite :memory: para tests
- Factories en `database/factories/`


## Code Style

- **Pint**: Estilo Laravel
- **declare(strict_types=1)** en todos los archivos
- **PHPStan**: Nivel 5


## Comandos


```bash
# Lint
composer pint

# Analisis estatico
composer phpstan

# Tests
composer test

# Tests con coverage
composer test-coverage

# Calidad completa
composer quality
```


## Notas Importantes

1. **Livewire es opcional**: Los componentes Livewire solo se registran si Livewire esta instalado
2. **Conditional registration**: registerLivewireComponents() verifica class_exists antes de registrar
3. **Extensible**: Aplicaciones pueden registrar layouts y bloques custom via registries
4. **Sanitizacion**: Todo HTML pasa por ContentSanitizer antes de renderizar
5. **Cache**: Bloques y menus soportan cache configurable
6. **Traducciones**: Usar spatie/laravel-translatable para campos multilingues


## Pre-Push Checks


```bash
./vendor/bin/pint
./vendor/bin/pest
./vendor/bin/phpstan analyse
```

Todos deben pasar antes de push.
