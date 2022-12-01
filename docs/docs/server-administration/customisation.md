# Customisation

::: warning
We currently only support changing the layout via CSS. You can customise HTML files and templates, but they **will** be overwritten during updates, so make sure to [set up hooks](#running-commands-before-and-after-updates) to restore your changes after an update.
:::

## Adding a new theme

Create a new theme in `/usr/local/hestia/web/css/theme/custom/my_theme.css`

```css
.body-login,
.body-reset {
	height: auto;
	padding-top: 10%;
	background: rgb(231, 102, 194) !important;
	background: radial-gradient(circle, rgba(231, 102, 197, 1), rgba(174, 43, 177, 1)) !important;
}
```

## Customising a default theme

Changes to default themes are always overwritten during updates. However, via the `v-add-sys-theme` command, you can create a new theme.

If you want to edit the dark theme,

```bash
cp $HESTIA/install/deb/themes/dark.css /root/my_dark.css
```

Apply your changes and follow the instructions above.

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
