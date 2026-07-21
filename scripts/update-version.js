#!/usr/bin/env node

const fs = require('fs');

// Get version from semantic-release command-line argument.
const version = process.argv[2];

if (!version) {
  console.error('Error: Version argument is required.');
  console.error('Usage: node scripts/update-version.js <version>');
  process.exit(1);
}

console.log(`Updating version to: ${version}`);

// Update package.json
const packageJsonPath = './package.json';
const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));
packageJson.version = version;
fs.writeFileSync(packageJsonPath, JSON.stringify(packageJson, null, 2) + '\n');
console.log(`Updated ${packageJsonPath}`);

// Update plugin/package.json
const pluginPackageJsonPath = './plugin/package.json';
const pluginPackageJson = JSON.parse(fs.readFileSync(pluginPackageJsonPath, 'utf8'));
pluginPackageJson.version = version;
fs.writeFileSync(pluginPackageJsonPath, JSON.stringify(pluginPackageJson, null, 2) + '\n');
console.log(`Updated ${pluginPackageJsonPath}`);

// Update plugin/composer.json
const composerJsonPath = './plugin/composer.json';
const composerJson = JSON.parse(fs.readFileSync(composerJsonPath, 'utf8'));
composerJson.version = version;
fs.writeFileSync(composerJsonPath, JSON.stringify(composerJson, null, 2) + '\n');
console.log(`Updated ${composerJsonPath}`);

// Update plugin/power-captcha-recaptcha.php
const pluginMainPath = './plugin/power-captcha-recaptcha.php';
let pluginMain = fs.readFileSync(pluginMainPath, 'utf8');

// Update Plugin header version.
pluginMain = pluginMain.replace(
  /(\*\s+Version:\s+)[\d.]+/,
  `$1${version}`
);

// Update @version annotation.
pluginMain = pluginMain.replace(
  /(\*\s+@version\s+)[\d.]+/,
  `$1${version}`
);

// Update PWRCAP_VERSION define.
pluginMain = pluginMain.replace(
  /('PWRCAP_VERSION'\s*,\s*')[\d.]+(')/,
  `$1${version}$2`
);

fs.writeFileSync(pluginMainPath, pluginMain);
console.log(`Updated ${pluginMainPath}`);

// Update plugin/readme.txt
const readmePath = './plugin/readme.txt';
let readme = fs.readFileSync(readmePath, 'utf8');

// Update Stable tag.
readme = readme.replace(
  /(Stable tag:\s+)[\d.]+/,
  `$1${version}`
);

fs.writeFileSync(readmePath, readme);
console.log(`Updated ${readmePath}`);

console.log('Version update completed successfully!');