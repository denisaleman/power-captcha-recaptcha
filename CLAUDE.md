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
- `pwrcap_no_captcha_code_sent` — Fires when CAPTCHA response is empty or not submitted
- `pwrcap_do_admin_notices` — Add admin notices
- `pwrcap_daily_event` — Scheduled daily cron
- `pwrcap_get_state_options_defaults` — Filter state defaults

## Development Notes

- **WordPress Coding Standards** enforced via `phpcs.xml` (PSR12 + WP + PHPCompatibility)
- **Composer** for PHP deps (`google/recaptcha`, phpcs); `vendor/` committed
- **npm** for JS/CSS build tooling (Vite, Sass, esbuild, clean-css)
- **Activity Report** feature: REST API at `/wp-json/pwrcap/v1/activity-report` (see `api.php`)
- **Debug mode**: Enable in Misc settings → outputs verification responses to browser console

## Commit Messages

Please follow [Conventional Commits](https://www.conventionalcommits.org/) when committing changes:

- `feat:` for new features
- `fix:` for bug fixes
- `docs:` for documentation changes
- `style:` for formatting, missing semicolons, etc.
- `refactor:` for code changes that neither fix bugs nor add features
- `perf:` for performance improvements
- `test:` for adding or correcting tests
- `chore:` for build process changes, tooling updates, etc.

Examples:
- `feat: add reCAPTCHA v3 support to WooCommerce checkout`
- `fix: resolve CAPTCHA validation issue on login form`
- `docs: update CLAUDE.md with commit conventions`
- `style: fix PHP whitespace per WordPress Coding Standards`
- `refactor: simplify CAPTCHA verification logic`
- `perf: cache reCAPTCHA siteverify responses for 5 minutes`
- `test: add unit tests for WooCommerce integration`
- `chore: update phpcs standards to WC 2.2`

This helps maintain a clean, readable commit history and enables automated changelog generation.

## Release Process

This repository uses [semantic-release](https://semantic-release.gitbook.io/semantic-release/) for automated version management and publishing. Releases are triggered automatically when commits are pushed to the `master` branch following the Conventional Commits specification.

### How Releases Work

1. **Development**: Developers make commits using conventional commit types (feat, fix, docs, etc.)
2. **CI Trigger**: When commits are pushed to `master`, GitHub Actions runs the release workflow
3. **Version Calculation**: semantic-release analyzes commits since last release to determine the next version number
4. **Changelog Generation**: Release notes are automatically generated from commit messages
5. **Version Updates**: All version references in the plugin are updated automatically
6. **GitHub Release**: A GitHub release is created with the updated plugin as a downloadable ZIP
7. **Tag Creation**: A Git tag is created for the release version
8. **Commit Back**: Updated version files are committed back to the repository

### Performing a Release

Releases are fully automated - no manual steps are required. Simply:
1. Ensure your commits follow [Conventional Commits](https://www.conventionalcommits.org/)
2. Push to the `main` or `master` branch
3. The CI system will automatically:
   - Calculate the next version number
   - Generate release notes
   - Update all version references
   - Create a Git tag
   - Publish a GitHub release
   - Commit the updated version files back to the repository

### Recovering from a Failed Release

If a release fails:

1. **Check the Actions tab** in GitHub for error details
2. **Fix the issue** (may require fixing commit messages, permissions, etc.)
3. **Re-trigger the workflow**:
   - If the failure was before a tag was created: simply push new commits to trigger a new attempt
   - If a tag was created but release failed: delete the tag locally and remotely, then push again
     ```bash
     # Delete local tag
     git tag -d vX.Y.Z
     # Delete remote tag
     git push origin --delete vX.Y.Z
     # Push commits again to trigger new release attempt
     git push
     ```
4. **Manual recovery** (only if absolutely necessary):
   - Follow the manual version update process documented in RELEASE.md
   - Commit the changes with `[skip ci]` to avoid triggering another release
   - Create the git tag manually
   - Push the tag to trigger a release

### Release Configuration

The release process is configured via:
- `.releaserc.js` - semantic-release configuration
- `.github/workflows/release.yml` - GitHub Actions workflow
- `scripts/update-version.js` - Custom script to update all version references in the project

### Version Files Updated Automatically

The release process automatically updates these files:

1. **plugin/power-captcha-recaptcha.php**:
   - Plugin Header Version: `Version:`
   - PWRCAP_VERSION constant

2. **plugin/readme.txt**:
   - Stable tag: `Stable tag:`

3. **plugin/package.json**:
   - `version` field

4. **plugin/composer.json**:
   - No explicit version field, but maintained through plugin consistency

5. **CHANGELOG.md**:
   - Generated release notes following Keep a Changelog format

See [RELEASE.md](RELEASE.md) for detailed release process documentation.

## File Conventions

- PHP files: `pwrcap_` prefix for functions, `PWRCAP_` for constants
- Hook naming: `pwrcap_` prefix
- Text domain: `power-captcha-recaptcha`
- Translation files in `plugin/languages/`