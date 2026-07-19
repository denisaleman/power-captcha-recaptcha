# Release Process

This document outlines the automated release process for the Power Captcha reCAPTCHA WordPress plugin using semantic-release and GitHub Actions.

## How Releases Work

Releases are automatically triggered by pushes to the `master` branch that contain commits following the [Conventional Commits](https://www.conventionalcommits.org/) specification.

### Release Process Flow

1. **Development**: Developers make changes and commit using conventional commit messages
2. **CI Trigger**: When commits are pushed to `master`, the GitHub Actions workflow runs
3. **Version Calculation**: semantic-release analyzes commit messages to determine the next version:
   - `fix:` → patch release (e.g., 1.0.0 → 1.0.1)
   - `feat:` → minor release (e.g., 1.0.0 → 1.1.0)
   - `feat!: ` or `feat: !` or `fix!: ` → major release (e.g., 1.0.0 → 2.0.0)
   - `perf:`, `docs:`, `style:`, `refactor:`, `test:`, `chore:` → no version bump (but included in release notes)
4. **Release Tasks**: semantic-release performs:
   - Updates version in all relevant files
   - Generates release notes from commit messages
   - Updates CHANGELOG.md
   - Creates a git tag
   - Creates a GitHub Release
   - Commits updated version files back to the repository
5. **GitHub Actions**: The workflow builds the plugin and attaches it as a release asset

## Conventional Commit Rules

All commits to the `master` branch should follow the Conventional Commits specification:

### Format
```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Types
- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Changes that do not affect the meaning of the code (white-space, formatting, missing semi-colons, etc)
- `refactor`: A code change that neither fixes a bug nor adds a feature
- `perf`: A code change that improves performance
- `test`: Adding missing tests or correcting existing tests
- `chore`: Changes to the build process or auxiliary tools and libraries

### Breaking Changes
A breaking change can be indicated by:
- Adding `!` after the type/scope: `feat!: add new API`
- Or in the footer: `BREAKING CHANGE: description of the change`

### Examples
```
feat: add reCAPTCHA v3 support to WooCommerce checkout
fix: resolve CAPTCHA validation issue on login form
docs: update CLAUDE.md with commit conventions
style: fix PHP whitespace per WordPress Coding Standards
refactor: simplify CAPTCHA verification logic
perf: cache reCAPTCHA siteverify responses for 5 minutes
test: add unit tests for WooCommerce integration
chore: update phpcs standards to WC 2.2
feat!: remove legacy reCAPTCHA v1 support
```

## Performing a Release

Releases are fully automated - no manual steps are required for regular releases:

1. Make your changes in a feature branch
2. Commit using conventional commit messages
3. Open a pull request to merge into `master`
4. Once merged into `master`, the GitHub Actions workflow will automatically:
   - Determine the next version
   - Generate release notes
   - Update all version references
   - Create a git tag
   - Create a GitHub release
   - Build and attach the plugin ZIP

### Manual Release (Advanced)
If you need to manually trigger a release:
```bash
# Ensure you're on master and up to date
git checkout master
git pull origin master

# Run semantic-release directly (requires GITHUB_TOKEN)
npx semantic-release
```

## Recovering from a Failed Release

If a release fails, follow these steps:

### 1. Identify the Failure
Check the GitHub Actions workflow run for errors. Common issues include:
- Missing GITHUB_TOKEN or NPM_TOKEN secrets
- Network issues during npm publish
- Git push failures due to protection rules
- Version conflicts

### 2. Fix the Issue
Depending on the failure:
- **Missing secrets**: Add required secrets to repository settings
- **Network issues**: Wait and retry
- **Git push failures**: Check branch protection rules and permissions
- **Version conflicts**: May need to manually reset version files

### 3. Clean Up Failed State
If a release failed partially:
```bash
# Reset to last known good commit
git reset --hard <last-good-commit>
git push origin master --force-with-lease

# Remove any tags that may have been created
git tag -d <failed-tag>
git push origin :refs/tags/<failed-tag>
```

### 4. Retry
Once the issue is resolved, simply push again to `master` or re-run the workflow:
```bash
# Make a trivial change to trigger rebuild if needed
git commit --allow-empty -m "chore: retry release"
git push origin master
```

### 5. Manual Recovery (if needed)
In extreme cases where automated recovery isn't possible:
```bash
# Manually set version using our script
node scripts/update-version.js

# Commit the version changes
git add package.json plugin/package.json plugin/power-captcha-recaptcha.php plugin/readme.txt plugin/composer.json CHANGELOG.md
git commit -m "chore: set version to X.Y.Z [skip ci]"

# Create tag and push
git tag vX.Y.Z
git push origin master --tags
```

## Version Files Updated Automatically

The release process automatically updates these files:

1. **plugin/power-captcha-recaptcha.php**:
   - Plugin Header Version: `Version:`
   - PWRCAP_VERSION constant

2. **plugin/readme.txt**:
   - Stable tag: `Stable tag:`

3. **plugin/package.json**:
   - `version` field

4. **plugin/composer.json**:
   - Implicitly maintained through plugin version (no explicit version field)

5. **package.json** (root):
   - `version` field (for tracking)

6. **CHANGELOG.md**:
   - Generated release notes

## Best Practices

1. **Always use conventional commits** - This ensures proper version bumps
2. **Keep commits atomic and focused** - Makes changelog generation more useful
3. **Include issue references in footers** when applicable:
   ```
   fix: resolve CAPTCHA validation issue
   
   Fixes #123
   ```
4. **Use breaking change indicators correctly** - Only when actually making breaking changes
5. **Let automation handle versioning** - Never manually bump versions in this repository
6. **Review generated changelog** - Check the auto-generated release notes for clarity

## Troubleshooting

### "npm ERR! 401 Unauthorized"
- Ensure NPM_TOKEN secret is configured in GitHub repository settings
- Token needs `publish` scope for npm

### "GitHub Error: 403"
- Ensure GITHUB_TOKEN has `contents: write` permission
- Check branch protection rules aren't blocking forced pushes (if applicable)

### "Nothing to commit"
- This is normal if no version changes were needed
- The workflow will still create a GitHub release if a tag was pushed

### Version not updating in all places
- Check that all version file paths are correct in `.releaserc.js`
- Verify the update-version.js script handles all file types correctly