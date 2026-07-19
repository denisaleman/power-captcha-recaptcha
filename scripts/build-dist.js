#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

try {
  console.log('Building distribution package...');

  // Get version from git tag or package.json
  let version;
  try {
    // Try to get version from latest git tag
    version = execSync('git describe --tags --abbrev=0', { encoding: 'utf8' }).trim();
    // Remove 'v' prefix if present
    if (version.startsWith('v')) {
      version = version.substring(1);
    }
  } catch (error) {
    // Fallback to package.json version
    const packageJson = JSON.parse(fs.readFileSync('./package.json', 'utf8'));
    version = packageJson.version;
  }

  console.log(`Building version: ${version}`);

  // Install production Composer dependencies without running scripts
  // We use --no-scripts to avoid running post-install commands that require dev dependencies
  console.log('Installing production Composer dependencies...');
  process.chdir(path.resolve(__dirname, '..', 'plugin'));
  execSync('composer install --no-dev --prefer-dist --optimize-autoloader --classmap-authoritative --no-scripts', { stdio: 'inherit' });
  process.chdir(path.resolve(__dirname, '..')); // Go back to project root

  // Define paths
  const distDir = path.resolve(__dirname, '..', 'dist');
  const zipName = `power-captcha-recaptcha-${version}.zip`;
  const zipPath = path.resolve(__dirname, '..', 'dist', zipName);

  // Clean up previous dist directory
  if (fs.existsSync(distDir)) {
    console.log('Cleaning previous dist directory...');
    execSync(`rm -rf "${distDir}"`, { stdio: 'inherit' });
  }

  // Create dist directory
  fs.mkdirSync(distDir, { recursive: true });

  // Create a temporary directory for building the clean plugin package
  const tempDir = path.resolve(__dirname, '..', 'temp-build', `power-captcha-recaptcha-${version}`);
  const tempPluginDir = path.join(tempDir, 'power-captcha-recaptcha');

  // Clean up previous temp directory
  if (fs.existsSync(tempDir)) {
    execSync(`rm -rf "${tempDir}"`, { stdio: 'inherit' });
  }

  // Create temp directory
  fs.mkdirSync(tempDir, { recursive: true });

  // Copy ONLY the necessary files from plugin directory
  console.log('Copying plugin files...');
  copyDirectorySelective(
    path.resolve(__dirname, '..', 'plugin'),
    tempPluginDir,
    [
      // Directories to exclude completely
      'node_modules',
      'assets/src',
      '.git',
      '.github',
      'tests',
      // Specific files to exclude
      'composer.json',
      'composer.lock',
      'package.json',
      'package-lock.json',
      'phpcs.xml',
      'vite.config.js'
    ]
  );

  // Create ZIP archive in the dist directory
  console.log(`Creating ZIP archive: ${zipName}`);
  const tempDirParent = path.dirname(tempDir);
  const folderName = path.basename(tempDir);
  execSync(`cd "${tempDirParent}" && zip -r "${zipPath}" "${folderName}"`, { stdio: 'inherit' });

  // Clean up temporary directory
  execSync(`rm -rf "${tempDir}"`, { stdio: 'inherit' });

  console.log(`Distribution package created: ${zipPath}`);
  console.log('Build completed successfully!');

} catch (error) {
  console.error('Build failed:', error.message);
  process.exit(1);
}

/**
 * Copy directory selectively, excluding specified patterns
 * @param {string} src - Source directory
 * @param {string} dest - Destination directory
 * @param {string[]} exclusions - Array of glob-style patterns to exclude
 */
function copyDirectorySelective(src, dest, exclusions = []) {
  function shouldExclude(filePath) {
    // Make the path.usePosix = true; // Ensure forward slashes for consistent matching
    const relativePath = path.relative(src, filePath);
    path.usePosix = false;

    for (const pattern of exclusions) {
      // Normalize pattern for consistent matching
      const normalizedPattern = pattern.replace(/\\/g, '/');

      // Handle directory patterns ending with /
      if (normalizedPattern.endsWith('/')) {
        const dirPattern = normalizedPattern.slice(0, -1);
        if (relativePath.startsWith(dirPattern + '/') ||
            relativePath.split(/[\\/]/).some(part => part === dirPattern)) {
          return true;
        }
      }
      // Handle exact file matches or path patterns
      else if (relativePath === normalizedPattern ||
               relativePath.startsWith(normalizedPattern + '/') ||
               `/${relativePath}`.includes(`/${normalizedPattern}/`)) {
        return true;
      }
    }

    return false;
  }

  function copySync(currentSrc, currentDest) {
    try {
      const entries = fs.readdirSync(currentSrc, { withFileTypes: true });

      for (const entry of entries) {
        const srcPath = path.join(currentSrc, entry.name);
        const destPath = path.join(currentDest, entry.name);

        if (shouldExclude(srcPath)) {
          continue;
        }

        if (entry.isDirectory()) {
          if (!fs.existsSync(destPath)) {
            fs.mkdirSync(destPath, { recursive: true });
          }
          copySync(srcPath, destPath);
        } else {
          // Ensure destination directory exists
          const destDir = path.dirname(destPath);
          if (!fs.existsSync(destDir)) {
            fs.mkdirSync(destDir, { recursive: true });
          }
          fs.copyFileSync(srcPath, destPath);
        }
      }
    } catch (error) {
      // Ignore errors for non-existent directories (can happen with some exclusion patterns)
      if (error.code !== 'ENOENT') {
        throw error;
      }
    }
  }

  copySync(src, dest);
}