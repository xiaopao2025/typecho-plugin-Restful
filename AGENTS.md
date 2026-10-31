# Typecho Plugin Restful — Agent Guide

## Project Overview
A Typecho plugin that exposes a RESTful API for blog content (posts, pages, categories, tags, comments, users, settings). Routes are auto-discovered from `Action.php` public methods ending in `Action`.

## Key Files
- `Plugin.php` — Plugin entry point, activation/deactivation, config panel, comment IP handling
- `Action.php` — All API endpoints (route auto-discovery via reflection)
- `composer.json` — Dev dependencies and scripts
- `tests/` — PHPUnit integration tests (requires live Typecho + MySQL)

## Commands
```bash
# Install dev dependencies
composer install

# Run full test suite (lint + tests)
composer test

# Run only PHPUnit tests
composer phpunit

# Run only code sniffer
composer phpcs

# Auto-fix code style
composer phpcbf
```

## Test Environment
Tests are **integration tests** requiring:
- MySQL database (`MYSQL_HOST`, `MYSQL_USER`, `MYSQL_PWD`, `MYSQL_DB`)
- PHP built-in server serving a Typecho install (`WEB_SERVER_HOST`, `WEB_SERVER_PORT`, `WEB_SERVER_DOCROOT`)
- Typecho auto-downloaded and installed by `tests/bootstrap.php`

Configure via `phpunit.xml` env vars or export before running.

## Coding Standard
PSR-2 with exclusions (see `phpcs.xml`):
- No short array syntax (`array()` not `[]`)
- Some PSR-1/PSR-2 rules relaxed for Typecho compatibility
- Run `composer phpcbf` to auto-fix

## Route Discovery
Routes are auto-generated in `Restful_Action::getRoutes()` by reflecting on public methods matching `*Action`. The URI prefix defaults to `/api/` but can be overridden in `config.inc.php`:
```php
define('__TYPECHO_RESTFUL_PREFIX__', '/rest/');
```
After changing the prefix, **disable and re-enable the plugin**.

## Security Configuration (Plugin Settings)
- `csrfSalt` — **Must change from default** to prevent CSRF
- `apiToken` — Optional token required on all requests (empty = disabled)
- `validateLogin` — When enabled, sensitive endpoints require authenticated user (cookie)
- `origin` — CORS allowlist (one per line, `*` for all)
- `fieldsPrivacy` — Comma-separated custom field names to hide from API
- `allowedOptions` — Comma-separated option keys allowed via `/api/settings?key=`

## Adding New Endpoints
1. Add a public method `xxxAction()` in `Action.php`
2. Doc comment first line becomes the description in config panel
3. Use `$this->lockMethod('get'|'post')` to restrict HTTP method
4. Call `$this->checkState('xxx')` to respect enable/disable toggle
5. Return JSON via `$this->throwData($data)` or `$this->throwError($msg, $status)`

## Common Gotchas
- **No namespace** — Typecho plugins run in global namespace
- **Typecho version** — Requires Typecho 1.3.0+ (PHP 7.4+)
- **Cookie names** — `__typecho_uid`, `__typecho_authCode`, `__typecho_remember_author`, `__typecho_remember_mail`
- **CSRF token** — Generated per-post via `generateCsrfToken()`, validated in `commentAction()`
- **Database prefix** — Use `$this->db->getPrefix()` or `table.` placeholder (auto-replaced)
- **Markdown parsing** — Uses `Widget_Abstract_Contents::markdown()` or `MarkdownExtraExtended`
- **Typed properties** — `Restful_Action::$request` and `::$response` must declare `Typecho\Widget\Request` and `Typecho\Widget\Response` types to match parent `Typecho\Widget` class

## CI / GitHub Actions
Workflow at `.github/workflows/test.yml` (referenced in README badge). Runs the same `composer test` pipeline.
