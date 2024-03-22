# DNS

要管理您的 DNS 区域和记录，请导航至 **DNS <i class="fas fa-fw fa-atlas"></i>** 选项卡。

## 添加 DNS 区域

1. 单击 **<i class="fas fa-fw fa-plus-circle"></i> 添加 DNS 区域** 按钮。
2. 在**域**字段中输入域名。
    - 为区域选择适当的模板。
    - 如果域需要不同的名称服务器，请在 **高级选项** 部分中更改它们。
3. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

## 编辑 DNS 区域

1. 将鼠标悬停在要编辑的区域上。
2. 单击区域域右侧的<i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">编辑</span></i>图标。
3. 进行所需的更改。
4. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

## 查看 DNSSEC 公钥

1. 将鼠标悬停在您要查看其 DNSSEC 密钥的区域上。
2. 单击区域域右侧的 <i class="fas fa-fw fa-key"><span class="visually-hidden">DNSSEC</span></i> 图标。

## 暂停 DNS 区域

1. 将鼠标悬停在要暂停的区域上。
2. 单击区域域右侧的<i class="fas fa-fw fa-pause"><span class="visually-hidden">暂停</span></i>图标。
3. 要取消暂停，请单击区域域右侧的<i class="fas fa-fw fa-play"><span class="visually-hidden">取消暂停</span></i>图标。

## 删除 DNS 区域

1. 将鼠标悬停在要删除的区域上。
2. 单击区域域右侧的<i class="fas fa-fw fa-trash"><span class="visually-hidden">删除</span></i>图标。

## DNS 区域配置

### IP地址

应用于根域的 IP 地址。

### 模板

- **默认**：标准 DNS 模板。 适用于大多数用例。
- **default-nomail**：标准 DNS 模板。 当您不想在 Hestia 上托管邮件时，适合大多数用例。
- **gmail**：当您的电子邮件提供商是 Google Workspace 时。
- **office365**：当您的电子邮件提供商是 Microsoft 365 (Exchange) 时。
- **zoho**：当您的电子邮件提供商是 Zoho 时。
- **child-ns**：当您打算使用域作为名称服务器时。

### 截止日期

此日期不被 Hestia 使用，但可以用作提醒。

### 面向服务架构

授权起始点 (SOA) 记录包含有关您的区域的管理信息，如域名系统 (DNS) 所定义。

### TTL

调整默认生存时间。 较短的 TTL 意味着更快的更改，但会导致向服务器发出更多请求。 如果您要更改 IP，将其减少到 300 秒（5 分钟）可能会有所帮助。

### DNSSEC

启用 DNSSEC 以提高安全性。 但是，此设置需要在您的域名注册商处进行一些更改才能生效。 有关更多信息，请参阅 [DNS 集群](../server-administration/dns) 文档。

## 添加 DNS 记录到区域

1. 单击 **<i class="fas fa-fw fa-plus-circle"></i> 添加记录** 按钮。
2. 填写字段。
3. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

## 编辑 DNS 记录

1. 单击记录器悬停时出现的<i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">编辑</span></i>图标。
2. 进行所需的更改。
3. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

## 暂停 DNS 记录

1. 将鼠标悬停在要暂停的记录上。
2. 单击记录域右侧的<i class="fas fa-fw fa-pause"><span class="visually-hidden">暂停</span></i>图标。
3. 要取消暂停，请单击记录域右侧的<i class="fas fa-fw fa-play"><span class="visually-hidden">取消暂停</span></i>图标。

## 删除DNS记录

1. 将鼠标悬停在要删除的记录上。
2. 单击记录域右侧的<i class="fas fa-fw fa-trash"><span class="visually-hidden">删除</span></i>图标。

## DNS记录配置

### 记录

记录名称。 `记录`.domain.tld。 使用`@`作为根。

### 类型

支持以下记录类型：

- A
- AAAA
- CAA
- CNAME
- DNSKEY
- IPSECKEY
- KEY
- MX
- NS
- PTR
- SPF
- SRV
- TLSA
- TXT

### IP 或值

您要使用的记录的 IP 或值。

### 优先事项

记录的优先级。 仅用于 MX 记录

### TTL

调整默认生存时间。 较短的 TTL 意味着更快的更改，但会导致向服务器发出更多请求。 如果您要更改 IP，将其减少到 300 秒（5 分钟）可能会有所帮助。
