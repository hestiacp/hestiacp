# Web Templates and FastCGI/Proxy Cache

## How do web templates work?

::: warning
Modifying templates could cause errors on the server and may cause some services to not be able to reload or start.
:::

Every time you rebuild the user or domain, the config files of the domain are overwritten by the new templates.

This happens when:

- HestiaCP is updated.
- The admin initiates it.
- The user modifies settings.

The templates can be found in `/usr/local/hestia/data/templates/web/`.

| Service                 | Location                                              |
| ----------------------- | ----------------------------------------------------- |
| Nginx (Proxy)           | /usr/local/hestia/data/templates/web/nginx/           |
| Nginx - PHP FPM         | /usr/local/hestia/data/templates/web/nginx/php-fpm/   |
| Apache2 (Legacy/modphp) | /usr/local/hestia/data/templates/web/apache2/         |
| Apache2 - PHP FPM       | /usr/local/hestia/data/templates/web/apache2/php-fpm/ |
| PHP-FPM                 | /usr/local/hestia/data/templates/web/php-fpm/         |

::: warning
Avoid modifying default templates as they are overwritten by updates. To prevent that, copy them instead:

```bash
cp original.tpl new.tpl
cp original.stpl new.stpl
cp original.sh new.sh
```

:::

When you are done editing your template, enable it for the desired domain from the control panel.

After modifying an existing template, you need to rebuild the user configuration. This can be done using the [v-rebuild-user](../reference/cli.md#v-rebuild-user) command or the bulk operation in the web interface..

### Available variables

| Name                 | Description                                           | Example                                    |
| -------------------- | ----------------------------------------------------- | ------------------------------------------ |
| `%ip%`               | IP Address of Server                                  | `123.123.123.123`                          |
| `%proxy_port%`       | Port of Proxy                                         | `80`                                       |
| `%proxy_port_ssl%`   | Port of Proxy (SSL)                                   | `443`                                      |
| `%web_port%`         | Port of Webserver                                     | `8080`                                     |
| `%web_ssl_port%`     | Port of Webserver (SSL)                               | `8443`                                     |
| `%domain%`           | Domain                                                | `domain.tld`                               |
| `%domain_idn%`       | Domain (Internationalised)                            | `domain.tld`                               |
| `%alias_idn%`        | Alias Domain (Internationalised)                      | `alias.domain.tld`                         |
| `%docroot%`          | Document root of domain                               | `/home/username/web/public_html/`          |
| `%sdocroot%`         | Private root of domain                                | `/home/username/web/public_shtml/`         |
| `%ssl_pem%`          | Location of SSL Certificate                           | `/usr/local/hestia/data/user/username/ssl` |
| `%ssl_key%`          | Location of SSL Key                                   | `/usr/local/hestia/data/user/username/ssl` |
| `%web_system%`       | Software used as web server                           | `Nginx`                                    |
| `%home%`             | Default home directory                                | `/home`                                    |
| `%user%`             | Username of current user                              | `username`                                 |
| `%backend_lsnr%`     | Your default FPM Server                               | `proxy:fcgi://127.0.0.1:9000`              |
| `%proxy_extentions%` | Extensions that should be handled by the proxy server | A list of extensions                       |

::: tip
`%sdocroot%` can also be set to `%docroot%` with settings
:::

## How can I change settings for a specific domain

With the switch to PHP-FPM there are currently 2 different ways:

1. Using `.user.ini` in the home directory `/home/user/web/domain.tld/public_html`.
2. Via the PHP-FPM pool config.

Config templates for the PHP pool can be found in `/usr/local/hestia/data/templates/web/php-fpm/`.

::: warning
Due to the fact we use multi PHP we need to recognise the PHP version to be used. Therefore we use the following naming scheme: `YOURNAME-PHP-X_Y.tpl`, where X_Y is your PHP version.

For example a PHP 8.1 template would be `YOURNAME-PHP-8_1.tpl`.
:::

## Installing PHP modules

```bash
apt install php-package-name
```

For example, the following command will install `php-memcached` and `php-redis`, including the required additional packages for PHP.

```bash
apt install php-memcached php-redis
```

## Nginx FastCGI Cache

::: tip
FastCGI only applies for Nginx + PHP-FPM servers. If you use Nginx + Apache2 + PHP-FPM, this will not apply to you!
:::

FastCGI cache is an option within Nginx allowing to cache the output of FastCGI (in this case PHP). It will temporarily create a file with the contents of the output. If another user requests the same page, Nginx will check if the age of the cached file is still valid and if it is, then it will send the cached file to the user, instead of requesting it to FastCGI.

FastCGI cache works best for sites get a lot of request and where the pages don’t change that often, for example a news site. For more dynamic sites, changes might be required to the configuration or it might require totally disabling it.

### Why does software package x and y not work with FastCGI cache

As we have over 20 different templates and we don’t use them all, we have decided to stop releasing new ones the future and hope the community helps improving the templates by [submitting a Pull Request](https://github.com/hestiacp/hestiacp/pulls).

If you want to add support to a certain template, follow the instructions below.

### How do I enable FastCGI cache for my custom template

Locate the block where you call `fastcgi_pass`:

```bash
location ~ [^/]\.php(/|$) {
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    try_files $uri =404;
    fastcgi_pass    %backend_lsnr%;
    fastcgi_index   index.php;
    include         /etc/nginx/fastcgi_params;
}
```

Add the following lines under `include /etc/nginx/fastcgi_params;`:

```bash
include %home%/%user%/conf/web/%domain%/nginx.fastcgi_cache.conf*;

if ($request_uri ~* "/path/with/exceptions/regex/whatever") {
    set $no_cache 1;
}
```

### How can I clear the cache?

When FastCGI cache is enabled a **<i class="fas fa-fw fa-trash"></i> Purge Nginx Cache** button is added to the web domain’s **Edit** page. You can also use the API, or the following command:

```bash
v-purge-nginx-cache user domain.tld
```

### Why don’t I have the option to use FastCGI cache

FastCGI cache is an option for Nginx mode only. If you are using Nginx + Apache2, you can select the proxy caching template and proxy cache will be enabled. It is functionally almost the same. In fact, the proxy caching will also work if you use a Docker image or a Node.js app.

To write custom caching templates, use the following naming scheme:

`caching-yourname.tpl`, `caching-yourname.stpl` and `caching-yourname.sh`

### Does Hestia support Web socket support

Yes, Hestia works fine with Web sockets how ever our default templates include on default:

```bash
proxy_hide_header Upgrade
```

This resolved an issue with Safari from loading websites.

To allow the use of Web sockets remove this line. Other wise Web sockets will not work
