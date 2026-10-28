// @ts-check
import { themes as prismThemes } from 'prism-react-renderer';

// Canonical site URL, e.g. https://laravel-sms.pages.dev. Set DOCS_URL in the
// Cloudflare Pages build settings; CF_PAGES_URL (the per-deployment preview
// URL) is used when it is missing.
const url = (process.env.DOCS_URL || process.env.CF_PAGES_URL || 'http://localhost:3000').replace(/\/$/, '');

const repo = 'https://github.com/shabayekdes/laravel-sms';

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'Laravel SMS',
  tagline: 'SMS and OTP gateway drivers for Laravel, behind one facade.',
  favicon: 'symbol.svg',
  url,
  baseUrl: '/',
  trailingSlash: false,

  onBrokenLinks: 'throw',
  onBrokenAnchors: 'throw',

  // Brand assets live in assets/branding and are served from the site root.
  staticDirectories: ['../assets/branding'],

  markdown: {
    // Plain .md files are parsed as CommonMark, so text like {code} is safe.
    format: 'detect',
    hooks: { onBrokenMarkdownLinks: 'throw' },
  },

  i18n: { defaultLocale: 'en', locales: ['en'] },

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: './sidebars.js',
          editUrl: `${repo}/edit/main/website/`,
          showLastUpdateTime: true,
        },
        blog: false,
        theme: { customCss: './src/css/custom.css' },
      }),
    ],
  ],

  themes: [
    [
      '@easyops-cn/docusaurus-search-local',
      /** @type {import('@easyops-cn/docusaurus-search-local').PluginOptions} */
      ({ hashed: true, indexBlog: false, highlightSearchTermsOnTargetPage: true }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      image: 'social-preview.png',
      metadata: [{ name: 'theme-color', content: '#2563EB' }],
      colorMode: { respectPrefersColorScheme: true },
      navbar: {
        logo: { alt: 'Laravel SMS', src: 'logo.svg', srcDark: 'logo-dark.svg' },
        items: [
          { type: 'docSidebar', sidebarId: 'guide', position: 'left', label: 'Guide' },
          { to: '/docs/changelog', label: 'Changelog', position: 'left' },
          { href: 'https://packagist.org/packages/shabayek/laravel-sms', label: 'Packagist', position: 'right' },
          { href: repo, label: 'GitHub', position: 'right' },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Docs',
            items: [
              { label: 'Getting started', to: '/docs/getting-started' },
              { label: 'Supported gateways', to: '/docs/gateways' },
              { label: 'Custom drivers', to: '/docs/custom-drivers' },
            ],
          },
          {
            title: 'Project',
            items: [
              { label: 'GitHub', href: repo },
              { label: 'Packagist', href: 'https://packagist.org/packages/shabayek/laravel-sms' },
              { label: 'Changelog', to: '/docs/changelog' },
            ],
          },
        ],
        copyright:
          'Copyright © Esmail Shabayek. Released under the MIT License. Laravel is a trademark of Taylor Otwell; this community package is not affiliated with or endorsed by Laravel.',
      },
      prism: {
        theme: prismThemes.github,
        darkTheme: prismThemes.vsDark,
        additionalLanguages: ['php', 'bash', 'json'],
      },
    }),
};

export default config;
