# Web Domains

To manage your web domains, navigate to the **Web <i class="fas fa-fw fa-globe-americas"></i>** tab.

## Adding a web domain

1. Click the **<i class="fas fa-fw fa-plus-circle"></i> Add Web Domain** button.
2. Enter the domain name in the **Domain** field.
   - If you wish to manage this domain’s DNS in Hestia, check the box labeled **Create DNS zone**
   - If you wish to enable mail for this domain, check the box labeled **Enable mail for this domain**.
3. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## Installing an app

1. Click the domain name or the <i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">edit</span></i> icon that appears on hover.
2. Click the **<i class="fas fa-fw fa-magic"></i> Quick install App** button in the top right.
3. Select the application you want to install and click the **Setup** button.
4. Fill out the fields. If the app uses a database, you will have the option to auto-create a database or use an existing one.
5. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

::: warning
Depending on the application you chose to install, this can take 30 seconds or longer. Do not reload or close the tab!
:::

## Editing a web domain

1. Click the domain name or the <i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">edit</span></i> icon that appears on hover.
2. Make your changes. The options are explained below.
3. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## Viewing access and error logs

1. Hover over the domain whose logs you want to view.
2. Click the <i class="fas fa-fw fa-binoculars"><span class="visually-hidden">logs</span></i> icon.
3. At the top of the page, you have the possibility to download the logs or view the error logs instead.

## Suspending a web domain

1. Hover over the domain you want to suspend.
2. Click the <i class="fas fa-fw fa-pause"><span class="visually-hidden">suspend</span></i> icon on the right of the web domain.

## Deleting a web domain

1. Hover over the domain you want to delete.
2. Click the <i class="fas fa-fw fa-trash"><span class="visually-hidden">delete user</span></i> icon on the right of the web domain. **Both** the web domain and the linked FTP accounts will get deleted.

## Web domain configuration

### Enabling statistics

1. Chose **awstats** in the selection boxed labelled **Web Statistics**.
2. If desired, enter a username and password.
3. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.
4. Navigate to `https://domain.tld/vstats/` to view the stats.

### Managing redirections

1. Check the **Enable domain redirection** box.
2. Select the option you want. When selecting **Redirect visitors to a custom domain or web address** you have to option to select the HTTP status code (301 by default).

::: warning
If your domain is an [internationalized domain name (IDN)](https://en.wikipedia.org/wiki/Internationalized_domain_name) containing special characters, even if you select `www.domain.tld` or `domain.tld`, it will convert the domain to [punycode](https://en.wikipedia.org/wiki/Punycode) and select **Redirect visitors to a custom domain or web address**.
:::

### Enabling SSL

1. Check the **Enable SSL for this domain** box.
2. Check the **Use Let’s Encrypt to obtain SSL certificate** box to use Let’s Encrypt.
3. Depending on your requirements, you can enable **Enable automatic HTTPS redirection** or **Enable HTTP Strict Transport Security (HSTS)**.
4. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

If you want to use your own SSL certificate you can enter the SSL certificate in the text area.

If you are having issues with enabling Let’s Encrypt, please refer to our [SSL certificates](../server-administration/ssl-certificates.md) documentation.

### Changing PHP version

::: info
This option is not always available. It may be disabled in the server settings. Please contact your server administrator for more information.
:::

1. Select the desired PHP version in the **Backend Template** field.

### Using a different root directory

1. Check the **Custom document root** box.
2. Select the domain name where you want this domain to point.
3. Select the path. For example, `/public/` will link to `/home/user/web/domain.tld/public_html/public/`.

### Additional FTP accounts

1. Check the **Additional FTP accounts** box.
2. Enter a username and a password (or generate one). The username will be prefixed by `user_`.
3. Enter the path the account will be able to access.
4. Optionally, provide an email address where the login details will be sent.

To add another FTP account, click the **Add FTP account** button, then click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

To delete an FTP account, click the **DELETE** link on the right of its name, then click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

To change the password, update the password field, then click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

### Proxy templates

::: info
Depending on the server setup, this option may not be available.
:::

- **default**: All purpose template. Suitable for most usecases.
- **caching**: Template with proxy cache enabled. Suitable for mostly static content, for example: blogs or news websites.
- **hosting**: Similar to default.

Any custom templates will also show up here.

::: tip
Any custom templates starting with `caching-` will allow the use of the **<i class="fas fa-fw fa-trash"></i> Purge Nginx Cache** button. Make sure a `.sh` file exists for `caching-my-template` with at least [this content](https://github.com/hestiacp/hestiacp/blob/main/install/deb/templates/web/nginx/caching.sh)
:::

### Web templates

For servers running Apache2 and Nginx, the **default** template will work fine.

For servers running Nginx only, pick the template matching the app name you are going to use.

### Managing Nginx caching

When Nginx caching is enabled (using FastCGI cache or with a caching-enabled template), you can purge the cache via the **<i class="fas fa-fw fa-trash"></i> Purge Nginx Cache** button.

When using Nginx only, you can enable FastCGI caching using the **Enable FastCGI Cache** box. When checked, an option is shown to determine for how long the cache is considered valid.
