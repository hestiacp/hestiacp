# Developing the Studio theme

Studio is an optional presentation layer. It enhances Hestia's rendered pages rather than introducing another administration backend. The integration is based on [Hestia Studio 2.1.1](https://github.com/OnurFICICI/hestia-studio/tree/v2.1.1), licensed GPL-3.0-or-later. Its dictionaries incorporate translations by Hestia Control Panel and its translation contributors, also under GPL-3.0-or-later.

## Source and loading

| Source                                         | Purpose                                                                    |
| ---------------------------------------------- | -------------------------------------------------------------------------- |
| `web/css/src/themes/studio.css`                | Built by Lightning CSS into the existing built-in theme directory          |
| `web/js/src/studio.js`                         | Separate esbuild entry; not included in the default JavaScript bundle      |
| `web/templates/includes/studio.php`            | Authenticated presentation context with escaped JSON and read-only metrics |
| `web/templates/includes/studio-locales/*.json` | 43 Studio dictionaries; no changes to the native gettext catalogs          |

The existing CSS loader and theme-list command discover `studio.min.css`. The JavaScript include loads the context and separate bundle only for an authenticated account whose effective theme is `studio`. Other themes and the existing custom-script hook retain their behavior. Asset URLs use Hestia's existing cache version. The standard package build copies the generated assets, PHP include and dictionaries with the rest of `web/`.

The PHP context checks administrator, impersonation and hide-services policies before including host metrics. The existing controllers remain the authorization boundary. Linux CPU ticks, memory and root filesystem space are sampled only on the permitted services overview. There are no shell commands or new endpoints. Statistics expose only numeric disk/bandwidth series. JavaScript uses a fixed same-origin route allowlist, rejects redirects and checks the account context of auxiliary pages.

The first plain administrator Users visit opens Overview once per session. Deep links, POST requests, later Users visits and impersonated sessions keep their normal destination. The only session addition is `hestia_studio_landing_seen`; account and server preferences are not rewritten. Form controls retain their original forms and Alpine ancestors. Hestia's logo and configured application name are preserved.

## Development checks

Install dependencies using the repository's contribution instructions, then run:

```sh
npm run test:studio
npm run build
php -l web/templates/includes/studio.php
php -l web/templates/includes/js.php
```

The tests require Node.js, PHP CLI and the repository's existing `jsdom` development dependency. They do not connect to a server or execute administration commands. The Test workflow runs them on Ubuntu 24.04 and verifies the built-in assets.

Coverage includes theme isolation, custom-hook preservation, all dictionary keys, account/server language mismatches in both directions, RTL, escaped context data, role/policy combinations, load-average scaling, missing samples, CPU/memory units, form ownership and values, CSRF preservation, Alpine ancestry, validation navigation, seven palettes, light/dark mode, the mobile menu, and duplicate initialization.

Use a disposable development VM for integration testing, following [Hestia's development guide](development.md). Check both Studio and the default theme, administrator and normal accounts, impersonation, restricted service policies, supported languages, and native add/edit/list workflows. Do not run Hestia's administrative test suite on a production server.

## Validation scope

The standalone Studio 2.1.1 release was reported installed and working on Ubuntu 24.04 / HestiaCP 1.10.9 on 2026-11-24. That report covered all 46 installed theme files matching the release, corrected load-average scale and Turkish display. Earlier checks also covered appearance controls, the mobile menu and opening server settings.

Those results do not certify this built-in integration on the current Hestia development branch. Live account/server language mismatch checks, a complete administration regression, standalone-to-built-in migration, package upgrade and rollback remain integration-test work. First-install restoration in the standalone project does not establish upgrade rollback. No production data or screenshots are included in these tests.

## Translations

Keep the same keys in all 43 JSON dictionaries. Empty account language falls back to the server language; invalid or unavailable dictionaries keep the gettext/English label fallback and English formatting. The browser's presentation language may change without writing either preference. Native forms continue to use Hestia's gettext catalogs. Human review of translations remains valuable.
