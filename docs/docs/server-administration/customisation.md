# Customisation

::: warning
For user customisations, use custom CSS. You can customise HTML files and templates, but they **will** be overwritten during updates, so make sure to [set up hooks](#running-commands-before-and-after-updates) to restore your changes after an update.
:::

## Studio theme

Studio is an optional built-in theme with sidebar navigation, light and dark appearances, seven accent palettes, an administrator overview, and 43 interface dictionaries. The default theme remains unchanged.

1. Open your user profile (**Edit User**).
2. Select **studio** in **Theme** and save.
3. Reload the page. If necessary, perform a hard refresh.

The server default in **Server settings → Basic Options → Theme** and the account preference are separate. An existing account's theme can override the server default. **Set as selected theme for all users** intentionally applies the server choice to all accounts; it is not needed to enable Studio for yourself. Theme changes remain subject to Hestia's existing account policies.

Use the sidebar's **Light** / **Dark** buttons and the top bar's **Color palette** control. Appearance preferences are stored in the current browser. Translations, RTL layout and number/date formatting follow the effective account language. Arabic, Persian, Sorani Kurdish and Urdu use RTL layout.

The administrator overview uses existing authenticated Hestia routes. Historical load average is shown in load units, memory in MiB, and live CPU in percent. The live sample and historical load average are different measurements. No additional service, database, external request or scheduled task is needed. Native forms, permissions and administration actions remain in place.

To leave Studio, choose another installed theme in your profile. Built-in Studio files are part of Hestia's normal build and updates; do not edit generated files in `web/css/themes/` or `web/js/dist/` directly.

::: warning Migrating from the standalone community theme
The standalone Studio installer uses `css/themes/custom/studio.css` and `js/custom_scripts/studio.*` with a `studio-locales` directory. Do not run both integrations together. Before testing the built-in version, switch affected accounts and the server default to another theme, sign out of existing Studio sessions, and use the standalone project's documented uninstaller to remove its custom assets. Keep its backup. Do not remove unrelated custom scripts. Then select the built-in `studio` theme and reload. This migration is separate from a normal Hestia upgrade and has not been validated on a live installation.
:::

## Adding a new theme

Create a new theme in `/usr/local/hestia/web/css/themes/custom/my_theme.css`

```css
.page-login,
.page-reset {
	height: auto;
	padding-top: 10%;
	background: rgb(231, 102, 194) !important;
	background: radial-gradient(circle, rgba(231, 102, 197, 1), rgba(174, 43, 177, 1)) !important;
}
```

## Customising a default theme

Changes to default themes are always overwritten during updates. Custom CSS files can be uploaded to `/usr/local/hestia/web/css/custom` in `.css` or `.min.css` format.

Please note that `default.css` base theme is always loaded. Other default and custom themes override the rules in this file.

## Customising the _Domain not found_ page

The _Domain not found_ page is located in `/var/www/html/index.html`. You can edit it using the following command:

```bash
nano /var/www/html/index.html
```

## Customising the default domain skeleton structure

The default structure that will be added to a domain when it gets created is located in `/usr/local/hestia/data/templates/web/skel/public_html`.

## Running commands before and after updates

With the release of Hestia 1.4.6 we have added pre-install and post-install hooks. For example, you can use hooks to:

- Disable and enable demo mode before and after an update.
- Restore a customised skeleton page.

Hooks are located in one of the following files:

- `/etc/hestiacp/hooks/pre_install.sh`
- `/etc/hestiacp/hooks/post_install.sh`

::: tip
Don’t forget to make the file executable by running `chmod +x /etc/hestiacp/hooks/[file].sh`.
:::

For example, to disable demo mode on pre-install:

```bash /etc/hestiacp/hooks/pre_install.sh
#!/bin/bash
sed -i "s|^DEMO_MODE=.*'|DEMO_MODE='no'|g" $HESTIA/conf/hestia.conf
```

::: warning
If you use custom error documents you will have to rebuild all websites again!
:::
