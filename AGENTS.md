# Codex Working Instructions

## Project Context

This repository is `aichadigital/lara-content`, a Laravel content package for pages, posts, blocks, menus, Blade views, optional Livewire components, and multilingual content.

Authoritative project sources:

- `README.md` for user-facing package usage.
- `composer.json` for supported PHP/Laravel versions and commands.
- `CLAUDE.md` for historical project context.
- `docs/ADR-001-uuid-first.md` for the accepted UUID-first direction.
- `docs/2026-05-10-spec-uuid-first-implementation.md` for the pending UUID-first implementation plan.

## Language Rules

- Conversation with the user is Spanish by default.
- Code must be written in English.
- Code comments and docblocks must be written in English.
- Do not translate class names, method names, variable names, tests, config keys, or command names into Spanish.

## Before Executing

Before multi-step work:

1. State assumptions explicitly.
2. Present multiple interpretations if they exist.
3. Mention the simpler approach when relevant.
4. Stop and ask if something material is unclear.
5. Convert the task into verifiable objectives.

Use concrete checks, for example: a test passes, PHPStan passes, Pint passes, an HTTP response is 200, or a schema assertion matches the expected column type.

## Change Discipline

- Minimal intervention.
- Surgical changes only.
- Touch only the files needed for the user request.
- Do not clean adjacent code, comments, formatting, or stale docs unless the request directly requires it.
- Respect existing indentation, quotes, naming, and local patterns.
- If the current change leaves imports, variables, functions, handlers, config keys, or files unused, remove only those made unused by the current change.
- If unrelated dead code, obsolete config, or orphaned rules are found, mention them instead of deleting them.
- Every modified line should trace directly to the user request.

## Host Platform

The local host is macOS/Darwin with BSD userland, not GNU/Linux.

- Prefer POSIX-compatible shell commands.
- Do not assume GNU-only flags for local commands.
- When in doubt, use portable shell or a small `python3 -c` helper.
- Check for Homebrew-prefixed GNU tools such as `gsed` before relying on GNU behavior.
- For commands executed over SSH on Linux servers, GNU behavior can be assumed for the remote side.

## Package Conventions

- PHP: `^8.3`.
- Laravel illuminate contracts: `^12.0||^13.0`.
- Tests use Pest and Orchestra Testbench.
- Static analysis uses PHPStan/Larastan.
- Code style uses Laravel Pint.
- Package namespace: `AichaDigital\LaraContent`.
- Package migrations live in `database/migrations/`.
- Blade views live in `resources/views/`.
- Package config is `config/content.php`.

Useful commands from `composer.json`:

- `composer test`
- `composer phpstan`
- `composer pint`
- `composer quality`
- `composer test-coverage`

## UUID-First Context

`docs/ADR-001-uuid-first.md` is accepted. The intended direction is UUID v7 `char(36)` for the consuming app `users.id`, with `content_posts.author_id` as the only current user foreign key in this package.

Current source may still contain legacy user ID agnosticism until the pending spec is implemented:

- `config/content.php` still has `content.user_id_type`.
- `src/Support/MigrationHelper.php` still has `int`, `uuid`, `ulid`, and auto-detection branches.
- `tests/TestCase.php` still sets `content.user_id_type`.
- `README.md` still shows `CONTENT_USER_ID_TYPE`.

Do not treat those legacy paths as the desired future architecture. Follow the ADR and spec when doing UUID-first work.

## Safety

- Do not run destructive commands without explicit approval.
- Do not revert unrelated user changes.
- Do not run application or staging migrations without explicit approval.
- Treat `.remember/` and `.claude/` as session/tooling artifacts unless the user asks to modify them.
