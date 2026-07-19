# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Power Captcha reCAPTCHA** — WordPress plugin integrating Google reCAPTCHA (v2 Checkbox, v2 Invisible, v3 Score-based) with WordPress core forms, WooCommerce forms, and Contact Form 7.

- **Main plugin file**: `plugin/power-captcha-recaptcha.php`
- **Text domain**: `power-captcha-recaptcha`
- **Requires**: WP 5.0+, PHP 5.5+, WooCommerce 9.7.1 (tested)

## Commands

### Build & Development

```bash
# Build JS/CSS assets (production)
cd plugin && npm run build

# Watch mode for development
cd plugin && npm run dev

# PHP linting (WordPress Coding Standards)
cd plugin && vendor/bin/phpcs

# Auto-fix PHP code style
cd plugin && vendor/bin/phpcbf

# Run E2E tests (requires WordPress test environment)
cd tests && npm run test:e2e

# Run unit tests (JS)
cd tests && npm run test:unit
```

### Testing

- **E2E tests**: `tests/tests/e2e/` — Puppeteer + Jest tests against running WP instance
- **Test config**: `tests/jest.config.js`, `tests/jest-puppeteer.config.js`
- **Test helpers**: `tests/tests/e2e/helpers.js` — shared utilities for test setup

## Architecture

### PHP Structure (`plugin/inc/`)

| File/Directory | Purpose |
|----------------|---------|
| `core.php` | Main logic: CAPTCHA rendering, verification, AJAX handlers, settings API |
| `admin.php` | Admin UI: settings pages, tabs, fields, notices, enqueue scripts |
| `api.php` | REST API endpoints for activity report, captcha report |
| `update.php` | Update checker / version management |
| `wp/` | WordPress core form integrations (login, register, comment, lost/reset password) |
| `woo/` | WooCommerce form integrations (checkout, login, register, review, lost/reset password) |
| `cf7/` | Contact Form 7 integration |

### Key Constants (defined in main plugin file)

- `PWRCAP_VERSION` — Plugin version
- `PWRCAP_DIR` — Plugin directory path
- `PWRCAP_URL` — Plugin URL

### Settings Architecture

Options stored in `wp_options` via Settings API, grouped by section:

| Option Group | Keys |
|--------------|------|
| `pwrcap_general_options` | `captcha_type` (v2/v3), `captcha_v2_type` (v2cbx/v2inv), `site_key`, `secret_key` |
| `pwrcap_captchas_options` | Per-form enable/disable toggles (wp_login, woo_checkout, etc.) |
| `pwrcap_misc_options` | `enable_debug` (console logging) |
| `pwrcap_state_options` | Dismissed notices, setup state |

Helper functions: `pwrcap_option($group, $key)`, `pwrcap_update_option($group, $key, $value)`, `pwrcap_is_setup_complete()`

### CAPTCHA Types & Rendering

| Type | Callback | JS Init Function |
|------|----------|------------------|
| v3 (Score) | `pwrcap_render_v3()` | `window.pwrcapInitV3()` |
| v2 Checkbox | `pwrcap_render_v2_checkbox()` | `window.pwrcapInitV2cbx()` |
| v2 Invisible | `pwrcap_render_v2_invisible()` | `window.pwrcapInitV2inv()` |

- Context-aware theming/sizing via `pwrcap_get_theme_by_context()`, `pwrcap_get_size_by_context()`
- Verification in `pwrcap_verify_captcha_response()` using `google/recaptcha` PHP library

### JS Architecture (`plugin/assets/src/js/`)

```
js/
├── admin.js          # Admin settings page interactions
├── captcha.js        # Entry point — registers window callbacks
├── notice.js         # Admin notice dismissal
└── captcha/
    ├── v2-checkbox.js
    ├── v2-invisible.js
    └── v3.js
```

- Vite build → `assets/dist/` (JS unminified + source maps, CSS minified + source maps)
- `vite.config.js` handles custom CSS extraction/minification via `buildCSS()` plugin

### Hooks & Filters (Key Extensibility Points)

- `pwrcap_admin_do_tab_navigation` — Add settings tabs
- `pwrcap_admin_do_tab_stage` — Add tab content
- `pwrcap_admin_init` — Add settings sections/fields
- `pwrcap_verification_response` — Filter reCAPTCHA verification result
- `pwrcap_do_admin_notices` — Add admin notices
- `pwrcap_daily_event` — Scheduled daily cron
- `pwrcap_get_state_options_defaults` — Filter state defaults

## Development Notes

- **WordPress Coding Standards** enforced via `phpcs.xml` (PSR12 + WP + PHPCompatibility)
- **Composer** for PHP deps (`google/recaptcha`, phpcs); `vendor/` committed
- **npm** for JS/CSS build tooling (Vite, Sass, esbuild, clean-css)
- **Activity Report** feature: REST API at `/wp-json/pwrcap/v1/activity-report` (see `api.php`)
- **Debug mode**: Enable in Misc settings → outputs verification responses to browser console

## File Conventions

- PHP files: `pwrcap_` prefix for functions, `PWRCAP_` for constants
- Hook naming: `pwrcap_` prefix
- Text domain: `power-captcha-recaptcha`
- Translation files in `plugin/languages/`