module.exports = {
  branches: ['master'],
  plugins: [
    '@semantic-release/commit-analyzer',
    '@semantic-release/release-notes-generator',
    [
      '@semantic-release/changelog',
      {
        changelogFile: 'CHANGELOG.md',
      },
    ],
    [
      '@semantic-release/github',
      {
        assets: [
          'plugin/**/*',
          '!plugin/**/composer.json',
          '!plugin/**/composer.lock',
          '!plugin/**/package.json',
          '!plugin/**/package-lock.json',
          '!plugin/**/phpcs.xml',
          '!plugin/**/vite.config.js',
          '!plugin/**/node_modules/**',
          { path: 'plugin/', label: 'power-captcha-recaptcha-${nextRelease.version}' }
        ],
      },
    ],
    [
      '@semantic-release/git',
      {
        assets: [
          'package.json',
          'plugin/package.json',
          'plugin/power-captcha-recaptcha.php',
          'plugin/readme.txt',
          'plugin/composer.json',
          'CHANGELOG.md',
        ],
        message: 'chore(release): ${nextRelease.version} [skip ci]\n\n${nextRelease.notes}',
      },
    ],
  ],
};