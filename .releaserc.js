module.exports = {
  branches: ['main'],
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
      '@semantic-release/npm',
      {
        pkgRoot: './plugin',
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
      '@semantic-release/exec',
      {
        'prepareCmd': 'node ./scripts/update-version.js'
      }
    ],
  ],
};