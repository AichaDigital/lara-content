# Changelog

All notable changes to `aichadigital/lara-content` will be documented in this file.

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
