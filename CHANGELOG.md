# Changelog

All notable changes to `aichadigital/lara-content` will be documented in this file.

## [0.3.0] - 2026-09-29

### Breaking changes — editorial workflow replaces is_published

**The `is_published` boolean is replaced by a `publish_status` string column**
on both `content_posts` and `content_pages`, backed by the `PublishStatus`
enum: `draft`, `review`, `ready`, `published`, `archived`. A migration backfills
existing rows (`is_published = 1` → `published`, the rest → `draft`) and
rebuilds the affected indexes. Consumers reading/writing `is_published`
directly must switch to `publish_status`; `Model::published()` semantics are
preserved (status `published` plus the `published_at` scheduling gate).

### Added

- `PublishStatus` enum and `publish_status` column on posts and pages, with
  labels in en/es and a draft default on new model instances.
- Taxonomies: `content_categories` and `content_tags` tables (translatable
  names, unique slugs), `content_post_categories` / `content_post_tags` pivot
  tables with composite primary keys and cascade FKs, `Category` and `Tag`
  models, and `Post::categories()` / `Post::tags()` relations. Both model
  classes are overridable via `content.models.*`.
- SEO and internal editorial fields on `content_posts`: `meta_title`,
  `meta_description`, `featured_image_alt` (translatable, nullable) and
  `focus_keyword`, `secondary_keywords`, `internal_notes` (internal-only).
  Internal fields are declared in `Post::INTERNAL_ATTRIBUTES` and excluded
  from `Post::publicAttributes()`; they must never be rendered or exposed by
  public API surfaces.
- `content:import-posts` command: idempotent upsert by slug of markdown files
  with YAML frontmatter, converting bodies to sanitized HTML at import
  (`content_type = html`). Field mapping, status mapping, import locale and
  meta-length limits are consumer configuration (`content.import.*`). Files
  without a `slug` key are skipped; validation failures exit non-zero.
- `symfony/yaml` declared as a direct dependency (frontmatter parsing).

### Fixed

- **The GFM extension was never active in `ContentSanitizer`**: a custom
  environment was passed as the second constructor argument of
  `CommonMarkConverter`, which accepts none and silently discarded it —
  tables, strikethrough and task lists were parsed as plain markdown (the bug
  was baselined away instead of fixed). `MarkdownConverter` now receives the
  environment directly; a regression test guards GFM tables.

## [0.2.0] - 2026-05-10

### Breaking changes — UUID-first migration

`lara-content` adopts UUID v7 char(36) as the only supported type for FK columns
referencing the consumer app's `users.id`. bigint and ULID are out of scope.
See [ADR-001](docs/ADR-001-uuid-first.md) (this package) and
[larabill ADR-006](https://github.com/AichaDigital/larabill/blob/main/docs/ADR-006-uuid-first-no-agnostic.md)
for the canonical rationale, and STD-001 in the AichaDigital umbrella standards.

#### Removed

- `content.user_id_type` config key and `CONTENT_USER_ID_TYPE` ENV var. The
  package no longer reads them.
- Legacy agnostic helpers in `Support\MigrationHelper`: `getUserIdType()`,
  `detectUserIdType()`, `getIdTypeDescription()`, `isSupportedIdType()`,
  `agnosticIdColumn()`. Only `userIdColumn()` remains, simplified to emit
  UUID char(36) unconditionally.

#### Changed

- `content_posts.author_id` is now always `char(36)` UUID. The column was
  already emitted via `MigrationHelper::userIdColumn(...)`; with the helper
  simplified, it is UUID-only.
- `Models\Post::$author_id` PHPDoc updated from `int|string|null` to
  `string|null` to reflect the UUID-only contract.
- `tests/TestCase.php` no longer sets `content.user_id_type` (key removed).

#### Added

- `tests/Integration/Mysql/MysqlIntegrationTestCase.php` and
  `tests/Integration/Mysql/FreshInstallTest.php` — verify the UUID-first
  contract against MySQL 8 with a fresh schema. Driven by
  `LARACONTENT_TEST_MYSQL_*` env vars (with fallback to
  `LARABILL_TEST_MYSQL_*` for umbrella-local convenience).
- CI job `mysql-integration` running the new suite against a MySQL 8 service.
- `docs/ADR-001-uuid-first.md` — local ADR materializing STD-001 for this
  package.
- README requirement section pointing at the shared
  `larabill/docs/setup-uuid.md` setup guide.

#### Migration notes

- Apps already on UUID `users.id`: no action required, the package keeps working.
- Apps on bigint or ULID `users.id`: not supported. Migrate `users` to UUID v7
  before installing — see the shared setup guide. Migrating an existing app's
  primary key is non-trivial and is out of `lara-content`'s scope.

## [0.1.0] - earlier

Initial alpha release.
