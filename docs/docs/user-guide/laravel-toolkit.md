# Laravel Toolkit

Laravel Toolkit helps you manage Laravel applications that are installed on Hestia web domains. It registers Laravel apps, shows their runtime status, and provides common Laravel maintenance tools without requiring SSH for every task.

To open it, navigate to the **Web <i class="fas fa-fw fa-globe-americas"></i>** tab, open a web domain, and click **Laravel** when an app is detected or registered. You can also open the Laravel app list from `/list/laravel/`.

## Requirements

A Laravel app must have these files in its application root:

- `artisan`
- `composer.json`
- `public/index.php`

For new installs, use **Quick install App** and select Laravel. Hestia will install the Laravel skeleton, set the Laravel web template, and register the app with Laravel Toolkit.

For existing apps, place the project inside the web domain directory, then scan or register it from the Laravel Toolkit commands. The default app root is:

```bash
/home/user/web/domain.tld/public_html
```

::: warning
Laravel projects should not expose the project root to web traffic. The Laravel web template serves the app from `<app_root>/public`.
:::

## Dashboard

The Dashboard tab gives a quick status overview:

- PHP version
- Application root and public path
- `APP_ENV` and `APP_DEBUG`
- Maintenance mode
- Scheduler status
- Queue worker status
- Git branch and commit when available

It also shows recommended next actions, such as disabling debug mode before production traffic or enabling the scheduler when the app uses scheduled tasks.

## Running commands

Use the command tabs to run common Laravel maintenance commands as the web domain owner inside the registered app root.

- **Artisan**: run `php artisan` commands such as `about`, `migrate --force`, `optimize`, `config:clear`, and `queue:restart`.
- **Composer**: run Composer commands such as `install --no-dev --optimize-autoloader`, `update`, and `dump-autoload`.
- **Node.js**: run `npm`, `yarn`, or `pnpm` commands such as `install`, `run build`, and `run dev`.

Command output is shown in a bounded console panel with the command status and timestamp.

::: info
Node.js support uses the package managers already installed on the server. Laravel Toolkit does not install Node.js or manage Node versions.
:::

## Deployment

The Deployment tab provides manual deployment and webhook deployment access.

Manual deployment runs the registered deployment script for the app. By default, the deploy scenario is intended to:

- Pull Git changes when a repository is registered.
- Install Composer dependencies for production.
- Install and build Node assets when `package.json` exists.
- Run migrations with `--force`.
- Rebuild Laravel caches and restart queues.

Webhook deployment uses a secret URL. Keep this URL private and rotate the app registration if the secret is exposed.

::: warning
Anyone with the webhook URL can trigger deployment for the registered app. Treat it like a password.
:::

## Environment variables

The Environment tab lets you edit the app’s `.env` file.

::: warning
The `.env` file may contain database passwords, API keys, and other secrets. Only open or edit it when you trust the current panel session and user.
:::

After saving `.env`, reload the Laravel Toolkit page to confirm the Dashboard `APP_ENV` and `APP_DEBUG` summary values changed as expected.

## Scheduler

Use the Scheduler tab to enable or disable Laravel’s schedule runner for the app. When enabled, Hestia manages one cron job for:

```bash
php artisan schedule:run
```

Enable this only when your Laravel app uses scheduled tasks.

## Queues and failed jobs

Use the Queue tab to manage a Laravel queue worker for the app. You can configure:

- Queue connection
- Timeout
- Max jobs
- Max time
- Stop when empty

Failed jobs are shown in the Queue tab. You can retry or flush failed jobs using Laravel’s native queue commands through Hestia.

## Logs

The Logs tab shows recent Laravel log lines from `storage/logs/laravel*.log` inside the registered app root. Logs are displayed in a fixed-height console so large stack traces do not stretch the whole page.

## CLI usage

Laravel Toolkit can also be managed from the command line. Common examples:

```bash
v-scan-laravel-app admin example.com
v-list-laravel-app admin example.com shell
v-run-laravel-artisan admin example.com about
v-change-laravel-maintenance admin example.com yes
v-change-laravel-scheduler admin example.com yes
v-change-laravel-queue admin example.com yes database 60 0 0 no
v-deploy-laravel-app admin example.com manual
```

See the [CLI reference](../reference/cli) for the full command list and options.
