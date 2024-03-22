# 网站域名

要管理您的网络域，请导航至 **Web <i class="fas fa-fw fa-globe-americas"></i>** 选项卡。

## 添加 Web 域

1. 单击 **<i class="fas fa-fw fa-plus-circle"></i> 添加 Web 域** 按钮。
2. 在**域**字段中输入域名。
    - 如果您希望在 Hestia 中管理此域的 DNS，请选中标有 **创建 DNS 区域** 的框
    - 如果您希望为此域启用邮件，请选中标有**为此域启用邮件**的框。
3. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

## 安装应用程序

1. 单击域名或悬停时出现的<i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">编辑</span></i>图标。
2. 单击右上角的**<i class="fas fa-fw fa-magic"></i>快速安装应用程序**按钮。
3. 选择您要安装的应用程序，然后单击“**安装**”按钮。
4. 填写字段。 如果应用程序使用数据库，您可以选择自动创建数据库或使用现有数据库。
5. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

：：： 警告
根据您选择安装的应用程序，这可能需要 30 秒或更长时间。 不要重新加载或关闭选项卡！
:::

## 编辑 Web 域

1. 单击域名或悬停时出现的<i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">编辑</span></i>图标。
2. 做出改变。 下面解释了这些选项。
3. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

## 查看访问和错误日志

1. 将鼠标悬停在要查看其日志的域上。
2. 单击<i class="fas fa-fw fa-binlinguals"><span class="visually-hidden">日志</span></i>图标。
3. 在页面顶部，您可以下载日志或查看错误日志。

## 暂停域名

1. 将鼠标悬停在要暂停的域上。
2. 单击网络域右侧的<i class="fas fa-fw fa-pause"><span class="visually-hidden">暂停</span></i>图标。

## 删除域名

1. 将鼠标悬停在要删除的域上。
2. 单击网络域右侧的<i class="fas fa-fw fa-trash"><span class="visually-hidden">删除用户</span></i>图标。 **网络域和链接的 FTP 帐户都将被删除。

## Web域配置

### 启用统计

1. 在标有 **Web Statistics** 的选择框中选择 **awstats**。
2. 如果需要，请输入用户名和密码。
3. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。
4. 导航到`https://domain.tld/vstats/`以查看统计信息。

### 管理重定向

1. 选中**启用域重定向**框。
2. 选择您想要的选项。 选择 **将访问者重定向到自定义域或网址** 时，您必须选择 HTTP 状态代码（默认为 301）。

：：： 警告
如果您的域名是包含特殊字符的[国际化域名 (IDN)](https://en.wikipedia.org/wiki/Internationalized_domain_name)，即使您选择“www.domain.tld”或“domain.tld”， 它将把域名转换为 [punycode](https://en.wikipedia.org/wiki/Punycode) 并选择**将访问者重定向到自定义域名或网址**
:::

### 启用 SSL

1. 选中**为此域启用 SSL** 框。
2. 选中 **使用 Let’s Encrypt 获取 SSL 证书** 框以使用 Let’s Encrypt。
3. 根据您的要求，您可以启用**启用自动 HTTPS 重定向**或**启用 HTTP 严格传输安全 (HSTS)**。
4. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

如果您想使用自己的 SSL 证书，可以在文本区域中输入 SSL 证书。

如果您在启用 Let's Encrypt 时遇到问题，请参阅我们的 [SSL 证书](../server-administration/ssl-certificates) 文档。

### 更改 PHP 版本

::: 信息
此选项并不总是可用。 它可能在服务器设置中被禁用。 请联系您的服务器管理员以获取更多信息。
:::

1. 在 **后端模板** 字段中选择所需的 PHP 版本。

### 使用不同的根目录

1. 选中**自定义文档根**框。
2. 选择您希望该域指向的域名。
3. 选择路径。 例如，`/public/`将链接到`/home/user/web/domain.tld/public_html/public/`。

### 其他 FTP 帐户

1. 选中 **其他 FTP 帐户** 框。
2. 输入用户名和密码（或生成一个）。 用户名将以`user_`为前缀。
3. 输入帐户能够访问的路径。
4. （可选）提供将发送登录详细信息的电子邮件地址。

要添加另一个 FTP 帐户，请单击 **添加 FTP 帐户** 按钮，然后单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

要删除 FTP 帐户，请单击其名称右侧的 **DELETE** 链接，然后单击 **<i class="fas fa-fw fa-save"></i> 保存** 按钮 右上。

要更改密码，请更新密码字段，然后单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

### 代理模板

::: 信息
根据服务器设置，此选项可能不可用。
:::

- **默认**：通用模板。 适用于大多数用例。
- **缓存**：启用代理缓存的模板。 适用于大多数静态内容，例如：博客或新闻网站。
- **托管**：与默认类似。

任何自定义模板也会显示在此处。

::: 提示
任何以“caching-”开头的自定义模板都将允许使用 **<i class="fas fa-fw fa-trash"></i> 清除 Nginx 缓存** 按钮。 确保“caching-my-template”存在一个 `.sh` 文件，其中至少包含[此内容](https://github.com/hestiacp/hestiacp/blob/main/install/deb/templates/web/nginx/caching.sh)
:::

### 网页模板

对于运行 Apache2 和 Nginx 的服务器，**默认**模板可以正常工作。

对于仅运行 Nginx 的服务器，选择与您要使用的应用程序名称匹配的模板。

### 管理 Nginx 缓存

启用 Nginx 缓存（使用 FastCGI 缓存或使用启用缓存的模板）时，您可以通过 **<i class="fas fa-fw fa-trash"></i> 清除 Nginx 缓存** 清除缓存 按钮。

仅使用 Nginx 时，您可以使用 **启用 FastCGI 缓存** 框启用 FastCGI 缓存。 选中后，会显示一个选项来确定缓存被视为有效的时间。
