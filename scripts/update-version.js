#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

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

console.log(`Updating version to: ${version}`);

// Update package.json
const packageJsonPath = './package.json';
let packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));
packageJson.version = version;
fs.writeFileSync(packageJsonPath, JSON.stringify(packageJson, null, 2) + '\n');
console.log(`Updated ${packageJsonPath}`);

// Update plugin/package.json
const pluginPackageJsonPath = './plugin/package.json';
let pluginPackageJson = JSON.parse(fs.readFileSync(pluginPackageJsonPath, 'utf8'));
pluginPackageJson.version = version;
fs.writeFileSync(pluginPackageJsonPath, JSON.stringify(pluginPackageJson, null, 2) + '\n');
console.log(`Updated ${pluginPackageJsonPath}`);

// Update plugin/composer.json
const composerJsonPath = './plugin/composer.json';
let composerJson = JSON.parse(fs.readFileSync(composerJsonPath, 'utf8'));
composerJson.version = version;
fs.writeFileSync(composerJsonPath, JSON.stringify(composerJson, null, 2) + '\n');
console.log(`Updated ${composerJsonPath}`);

// Update plugin/power-captcha-recaptcha.php
const pluginMainPath = './plugin/power-captcha-recaptcha.php';
let pluginMain = fs.readFileSync(pluginMainPath, 'utf8');

// Update Plugin Name header version
pluginMain = pluginMain.replace(
  /(\*\s+Version:\s+)[\d.]+/,
  `$1${version}`
);

// Update @version annotation
pluginMain = pluginMain.replace(
  /(\*\s+@version\s+)[\d.]+/,
  `$1${version}`
);

// Update PWRCAP_VERSION define
pluginMain = pluginMain.replace(
  /(define\s*\(\s*'PWRCAP_VERSION'\s*,\s')[\d.]+('\)\s*;)/,
  `$1${version}$2`
);

fs.writeFileSync(pluginMainPath, pluginMain);
console.log(`Updated ${pluginMainPath}`);

// Update plugin/readme.txt
const readmePath = './plugin/readme.txt';
let readme = fs.readFileSync(readmePath, 'utf8');

// Update Stable tag
readme = readme.replace(
  /(Stable tag:\s+)[\d.]+/,
  `$1${version}`
);

// Update stable tag in version header
readme = readme.replace(
  /(=== Power Captcha reCAPTCHA ===[\s\S]*?Stable tag:\s+)[\d.]+/,
  `$1${version}`
);

// Update the latest version header in changelog
readme = readme.replace(
  /(= [\d.]+ =)[\s\S]*?(\n= [\d.]+ =)/,
  `= ${version} =\n* Updated via semantic-release\n\n$2`
);

fs.writeFileSync(readmePath, readme);
console.log(`Updated ${readmePath}`);

// Update CHANGELOG.md if it exists
const changelogPath = './CHANGELOG.md';
if (fs.existsSync(changelogPath)) {
  let changelog = fs.readFileSync(changelogPath, 'utf8');

  // Add new version section at the top after initial header
  const insertPosition = changelog.indexOf('\n## ');
  if (insertPosition !== -1) {
    const newChangelog =
      changelog.slice(0, insertPosition + 1) +
      `## ${version} (${new Date().toISOString().split('T')[0]})\n\n* Initial release via semantic-release\n\n` +
      changelog.slice(insertPosition + 1);

    fs.writeFileSync(changelogPath, newChangelog);
    console.log(`Updated ${changelogPath}`);
  }
}

// Update any PHP constants that might duplicate the version
const phpFiles = [
  './plugin/inc/core.php',
  './plugin/inc/admin.php',
  './plugin/inc/api.php',
  './plugin/inc/update.php'
];

phpFiles.forEach(filePath => {
  if (fs.existsSync(filePath)) {
    let content = fs.readFileSync(filePath, 'utf8');

    // Look for any version defines or constants that might need updating
    const versionDefineMatches = content.match(/define\s*\(\s*['"][_A-Z0-9_]*VERSION['"]\s*,\s*['"]([\d.]+)['"]\s*\)/g);
    if (versionDefineMatches) {
      versionDefineMatches.forEach(match => {
        const currentVersionMatch = match.match(/['"]([\d.]+)['"]/);
        if (currentVersionMatch && currentVersionMatch[1] !== version) {
          content = content.replace(match, match.replace(currentVersionMatch[1], version));
        }
      });

      fs.writeFileSync(filePath, content);
      console.log(`Updated version constants in ${filePath}`);
    }
  }
});

console.log('Version update completed successfully!');