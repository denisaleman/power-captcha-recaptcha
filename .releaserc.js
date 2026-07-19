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
          { path: 'plugin/power-captcha-recaptcha.php', label: 'WordPress Plugin' },
          { path: 'plugin/readme.txt', label: 'Readme file' },
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