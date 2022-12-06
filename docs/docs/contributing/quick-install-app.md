# Quick install app

One of Hestia’s most requested feature is to add support for Softaculous. However, due to the required use of Ioncube in hestia-php and because we are against the use of proprietary software, we have instead developed our own **Quick install app** solution.

More information can be found in the [hestia-quick-install repo](https://github.com/hestiacp/hestia-quick-install/blob/main/Example/ExampleSetup.php)

## Creating a new app

1. Make a new folder called `Example` in `/usr/local/hestia/web/src/app/WebApp/Installers/`
2. Create a file named `ExampleSetup.php`.
3. Copy the [example file’s content](https://github.com/hestiacp/hestia-quick-install/blob/main/Example/ExampleSetup.php) into your new file.

This will add an app called “Example” when you open the **Quick install app** page.

## Info

The following settings are required to display the info on the **Quick install app** list:

- Name: Display name of the application. Please be aware that the naming of your app should follow the following regex: `[a-zA-Z][a-zA-Z0,9]`. Otherwise, it will not register as a working app!
- Group: Currently not used, but we might add features that use it in the future. Currently used: `cms`, `ecommerce`, `framework`.
- Enabled: Whether or not to show the app in the **Quick install app** page. Default set to `true`.
- Version: `x.x.x` or `latest`.
- Thumbnail: The image file for the app icon, include it in the same folder. The max size is 300px by 300px.

## Settings

### Form fields

The following fields are available:

- Text input
- Selection dropdown
- Checkbox
- Radio button

Since this is quite a complex feature, please check our existing apps for usage examples.

### Database

Flag to enable database auto-creation. If enabled, a checkbox is shown, allowing the user to automatically create a new database, as well as the 3 following fields:

- Database Name
- Database User
- Database Password

### Downloading the app’s source code

Currently the following methods of download are supported:

- Download a archive from a URL.
- Via [Composer](https://getcomposer.org).
- Via [WP-CLI](https://wp-cli.org).

### Server settings

Enables you to set app requirements and web server templates. For example, some apps require a specific Nginx template or will only run on PHP 8.0 or higher.

- Nginx: Template used for Nginx + PHP-FPM setup.
- Apache2: Template used for Apache2 setup. Can be usually be omitted.
- PHP version: Array of all supported PHP versions.

## Installing the web application

There are multiple ways to install and configure the web app after it is has been downloaded.

- Manipulation of config files.
- Run commands. For example, use `drush` to install [Drupal](https://github.com/hestiacp/hestiacp/blob/88598deb49cec6a39be4682beb8e9b8720d59c7b/web/src/app/WebApp/Installers/Drupal/DrupalSetup.php#L56-L65).
- Using curl to provide configure the app over HTTP.

::: warning
To prevent any issues, make that all commands are executed as the user, instead of `root` or `admin`. All the commands that are supplied by HestiaCP do this by default.
:::

## Sharing

Once you are done, you can [submit a Pull Request](https://github.com/hestiacp/hestiacp/pulls) and we will review the code. If it meets our standards, we will include in the next release.
