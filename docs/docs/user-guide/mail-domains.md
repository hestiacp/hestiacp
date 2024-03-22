# 邮件域介绍

要管理您的邮件域，请导航至 **邮件 <i class="fas fa-fw fa-mail-bulk"></i>** 选项卡。

## 添加邮件域

1. 单击 **<i class="fas fa-fw fa-plus-circle"></i> 添加邮件域** 按钮。
2. 输入您的域名。
3. 选择您要使用的选项。
4. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

## 编辑邮件域

1. 将鼠标悬停在要编辑的域上。
2. 单击邮件域右侧的<i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">编辑</span></i>图标。
3. 编辑字段。
4. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

## 暂停域名

1. 将鼠标悬停在要暂停的域上。
2. 单击邮件域右侧的<i class="fas fa-fw fa-pause"><span class="visually-hidden">暂停</span></i>图标。
3. 要取消暂停，请单击邮件域右侧的<i class="fas fa-fw fa-play"><span class="visually-hidden">取消暂停</span></i>图标。

## 删除域名

1. 将鼠标悬停在要删除的域上。
2. 单击邮件域右侧的<i class="fas fa-fw fa-trash"><span class="visually-hidden">删除</span></i>图标。 邮件域和**所有**邮件帐户都将被删除。

## 邮件域配置

### 网络邮件客户端

我们目前支持 Roundcube 和 SnappyMail（可选安装）。 您还可以禁用网络邮件访问。

### 捕获所有电子邮件

此电子邮件地址将接收该域发送给不存在的用户的所有电子邮件。

### 速率限制

::: 信息
此选项仅适用于管理员用户。
:::

设置帐户每小时可以发送的电子邮件数量限制。

### 垃圾邮件过滤器

::: 信息
此选项并不总是可用。
:::

为此域启用垃圾邮件刺客。

### 防病毒软件

::: 信息
此选项并不总是可用
:::

为此域启用 ClamAV。

### DKIM

为此域启用 DKIM。

### SSL

1. 选中**为此域启用 SSL** 框。
2. 选中 **使用 Let’s Encrypt 获取 SSL 证书** 框以使用 Let’s Encrypt。
3. 根据您的要求，您可以启用**启用自动 HTTPS 重定向**或**启用 HTTP 严格传输安全 (HSTS)**。
4. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

如果您想使用自己的 SSL 证书，可以在文本区域中输入 SSL 证书。

如果您在启用 Let's Encrypt 时遇到问题，请参阅我们的 [SSL 证书](../server-administration/ssl-certificates) 文档。

### SMTP 中继

此选项允许用户使用与服务器定义的不同的 SMTP 中继或绕过默认 Exim 路由。 这可以提高交付能力。

1. 选中 **SMTP 中继** 框，将出现一个表单。
2. 输入 SMTP 中继提供商提供的信息。

### 获取DNS记录

如果您不在 Hestia 中托管 DNS，但仍想使用其电子邮件服务，请点击 <i class="fas fa-atlas"><span class="visually-hidden">DNS</span></i> 图标可查看您需要添加到 DNS 提供商的 DNS 记录。

### 网络邮件

默认情况下，启用 SSL 后，可通过`https://webmail.domain.tld`或`https://mail.domain.tld`访问网络邮件。 否则请使用`http://`代替。

## 添加邮件帐户到域

1. 单击邮件域。
2. 单击**<i class="fas fa-fw fa-plus-circle"></i>添加邮件帐户**按钮。
3. 输入帐户名（不含`@domain.tld`部分）和密码。
4. （可选）提供将接收登录详细信息的电子邮件地址。
5. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

如果需要，您还可以修改**高级选项**，如下所述。

在右侧，您可以看到通过 SMTP、IMAP 和 POP3 访问您的邮件帐户的方法。

## 编辑邮件帐户

1. 将鼠标悬停在要编辑的帐户上。
2. 单击邮件帐户右侧的<i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">编辑</span></i>图标。
3. 编辑字段。
4. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

## 暂停邮件帐户

1. 将鼠标悬停在您要暂停的帐户上。
2. 单击邮件帐户右侧的<i class="fas fa-fw fa-pause"><span class="visually-hidden">暂停</span></i>图标。
3. 要取消暂停，请单击邮件帐户右侧的<i class="fas fa-fw fa-play"><span class="visually-hidden">取消暂停</span></i>图标。

## 删除邮件帐户

1. 将鼠标悬停在要删除的帐户上。
2. 单击邮件帐户右侧的<i class="fas fa-fw fa-trash"><span class="visually-hidden">删除</span></i>图标。

## 邮件账户配置

### 配额

允许帐户使用的最大空间。 这包括邮件、联系人等。

### 别名

添加别名以将邮件重定向到主帐户。 仅输入用户名。 例如：`alice`。

### 丢弃所有邮件

所有传入的邮件都不会被转发并被删除。

### 不存储转发的邮件

如果选择此选项，所有转发的邮件将被删除。

### 自动回复

设置自动回复。

### 转发邮件

将所有收到的邮件转发到输入的电子邮件地址。

::: 警告
许多垃圾邮件过滤器可能会默认将转发的邮件标记为垃圾邮件！
:::

### 速率限制

设置帐户每小时可以发送的电子邮件数量限制。
