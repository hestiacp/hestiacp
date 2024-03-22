# Web 模板和 FastCGI/代理缓存

## 网页模板如何工作？

：：： 警告
修改模板可能会导致服务器出现错误，并可能导致某些服务无法重新加载或启动。
:::

每次重建用户或域时，域的配置文件都会被新模板覆盖。

出现这种情况时：

- HestiaCP 已更新。
- 管理员启动它。
- 用户修改设置。

模板可以在`/usr/local/hestia/data/templates/web/`中找到。

| 服务                    | 路径                                                   |
| ----------------------- | ----------------------------------------------------- |
| Nginx (Proxy)           | /usr/local/hestia/data/templates/web/nginx/           |
| Nginx - PHP FPM         | /usr/local/hestia/data/templates/web/nginx/php-fpm/   |
| Apache2 (Legacy/modphp) | /usr/local/hestia/data/templates/web/apache2/         |
| Apache2 - PHP FPM       | /usr/local/hestia/data/templates/web/apache2/php-fpm/ |
| PHP-FPM                 | /usr/local/hestia/data/templates/web/php-fpm/         |

::: 警告
避免修改默认模板，因为它们会被更新覆盖。 为了防止这种情况，请复制它们：

```bash
cp original.tpl new.tpl

cp original.stpl new.stpl

cp original.sh new.sh
```

:::

编辑完模板后，从控制面板为所需的域启用它。

修改现有模板后，需要重建用户配置。 这可以使用 [v-rebuild-user](../reference/cli#v-rebuild-user) 命令或 Web 界面中的批量操作来完成。

### 可用变量

| 名称                 | 描述                                           | 示例                                         |
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
| `%proxy_extentions%` | 应由代理服务器处理的扩展                                 | 扩展列表                      |

::: 提示
`%sdocroot%` 也可以通过设置设置为 `%docroot%`
:::

## 如何更改特定域的设置

切换到 PHP-FPM 目前有 2 种不同的方式：

1. 使用主目录`/home/user/web/domain.tld/public_html`中的`.user.ini`。
2. 通过 PHP-FPM 池配置。

PHP 池的配置模板可以在 `/usr/local/hestia/data/templates/web/php-fpm/` 中找到。

：：： 警告
由于我们使用多 PHP，我们需要识别要使用的 PHP 版本。 因此，我们使用以下命名方案：`YOURNAME-PHP-X_Y.tpl`，其中 `X_Y`是您的 PHP 版本。

例如，PHP 8.1 模板将为`YOURNAME-PHP-8_1.tpl`。
:::

```bash
apt install php-package-name
```

例如，以下命令将安装`php-memcached”和“php-redis`，包括 PHP 所需的附加包。

```bash
apt install php-memcached php-redis
```

## Nginx FastCGI 缓存

：：： 提示
FastCGI仅适用于Nginx + PHP-FPM服务器。 如果您使用 Nginx + Apache2 + PHP-FPM，则这不适用于您！
:::

FastCGI 缓存是 Nginx 中的一个选项，允许缓存 FastCGI 的输出（在本例中为 PHP）。 它将临时创建一个包含输出内容的文件。 如果另一个用户请求同一页面，Nginx 将检查缓存文件的年龄是否仍然有效，如果有效，则将缓存文件发送给该用户，而不是向 FastCGI 请求。

FastCGI 缓存最适合收到大量请求且页面不经常更改的网站，例如新闻网站。 对于更多动态站点，可能需要更改配置，或者可能需要完全禁用它。

### 为什么软件包 x 和 y 不能与 FastCGI 缓存一起使用

由于我们有 20 多个不同的模板，并且我们不会全部使用它们，因此我们决定将来停止发布新模板，并希望社区通过[提交 Pull 请求](https://github.com/hestiacp/hestiacp/pulls).

如果您想添加对某个模板的支持，请按照以下说明操作。

### 如何为我的自定义模板启用 FastCGI 缓存

找到调用“fastcgi_pass”的块：

```bash
location ~ [^/]\.php(/|$) {
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    try_files $uri =404;
    fastcgi_pass    %backend_lsnr%;
    fastcgi_index   index.php;
    include         /etc/nginx/fastcgi_params;
}
```

在 `include /etc/nginx/fastcgi_params;` 下添加以下行：

```bash
include %home%/%user%/conf/web/%domain%/nginx.fastcgi_cache.conf*;

if ($request_uri ~* "/path/with/exceptions/regex/whatever") {
    set $no_cache 1;
}
```

### 如何清除缓存？

启用 FastCGI 缓存后，**<i class="fas fa-fw fa-trash"></i> 清除 Nginx 缓存** 按钮将添加到 Web 域的 **编辑** 页面。 您还可以使用API或以下命令：

```bash
v-purge-nginx-cache user domain.tld
```

### 为什么我没有使用 FastCGI 缓存的选项

FastCGI 缓存仅适用于 Nginx 模式。 如果您使用的是Nginx + Apache2，则可以选择代理缓存模板并启用代理缓存。 它的功能几乎相同。 事实上，如果您使用 Docker 映像或 Node.js 应用程序，代理缓存也将起作用。

要编写自定义缓存模板，请使用以下命名方案：

`caching-yourname.tpl`、`caching-yourname.stpl` 和 `caching-yourname.sh`

### Hestia 是否支持 Web 套接字支持

是的，Hestia 可以很好地与 Web 套接字配合使用，但我们的默认模板默认包含以下内容：

```bash
proxy_hide_header Upgrade
```

这解决了 Safari 加载网站的问题。

要允许使用 Web 套接字，请删除此行。 否则 Web 套接字将无法工作
