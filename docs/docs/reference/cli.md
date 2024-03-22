# CLI命令行介绍

::: warning 温馨提示
自从*T爆出获取用户信息之后偶然间发现了这个服务器管理面板，由于作者对英文不友好，刚开始安装遇到了很多热心肠的人帮助。在此感谢各位。至此有了这个中文翻译站的建立。本站不提供任何数据软件包，仅支持web网站中文，如果你需要繁体中文请提交更新文件。Hestia集成开发了大量的CLI命令行，如果你是一个初学开发者建议你仔细阅读本章手册文档，（PS:高手不在本章讨论范围。）相信对你开发服务器及网页程序系统和部署其它商业性软件有不小的帮助。如你在使用中发现部分命令与描述不符，请在git修改更新或者在中文论坛发帖反馈也可。
其他错误翻译也麻烦提交GIT更新或者发帖反馈一下！感谢你对本站的翻译支持。
:::

## 添加/修改用户权限及其它配置系列命令

命令: v-acknowledge-user-notification

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-acknowledge-user-notification)

更新用户通知

**选项**: `USER` `NOTIFICATION`

此功能更新用户通知。

命令: v-add-access-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-access-key)

生成访问密钥

**选项**: `USER` `[PERMISSIONS]` `[COMMENT]` `[FORMAT]`

**示例**:

```bash
v-add-access-key admin v-purge-nginx-cache,v-list-mail-accounts comment json
```

“PERMISSIONS”参数仅对管理员用户是可选的。
该函数创建一个密钥文件`/HESTIA/data/access-keys/`

命令: v-add-backup-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-backup-host)

添加备份主机

**选项**: `TYPE` `HOST` `USERNAME` `PASSWORD` `[PATH]` `[PORT]`

**示例**:

```bash
v-add-backup-host sftp backup.acme.com admin p4$$w@Rd
v-add-backup-host b2 bucketName keyID applicationKey
```

添加新的远程备份位置。 目前支持 SFTP、FTP 和 Backblaze

命令: v-add-cron-hestia-autoupdate

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-cron-hestia-autoupdate)

添加 cron 作业以实现 hestia 自动更新

**选项**: `MODE`

该功能添加了一个用于 hestia 自动更新的 cronjob
可以从 apt 或 git 下载。

命令: v-add-cron-job

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-cron-job)

添加计划任务

**选项**: `USER` `MIN` `HOUR` `DAY` `MONTH` `WDAY` `COMMAND` `[JOB]` `[RESTART]`

**示例**:

```bash
v-add-cron-job admin * * * * * sudo /usr/local/hestia/bin/v-backup-users
```

该函数向 cron 守护进程添加一个作业。 执行命令时，任何输出
如果参数 REPORTS 设置为“yes”，则会邮寄到用户的电子邮件。

命令: v-add-cron-letsencrypt-job

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-cron-letsencrypt-job)

为 Let's Encrypt 证书添加 cron 作业

**选项**: –

此函数为 Let's Encrypt 添加了一个新的 cron 作业。

命令: v-add-cron-reports

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-cron-reports)

添加 cron 报告

**选项**: `USER`

**示例**:

```bash
v-add-cron-reports admin
```

此功能用于启用有关 cron 任务和管理的报告
通知。

命令: v-add-cron-restart-job

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-cron-restart-job)

添加 cron 报告

**选项**: –

此功能用于启用重新启动 cron 任务

命令: v-add-database

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-database)

添加数据库

**选项**: `USER` `DATABASE` `DBUSER` `DBPASS` `[TYPE]` `[HOST]` `[CHARSET]`

**示例**:

```bash
v-add-database admin wordpress_db matt qwerty123
```

`admin` 这是运行命令的用户或角色，`wordpress_db`这是新数据库的名称`matt` 这是新数据库的用户名。`qwerty123`这是与`matt`用户名关联的密码。

该函数创建了连接 username 和 user_db 的数据库。您可以使用 v-list-sys-config 脚本获取支持的数据库类型。

命令: v-add-database-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-database-host)

添加新的数据库服务器

**选项**: `TYPE` `HOST` `DBUSER` `DBPASS` `[MAX_DB]` `[CHARSETS]` `[TEMPLATE]` `[PORT]`

**示例**:

```bash
v-add-database-host mysql localhost alice p@$$wOrd
```

此函数将新的数据库服务器添加到服务器池中。 它支持本地
和远程数据库服务器，这对于集群很有用。 通过添加主机
您可以设置主机上数据库数量的限制。 模板参数为
仅用于 PostgreSQL，并具有默认值“template1”。 你可以阅读
有关模板的更多信息，请参见 PostgreSQL 官方文档。

命令: v-add-database-temp-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-database-temp-user)

添加临时数据库用户

**选项**: `USER` `DATABASE` `[TYPE]` `[HOST]` `[TTL]`

**示例**:

```bash
v-add-database-temp-user wordress wordpress_db mysql
```

该函数创建一个临时数据库用户 mysql_sso_db_XXXXXXXX 和一个随机密码
用户的有效性有限，仅授予对特定数据库的访问权限
返回 json 供 SSO 脚本读取

命令: v-add-dns-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-dns-domain)

添加 DNS 网站

**选项**: `USER` `DOMAIN` `IP` `[NS1]` `[NS2]` `[NS3]` `[NS4]` `[NS5]` `[NS6]` `[NS7]` `[NS8]` `[RESTART]`

**示例**:

```bash
v-add-dns-domain admin example.com ns1.example.com ns2.example.com '' '' '' '' '' '' yes
```

此功能添加具有模板中定义的记录的 DNS 区网站。 如果不输入说明参数， 默认情况下是第一个使用用户的 NS 服务器。 TTL 设置为该区网站和所有区网站通用其记录的默认值为 14400 秒。

命令: v-add-dns-on-web-alias

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-dns-on-web-alias)

在 Web 网站别名添加 dns 网站或 dns 记录

**选项**: `USER` `ALIAS` `IP` `[RESTART]`

**示例**:

```bash
v-add-dns-on-web-alias admin www.example.com 8.8.8.8
```

该功能根据Web网站别名添加dns网站或dns记录。

命令: v-add-dns-record

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-dns-record)

添加DNS记录

**选项**: `USER` `DOMAIN` `RECORD` `TYPE` `VALUE` `[PRIORITY]` `[ID]` `[RESTART]` `[TTL]`

**示例**:

```bash
v-add-dns-record admin acme.com www A 162.227.73.112
```

该函数用于添加新的DNS记录。 TXT、MX 等复杂记录SRV 类型可以通过填写“value”参数来使用。 这个功能还获取一个 ID 参数，用于定义某些记录标识符或记录监管。

命令: v-add-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-domain)

添加 web/dns/mail 网站

**选项**: `USER` `DOMAIN` `[IP]` `[RESTART]`

**示例**:

```bash
v-add-domain admin example.com
```

此功能将 web/dns/mail 网站添加到服务器。

命令: v-add-fastcgi-cache

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-fastcgi-cache)

为 nginx 启用 FastCGI 缓存

**选项**: `USER` `DOMAIN` `[DURATION]` `[RESTART]`

**示例**:

```bash
v-add-fastcgi-cache user domain.tld 30m
```

该函数为nginx启用FastCGI缓存可接受的持续时间值为以秒 (10s) 分钟 (10m) 或天 (10d) 为单位的时间添加“yes”作为最后一个参数以重新启动 nginx

命令: v-add-firewall-ban

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-firewall-ban)

添加防火墙拦截规则

**选项**: `IP` `CHAIN`

**示例**:

```bash
v-add-firewall-ban 37.120.129.20 MAIL
```

该功能为系统防火墙添加新的拦截规则

命令: v-add-firewall-chain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-firewall-chain)

添加防火墙规则

**选项**: `CHAIN` `[PORT]` `[PROTOCOL]`

**示例**:

```bash
v-add-firewall-chain CRM 5678 TCP
```

该功能为系统防火墙添加新规则

命令: v-add-firewall-ipset

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-firewall-ipset)

添加防火墙ipset

**选项**: `NAME` `[SOURCE]` `[IPVERSION]` `[AUTOUPDATE]` `[REFRESH]`

**示例**:

```bash
v-add-firewall-ipset country-nl "https://raw.githubusercontent.com/ipverse/rir-ip/master/country/nl/ipv4-aggregated.txt"
```

该功能向系统防火墙添加新的ipset

命令: v-add-firewall-rule

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-firewall-rule)

添加防火墙规则

**选项**: `ACTION` `IP` `PORT` `[PROTOCOL]` `[COMMENT]` `[RULE]`

**示例**:

```bash
v-add-firewall-rule DROP 185.137.111.77 25
```

该功能为系统防火墙添加新规则

命令: v-add-fs-archive

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-fs-archive)

存档目录

**选项**: `USER` `ARCHIVE` `github脚本查看` `[github脚本查看...]`

**示例**:

```bash
v-add-fs-archive admin archive.tar readme.txt
```

该函数创建 tar 存档

命令: v-add-fs-directory

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-fs-directory)

添加目录

**选项**: `USER` `DIRECTORY`

**示例**:

```bash
v-add-fs-directory admin mybar
```

该函数在文件系统上创建新目录

命令: v-add-fs-file

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-fs-file)

添加文件

**选项**: `USER` `FILE`

**示例**:

```bash
v-add-fs-file admin readme.md
```

该函数在文件系统上创建新文件

命令: v-add-letsencrypt-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-letsencrypt-domain)

检查 LetsEncrypt 网站

**选项**: `USER` `DOMAIN` `[ALIASES]` `[MAIL]`

**示例**:

```bash
v-add-letsencrypt-domain admin wonderland.com www.wonderland.com,demo.wonderland.com
example: v-add-letsencrypt-domain admin wonderland.com '' yes
```

此函数使用 Let's Encrypt 检查并验证网站

命令: v-add-letsencrypt-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-letsencrypt-host)

为主机和后端添加letencrypt

**选项**: –

此函数检查并验证后端证书并生成新的让我们的加密证书。

命令: v-add-letsencrypt-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-letsencrypt-user)

注册LetsEncrypt用户帐户

**选项**: `USER`

**示例**:

```bash
v-add-letsencrypt-user bob
```

该函数创建并注册LetsEncrypt账户

命令: v-add-mail-account

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-account)

添加邮件网站帐户

**选项**: `USER` `DOMAIN` `ACCOUNT` `PASSWORD` `[QUOTA]`

**示例**:

```bash
v-add-mail-account user example.com john P4$$vvOrD
```

此功能添加新的电子邮件帐户。

命令: v-add-mail-account-alias

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-account-alias)

添加邮件帐户别名

**选项**: `USER` `DOMAIN` `ACCOUNT` `ALIAS`

**示例**:

```bash
v-add-mail-account-alias admin acme.com alice alicia
```

此功能添加新的电子邮件别名。

命令: v-add-mail-account-autoreply

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-account-autoreply)

添加邮件帐户自动回复消息

**选项**: `USER` `DOMAIN` `ACCOUNT` `MESSAGE`

**示例**:

```bash
v-add-mail-account-autoreply admin example.com user Hello from e-mail!
```

此功能添加新的电子邮件帐户。

命令: v-add-mail-account-forward

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-account-forward)

添加邮件帐户转发地址

**选项**: `USER` `DOMAIN` `ACCOUNT` `FORWARD`

**示例**:

```bash
v-add-mail-account-forward admin acme.com alice bob
```

此功能添加新的电子邮件转发帐号地址。

命令: v-add-mail-account-fwd-only

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-account-fwd-only)

添加邮件帐户仅转发标题

**选项**: `USER` `DOMAIN` `ACCOUNT`

**示例**:

```bash
v-add-mail-account-fwd-only admin example.com user
```

该函数添加仅转发邮件标题

命令: v-add-mail-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-domain)

添加邮件网站名

**选项**: `USER` `DOMAIN` `[ANTISPAM]` `[ANTIVIRUS]` `[DKIM]` `[DKIM_SIZE]` `[RESTART]` `[REJECT_SPAM]`

**示例**:

```bash
v-add-mail-domain admin mydomain.tld
```

该功能添加邮件网站名。

命令: v-add-mail-domain-antispam

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-domain-antispam)

添加邮件网站反垃圾邮件支持

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-add-mail-domain-antispam admin mydomain.tld
```

此功能启用 `防御垃圾信` 来接收传入的电子邮件。

命令: v-add-mail-domain-antivirus

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-domain-antivirus)

添加邮件网站防病毒支持

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-add-mail-domain-antivirus admin mydomain.tld
```

此功能启用 clamav 扫描传入的电子邮件。

命令: v-add-mail-domain-catchall

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-domain-catchall)

添加邮件网站管理帐户

**选项**: `USER` `DOMAIN` `EMAIL`

**示例**:

```bash
v-add-mail-domain-catchall admin example.com master@example.com
```

此功能可以为传入的电子邮件启用管理邮件的帐户。

命令: v-add-mail-domain-dkim

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-domain-dkim)

添加邮件网站 dkim 支持

**选项**: `USER` `DOMAIN` `[DKIM_SIZE]`

**示例**:

```bash
v-add-mail-domain-dkim admin acme.com
```

此功能将 DKIM 签名添加到外发网站电子邮件中。

命令: v-add-mail-domain-reject

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-domain-reject)

添加邮件网站拒绝垃圾邮件支持

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-add-mail-domain-reject admin mydomain.tld
```

该功能可以拒绝传入电子邮件的垃圾邮件。

命令: v-add-mail-domain-smtp-relay

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-domain-smtp-relay)

添加邮件网站smtp中继支持

**选项**: `USER` `DOMAIN` `HOST` `[USERNAME]` `[PASSWORD]` `[PORT]`

**示例**:

```bash
v-add-mail-domain-smtp-relay user domain.tld srv.smtprelay.tld uname123 pass12345
```

该功能增加邮件网站smtp中继支持。

命令: v-add-mail-domain-ssl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-domain-ssl)

为网站添加邮件 SSL证书

**选项**: `USER` `DOMAIN` `SSL_DIR` `[RESTART]`

此功能打开邮件网站的 SSL 支持。 参数 ssl_dir
是可以找到 2 或 3 个 ssl 文件的目录的路径。 证书文件
mail.domain.tld.crt 及其密钥 mail.domain.tld.key 是必需的。 中间证书 mail.domain.tld.ca 文件是可选的。

命令: v-add-mail-domain-webmail

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-mail-domain-webmail)

添加对网站的网络邮件支持

**选项**: `USER` `DOMAIN` `[WEBMAIL]` `[RESTART]` `[QUIET]`

**示例**:

```bash
v-add-mail-domain-webmail user domain.com
example: v-add-mail-domain-webmail user domain.com snappymail
example: v-add-mail-domain-webmail user domain.com roundcube
```

此功能为邮件网站启用 Webmail 客户端。

命令: v-add-remote-dns-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-remote-dns-domain)

添加远程 DNS 网站

**选项**: `USER` `DOMAIN` `[FLUSH]`

**示例**:

```bash
v-add-remote-dns-domain admin mydomain.tld yes
```

此功能将 dns 网站与远程服务器同步。

命令: v-add-remote-dns-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-remote-dns-host)

添加新的远程 DNS 主机

**选项**: `HOST` `PORT` `USER` `PASSWORD` `[TYPE]` `[DNS_USER]`

**示例**:

```bash
v-add-remote-dns-host slave.your_host.com 8083 admin your_passw0rd
v-add-remote-dns-host slave.your_host.com 8083 api_key ''
```

该功能将远程dns服务器添加到dns集群中。
作为在从服务器上生成的替代 api_key。
查看v-generate-api-key可用于连接远程dns服务器

命令: v-add-remote-dns-record

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-remote-dns-record)

添加远程dns网站名记录

**选项**: `USER` `DOMAIN` `ID`

**示例**:

```bash
v-add-remote-dns-record bob acme.com 23
```

此功能将 dns 网站与远程服务器同步。

命令: v-add-sys-api-ip

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-api-ip)

将 IP 地址添加到 API 允许列表

**选项**: `IP`

**示例**:

```bash
v-add-sys-api-ip 1.1.1.1
```

命令: v-add-sys-dependencies

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-dependencies)

**选项**:

**示例**:

```bash
v-add-sys-dependencies
```

向 Hestia 添加 php 依赖项

选项: [MODE]

命令: v-add-sys-filemanager

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-filemanager)

向 Hestia 控制面板添加文件管理器功能

**选项**: `[MODE]`

此功能在服务器上安装文件管理器通过 Web 界面进行访问。

命令: v-add-sys-firewall

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-firewall)

添加系统防火墙

**选项**: –

该功能启用系统防火墙。

命令: v-add-sys-ip

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-ip)

添加系统IP地址

**选项**: `IP` `NETMASK` `[INTERFACE]` `[USER]` `[IP_STATUS]` `[IP_NAME]` `[NAT_IP]`

**示例**:

```bash
v-add-sys-ip 203.0.113.1 255.255.255.0
```

此功能将 IP 地址添加到系统中。 它还创建 rc 脚本。 你可以指定将用作临时别名的根网站的 IP 名称。

例如，如果您将 a1.myhosting.com 设置为名称，则创建的每个新网站该 IP 将自动接收别名 $domain.a1.myhosting.com

当然您必须有通配符记录 \*.a1.myhosting.com 指向 IP。 此功能当客户想要在 DNS 迁移之前测试网站时非常方便。

命令: v-add-sys-pma-sso

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-pma-sso)

启用对 phpMyAdmin 的单点登录支持

**选项**: `[MODE]`

此功能启用对 phpMyAdmin 的 SSO 支持

命令: v-add-sys-quota

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-quota)

添加系统配额

**选项**: –

此功能启用 /home 分区上的文件系统配额某些内核确实需要先安装额外的软件包

命令: v-add-sys-roundcube

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-roundcube)

安装 Roundcube 网络邮件客户端

**选项**: `[MODE]`

此函数安装 Roundcube Webmail 客户端。

命令: v-add-sys-sftp-jail

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-sftp-jail)

添加系统sftp上传工具

**选项**: `[RESTART]`

**示例**:

```bash
v-add-sys-sftp-jail yes
```

此功能启用21端口 sftp 上传环境。

命令: v-add-sys-smtp

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-smtp)

添加 SMTP 帐户用于日志记录、通知和内部邮件

**选项**: `DOMAIN` `PORT` `SMTP_SECURITY` `USERNAME` `PASSWORD` `EMAIL`

**示例**:

```bash
v-add-sys-smtp example.com 587 STARTTLS test@domain.com securepassword test@example.com
```

此功能允许配置 SMTP 帐户以供服务器使用用于记录、通知和警告电子邮件等。

命令: v-add-sys-smtp-relay

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-smtp-relay)

添加邮件系统的 smtp 中继支持

**选项**: `HOST` `[USERNAME]` `[PASSWORD]` `[PORT]`

**示例**:

```bash
v-add-sys-smtp-relay srv.smtprelay.tld uname123 pass12345
```

此功能添加了邮件系统的 smtp 中继支持。

命令: v-add-sys-snappymail

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-snappymail)

安装 SnappyMail 网络邮件客户端

**选项**: `[MODE]`

此函数安装 SnappyMail 网络邮件客户端。

命令: v-add-sys-web-terminal

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-sys-web-terminal)

添加系统Web终端

**选项**: –

此功能启用网络终端。

命令: v-add-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-user)

添加邮件系统用户

**选项**: `USER` `PASSWORD` `EMAIL` `[PACKAGE]` `[NAME]` `[LASTNAME]`

**示例**:

```bash
v-add-user user P4$$w@rD bgates@aol.com
```

此功能创建新的邮件用户帐户。

命令: v-add-user-2fa

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-user-2fa)

将 2fa 令牌添加到现有用户

**选项**: `USER`

**示例**:

```bash
v-add-user-2fa admin
```

此函数为用户创建一个新的 2fa 令牌。

命令: v-add-user-composer

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-user-composer)

为用户添加composer（php 依赖管理器）

**选项**: `USER`

**示例**:

```bash
v-add-user-composer user [version]
```

该函数添加了对composer（php依赖管理器）的支持[composer主页](https://getcomposer.org)

命令: v-add-user-notification

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-user-notification)

添加用户通知

**选项**: `USER` `TOPIC` `NOTICE` `[TYPE]`

此功能向面板添加新用户通知。

命令: v-add-user-package

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-user-package)

添加用户软件包

**选项**: `TMPFILE` `PACKAGE` `[REWRITE]`

该功能向系统添加新的用户软件包

命令: v-add-user-sftp-jail

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-user-sftp-jail)

添加 sftp 上传用户

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-add-user-sftp-jail admin
```

该功能启用 sftp 上传配置

命令: v-add-user-sftp-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-user-sftp-key)

添加用户 sftp 密钥

**选项**: `USER` `[TTL]`

此函数创建并更新 SSH 密钥以与文件管理器一起使用。

命令: v-add-user-ssh-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-user-ssh-key)

添加 ssh 密钥

**选项**: `USER` `KEY`

**示例**:

```bash
v-add-user-ssh-key user 'valid ssh key'
```

函数检查 $user/.ssh/authorized_keys 是否存在并创建它。之后它会附加新密钥

命令: v-add-user-wp-cli

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-user-wp-cli)

为用户添加 wp-cli

**选项**: `USER`

**示例**:

```bash
v-add-user-wp-cli user
```

此功能为用户帐户添加对 wp-cli 的支持

命令: v-add-web-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain)

添加网络网站

**选项**: `USER` `DOMAIN` `[IP]` `[RESTART]` `[ALIASES]` `[PROXY_EXTENSIONS]`

**示例**:

```bash
v-add-web-domain admin wonderland.com 192.18.22.43 yes www.wonderland.com
```

此功能将虚拟主机添加到服务器。 如果 ip 是脚本中未定义，将使用“默认”模板。 的别名<www.domain.tld> 类型将自动分配给网站，除非“none”作为参数传输。
如果 ip 有关联的 dns 名称，则此网站还将获得别名domain-tpl.ip名称和ip的别名，在站点测试期间很有用。

命令: v-add-web-domain-alias

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-alias)

添加 Web 网站别名

**选项**: `USER` `DOMAIN` `ALIASES` `[RESTART]`

**示例**:

```bash
v-add-web-domain-alias admin acme.com www.acme.com yes
```

此函数向网站添加一个或多个别名（也称为
“网站停放”）。 该函数支持通配符<\*.domain.tld>

命令: v-add-web-domain-allow-users

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-allow-users)

允许其他用户创建子网站

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-add-web-domain-allow-users admin admin.com
```

绕过对特定网站强制执行子网站所有权的规则检查。将 /edit/server/ 中的子网站所有权设置强制设置为 no 将始终覆盖此行为
例如：admin 添加 admin.com 用户可以创建user.admin.com

命令: v-add-web-domain-backend

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-backend)

添加 Web 网站后端

**选项**: `USER` `DOMAIN` `[TEMPLATE]` `[RESTART]`

**示例**:

```bash
v-add-web-domain-backend admin example.com default yes
```

该函数用于添加Web后端配置。

命令: v-add-web-domain-ftp

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-ftp)

添加 Web 网站的 ftp 帐户。

**选项**: `USER` `DOMAIN` `FTP_USER` `FTP_PASSWORD` `[FTP_PATH]`

**示例**:

```bash
v-add-web-domain-ftp alice wonderland.com alice_ftp p4$$vvOrD
```

此功能为 Web 网站创建额外的 ftp 帐户。

命令: v-add-web-domain-httpauth

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-httpauth)

为 Web 网站添加密码保护

**选项**: `USER` `DOMAIN` `AUTH_USER` `AUTH_PASSWORD` `[RESTART]`

**示例**:

```bash
v-add-web-domain-httpauth admin acme.com user02 super_pass
```

此功能用于通过 http 身份验证保护 Web 网站

命令: v-add-web-domain-proxy

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-proxy)

添加Web网站代理支持

**选项**: `USER` `DOMAIN` `[TEMPLATE]` `[EXTENTIONS]` `[RESTART]`

**示例**:

```bash
v-add-web-domain-proxy admin example.com
```

此功能启用网站的代理支持。 这可以显着提高网站速度。

命令: v-add-web-domain-redirect

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-redirect)

添加强制重定向到网站

**选项**: `USER` `DOMAIN` `REDIRECT` `HTTPCODE` `[RESTART]`

**示例**:

```bash
v-add-web-domain-redirect user domain.tld domain.tld
example: v-add-web-domain-redirect user domain.tld www.domain.tld
example: v-add-web-domain-redirect user domain.tld shop.domain.tld
example: v-add-web-domain-redirect user domain.tld different-domain.com
example: v-add-web-domain-redirect user domain.tld shop.different-domain.com
example: v-add-web-domain-redirect user domain.tld different-domain.com 302
```

函数创建强制重定向到网站

命令: v-add-web-domain-ssl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-ssl)

为网站添加 ssl

**选项**: `USER` `DOMAIN` `SSL_DIR` `[SSL_HOME]` `[RESTART]`

**示例**:

```bash
v-add-web-domain-ssl admin example.com /home/admin/conf/example.com/web
```

此函数打开对网站的 SSL 支持。 参数 ssl_dir 是一个路径到可以找到 2 或 3 个 ssl 文件的目录。 证书文件domain.tld.crt 及其密钥domain.tld.key 是强制性的。

中间证书domain.tld.ca 文件是可选的。如果主目录参数(ssl_home) 未设置，https 网站使用 public_shtml 作为单独的文档根目录。

命令: v-add-web-domain-ssl-force

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-ssl-force)

为网站添加强制 SSL

**选项**: `USER` `DOMAIN` `[RESTART]` `[QUIET]`

**示例**:

```bash
v-add-web-domain-ssl-force admin acme.com
```

此函数对请求的网站强制使用 SSL。

命令: v-add-web-domain-ssl-hsts

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-ssl-hsts)

将严格传输安全性`hsts`添加到网站

**选项**: `USER` `DOMAIN` `[RESTART]` `[QUIET]`

此函数为请求的网站启用严格传输安全性 HSTS 协议。是一个Web安全策略机制。

命令: v-add-web-domain-ssl-preset

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-ssl-preset)

为网站添加强制 SSL

**选项**: `USER` `DOMAIN` `[SSL]`

创建 Web 网站时，由于 DNS 集群上的 DNS 属性导致 LE 延迟，因此设置 SSL 强制值当 LE 被激活时，它将设置操作

命令: v-add-web-domain-stats

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-stats)

添加日志分析器以生成网站统计信息

**选项**: `USER` `DOMAIN` `TYPE`

**示例**:

```bash
v-add-web-domain-stats admin example.com awstats
```

该功能用于为网站启用日志分析系统。 供观看网站统计信息使用 <http://domain.tld/vstats/> 链接。 访问此页面。默认情况下不受保护。 如果您想使用密码保护它应该使用 `v-add-web-domain_stat_auth` 脚本。

命令: v-add-web-domain-stats-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-domain-stats-user)

为网站网站统计添加密码保护

**选项**: `USER` `DOMAIN` `STATS_USER` `STATS_PASSWORD` `[RESTART]`

**示例**:

```bash
v-add-web-domain-stats-user admin example.com watchdog your_password
```

此功能用于保护网络统计页面的安全。

命令: v-add-web-php

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-add-web-php)

添加 php 版本

**选项**: `VERSION`

**示例**:

```bash
v-add-web-php 8.3
```

安装 php 版本

命令: v-backup-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-backup-user)

备份系统用户及其所有对象

**选项**: `USER` `NOTIFY`

**示例**:

```bash
v-backup-user admin yes
```

此功能用于备份用户及其所有网站和数据库。

命令: v-backup-users

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-backup-users)

备份所有用户

**选项**: –

此功能备份所有系统用户。

命令: v-change-cron-job

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-cron-job)

修改 cron 定时任务

**选项**: `USER` `JOB` `MIN` `HOUR` `DAY` `MONTH` `WDAY` `COMMAND`

**示例**:

```bash
v-change-cron-job admin 7 * * * * * * /usr/bin/uptime
```

该功能用于改变现有的工作。 它完全取代了工作
参数为新参数但具有相同的 id。

命令: v-change-database-host-password

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-database-host-password)

修改数据库服务器密码

**选项**: `TYPE` `HOST` `USER` `PASSWORD`

**示例**:

```bash
v-change-database-host-password mysql localhost www_user pA$$w@rD
```

此功能修改数据库服务器密码。`mysql`为数据库类型`www_user`为数据库账户 `pA$$w@rD`为密码

命令: v-change-database-owner

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-database-owner)

修改数据库所有者

**选项**: `DATABASE` `USER`

**示例**:

```bash
v-change-database-owner mydb alice
```

此功能用于修改数据库所有者。

命令: v-change-database-password

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-database-password)

修改数据库密码

**选项**: `USER` `DATABASE` `DBPASS`

**示例**:

```bash
v-change-database-password admin www_db neW_pAssWorD
```

该函数用于修改数据库的数据库用户密码。 它使用数据库的全名作为参数。

命令: v-change-database-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-database-user)

修改数据库用户名

**选项**: `USER` `DATABASE` `DBUSER` `[DBPASS]`

**示例**:

```bash
v-change-database-user admin my_db joe_user
```

该功能用于修改数据库用户名

命令: v-change-dns-domain-dnssec

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-dns-domain-dnssec)

修改 dns 网站 dnssec 状态

**选项**: `USER` `DOMAIN` `STATUS`

**示例**:

```bash
v-change-dns-domain-dnssec admin domain.pp.ua yes
```

命令: v-change-dns-domain-exp

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-dns-domain-exp)

修改 dns 网站到期日期

**选项**: `USER` `DOMAIN` `EXP`

**示例**:

```bash
v-change-dns-domain-exp admin domain.pp.ua 2020-11-20
```

更改网站名注册期限的功能。 更新时序列号会自动刷新。

命令: v-change-dns-domain-ip

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-dns-domain-ip)

更改dns网站名ip地址

**选项**: `USER` `DOMAIN` `IP` `[RESTART]`

**示例**:

```bash
v-change-dns-domain-ip admin domain.com 123.212.111.222
```

此功能用于更改 DNS 网站的主ip。

命令: v-change-dns-domain-soa

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-dns-domain-soa)

更改dns网站soa记录

**选项**: `USER` `DOMAIN` `SOA` `[RESTART]`

**示例**:

```bash
v-change-dns-domain-soa admin acme.com d.ns.domain.tld
```

该函数用于更改 SOA 记录。 此类记录不能通过 `v-change-dns-record` 调用修改。

命令: v-change-dns-domain-tpl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-dns-domain-tpl)

更改 dns 网站模板

**选项**: `USER` `DOMAIN` `TEMPLATE` `[RESTART]`

**示例**:

```bash
v-change-dns-domain-tpl admin example.com child-ns yes
```

此功能用于更改记录模板。 通过更新旧记录将被删除并根据以下规则生成新记录新模板的参数。

命令: v-change-dns-domain-ttl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-dns-domain-ttl)

更改 dns 网站 ttl

**选项**: `USER` `DOMAIN` `TTL` `[RESTART]`

**示例**:

```bash
v-change-dns-domain-ttl alice example.com 14400
```

此函数用于更改所有记录的生存时间 TTL 参数。

命令: v-change-dns-record

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-dns-record)

更改dns网站名记录

**选项**: `USER` `DOMAIN` `ID` `RECORD` `TYPE` `VALUE` `[PRIORITY]` `[RESTART]` `[TTL]`

**示例**:

```bash
v-change-dns-record admin domain.ua 42 192.18.22.43
```

此功能用于更改 DNS 记录。

命令: v-change-dns-record-id

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-dns-record-id)

更改dns网站名记录id

**选项**: `USER` `DOMAIN` `ID` `NEWID` `[RESTART]`

**示例**:

```bash
v-change-dns-record-id admin acme.com 24 42 yes
```

该函数用于更改 dns 内部记录 ID。

命令: v-change-domain-owner

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-domain-owner)

更改网站名所有者

**选项**: `DOMAIN` `USER`

**示例**:

```bash
v-change-domain-owner www.example.com bob
```

这个改变网站名所有权的功能。

命令: v-change-firewall-rule

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-firewall-rule)

更改防火墙规则

**选项**: `RULE` `ACTION` `IP` `PORT` `[PROTOCOL]` `[COMMENT]`

**示例**:

```bash
v-change-firewall-rule 3 ACCEPT 5.188.123.17 443
```

该功能用于更改现有的防火墙规则。它完全用新规则替换规则，但保留相同的 id。

命令: v-change-fs-file-permission

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-fs-file-permission)

更改文件权限

**选项**: `USER` `FILE` `PERMISSIONS`

**示例**:

```bash
v-change-fs-file-permission admin readme.txt 0777
```

该函数更改文件系统上的文件访问权限

命令: v-change-mail-account-password

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-mail-account-password)

更改邮件帐户密码

**选项**: `USER` `DOMAIN` `ACCOUNT` `PASSWORD`

**示例**:

```bash
v-change-mail-account-password admin mydomain.tld user p4$$vvOrD
```

此功能更改电子邮件帐户密码。

命令: v-change-mail-account-quota

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-mail-account-quota)

更改邮件帐户配额

**选项**: `USER` `DOMAIN` `ACCOUNT` `QUOTA`

**示例**:

```bash
v-change-mail-account-quota admin mydomain.tld user01 unlimited
```

此功能更改电子邮件帐户磁盘配额。

命令: v-change-mail-account-rate-limit

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-mail-account-rate-limit)

更改邮件帐户速率限制

**选项**: `USER` `DOMAIN` `ACCOUNT` `RATE`

**示例**:

```bash
v-change-mail-account-rate-limit admin mydomain.tld user01 100
```

此功能更改电子邮件帐户速率限制。 使用系统使用网站名或“服务器”设置

命令: v-change-mail-domain-catchall

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-mail-domain-catchall)

更改邮件网站电子邮件

**选项**: `USER` `DOMAIN` `EMAIL`

**示例**:

```bash
v-change-mail-domain-catchall user01 mydomain.tld master@mydomain.tld
```

此功能更改用户邮件网站。（官方未详细说明）使用者如使用到这个命令在论坛贴个命令反馈一下。我将不定期修复。

命令: v-change-mail-domain-rate-limit

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-mail-domain-rate-limit)

更改邮件网站速率限制

**选项**: `USER` `DOMAIN` `RATE`

**示例**:

```bash
v-change-mail-domain-rate-limit admin mydomain.tld 100
```

此功能更改网站的电子邮件帐户速率限制。 帐户特定设置将覆盖网站设置！

命令: v-change-mail-domain-sslcert

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-mail-domain-sslcert)

更改网站名ssl证书

**选项**: `USER` `DOMAIN` `SSL_DIR` `[RESTART]`

此功能更改 SSL 网站证书和密钥。 如果存在 ca 文件它也将被替换。

命令: v-change-remote-dns-domain-exp

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-remote-dns-domain-exp)

更改远程 DNS 网站到期日期

**选项**: `USER` `DOMAIN`

此功能将 dns 网站与远程服务器同步。

命令: v-change-remote-dns-domain-soa

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-remote-dns-domain-soa)

更改远程 DNS 网站 SOA

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-change-remote-dns-domain-soa admin example.org.uk
```

此功能将 dns 网站与远程服务器同步。

命令: v-change-remote-dns-domain-ttl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-remote-dns-domain-ttl)

更改远程 DNS 网站 TTL

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-change-remote-dns-domain-ttl admin domain.tld
```

此功能将 dns 网站与远程服务器同步。

命令: v-change-sys-api

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-api)

启用/禁用 API 访问

**选项**: `STATUS`

**示例**:

```bash
v-change-sys-api enable legacy
# Enable legacy api currently default on most of api based systems
example: v-change-sys-api enable api
# Enable api
v-change-sys-api disable
# Disable API
```

此功能将启用或禁用 API

命令: v-change-sys-config-value

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-config-value)

更改系统配置值

**选项**: `KEY` `VALUE`

**示例**:

```bash
v-change-sys-config-value VERSION 1.0
```

此功能用于更改主要配置设置，例如 COMPANY_NAME 或COMPANY_EMAIL 等等。

命令: v-change-sys-db-alias

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-db-alias)

更改 phpmyadmin/phppgadmin 别名 url

**选项**: `TYPE` `ALIAS`

**示例**:

```bash
v-change-sys-db-alias pma phpmyadmin
# Sets phpMyAdmin alias to phpmyadmin
v-change-sys-db-alias pga phppgadmin
# Sets phpPgAdmin alias to phppgadmin
```

此函数更改数据库编辑器 urlapache2 或 nginx 配置。

命令: v-change-sys-demo-mode

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-demo-mode)

启用或禁用演示模式

**选项**: `ACTIVE`

该函数将设置演示模式变量，这将阻止在后端使用某些 v 脚本并防止修改控制面板中的对象。
它还将禁用 Apache 和 NGINX 的虚拟主机对于已创建的网站。

命令: v-change-sys-hestia-ssl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-hestia-ssl)

更改 Hestia控制面板 ssl 证书

**选项**: `SSL_DIR` `[RESTART]`

**示例**:

```bash
v-change-sys-hestia-ssl /home/new/dir/path yes
```

此功能更改 hestia 控制面板的 SSL 证书和密钥。

命令: v-change-sys-hostname

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-hostname)

更改主机名

**选项**: `HOSTNAME`

**示例**:

```bash
v-change-sys-hostname mydomain.tld
```

该函数用于更改系统主机名。

命令: v-change-sys-ip-name

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-ip-name)

更改IP名称

**选项**: `IP` `NAME`

**示例**:

```bash
v-change-sys-ip-name 203.0.113.1 acme.com
```

此功能用于更改与 IP 关联的 dns 域。

命令: v-change-sys-ip-nat

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-ip-nat)

更改NAT IP地址

**选项**: `IP` `NAT_IP` `[RESTART]`

**示例**:

```bash
v-change-sys-ip-nat 10.0.0.1 203.0.113.1
```

此功能用于更改与 IP 关联的 NAT IP。

命令: v-change-sys-ip-owner

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-ip-owner)

更改IP所有者

**选项**: `IP` `USER`

**示例**:

```bash
v-change-sys-ip-owner 203.0.113.1 admin
```

这个改变IP地址所有权的功能。

命令: v-change-sys-ip-status

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-ip-status)

改变IP状态

**选项**: `IP` `IP_STATUS`

**示例**:

```bash
v-change-sys-ip-status 203.0.113.1 yourstatus
```

该功能改变IP地址的状态。

命令: v-change-sys-language

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-language)

更改系统语言

**选项**: `LANGUAGE` `[UPDATE_USERS]`

**示例**:

```bash
v-change-sys-language ru
```

此功能用于更改系统语言。请在最后位置输入你要更改的语言代码,具体代码请参考上面的文档，如果你看不明白请到英文中文论坛查找或者在论坛发帖询问。

命令: v-change-sys-php

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-php)

更改服务器范围内的默认 php 版本

**选项**: `VERSION`

**示例**:

此功能用于更改服务器范围内的默认 php 版本

```bash
v-change-sys-php 8.0
```

命令: v-change-sys-port

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-port)

更改系统后端端口

**选项**: `PORT`

**示例**:

```bash
v-change-sys-port 5678
```

此功能用于更改 NGINX 配置中的系统后端端口。

命令: v-change-sys-release

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-release)

更新 web 模板

**选项**: `[RESTART]`

该函数用于更改发布分支赫斯提亚控制面板。 这允许用户在之间切换稳定和预发布版本将自动更新如果自动更新是基于适当的发布计划

命令: v-change-sys-service-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-service-config)

更改服务配置

**选项**: `CONFIG` `SERVICE` `[RESTART]`

**示例**:

```bash
v-change-sys-service-config /home/admin/dovecot.conf dovecot yes
```

此功能用于更改服务配置。

命令: v-change-sys-timezone

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-timezone)

更改系统时区

**选项**: `TIMEZONE`

**示例**:

```bash
v-change-sys-timezone Europe/Berlin
```

该函数用于更改系统时区。

命令: v-change-sys-web-terminal-port

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-web-terminal-port)

更改系统Web终端后端端口

**选项**: `PORT`

**示例**:

```bash
v-change-sys-web-terminal-port 5678
```

此功能用于在 NGINX 配置中更改系统的 Web 终端后端端口。

命令: v-change-sys-webmail

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-sys-webmail)

更改网络邮件别名 url

**选项**: `WEBMAIL`

**示例**:

```bash
v-change-sys-webmail YourtrickyURLhere
```

此函数更改 apache2 或 nginx 配置中的 webmail url别名。

## 管理修改用户信息的系列命令

命令: v-change-user-config-value

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-config-value)

更改用户配置关键字/值

**选项**: `USER` `KEY` `VALUE`

**示例**:

```bash
v-change-user-config-value admin ROLE admin
```

此函数更改指定用户的关键字/值。

命令: v-change-user-contact

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-contact)

更改用户联系电子邮件

**选项**: `USER` `EMAIL`

**示例**:

```bash
v-change-user-contact admin admin@yahoo.com
```

此功能用于更改与特定用户关联的电子邮件。

命令: v-change-user-language

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-language)

更改用户语言

**选项**: `USER` `LANGUAGE`

**示例**:

```bash
v-change-user-language admin en
```

此功能用于更改语言。

命令: v-change-user-name

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-name)

更改用户全名

**选项**: `USER` `NAME` `[LAST_NAME]`

**示例**:

```bash
v-change-user-name admin John Smith
```

此功能允许更改用户的全名。

命令: v-change-user-ns

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-ns)

更改用户名服务器

**选项**: `USER` `NS1` `NS2` `[NS3]` `[NS4]` `[NS5]` `[NS6]` `[NS7]` `[NS8]`

**示例**:

```bash
v-change-user-ns ns1.domain.tld ns2.domain.tld
```

此功能用于更改特定用户的默认名称服务器。

命令: v-change-user-package

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-package)

更改用户包

**选项**: `USER` `PACKAGE` `[FORCE]`

**示例**:

```bash
v-change-user-package admin yourpackage
```

此功能更改用户的托管套餐。

命令: v-change-user-password

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-password)

更改用户密码

**选项**: `USER` `PASSWORD`

**示例**:

```bash
v-change-user-password admin NewPassword123
```

该函数更改用户密码并更新。

命令: v-change-user-php-cli

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-php-cli)

将 php 版本别名添加到 .bash_aliases

**选项**: `USER` `VERSION`

**示例**:

```bash
v-change-user-php-cli user 7.4
```

此功能更改将别名添加到 .bash_aliases 以设置默认 php 命令行，启用多 php 时的版本。

.bash_aliases 文件是一个 Bash shell 的配置文件，用于定义别名（aliases）。别名是命令的简短替代形式，允许用户为经常使用的复杂命令或命令序列创建简单的名称。

使用 .bash_aliases 文件，你可以：

简化命令：通过为常用命令创建别名，你可以减少输入，从而更快地执行命令。

自定义命令：创建自定义的别名，执行一系列命令或复杂的操作。

提高可读性：为复杂的命令或命令序列创建更有意义的名称，使其更容易理解。

组织命令：将所有别名放在一个文件中，便于管理和查找。

例如，假设你经常需要查看当前目录下的所有文件和目录，并按大小排序。你可以使用以下命令：

```bash
ls -lh | sort -rh -k5
```

为了简化这个命令，你可以在 .bash_aliases 文件中添加以下行：

bash
alias lss='ls -lh | sort -rh -k5'

然后，在 Bash shell 中，你只需要输入 lss 来执行这个命令。

要使用 .bash_aliases 文件，你需要确保它在你的 shell 启动时被加载。

这通常通过在 ~/.bashrc 或 ~/.bash_profile 文件中添加以下行来完成：

```bash
if [ -f ~/.bash_aliases ]; then  
    source ~/.bash_aliases  
fi
```

然后，每次你启动一个新的 Bash shell 会话时，.bash_aliases 文件中的别名都会自动加载。

注意：不同的系统和 shell 配置可能会有所不同，因此请根据你的具体环境进行调整。

## 配置用户权限的的系列命令

命令: v-change-user-rkey

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-rkey)

更改用户随机密钥

**选项**: `USER` `[HASH]`

此功能更改已用于安全值的用户 KEY 值，仅用于忘记密码功能。

命令: v-change-user-role

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-role)

更改/撤销用户管理员权限

**选项**: `USER` `ROLE`

**示例**:

```bash
v-change-user-role user administrator
```

此功能更改/撤销用户管理员权限以管理员身份管理所有帐户

命令: v-change-user-shell

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-shell)

更改禁止用户登录

**选项**: `USER` `SHELL`

**示例**:

```bash
v-change-user-shell admin nologin
```

此函数更改用户禁止登录的系统。 Shell 提供的使用 ssh 的能力。

命令: v-change-user-sort-order

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-sort-order)

更新用户角色

**选项**: `USER` `SORT_ORDER`

**示例**:

```bash
v-change-user-sort-order user date
```

更改指定用户的 Web 界面显示排序顺序。

命令: v-change-user-template

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-template)

更改用户默认模板

**选项**: `USER` `TYPE` `TEMPLATE`

**示例**:

```bash
v-change-user-template admin WEB wordpress
```

此功能更改默认用户网页模板。

命令: v-change-user-theme

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-user-theme)

更新用户主题

**选项**: `USER` `THEME`

**示例**:

```bash
v-change-user-theme admin dark
example: v-change-user-theme peter vestia
```

更改指定用户的 Web UI 显示主题。dark为主题名称

命令: v-change-web-domain-backend-tpl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-backend-tpl)

更改Web域后端模板

**选项**: `USER` `DOMAIN` `TEMPLATE` `[RESTART]`

**示例**:

```bash
v-change-web-domain-backend-tpl admin acme.com PHP-7_4
```

此功能更改后端模板

命令: v-change-web-domain-dirlist

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-dirlist)

启用/禁用目录列表

**选项**: `USER` `DOMAIN` `MODE`

**示例**:

```bash
v-change-web-domain-dirlist user demo.com on
```

该函数用于改变目录列表模式。

命令: v-change-web-domain-docroot

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-docroot)

## 管理 WEB 的系列命令

更改现有 Web 域的文档根

**选项**: `USER` `DOMAIN` `TARGET_DOMAIN` `[DIRECTORY]` `[PHP]`

**示例**:

```bash
v-change-web-domain-docroot admin domain.tld otherdomain.tld
# 添加自定义文档根目录
# 将domain.tld 指向otherdomain.tld 的文档根目录。
v-change-web-domain-docroot admin test.local default
# 删除自定义文档根目录
# 将文档根返回到域的默认值。
```

这个调用将所选Web域名的文档根目录更改为用户的另一个可用域名

命令: v-change-web-domain-ftp-password

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-ftp-password)

修改ftp用户密码。

**选项**: `USER` `DOMAIN` `FTP_USER` `FTP_PASSWORD`

**示例**:

```bash
v-change-web-domain-ftp-password admin example.com ftp_usr ftp_qwerty
```

该函数修改ftp用户密码。

命令: v-change-web-domain-ftp-path

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-ftp-path)

更改 ftp 用户的路径。

**选项**: `USER` `DOMAIN` `FTP_USER` `FTP_PATH`

**示例**:

```bash
v-change-web-domain-ftp-path admin example.com /home/admin/example.com
```

此功能更改 ftp 用户路径。

命令: v-change-web-domain-httpauth

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-httpauth)

更改http 授权用户的密码

**选项**: `USER` `DOMAIN` `AUTH_USER` `AUTH_PASSWORD` `[RESTART]`

**示例**:

```bash
v-change-web-domain-httpauth admin acme.com alice white_rA$$bIt
```

该函数用于修改http 授权用户密码

命令: v-change-web-domain-ip

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-ip)

更改网站域名ip

**选项**: `USER` `DOMAIN` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-change-web-domain-ip admin example.com 167.86.105.230 yes
```

该功能用于更改域名ip

命令: v-change-web-domain-name

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-name)

更改网络域名

**选项**: `USER` `DOMAIN` `NEW_DOMAIN` `[RESTART]`

**示例**:

```bash
v-change-web-domain-name alice wonderland.com lookinglass.com yes
```

该功能用于更改域名。

命令: v-change-web-domain-proxy-tpl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-proxy-tpl)

更改 Web 域代理模板

**选项**: `USER` `DOMAIN` `TEMPLATE` `[EXTENTIONS]` `[RESTART]`

**示例**:

```bash
v-change-web-domain-proxy-tpl admin domain.tld hosting
```

此功能更改代理模板

命令: v-change-web-domain-sslcert

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-sslcert)

更改域名ssl证书

**选项**: `USER` `DOMAIN` `SSL_DIR` `[RESTART]`

**示例**:

```bash
v-change-web-domain-sslcert admin example.com /home/admin/tmp
```

此功能更改 SSL 域证书和密钥。 如果存在 ca 文件它也将被替换。

命令: v-change-web-domain-sslhome

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-sslhome)

更改web域名的SSL主目录

**选项**: `USER` `DOMAIN` `SSL_HOME` `[RESTART]`

**示例**:

```bash
v-change-web-domain-sslhome admin acme.com single
example: v-change-web-domain-sslhome admin acme.com same
```

此函数更改web域名的SSL主目录。 Single 将分隔 public_html / public_shtml。 同样将始终指向 public_shtml

命令: v-change-web-domain-stats

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-stats)

更改网站域统计信息

**选项**: `USER` `DOMAIN` `TYPE`

**示例**:

```bash
v-change-web-domain-stats admin example.com awstats
```

此功能删除网站系统的统计数据。 它的类型是自动从客户端的配置文件中选择。

命令: v-change-web-domain-tpl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-change-web-domain-tpl)

更改 Web 域模板

**选项**: `USER` `DOMAIN` `TEMPLATE` `[RESTART]`

**示例**:

```bash
v-change-web-domain-tpl admin acme.com opencart
```

此功能更改 Web 配置文件的模板。 Web 域目录的内容保持不变。

## 配置哈希值和令牌的系列命令

命令: v-check-access-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-check-access-key)

检查访问密钥

**选项**: `ACCESS_KEY_ID` `SECRET_ACCESS_KEY` `COMMAND` `[IP]` `[FORMAT]`

**示例**:

```bash
v-check-access-key key_id secret v-purge-nginx-cache 127.0.0.1 json
```

- 检查密钥是否存在；
- 检查秘密是否属于密钥；
- 检查关键用户是否被暂停；
- 检查密钥是否有权运行该命令。

命令: v-check-api-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-check-api-key)

检查 API 密钥

**选项**: `KEY` `[IP]`

**示例**:

```bash
v-check-api-key random_key 127.0.0.1
```

该函数检查 $HESTIA/data/keys/ 中的密钥文件

命令: v-check-fs-permission

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-check-fs-permission)

打开文件

**选项**: `USER` `FILE`

**示例**:

```bash
v-check-fs-permission admin readme.txt
```

该函数打开/读取文件系统上的文件

命令: v-check-mail-account-hash

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-check-mail-account-hash)

检查用户密码

**选项**: `TYPE` `PASSWORD` `HASH`

**示例**:

```bash
v-check-mail-account-hash ARGONID2 PASS HASH
```

此函数验证电子邮件帐户密码哈希值

命令: v-check-user-2fa

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-check-user-2fa)

检查用户令牌

**选项**: `USER` `TOKEN`

**示例**:

```bash
v-check-user-2fa admin 493690
```

该函数验证用户 2fa 令牌。

命令: v-check-user-hash

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-check-user-hash)

检查用户哈希值

**选项**: `USER` `HASH` `[IP]`

**示例**:

```bash
v-check-user-hash admin CN5JY6SMEyNGnyCuvmK5z4r7gtHAC4mRZ...
```

该函数验证用户哈希值

命令: v-check-user-password

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-check-user-password)

检查用户密码

**选项**: `USER` `PASSWORD` `[IP]` `[RETURN_HASH]`

**示例**:

```bash
v-check-user-password admin qwerty1234
```

该函数从文件中验证用户密码

## 复制文件的系列命令

命令: v-copy-fs-directory

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-copy-fs-directory)

复制目录

**选项**: `USER` `SRC_DIRECTORY` `DST_DIRECTORY`

**示例**:

```bash
v-copy-fs-directory alice /home/alice/dir1 /home/bob/dir2
```

该函数复制文件系统上的目录

命令: v-copy-fs-file

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-copy-fs-file)

复制文件
**选项**: `USER` `SRC_FILE` `DST_FILE`

**示例**:

```bash
v-copy-fs-file admin readme.txt readme_new.txt
```

该函数复制文件系统上的文件

命令: v-copy-user-package

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-copy-user-package)

重复现有包

**选项**: `PACKAGE` `NEW_PACKAGE`

**示例**:

```bash
v-copy-user-package default new
```

此功能允许用户复制现有的包文件以方便配置。

命令: v-delete-access-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-access-key)

删除访问密钥

**选项**: `ACCESS_KEY_ID`

**示例**:

```bash
v-delete-access-key mykey
```

此函数从 $HESTIA/data/access-keys/ 中删除密钥

命令: v-delete-backup-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-backup-host)

删除备份 ftp 服务器

**选项**: `TYPE` `[HOST]`

**示例**:

```bash
v-delete-backup-host sftp
```

该函数删除ftp备份主机

## 管理 CRON 定时任务的系列命令

命令: v-delete-cron-hestia-autoupdate

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-cron-hestia-autoupdate)

删除 hestia 自动更新 cron 定时任务

**选项**: –

此函数删除 hestia 自动更新 cron 定时任务。

命令: v-delete-cron-job

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-cron-job)

删除定时任务

**选项**: `USER` `JOB`

**示例**:

```bash
v-delete-cron-job admin 9
```

该函数删除 cron 定时任务。

命令: v-delete-cron-reports

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-cron-reports)

删除 cron 报告

**选项**: `USER`

**示例**:

```bash
v-delete-cron-reports admin
```

此功能用于禁用 cron 任务和管理通知的报告。

命令: v-delete-cron-restart-job

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-cron-restart-job)

删除重启任务

**选项**: –

此功能用于禁用重新启动 cron 任务

## 管理数据库的系列命令

命令: v-delete-database

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-database)

删除数据库

**选项**: `USER` `DATABASE`

**示例**:

```bash
v-delete-database admin www_db
```

该函数用于删除数据库。 如果数据库用户有权访问另一个数据库，他不会被删除。

命令: v-delete-database-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-database-host)

删除数据库服务器

**选项**: `TYPE` `HOST`

**示例**:

```bash
v-delete-database-host pgsql localhost
```

此函数用于它会从 hestia 删除配置中的数据库主机。 如果没有在其上创建数据库，则将其删除。

命令: v-delete-database-temp-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-database-temp-user)

删除临时数据库用户

**选项**: `USER` `DBUSER` `[TYPE]` `[HOST]`

**示例**:

```bash
v-add-database-temp-user wordress hestia_sso_user mysql
```

撤销“临时用户”对数据库的访问权限并删除该用户
与 `v-add-database-temp-user` 结合使用

命令: v-delete-databases

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-databases)

删除用户数据库

**选项**: `USER`

**示例**:

```bash
v-delete-databases admin
```

此功能删除所有用户数据库。

## 管理 DNS 的系列命令

命令: v-delete-dns-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-dns-domain)

删除dns域名

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-dns-domain alice acme.com
```

此功能用于删除 DNS 域。 通过删除它，所有记录也将被删除

命令: v-delete-dns-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-dns-domains)

删除 dns 域

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-delete-dns-domains bob
```

此功能用于删除所有用户的 DNS 域。

命令: v-delete-dns-domains-src

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-dns-domains-src)

根据 SRC 字段删除 dns 域

**选项**: `USER` `SRC` `[RESTART]`

**示例**:

```bash
v-delete-dns-domains-src admin '' yes
```

该功能用于删除与某个主机相关的DNS域。

命令: v-delete-dns-on-web-alias

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-dns-on-web-alias)

删除 dns 域或基于 web 域别名的 dns 记录

**选项**: `USER` `DOMAIN` `ALIAS` `[RESTART]`

**示例**:

```bash
v-delete-dns-on-web-alias admin example.com www.example.com
```

该功能根据Web域别名删除dns域或dns记录。

命令: v-delete-dns-record

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-dns-record)

删除dns记录

**选项**: `USER` `DOMAIN` `ID` `[RESTART]`

**示例**:

```bash
v-delete-dns-record bob acme.com 42 yes
```

该功能用于删除DNS区域的某个记录。

命令: v-delete-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-domain)

删除 web/dns/mail 域

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-domain admin domain.tld
```

此功能删除 web/dns/mail 域。

命令: v-delete-fastcgi-cache

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-fastcgi-cache)

禁用 nginx 的 FastCGI 缓存

**选项**: `USER` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-delete-fastcgi-cache user domain.tld
```

该函数禁用 nginx 的 FastCGI 缓存

## 管理防火墙的系列命令

命令: v-delete-firewall-ban

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-firewall-ban)

删除防火墙拦截规则

**选项**: `IP` `CHAIN`

**示例**:

```bash
v-delete-firewall-ban 198.11.130.250 MAIL
```

该功能删除系统防火墙的拦截规则

命令: v-delete-firewall-chain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-firewall-chain)

删除防火墙链

**选项**: `CHAIN`

**示例**:

```bash
v-delete-firewall-chain WEB
```

该功能为系统防火墙添加新规则

命令: v-delete-firewall-ipset

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-firewall-ipset)

删除防火墙ipset

**选项**: `NAME`

**示例**:

```bash
v-delete-firewall-ipset country-nl
```

此函数从系统和 Hestia 中删除 ipset

命令: v-delete-firewall-rule

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-firewall-rule)

删除防火墙规则

**选项**: `RULE`

**示例**:

```bash
v-delete-firewall-rule SSH_BLOCK
```

该功能删除防火墙规则。

命令: v-delete-fs-directory

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-fs-directory)

删除目录

**选项**: `USER` `DIRECTORY`

**示例**:

```bash
v-delete-fs-directory admin report1
```

该函数删除文件系统上的目录

命令: v-delete-fs-file

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-fs-file)

删除文件

**选项**: `USER` `FILE`

**示例**:

```bash
v-delete-fs-file admin readme.txt
```

该函数删除文件系统上的文件

## 管理配置 SSL证书和电子邮件系统的系列命令

命令: v-delete-letsencrypt-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-letsencrypt-domain)

删除域的 LetsEncrypt SSL 证书

**选项**: `USER` `DOMAIN` `[RESTART]` `[MAIL]`

**示例**:

```bash
v-delete-letsencrypt-domain admin acme.com yes
```

此函数关闭对域的 LetsEncrypt SSL 支持。

命令: v-delete-mail-account

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-account)

删除邮件帐户

**选项**: `USER` `DOMAIN` `ACCOUNT`

**示例**:

```bash
v-delete-mail-account admin acme.com alice
```

此功能删除电子邮件帐户。

命令: v-delete-mail-account-alias

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-account-alias)

删除邮件帐户别名又名昵称

**选项**: `USER` `DOMAIN` `ACCOUNT` `ALIAS`

**示例**:

```bash
v-delete-mail-account-alias admin example.com alice alicia
```

此功能删除电子邮件帐户别名。

命令: v-delete-mail-account-autoreply

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-account-autoreply)

删除邮件帐户自动回复消息

**选项**: `USER` `DOMAIN` `ACCOUNT` `ALIAS`

**示例**:

```bash
v-delete-mail-account-autoreply admin mydomain.tld bob
```

此功能删除电子邮件帐户自动回复。

命令: v-delete-mail-account-forward

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-account-forward)

删除邮件帐户转发

**选项**: `USER` `DOMAIN` `ACCOUNT` `EMAIL`

**示例**:

```bash
v-delete-mail-account-forward admin acme.com tony bob@acme.com
```

此功能删除电子邮件帐户转发地址。

命令: v-delete-mail-account-fwd-only

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-account-fwd-only)

删除邮件帐户转发标题配置

**选项**: `USER` `DOMAIN` `ACCOUNT`

**示例**:

```bash
v-delete-mail-account-fwd-only admin example.com jack
```

该函数删除仅转发标题配置

命令: v-delete-mail-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-domain)

删除邮件域

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-mail-domain admin mydomain.tld
```

此功能用于删除电子邮件域。 通过删除它，所有帐户都会被删除。

命令: v-delete-mail-domain-antispam

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-domain-antispam)

删除邮件域反垃圾邮件支持

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-mail-domain-antispam admin mydomain.tld
```

此功能禁用 spamassasin 接收传入电子邮件。

命令: v-delete-mail-domain-antivirus

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-domain-antivirus)

删除邮件域防病毒配置

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-mail-domain-antivirus admin mydomain.tld
```

此功能禁用 clamav 扫描传入电子邮件。

命令: v-delete-mail-domain-catchall

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-domain-catchall)

删除邮件域的电子邮件捕获(Catch-all)功能

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-mail-domain-catchall admin mydomain.tld
```

此功能禁用邮件域捕获(Catch-all)功能

命令: v-delete-mail-domain-dkim

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-domain-dkim)

删除邮件域 dkim 支持

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-mail-domain-dkim admin mydomain.tld
```

此功能删除 DKIM 域 pem配置。删除后将无法正常使用邮箱收发邮件。

命令: v-delete-mail-domain-reject

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-domain-reject)

删除邮件域拒绝垃圾邮件支持

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-mail-domain-reject admin mydomain.tld
```

该功能禁用传入电子邮件的垃圾邮件拒绝。

命令: v-delete-mail-domain-smtp-relay

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-domain-smtp-relay)

删除邮件域 smtp 中继支持

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-mail-domain-smtp-relay user domain.tld
```

此功能删除邮件域 smtp 中继支持。

命令: v-delete-mail-domain-ssl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-domain-ssl)

删除邮件域 ssl 支持

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-mail-domain-ssl user demo.com
```

此函数删除邮件域 ssl 证书。

命令: v-delete-mail-domain-webmail

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-domain-webmail)

删除对域的网络邮件支持

**选项**: `USER` `DOMAIN` `[RESTART]` `[QUIET]`

**示例**:

```bash
v-delete-mail-domain-webmail user demo.com
```

此功能删除了对网络邮件的支持指定的邮件域。

命令: v-delete-mail-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-mail-domains)

删除邮件域

**选项**: `USER`

**示例**:

```bash
v-delete-mail-domains admin
```

此功能用于删除所有用户的邮件域。

## 管理 DNS 的系列命令

命令: v-delete-remote-dns-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-remote-dns-domain)

删除远程 DNS 域

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-remote-dns-domain admin example.tld
```

此功能将删除 dns 与远程服务器同步的配置。

命令: v-delete-remote-dns-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-remote-dns-domains)

删除远程 DNS 域

**选项**: `[HOST]`

此功能删除远程 dns 域。

命令: v-delete-remote-dns-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-remote-dns-host)

删除远程dns主机

**选项**: `HOST`

**示例**:

```bash
v-delete-remote-dns-host example.org
```

此功能用于从 hestia 配置中删除远程服务器的 dns 主机。

命令: v-delete-remote-dns-record

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-remote-dns-record)

删除远程dns域记录

**选项**: `USER` `DOMAIN` `ID`

**示例**:

```bash
v-delete-remote-dns-record user07 acme.com 44
```

此功能将删除 dns 与远程服务器同步。

## 管理IP的系列命令

命令: v-delete-sys-api-ip

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-sys-api-ip)

从允许的 ip 列表中删除 api 地址

**选项**: `IP`

**示例**:

```bash
v-delete-sys-api-ip 1.1.1.1
```

命令: v-delete-sys-filemanager

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-sys-filemanager)

从 Hestia 控制面板中禁用文件管理器功能

**选项**: `[MODE]`

此功能删除文件管理器及其入口

命令: v-delete-sys-firewall

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-sys-firewall)

删除系统防火墙

**选项**: –

此功能禁用防火墙支持

命令: v-delete-sys-ip

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-sys-ip)

删除系统IP

**选项**: `IP`

**示例**:

```bash
v-delete-sys-ip 203.0.113.1
```

该函数用于删除系统IP。 不允许删除第一个IP在接口上，不允许删除 Web 域使用的 IP。

命令: v-delete-sys-mail-queue

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-sys-mail-queue)

删除 exim 邮件队列

**选项**: –

此函数检查滞留在 exim 邮件队列中的邮件并提示用户根据需要清除的邮件列表。

命令: v-delete-sys-pma-sso

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-sys-pma-sso)

禁用 PHPMYADMIN 上对单点登录的支持

**选项**: `[MODE]`

禁用对 phpMyAdmin 的 单点登录 支持

命令: v-delete-sys-quota

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-sys-quota)

删除系统配额

**选项**: –

此功能禁用 /home 分区上的文件系统容量配额

命令: v-delete-sys-sftp-jail

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-sys-sftp-jail)

删除系统sftp的jail环境

**选项**: –

此功能删除 sftp 的jail（通常指的是一个限制用户访问的目录环境）配置。

命令: v-delete-sys-smtp

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-sys-smtp)

删除用于记录、通知和内部邮件的 SMTP 帐户

**选项**: –

此功能允许配置 SMTP 帐户以供服务器使用，用于记录、通知和警告电子邮件等。

命令: v-delete-sys-smtp-relay

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-sys-smtp-relay)

删除系统范围的 SMTP 中继支持

**选项**:

命令: v-delete-sys-web-terminal

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-sys-web-terminal)

删除网络终端

**选项**: –

此功能禁用网络终端。

命令: v-delete-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user)

## 管理用户的系列命令

删除用户

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-delete-user whistler
```

该功能删除某个用户及其所有资源，例如域、数据库、cron 作业等

命令: v-delete-user-2fa

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user-2fa)

删除现有用户的2fa

**选项**: `USER`

**示例**:

```bash
v-delete-user-2fa admin
```

该函数删除用户的2fa令牌。

命令: v-delete-user-auth-log

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user-auth-log)

删除用户的身份验证日志文件

**选项**:

此功能用于删除用户身份验证日志文件

命令: v-delete-user-backup

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user-backup)

## 管理备份的系列命令

删除用户备份

**选项**: `USER` `BACKUP`

**示例**:

```bash
v-delete-user-backup admin admin.2012-12-21_00-10-00.tar
```

该功能删除用户备份。

命令: v-delete-user-backup-exclusions

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user-backup-exclusions)

删除备份排除

**选项**: `USER` `[SYSTEM]`

**示例**:

```bash
v-delete-user-backup-exclusions admin
```

此功能用于删除备份排除(用户可以定义排除项来指定哪些文件或目录不应该被包括在备份中。这个命令可能用于删除之前设置的这些排除项。)

命令: v-delete-user-ips

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user-ips)

删除用户ip

**选项**: `USER`

**示例**:

```bash
v-delete-user-ips admin
```

此功能删除用户的所有 IP 地址。

命令: v-delete-user-log

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user-log)

删除用户的日志文件

**选项**: `USER`

**示例**:

```bash
v-delete-user-log user
```

该函数用于删除用户日志文件

命令: v-delete-user-notification

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user-notification)

删除用户通知

**选项**: `USER` `NOTIFICATION`

**示例**:

```bash
v-delete-user-notification admin 1
```

该功能删除用户通知。

命令: v-delete-user-package

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user-package)

删除用户软件包

**选项**: `PACKAGE`

**示例**:

```bash
v-delete-user-package admin palegreen
```

该功能用于删除用户软件包。

命令: v-delete-user-sftp-jail

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user-sftp-jail)

删除用户 sftp jail

**选项**: `USER`

**示例**:

```bash
v-delete-user-sftp-jail whistler
```

此功能禁用 USER 的 sftp 的jail（通常指的是一个限制用户访问的目录环境）配置。

命令: v-delete-user-ssh-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user-ssh-key)

删除用户 ssh 密钥

**选项**: `USER` `KEY`

**示例**:

```bash
v-delete-user-ssh-key user unique_id
```

从authorized_keys中删除用户ssh密钥

命令: v-delete-user-stats

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-user-stats)

删除用户使用统计

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-user-stats user
example: v-delete-user-stats admin overall
```

该功能删除用户统计数据。

命令: v-delete-web-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain)

删除网络域

**选项**: `USER` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-delete-web-domain admin wonderland.com
```

函数的调用导致域及其所有组件的删除（统计数据、文件夹内容、ssl 证书等）。 这个操作是不完全支持“撤消”功能，因此可以恢复数据，仅在保留副本的前提下。

命令: v-delete-web-domain-alias

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-alias)

删除 Web 域别名

**选项**: `USER` `DOMAIN` `ALIAS` `[RESTART]`

**示例**:

```bash
v-delete-web-domain-alias admin example.com www.example.com
```

此功能删除别名域（域名文件夹名称）。 通过这个命令默认的 www 别名也可以删除。

命令: v-delete-web-domain-allow-users

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-allow-users)

禁止其他用户创建子域

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-web-domain-allow-users admin admin.com
```

启用对特定域强制执行子域所有权的规则检查。将 /edit/server/ 中的子域所有权设置强制设置为 no 将始终覆盖此行为。例如：admin 添加 admin.com

用户可以创建user.admin.com

命令: v-delete-web-domain-backend

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-backend)

删除Web域后端配置

**选项**: `USER` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-delete-web-domain-backend admin acme.com
```

该功能删除虚拟主机后端配置。

命令: v-delete-web-domain-ftp

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-ftp)

删除web网站的 ftp帐户

**选项**: `USER` `DOMAIN` `FTP_USER`

**示例**:

```bash
v-delete-web-domain-ftp admin wonderland.com bob_ftp
```

此功能删除网站的 ftp 帐户。

命令: v-delete-web-domain-httpauth

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-httpauth)

删除用户的http验证

**选项**: `USER` `DOMAIN` `AUTH_USER` `[RESTART]`

**示例**:

```bash
v-delete-web-domain-httpauth admin example.com alice
```

## 管理修改域名配置信息的系列命令

该函数用于删除 Web 域名的HTTP认证设置的命令

命令: v-delete-web-domain-proxy

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-proxy)

删除Web域代理配置

**选项**: `USER` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-delete-web-domain-proxy alice lookinglass.com
```

此功能删除虚拟主机代理配置。

命令: v-delete-web-domain-redirect

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-redirect)

删除强制重定向到域

**选项**: `USER` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-add-web-domain-redirect user domain.tld
```

删除强制重定向到域功能

命令: v-delete-web-domain-ssl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-ssl)

删除 Web 域 SSL 支持

**选项**: `USER` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-delete-web-domain-ssl admin acme.com
```

此函数禁用 https 支持并删除 SSL 证书。

命令: v-delete-web-domain-ssl-force

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-ssl-force)

从域中强制删除 ssl

**选项**: `USER` `DOMAIN` `[RESTART]` `[QUIET]`

**示例**:

```bash
v-delete-web-domain-ssl-force admin domain.tld
```

此功能删除强制 SSL 配置。

命令: v-delete-web-domain-ssl-hsts

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-ssl-hsts)

从域中强制删除 (HSTS)配置

**选项**: `USER` `DOMAIN` `[RESTART]` `[QUIET]`

**示例**:

```bash
v-delete-web-domain-ssl-hsts user domain.tld
```

此功能删除强制 SSL 严格传输安全性 (HSTS)配置

::: warning 介绍
HTTP严格传输安全（HSTS）是一种安全策略机制，它允许网站通过HTTP响应头来告知浏览器只能通过HTTPS来访问该网站，即使用户尝试通过HTTP访问。也无法成功访问。
:::

命令: v-delete-web-domain-stats

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-stats)

删除网站域统计信息

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-delete-web-domain-stats user02 h1.example.com
```

此功能删除网站系统的统计数据。 它的类型是自动从客户端的配置文件中选择。

命令: v-delete-web-domain-stats-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domain-stats-user)

禁用 Web 域统计身份验证支持

**选项**: `USER` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-delete-web-domain-stats-user admin acme.com
```

该功能消除了统计系统的认证。 如果不命名某个用户而调用，则所有用户都将被删除。删除所有统计数据即可查看，无需验证。

命令: v-delete-web-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-domains)

删除网站域名称

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-delete-web-domains admin
```

此功能删除用户所有的网站域。

命令: v-delete-web-php

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-delete-web-php)

删除 php fpm 版本

**选项**: `VERSION`

**示例**:

```bash
v-delete-web-php 7.3
```

此函数检查并删除 fpm php 版本（如果没有被任何域使用）。

## 下载备份的系列命令

命令: v-download-backup

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-download-backup)

下载备份

**选项**: `USER` `BACKUP`

**示例**:

```bash
v-download-backup admin admin.2020-11-05_05-10-21.tar
```

此功能从远程服务器下载备份

命令: v-dump-database

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-dump-database)

将数据库内容转储到 STDIN/文件中

**选项**: `USER` `DATABASE` `[FILE]`

**示例**:

```bash
v-dump-database user user_databse > test.sql
example: v-dump-database user user_databse file
```

以 STDIN 或 /backup/user.database.type.sql 转储数据库

命令: v-dump-site

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-dump-site)

将站点的文件转储到 zip 存档中

**选项**: `USER` `DOMAIN` `[TYPE]`

**示例**:

```bash
v-dump-site user domain
example: v-dump-site user domain full
```

将站点文件转储到 /backup/user.domain.timestamp.zip 中

命令: v-export-rrd

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-export-rrd)

将rrd图表导出为json

**选项**: `[CHART]` `[TIMESPAN]`

**示例**:

```bash
v-export-rrd chart format
```

命令: v-extract-fs-archive

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-extract-fs-archive)

存档到目录

**选项**: `USER` `ARCHIVE` `DIRECTORY` `[SELECTED_DIR]` `[STRIP]` `[TEST]`

**示例**:

```bash
v-extract-fs-archive admin latest.tar.gz /home/admin
```

此函数将存档提取到文件系统上的目录中

命令: v-generate-api-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-generate-api-key)

## 配置API密钥哈希值命令

生成API密钥

**选项**: –

该函数在 $HESTIA/data/keys/ 中创建一个密钥文件

命令: v-generate-debug-report

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-generate-debug-report)

**选项**:

shellcheck github脚本查看=/etc/hestiacp/hestia.conf

命令: v-generate-password-hash

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-generate-password-hash)

生成密码哈希

**选项**: `HASH_METHOD` `SALT` `PASSWORD`

**示例**:

```php
		v-generate-password-hash sha-512 rAnDom_string yourPassWord
```

该函数生成密码哈希值

命令: v-generate-ssl-cert

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-generate-ssl-cert)

生成自签名证书和 CSR 请求

**选项**: `DOMAIN` `EMAIL` `COUNTRY` `STATE` `CITY` `ORG` `UNIT` `[ALIASES]` `[FORMAT]`

**示例**:

```bash
v-generate-ssl-cert example.com mail@yahoo.com USA California Monterey ACME.COM IT
```

此函数生成自签名 SSL 证书和 CSR 请求

命令: v-get-dns-domain-value

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-get-dns-domain-value)

获取dns域名值

**选项**: `USER` `DOMAIN` `KEY`

**示例**:

```bash
v-get-dns-domain-value admin example.com SOA
```

该函数用于获取某个DNS域参数。

命令: v-get-fs-file-type

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-get-fs-file-type)

获取文件类型

**选项**: `USER` `FILE`

**示例**:

```bash
v-get-fs-file-type admin index.html
```

该函数显示文件类型

命令: v-get-mail-account-value

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-get-mail-account-value)

获取邮件帐户值

**选项**: `USER` `DOMAIN` `ACCOUNT` `KEY`

**示例**:

```bash
v-get-mail-account-value admin example.tld tester QUOTA
```

该函数用于获取某个邮件帐户参数。

命令: v-get-mail-domain-value

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-get-mail-domain-value)

获取邮件域名值

**选项**: `USER` `DOMAIN` `KEY`

**示例**:

```bash
v-get-mail-domain-value admin example.com DKIM
```

该函数用于获取某个邮件域参数。

命令: v-get-sys-timezone

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-get-sys-timezone)

## 设置系统时区哈希值命令

获取系统时区

**选项**: `[FORMAT]`

该函数获取系统时区

命令: v-get-sys-timezones

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-get-sys-timezones)

查看系统时区

**选项**: `[FORMAT]`

**示例**:

```bash
v-get-sys-timezones json
```

该函数检查系统时区设置

命令: v-get-user-salt

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-get-user-salt)

获取用户加密盐值（salt）

**选项**: `USER` `[IP]` `[FORMAT]`

**示例**:

```bash
v-get-user-salt admin
```

该函数为用户提供加密盐值（salt）的命令。在密码学中，盐值通常与密码哈希一起使用，以增加哈希的安全性并防止彩虹表攻击。

命令: v-get-user-value

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-get-user-value)

获取用户参数

**选项**: `USER` `KEY`

**示例**:

```bash
v-get-user-value admin FNAME
```

该函数用于获取某些用户的参数。

命令: v-import-cpanel

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-import-cpanel)

将 Cpanel 备份导入到新用户

**选项**: `BACKUP` `[MX]`

**示例**:

```bash
v-import-cpanel /backup/backup.tar.gz yes
```

基于 sk-import-cpanel-backup-to-vestacp
致谢：Maks Usmanov (skamasle) 和贡献者：[感谢](https://github.com/Skamasle/sk-import-cpanel-backup-to-vestacp/graphs/contributors)

## 写入DNS的系列命令

命令: v-insert-dns-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-insert-dns-domain)

插入 DNS 域

**选项**: `USER` `DATA` `[SRC]` `[FLUSH]` `#`

此函数将原始记录插入到 dns.conf配置中

命令: v-insert-dns-record

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-insert-dns-record)

插入DNS记录

**选项**: `USER` `DOMAIN` `DATA`

此函数将原始 dns 记录插入域conf

命令: v-insert-dns-records

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-insert-dns-records)

插入 dns 记录

**选项**: `USER` `DOMAIN` `DATA_FILE`

该函数将dns记录复制到域conf中

## 查看API的系列命令

命令: v-list-access-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-access-key)

查看所有 API 访问密钥

**选项**: `ACCESS_KEY_ID` `[FORMAT]`

**示例**:

```bash
v-list-access-key 1234567890ABCDefghij json
```

命令: v-list-access-keys

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-access-keys)

查看所有 API 访问密钥

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-access-keys json
```

命令: v-list-api

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-api)

查看API

**选项**: `API` `[FORMAT]`

**示例**:

```bash
v-list-api mail-accounts json
```

命令: v-list-apis

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-apis)

查看可用的 API

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-apis json
```

命令: v-list-backup-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-backup-host)

查看备份主机

**选项**: `TYPE` `[FORMAT]`

**示例**:

```bash
v-list-backup-host local
```

该函数用于获取备份主机参数列表。

命令: v-list-cron-job

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-cron-job)

查看 cron 作业

**选项**: `USER` `JOB` `[FORMAT]`

**示例**:

```bash
v-list-cron-job admin 7
```

该函数获取cron作业参数。

命令: v-list-cron-jobs

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-cron-jobs)

查看用户 cron 作业

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-cron-jobs admin
```

该函数用于获取所有用户的 cron 作业列表。

命令: v-list-database

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-database)

查看数据库

**选项**: `USER` `DATABASE` `[FORMAT]`

**示例**:

```bash
v-list-database www_db
```

该函数用于获取所有数据库的参数。

命令: v-list-database-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-database-host)

查看数据库主机

**选项**: `TYPE` `HOST` `[FORMAT]`

**示例**:

```bash
v-list-database-host mysql localhost
```

该函数用于获取数据库主机参数。

命令: v-list-database-hosts

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-database-hosts)

查看数据库主机

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-database-hosts json
```

该函数用于获取所有配置的数据库主机的列表。

命令: v-list-database-types

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-database-types)

查看支持的数据库类型

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-database-types json
```

该函数用于获取数据库类型列表。

命令: v-list-databases

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-databases)

查看数据库

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-databases user json
```

该函数用于获取所有用户的数据库列表。

命令: v-list-default-php

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-default-php)

查看默认模板使用的默认 PHP 版本

**选项**: `[FORMAT]`

查看默认模板使用的默认PHP 版本

命令: v-list-dns-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-dns-domain)

查看 DNS 域

**选项**: `USER` `DOMAIN` `[FORMAT]`

**示例**:

```bash
v-list-dns-domain alice wonderland.com
```

该功能获取dns域名参数列表。

命令: v-list-dns-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-dns-domains)

查看 DNS 域

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-dns-domains admin
```

该函数用于获取用户的所有DNS域名。

命令: v-list-dns-records

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-dns-records)

查看 dns 域记录

**选项**: `USER` `DOMAIN` `[FORMAT]`

**示例**:

```bash
v-list-dns-records admin example.com
```

该函数用于获取所有 DNS 域记录。

命令: v-list-dns-template

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-dns-template)

查看 DNS 模板

**选项**: `TEMPLATE` `[FORMAT]`

**示例**:

```bash
v-list-dns-template zoho
```

该函数用于获取DNS模板参数。

命令: v-list-dns-templates

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-dns-templates)

查看 DNS 模板

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-dns-templates json
```

此函数用于获取所有可用 DNS 模板的列表。

命令: v-list-dnssec-public-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-dnssec-public-key)

查看公共 DNSSEC 密钥

**选项**: `USER` `DOMAIN` `[FROMAT]`

**示例**:

```bash
v-list-dns-public-key admin acme.com
```

此函数查看了与 DNSSEC 一起使用的公钥，并且需要添加到域寄存器中。

命令: v-list-firewall

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-firewall)

查看 iptables 规则

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-firewall json
```

该函数获取所有iptables规则列表。

命令: v-list-firewall-ban

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-firewall-ban)

查看防火墙阻止列表

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-firewall-ban json
```

该功能获取当前被阻止的ip列表。

命令: v-list-firewall-ipset

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-firewall-ipset)

查看防火墙 ipset

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-firewall-ipset json
```

此函数打印定义的 ipset 列表

命令: v-list-firewall-rule

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-firewall-rule)

查看防火墙规则

**选项**: `RULE` `[FORMAT]`

**示例**:

```bash
v-list-firewall-rule 2
```

该功能获取防火墙规则参数。

命令: v-list-fs-directory

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-fs-directory)

查看目录

**选项**: `USER` `DIRECTORY`

**示例**:

```bash
v-list-fs-directory /home/admin/web
```

该函数查看文件系统上的目录

命令: v-list-letsencrypt-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-letsencrypt-user)

查看加密密钥

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-letsencrypt-user admin
```

此函数用于获取 LetsEncrypt 密钥指纹

命令: v-list-mail-account

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-mail-account)

查看邮件域帐户

**选项**: `USER` `DOMAIN` `ACCOUNT` `[FORMAT]`

**示例**:

```bash
v-list-mail-account admin domain.tld tester
```

该函数获取账户参数列表。

命令: v-list-mail-account-autoreply

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-mail-account-autoreply)

查看邮件帐户自动回复

**选项**: `USER` `DOMAIN` `ACCOUNT` `[FORMAT]`

**示例**:

```bash
v-list-mail-account-autoreply admin example.com testing
```

此功能获取邮件帐户自动回复消息。

命令: v-list-mail-accounts

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-mail-accounts)

查看邮件域帐户

**选项**: `USER` `DOMAIN` `[FORMAT]`

**示例**:

```bash
v-list-mail-accounts admin acme.com
```

该功能获取所有用户域的列表。

命令: v-list-mail-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-mail-domain)

查看邮件域

**选项**: `USER` `DOMAIN` `[FORMAT]`

**示例**:

```bash
v-list-mail-domain user01 mydomain.com
```

该函数获取域参数列表。

命令: v-list-mail-domain-dkim

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-mail-domain-dkim)

查看邮件域 dkim

**选项**: `USER` `DOMAIN` `[FORMAT]`

**示例**:

```bash
v-list-mail-domain-dkim admin maildomain.tld
```

这个获取域dkim文件的配置。

命令: v-list-mail-domain-dkim-dns

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-mail-domain-dkim-dns)

查看邮件域 dkim dns 记录

**选项**: `USER` `DOMAIN` `[FORMAT]`

**示例**:

```bash
v-list-mail-domain-dkim-dns admin example.com
```

此功能获取域 dkim dns 记录以进行正确设置。

命令: v-list-mail-domain-ssl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-mail-domain-ssl)

查看邮件域 ssl 证书

**选项**: `USER` `DOMAIN` `[FORMAT]`

**示例**:

```bash
v-list-mail-domain-ssl user acme.com json
```

这个获取域名ssl文件的功能。

命令: v-list-mail-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-mail-domains)

查看邮件域

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-mail-domains admin
```

该功能获取所有用户域的列表。

命令: v-list-remote-dns-hosts

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-remote-dns-hosts)

查看远程 DNS 主机

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-remote-dns-hosts json
```

该函数用于获取远程dns主机列表。

命令: v-list-sys-clamd-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-clamd-config)

查看clamd配置参数

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-sys-clamd-config
```

该函数用于获取clamd配置参数列表。

命令: v-list-sys-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-config)

查看系统配置

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-sys-config json
```

该函数用于获取系统参数列表。

命令: v-list-sys-cpu-status

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-cpu-status)

查看系统CPU信息

**选项**:

选项:

命令: v-list-sys-db-status

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-db-status)

查看数据库状态

**选项**:

选项:

命令: v-list-sys-disk-status

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-disk-status)

查看磁盘信息

**选项**:

选项:

命令: v-list-sys-dns-status

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-dns-status)

查看 DNS 状态

**选项**:

选项:

命令: v-list-sys-dovecot-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-dovecot-config)

查看 dovecot 配置参数

**选项**: `[FORMAT]`

该函数用于获取 dovecot 配置参数列表。

命令: v-list-sys-hestia-autoupdate

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-hestia-autoupdate)

查看 Hestia 自动更新设置

**选项**: `[FORMAT]`

该函数用于获取自动更新设置。

命令: v-list-sys-hestia-ssl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-hestia-ssl)

查看 Hestia ssl 证书

**选项**: `[FORMAT]`

这个获取hestia ssl文件的功能。

命令: v-list-sys-hestia-updates

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-hestia-updates)

查看系统更新

**选项**: `[FORMAT]`

此函数检查 hestia 软件包的可用更新。

命令: v-list-sys-info

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-info)

查看系统操作系统

**选项**: `[FORMAT]`

此函数检查 hestia 软件包的可用更新。

命令: v-list-sys-interfaces

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-interfaces)

查看系统接口

**选项**: `[FORMAT]`

该函数用于获取网络接口列表。

命令: v-list-sys-ip

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-ip)

查看系统IP

**选项**: `IP` `[FORMAT]`

**示例**:

```bash
v-list-sys-ip 203.0.113.1
```

该函数用于获取系统IP参数列表。

命令: v-list-sys-ips

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-ips)

查看系统IP

**选项**: `[FORMAT]`

该函数用于获取系统IP地址列表。

命令: v-list-sys-languages

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-languages)

查看系统语言

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-sys-languages json
```

此函数用于获取 HestiaCP 的可用语言，输出始终采用 ISO 语言代码

命令: v-list-sys-mail-status

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-mail-status)

查看邮件状态

**选项**:

选项:

命令: v-list-sys-memory-status

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-memory-status)

查看虚拟内存信息

**选项**:

选项:

命令: v-list-sys-mysql-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-mysql-config)

查看 mysql 配置参数

**选项**: `[FORMAT]`

This function for obtaining the list of mysql config parameters.

命令: v-list-sys-network-status

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-network-status)

查看系统网络状态

**选项**:

选项:

命令: v-list-sys-nginx-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-nginx-config)

查看 nginx 配置参数

**选项**: `[FORMAT]`

该函数用于获取nginx配置参数列表。

命令: v-list-sys-pgsql-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-pgsql-config)

查看 postgresql 配置参数

**选项**: `[FORMAT]`

该函数用于获取 postgresql 配置参数列表。

命令: v-list-sys-php

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-php)

查看已安装的可用 PHP 版本

**选项**: `[FORMAT]`

查看 /etc/php/\* 版本检查文件夹 fpm 是否可用

命令: v-list-sys-php-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-php-config)

查看 php 配置参数

**选项**: `[FORMAT]`

该函数用于获取php配置参数列表。

命令: v-list-sys-proftpd-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-proftpd-config)

查看 proftpd 配置参数

**选项**: `[FORMAT]`

该函数用于获取proftpd配置参数列表。

命令: v-list-sys-rrd

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-rrd)

查看系统rrd图表

**选项**: `[FORMAT]`

查看可用的 rrd 图形、其标题和路径。

命令: v-list-sys-services

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-services)

查看系统服务

**选项**: `[FORMAT]`

**示例**:

```bash
v-list-sys-services json
```

该函数用于获取已配置的系统服务列表。

命令: v-list-sys-shells

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-shells)

查看系统 shell

**选项**: `[FORMAT]`

该函数用于获取系统 shell 列表。

命令: v-list-sys-spamd-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-spamd-config)

查看 spamassassin 配置参数

**选项**: `[FORMAT]`

该函数用于获取 spamassassin 配置参数列表，是一种安装在邮件伺服主机上的邮件过滤器。

命令: v-list-sys-sshd-port

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-sshd-port)

查看 sshd 端口

**选项**: `[FORMAT]`

该函数用于获取sshd监听的端口

命令: v-list-sys-themes

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-themes)

查看系统主题

**选项**: `[FORMAT]`

该函数用于获取主题中的主题列表库并在后端或用户界面中显示它们。

命令: v-list-sys-users

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-users)

查看系统用户

**选项**: `[FORMAT]`

该函数用于获取系统用户列表，无需详细资料。

命令: v-list-sys-vsftpd-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-vsftpd-config)

查看 vsftpd 配置参数

**选项**: `[FORMAT]`

该函数用于获取 vsftpd 配置参数列表。

命令: v-list-sys-web-status

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-web-status)

查看网络状态

**选项**:

选项:

命令: v-list-sys-webmail

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-sys-webmail)

查看可用的网络邮件客户端

**选项**: `[FORMAT]`

查看可用的网络邮件客户端

命令: v-list-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user)

查看用户参数

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-user admin
```

该函数获取用户参数。

命令: v-list-user-auth-log

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-auth-log)

查看用户日志

**选项**: `USER` `[FORMAT]`

此功能获取最后 10 个用户命令的列表。

命令: v-list-user-backup

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-backup)

查看用户备份

**选项**: `USER` `BACKUP` `[FORMAT]`

**示例**:

```bash
v-list-user-backup admin admin.2019-05-19_03-31-30.tar
```

该功能获取备份参数列表。 这个调用，正如所有 v*list*\* 调用，支持 3 种格式 - json、shell 和 plain。

命令: v-list-user-backup-exclusions

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-backup-exclusions)

查看备份排除项

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-user-backup-exclusions admin
```

该函数用于获取备份排除列表

命令: v-list-user-backups

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-backups)

查看用户备份

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-user-backups admin
```

此功能用于获取可用用户备份的列表。

命令: v-list-user-ips

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-ips)

查看用户IP

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-user-ips admin
```

该函数用于获取可用IP地址列表。

命令: v-list-user-log

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-log)

查看用户日志

**选项**: `USER` `[FORMAT]`

此功能获取最后 100 个用户命令的列表。

命令: v-list-user-notifications

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-notifications)

查看用户通知

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-user-notifications admin
```

该函数用于获取列表通知

命令: v-list-user-ns

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-ns)

查看用户名称服务器

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-user-ns admin
```

获取用户DNS服务器列表的功能。

命令: v-list-user-package

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-package)

查看用户软件包

**选项**: `PACKAGE` `[FORMAT]`

该函数用于获取系统ip参数列表。

命令: v-list-user-packages

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-packages)

此功能用于查看用户软件包

**选项**: `[FORMAT]`

该函数用于获取可用托管包的列表。

命令: v-list-user-ssh-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-ssh-key)

添加 ssh 密钥

**选项**: `USER` `[FORMAT]`

Lists $user/.ssh/authorized_keys

命令: v-list-user-stats

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-user-stats)

查看用户统计信息

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-user-stats admin
```

此功能用于查看用户统计信息

命令: v-list-users`

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-users)

查看用户

**选项**: `[FORMAT]`

该函数获取所有系统用户的列表。

命令: `v-list-users-stats`

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-users-stats)

查看总体用户统计数据

**选项**: `[FORMAT]`

此功能用于查看总体用户统计数据

命令: `v-list-web-domain`

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-web-domain)

查看 Web 域参数

**选项**: `USER` `DOMAIN` `[FORMAT]`

**示例**:

```bash
v-list-web-domain admin example.com
```

该函数用于获取web域参数。

命令: v-list-web-domain-accesslog

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-web-domain-accesslog)

查看Web域访问日志

**选项**: `USER` `DOMAIN` `[LINES]` `[FORMAT]`

**示例**:

```bash
v-list-web-domain-accesslog admin example.com
```

该功能获取原始访问Web域日志。

命令: v-list-web-domain-errorlog

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-web-domain-errorlog)

查看 Web 域错误日志

**选项**: `USER` `DOMAIN` `[LINES]` `[FORMAT]`

**示例**:

```bash
v-list-web-domain-errorlog admin acme.com
```

该功能获取原始错误Web域日志。

命令: v-list-web-domain-ssl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-web-domain-ssl)

查看 Web 域 ssl 证书

**选项**: `USER` `DOMAIN` `[FORMAT]`

**示例**:

```bash
v-list-web-domain-ssl admin wonderland.com
```

这个获取域名ssl文件的功能。

命令: v-list-web-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-web-domains)

查看网络域

**选项**: `USER` `[FORMAT]`

**示例**:

```bash
v-list-web-domains alice
```

此函数用于获取所有用户 Web 域的列表。

命令: v-list-web-stats

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-web-stats)

查看网络统计数据

**选项**: `[FORMAT]`

该函数用于获取网页统计分析器列表。

命令: v-list-web-templates

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-web-templates)

查看网页模板

**选项**: `[FORMAT]`

该函数用于获取用户可用的网页模板列表。

命令: v-list-web-templates-backend

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-web-templates-backend)

查看后端模板

**选项**: `[FORMAT]`

该函数用于获取可用后端模板的列表。

命令: v-list-web-templates-proxy

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-list-web-templates-proxy)

查看代理模板

**选项**: `[FORMAT]`

此函数用于获取用户可用的代理模板列表。

命令: v-log-action

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-log-action)

将操作事件添加到用户或系统日志

**选项**: `LOG_TYPE` `USER`

## 打开和移动文件及重命名的系列命令

命令: v-log-user-login

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-log-user-login)

添加用户登录

**选项**: `USER` `IP` `STATUS` `[FINGERPRINT]`

命令: v-log-user-logout

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-log-user-logout)

记录用户注销事件

**选项**: `USER` `FINGERPRINT`

命令: v-move-fs-directory

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-move-fs-directory)

移动文件

**选项**: `USER` `SRC_DIRECTORY` `DST_DIRECTORY`

**示例**:

```bash
v-move-fs-directory admin /home/admin/web /home/user02/
```

此函数移动文件系统上的文件或目录。 这个功能也可以像普通的 mv 命令一样用于重命名文件。

命令: v-move-fs-file

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-move-fs-file)

移动文件

**选项**: `USER` `SRC_FILE` `DST_FILE`

**示例**:

```bash
v-move-fs-file admin readme.txt new_readme.txt
```

此函数移动文件系统上的文件或目录。 这个功能也可以像普通的 mv 命令一样用于重命名文件。

命令: v-open-fs-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-open-fs-config)

打开配置

**选项**: `CONFIG`

**示例**:

```bash
v-open-fs-config /etc/mysql/my.cnf
```

该函数打开/读取文件系统上的配置文件

命令: v-open-fs-file

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-open-fs-file)

open file

**选项**: `USER` `FILE`

**示例**:

```bash
v-open-fs-file admin README.md
```

该函数打开/读取文件系统上的文件

命令: v-purge-nginx-cache

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-purge-nginx-cache)

清除 nginx 缓存

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-purge-nginx-cache user domain.tld
```

该函数清除 nginx 缓存。

## 新建文件及文件夹系列命令

命令: v-rebuild-all

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-all)

新建指定用户的所有资产

**选项**: `USER` `[RESTART]`

此函数新建用户帐户的所有资产：

命令: v-rebuild-cron-jobs

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-cron-jobs)

新建 cron 作业

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-rebuild-cron-jobs admin yes
```

此功能为指定用户新建系统 cron 配置文件。

命令: v-rebuild-database

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-database)

新建数据库

**选项**: `USER` `DATABASE`

**示例**:

```bash
v-rebuild-database user user_wordpress
```

该功能用于为用户新建单个数据库

命令: v-rebuild-databases

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-databases)

新建数据库

**选项**: `USER`

**示例**:

```bash
v-rebuild-databases admin
```

该功能用于新建单个用户的所有数据库。

命令: v-rebuild-dns-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-dns-domain)

新建dns域名

**选项**: `USER` `DOMAIN` `[RESTART]` `[UPDATE_SERIAL]`

**示例**:

```bash
v-rebuild-dns-domain alice wonderland.com
```

此功能新建 DNS 配置文件。

命令: v-rebuild-dns-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-dns-domains)

新建dns域

**选项**: `USER` `[RESTART]` `[UPDATE_SERIAL]`

**示例**:

```bash
v-rebuild-dns-domains alice
```

此功能新建 DNS 配置文件。

命令: v-rebuild-mail-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-mail-domain)

新建邮件域

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-rebuild-mail-domain user domain.tld
```

此功能为单个域新建配置文件。

命令: v-rebuild-mail-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-mail-domains)

新建邮件域

**选项**: `USER`

**示例**:

```bash
v-rebuild-mail-domains admin
```

此功能新建所有邮件域的 EXIM 配置文件。

命令: v-rebuild-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-user)

新建系统用户

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-rebuild-user admin yes
```

此功能新建系统用户帐户。

命令: v-rebuild-users

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-users)

新建系统用户

**选项**: `[RESTART]`

此功能为所有用户新建用户配置。

命令: v-rebuild-web-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-web-domain)

新建网站域名

**选项**: `USER` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-rebuild-web-domain user domain.tld
```

此功能新建 Web 配置文件。

命令: v-rebuild-web-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rebuild-web-domains)

新建网域

**选项**: `USER` `[RESTART]`

此功能新建 Web 配置文件。

命令: v-refresh-sys-theme

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-refresh-sys-theme)

更改活动系统主题

**选项**: –

此功能用于更改当前活动的系统主题。

命令: v-rename-user-package

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-rename-user-package)

更改软件包名称

**选项**: `OLD_NAME` `NEW_NAME` `[MODE]`

**示例**:

```bash
v-rename-package package package2
```

此函数更改现有软件包的名称。

## 恢复和重启系列命令 

命令: v-repair-sys-config

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-repair-sys-config)

恢复系统配置

**选项**: `[SYSTEM]`

该功能修复或恢复系统配置文件。

命令: v-restart-cron

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restart-cron)

重新启动 cron 服务

**选项**: –

该函数告诉 crond 服务重新读取其配置文件。

命令: v-restart-dns

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restart-dns)

重启dns服务

**选项**: –

此函数告诉 BIND 服务重新加载 dns 区域文件。

命令: v-restart-ftp

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restart-ftp)

重新启动 ftp 服务

**选项**: –

该函数告诉 ftp 服务器重新读取其配置。

命令: v-restart-mail

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restart-mail)

重新启动邮件服务

**选项**: `[RESTART]`

此函数告诉 exim 或 dovecot 服务重新加载配置文件。

命令: v-restart-proxy

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restart-proxy)

重新启动代理服务器

**选项**: –

**示例**:

```bash
v-restart-proxy [RESTART]
```

此函数重新加载代理服务器配置。

命令: v-restart-service

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restart-service)

重启服务

**选项**: `SERVICE` `[RESTART]`

**示例**:

```bash
v-restart-service apache2
```

该函数会重启系统服务。

命令: v-restart-system

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restart-system)

重新启动操作系统

**选项**: `RESTART` `[DELAY]`

**示例**:

```bash
v-restart-system yes
```

该函数会重新启动操作系统。

命令: v-restart-web

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restart-web)

重新启动网络服务器

**选项**: `[RESTARRT]`

此函数重新加载 Web 服务器配置。

命令: v-restart-web-backend

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restart-web-backend)

重启php解释器

**选项**: –

该函数重新加载 php 解释器配置。

命令: v-restore-cron-job

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restore-cron-job)

恢复单个 cron 作业

**选项**: `USER` `BACKUP` `DOMAIN` `[NOTIFY]`

**示例**:

```bash
v-restore-cron-job USER BACKUP CRON [NOTIFY]
```

该功能允许用户恢复单个 cron 作业来自备份存档。

命令: v-restore-database

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restore-database)

恢复单个数据库

**选项**: `USER` `BACKUP` `DATABASE` `[NOTIFY]`

**示例**:

```bash
v-restore-database USER BACKUP DATABASE [NOTIFY]
```

该功能允许用户恢复单个数据库来自备份存档。

命令: v-restore-dns-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restore-dns-domain)

恢复单个 DNS 域

**选项**: `USER` `BACKUP` `DOMAIN` `[NOTIFY]`

**示例**:

```bash
v-restore-dns-domain USER BACKUP DOMAIN [NOTIFY]
```

此功能允许用户恢复单个 DNS 域，来自备份存档。

命令: v-restore-mail-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restore-mail-domain)

恢复单个邮件域

**选项**: `USER` `BACKUP` `DOMAIN` `[NOTIFY]`

**示例**:

```bash
v-restore-mail-domain USER BACKUP DOMAIN [NOTIFY]
```

该功能允许用户恢复单个邮件域。来自备份存档。

命令: v-restore-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restore-user)

恢复用户

**选项**: `USER` `BACKUP` `[WEB]` `[DNS]` `[MAIL]` `[DB]` `[CRON]` `[UDIR]` `[NOTIFY]`

**示例**:

```bash
v-restore-user admin 2019-04-22_01-00-00.tar
```

此功能用于从备份中恢复用户。 为了能够恢复备份，存档需要放置在/backup 中。

命令: v-restore-web-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-restore-web-domain)

恢复单个 Web 域

**选项**: `USER` `BACKUP` `DOMAIN` `[NOTIFY]`

**示例**:

```bash
v-restore-web-domain USER BACKUP DOMAIN [NOTIFY]
```

此功能允许用户恢复单个 Web 域。来自备份存档。

命令: v-revoke-api-key

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-revoke-api-key)

撤销 API 密钥

**选项**: `[HASH]`

**示例**:

```bash
v-revoke-api-key mykey
```

此函数从 $HESTIA/data/keys/ 中删除一个密钥

## 运行系列命令

命令:`v-run-cli-cmd`

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-run-cli-cmd)

运行 cli 命令

**选项**: `USER` `CMD` `[ARG...]`

**示例**:

```bash
v-run-cli-cmd user composer require package
```

此函数运行有限的 cli 命令列表，并删除特定 hestia 用户的权限

命令: v-schedule-letsencrypt-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-schedule-letsencrypt-domain)

添加 cron 作业来安装 LetsEncrypt 证书

**选项**: `USER` `DOMAIN` `[ALIASES]`

**示例**:

```bash
v-schedule-letsencrypt-domain admin example.com www.example.com
```

该函数添加了letsencrypt ssl证书安装的cronjob

命令: v-schedule-user-backup

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-schedule-user-backup)

安排用户备份创建

**选项**: `USER`

**示例**:

```bash
v-schedule-user-backup admin
```

此功能用于安排用户备份创建。

命令: v-schedule-user-backup-download

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-schedule-user-backup-download)

安排备份

**选项**: `USER` `BACKUP`

**示例**:

```bash
v-schedule-user-backup-download admin 2019-04-22_01-00-00.tar
```

此功能用于安排用户备份创建。

命令: v-schedule-user-restore

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-schedule-user-restore)

安排用户备份恢复

**选项**: `USER` `BACKUP` `[WEB]` `[DNS]` `[MAIL]` `[DB]` `[CRON]` `[UDIR]`

**示例**:

```bash
v-schedule-user-restore 2019-04-22_01-00-00.tar
```

该功能用于安排用户备份恢复。

## 搜索系统命令

命令: v-search-command

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-search-command)

搜索可用命令

**选项**: `ARG1` `[ARG...]`

**示例**:

```bash
v-search-command web
```

此功能搜索可用的 Hestia 控制面板命令
并根据指定条件返回结果。
最初由 Federico Krum 为 VestaCP 开发[Federico Krum的github](https://github.com/FastDigitalOceanDroplets/VestaCP/blob/master/files/v-search-command)

命令: v-search-domain-owner

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-search-domain-owner)

搜索域名所有者

**选项**: `DOMAIN` `[TYPE]`

**示例**:

```bash
v-search-domain-owner acme.com
```

该函数允许查找用户对象。

命令: v-search-fs-object

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-search-fs-object)

搜索文件或目录

**选项**: `USER` `OBJECT` `[PATH]`

**示例**:

```bash
v-search-fs-object admin hello.txt
```

该函数搜索文件系统上的文件和目录

命令: v-search-object

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-search-object)

搜索对象

**选项**: `OBJECT` `[FORMAT]`

**示例**:

```bash
v-search-object example.com json
```

该函数允许查找系统对象。

命令: v-search-user-object

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-search-user-object)

搜索对象

**选项**: `USER` `OBJECT` `[FORMAT]`

**示例**:

```bash
v-search-user-object admin example.com json
```

该函数允许查找用户对象。

## 启动/暂停/停止系列命令

命令: v-start-service

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-start-service)

启动服务

**选项**: `SERVICE`

**示例**:

```bash
v-start-service mysql
```

该函数启动系统服务。

命令: v-stop-firewall

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-stop-firewall)

停止系统防火墙

**选项**: –

该函数用于停止系统防火墙

命令: v-stop-service

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-stop-service)

停止服务

**选项**: `SERVICE`

**示例**:

```bash
v-stop-service apache2
```

该函数停止系统服务。

命令: v-suspend-cron-job

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-cron-job)

暂停 cron 作业

**选项**: `USER` `JOB` `[RESTART]`

**示例**:

```bash
v-suspend-cron-job admin 5 yes
```

该函数暂停 cron 调度程序的某个作业。

命令: v-suspend-cron-jobs

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-cron-jobs)

暂停 sys cron 作业

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-suspend-cron-jobs admin
```

此函数暂停所有用户 cron 作业。

命令: v-suspend-database

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-database)

暂停数据库

**选项**: `USER` `DATABASE`

**示例**:

```bash
v-suspend-database admin admin_wordpress_db
```

该函数用于暂停某个用户数据库。

命令: v-suspend-database-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-database-host)

暂停数据库服务器

**选项**: `TYPE` `HOST`

**示例**:

```bash
v-suspend-database-host mysql localhost
```

该函数用于暂停数据库服务器。

命令: v-suspend-databases

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-databases)

暂停数据库

**选项**: `USER`

**示例**:

```bash
v-suspend-databases admin
```

该功能用于暂停单个用户的所有数据库。

命令: v-suspend-dns-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-dns-domain)

暂停 DNS 域

**选项**: `USER` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-suspend-dns-domain alice acme.com
```

该功能可以暂停某个用户的域。

命令: v-suspend-dns-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-dns-domains)

暂停 DNS 域

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-suspend-dns-domains admin yes
```

此功能暂停所有用户的 DNS 域。

命令: v-suspend-dns-record

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-dns-record)

暂停DNS域名记录

**选项**: `USER` `DOMAIN` `ID` `[RESTART]`

**示例**:

```bash
v-suspend-dns-record alice wonderland.com 42 yes
```

该功能暂停某个域记录。

命令: v-suspend-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-domain)

暂停 web/dns/mail 域

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-suspend-domain admin example.com
```

此功能暂停 web/dns/mail 域。

命令: v-suspend-firewall-rule

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-firewall-rule)

暂停防火墙规则

**选项**: `RULE`

**示例**:

```bash
v-suspend-firewall-rule 7
```

该功能暂停某个防火墙规则。

命令: v-suspend-mail-account

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-mail-account)

暂停邮件帐户

**选项**: `USER` `DOMAIN` `ACCOUNT`

**示例**:

```bash
v-suspend-mail-account admin acme.com bob
```

此功能暂停邮件帐户。

命令: v-suspend-mail-accounts

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-mail-accounts)

暂停所有邮件域帐户

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-suspend-mail-accounts admin example.com
```

此功能暂停所有邮件域帐户。

命令: v-suspend-mail-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-mail-domain)

暂停邮件域

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-suspend-mail-domain admin domain.com
```

此功能暂停邮件域。

命令: v-suspend-mail-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-mail-domains)

暂停邮件域

**选项**: `USER`

**示例**:

```bash
v-suspend-mail-domains admin
```

此功能暂停所有用户的 MAIL 域。

命令: v-suspend-remote-dns-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-remote-dns-host)

暂停远程 DNS 服务器

**选项**: `HOST`

**示例**:

```bash
v-suspend-remote-dns-host hostname.tld
```

该功能用于暂停远程dns服务器。

命令: v-suspend-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-user)

暂停用户

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-suspend-user alice yes
```

该函数暂停某个用户及其所有对象。

命令: v-suspend-web-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-web-domain)

暂停域名

**选项**: `USER` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-suspend-web-domain admin example.com yes
```

该功能用于暂停网站的运行。 全部封锁之后访问者将被重定向到解释暂停原因的网页。通过阻止该站点，其所有目录的内容将保持不变。

命令: v-suspend-web-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-suspend-web-domains)

暂停域名

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-suspend-web-domains bob
```

此功能暂停所有用户的网站。

## 恢复和同步文件及文件夹系列命令

命令: v-sync-dns-cluster

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-sync-dns-cluster)

同步 DNS 域

**选项**: `HOST`

此功能同步所有 dns 域。

命令: v-unsuspend-cron-job

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-cron-job)

恢复暂停 cron 作业

**选项**: `USER` `JOB` `[RESTART]`

**示例**:

```bash
v-unsuspend-cron-job admin 7 yes
```

此功能恢复暂停某些 cron 作业。

命令: v-unsuspend-cron-jobs

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-cron-jobs)

恢复暂停系统 cron

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-unsuspend-cron-jobs admin no
```

此函数恢复暂停所有暂停的 cron 作业。

命令: v-unsuspend-database

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-database)

恢复暂停数据库

**选项**: `USER` `DATABASE`

**示例**:

```bash
v-unsuspend-database admin mydb
```

该函数用于恢复暂停数据库。

命令: v-unsuspend-database-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-database-host)

该函数用于恢复暂停数据库。

**选项**: `TYPE` `HOST`

**示例**:

```bash
v-unsuspend-database-host mysql localhost
```

该函数用于恢复暂停数据库服务器。

命令: v-unsuspend-databases

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-databases)

恢复暂停数据库

**选项**: `USER`

此功能用于恢复暂停所有用户的数据库。

命令: v-unsuspend-dns-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-dns-domain)

恢复暂停 DNS 域

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-unsuspend-dns-domain alice wonderland.com
```

此功能恢复暂停某个用户的域。

命令: v-unsuspend-dns-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-dns-domains)

恢复暂停 DNS 域

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-unsuspend-dns-domains alice
```

此功能恢复暂停所有用户的 DNS 域。

命令: v-unsuspend-dns-record

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-dns-record)

恢复暂停 DNS 域名记录

**选项**: `USER` `DOMAIN` `ID` `[RESTART]`

**示例**:

```bash
v-unsuspend-dns-record admin example.com 33
```

此功能恢复暂停某个域记录。

命令: v-unsuspend-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-domain)

恢复暂停 web/dns/mail 域

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-unsuspend-domain admin acme.com
```

此功能恢复暂停 web/dns/mail 域。

命令: v-unsuspend-firewall-rule

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-firewall-rule)

恢复暂停防火墙规则

**选项**: `RULE`

**示例**:

```bash
v-unsuspend-firewall-rule 7
```

此功能恢复暂停某个防火墙规则。

命令: v-unsuspend-mail-account

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-mail-account)

恢复暂停邮件帐户

**选项**: `USER` `DOMAIN` `ACCOUNT`

**示例**:

```bash
v-unsuspend-mail-account admin acme.com tester
```

此功能可恢复暂停邮件帐户。

命令: v-unsuspend-mail-accounts

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-mail-accounts)

恢复暂停所有邮件域帐户

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-unsuspend-mail-accounts admin acme.com
```

此功能恢复暂停所有邮件域帐户。

命令: v-unsuspend-mail-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-mail-domain)

恢复暂停邮件域

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-unsuspend-mail-domain user02 acme.com
```

此功能恢复挂起邮件域。

命令: v-unsuspend-mail-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-mail-domains)

恢复暂停邮件域

**选项**: `USER`

**示例**:

```bash
v-unsuspend-mail-domains admin
```

此功能恢复暂停所有用户的 MAIL 域。

命令: v-unsuspend-remote-dns-host

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-remote-dns-host)

恢复挂起远程 DNS 服务器

**选项**: `HOST`

**示例**:

```bash
v-unsuspend-remote-dns-host hosname.com
```

此功能用于恢复挂起远程 dns 服务器。

命令: v-unsuspend-user

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-user)

恢复暂停用户

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-unsuspend-user bob
```

此函数恢复暂停用户及其所有对象。

命令: v-unsuspend-web-domain

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-web-domain)

恢复暂停域名

**选项**: `USER` `DOMAIN` `[RESTART]`

**示例**:

```bash
v-unsuspend-web-domain admin acme.com
```

这个功能就是恢复域名挂起。

命令: v-unsuspend-web-domains

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-unsuspend-web-domains)

恢复暂停域名

**选项**: `USER` `[RESTART]`

**示例**:

```bash
v-unsuspend-web-domains admin
```

此功能可以恢复暂停所有用户的站点。

## 更新配置和文件系列命令

命令: v-update-database-disk

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-database-disk)

更新数据库磁盘使用情况

**选项**: `USER` `DATABASE`

**示例**:

```bash
v-update-database-disk admin www_db
```

此函数重新计算特定数据库的磁盘使用情况。

命令: v-update-databases-disk

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-databases-disk)

更新数据库磁盘使用情况

**选项**: `USER`

**示例**:

```bash
v-update-databases-disk admin
```

此函数重新计算所有用户数据库的磁盘使用情况。

命令: v-update-dns-templates

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-dns-templates)

更新 DNS 模板

**选项**: `[RESTART]`

此函数用于从 Hestia 包获取更新的 dns 模板。

命令: v-update-firewall

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-firewall)

更新系统防火墙规则

**选项**: –

该函数更新系统防火墙规则

命令: v-update-firewall-ipset

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-firewall-ipset)

更新防火墙ipset

**选项**: `[REFRESH]`

此函数创建 ipset 列表，并在列表过期或按需时更新列表

命令: v-update-host-certificate

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-host-certificate)

更新 hestia 的主机证书

**选项**: `USER` `HOSTNAME`

**示例**:

```bash
v-update-host-certificate admin example.com
```

此功能更新用于 Hestia 控制面板的 SSL 证书.

命令: v-update-letsencrypt-ssl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-letsencrypt-ssl)

更新letsencrypt ssl证书

**选项**: –

This function for renew letsencrypt expired ssl certificate for all users

命令: v-update-mail-domain-disk

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-mail-domain-disk)

更新邮件域磁盘使用情况

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-update-mail-domain-disk admin example.com
```

此功能更新域磁盘使用情况。

命令: v-update-mail-domain-ssl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-mail-domain-ssl)

更新域的 ssl 证书

**选项**: `USER` `DOMAIN` `SSL_DIR` `[RESTART]`

**示例**:

```bash
v-update-mail-domain-ssl admin domain.com /home/admin/tmp
```

此函数更新域的 SSL 证书。 参数 ssl_dir 是一个路径到可以找到 2 或 3 个 ssl 文件的目录。 证书文件domain.tld.crt 及其密钥domain.tld.key 是强制性的。中间证书权限domain.tld.ca 文件是可选的。

命令: v-update-mail-domains-disk

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-mail-domains-disk)

计算所有邮件域的磁盘使用情况

**选项**: `USER`

**示例**:

```bash
v-update-mail-domains-disk admin
```

此函数计算所有邮件域的磁盘使用情况。

命令: v-update-mail-templates

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-mail-templates)

更新邮件模板

**选项**: `[RESTART]` `[SKIP]`

此功能用于从 Hestia 包获取更新的网络邮件模板。

命令: v-update-sys-defaults

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-defaults)

更新默认密钥数据库

**选项**: `[SYSTEM]`

**示例**:

```bash
v-update-sys-defaults
example: v-update-sys-defaults user
```

该函数对数据库更新已知的键/值

命令: v-update-sys-hestia

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-hestia)

更新 hestia 软件包/配置

**选项**: `PACKAGE`

**示例**:

```bash
v-update-sys-hestia hestia-php
```

该函数作为 apt update 触发器运行。 它从 Hestia 中提取 shell 脚本
服务器并运行它。 （hestia、hestia-nginx 和 hestia-php 是有效选项）

命令: v-update-sys-hestia-all

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-hestia-all)

更新所有 hestia 软件包

**选项**: –

此功能更新所有 hestia 软件包

命令: v-update-sys-hestia-git

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-hestia-git)

从 Git 存储库安装更新

**选项**: `REPOSITORY` `BRANCH` `INSTALL`

**示例**:

```bash
v-update-sys-hestia-git hestiacp staging/beta install
# 将从 hestiacp 存储库下载
# 从 staging/beta 分支中提取代码
# install: 立即安装包
# install-auto：安装包并安排 Git 自动更新
```

从 GitHub 存储库下载并编译/安装包

命令: v-update-sys-ip

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-ip)

更新系统IP

**选项**: –

**示例**:

```bash
v-update-sys-ip
# 扫描系统中配置的IP并将其注册到Hestia内部数据库
```

该功能扫描系统中配置的IP并将其注册到Hestia内部数据库。 此调用旨在用于 vps 服务器，其中 IP 是由管理程序设置。

命令: v-update-sys-ip-counters

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-ip-counters)

更新 IP 使用计数器

**选项**: `IP`

函数更新 U_WEB_ADOMAINS 和 U_SYS_USERS 计数器的使用情况。

命令: v-update-sys-queue

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-queue)

更新系统队列

**选项**: `PIPE`

该函数负责队列处理。 重新启动服务，计划备份、Web 日志解析和其他重度 regithub 脚本查看操作由该脚本处理。 它有助于优化系统行为。
简而言之，即使有 10 个域，Apache 也只会重新启动一次添加或删除。

命令: v-update-sys-rrd

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd)

更新系统rrd图表

**选项**: –

该函数是所有rrd 函数的包装器。 它立即更新 allv-update-sys-rrd\_\* 。

命令: v-update-sys-rrd-apache2

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd-apache2)

更新apache2rrd

**选项**: `PERIOD`

该功能用于更新apache rrd数据库和图形。

命令: v-update-sys-rrd-ftp

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd-ftp)

更新 ftp RRD

**选项**: `PERIOD`

此功能用于更新 ftpd rrd 数据库和图形。

命令: v-update-sys-rrd-httpd

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd-httpd)

更新httpd rrd

**选项**: `PERIOD`

该功能用于更新apache rrd数据库和图形。

命令: v-update-sys-rrd-la

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd-la)

更新平均负载 rrd

**选项**: `PERIOD`

该功能用于更新平均负载rrd数据库和图形。

命令: v-update-sys-rrd-mail

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd-mail)

更新邮件rrd

**选项**: `PERIOD`

该功能用于更新邮件rrd数据库和图形。

命令: v-update-sys-rrd-mem

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd-mem)

更新内存 rrd

**选项**: `PERIOD`

该功能用于更新内存rrd数据库和图形。

命令: v-update-sys-rrd-mysql

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd-mysql)

更新MySQL rrd

**选项**: `PERIOD`

该功能用于更新mysql rrd数据库和图形。

命令: v-update-sys-rrd-net

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd-net)

更新网络 rrd

**选项**: `PERIOD`

该功能用于更新网络使用rrd数据库和图形。

命令: v-update-sys-rrd-nginx

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd-nginx)

更新 nginx rrd

**选项**: `PERIOD`

该函数用于更新nginx rrd数据库和图形。

命令: v-update-sys-rrd-pgsql

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd-pgsql)

更新 PostgreSQL rrd

**选项**: `PERIOD`

该函数用于更新postgresql rrd数据库和图形。

命令: v-update-sys-rrd-ssh

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-sys-rrd-ssh)

更新 ssh rrd

**选项**: `PERIOD`

该功能用于更新ssh rrd数据库和图形。

命令: v-update-user-backup-exclusions

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-user-backup-exclusions)

更新备份排除列表

**选项**: `USER` `FILE`

**示例**:

```bash
v-update-user-backup-exclusions admin /tmp/backup_exclusions
```

此功能用于更新备份排除列表

命令: v-update-user-counters

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-user-counters)

更新用户使用计数器

**选项**: `USER`

**示例**:

```bash
v-update-user-counters admin
```

函数更新使用计数器，如 U_WEB_DOMAINS、U_MAIL_ACCOUNTS 等。

命令: v-update-user-disk

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-user-disk)

更新用户磁盘使用情况

**选项**: `USER`

**示例**:

```bash
v-update-user-disk admin
```

该函数重新计算磁盘使用情况并更新数据库。

命令: v-update-user-package

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-user-package)

更新用户软件包

**选项**: `PACKAGE`

**示例**:

```bash
v-update-user-package default
```

此函数将软件包传播给连接的用户。

命令: v-update-user-quota

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-user-quota)

更新用户磁盘配额

**选项**: `USER`

**示例**:

```bash
v-update-user-quota alice
```

该功能更新特定用户的磁盘配额

命令: v-update-user-stats

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-user-stats)

更新用户统计数据

**选项**: `USER`

**示例**:

```bash
v-update-user-stats admin
```

功能将用户参数记录到统计数据库中。

命令: v-update-web-domain-disk

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-web-domain-disk)

更新域的磁盘使用情况

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-update-web-domain-disk alice wonderland.com
```

此函数重新计算特定 Web 域的磁盘使用情况。

命令: v-update-web-domain-ssl

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-web-domain-ssl)

更新域的 ssl 证书

**选项**: `USER` `DOMAIN` `SSL_DIR` `[RESTART]`

**示例**:

```bash
v-update-web-domain-ssl admin domain.com /home/admin/tmp
```

此函数更新域的 SSL 证书。 参数 ssl_dir 是一个路径到可以找到 2 或 3 个 ssl 文件的目录。 证书文件domain.tld.crt 及其密钥domain.tld.key 是强制性的。 证书权限domain.tld.ca 文件是可选的。

命令: v-update-web-domain-stat

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-web-domain-stat)

更新域统计信息

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-update-web-domain-stat alice acme.com
```

此函数运行特定 Web 域的日志分析器。

命令: v-update-web-domain-traff

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-web-domain-traff)

更新域带宽使用情况

**选项**: `USER` `DOMAIN`

**示例**:

```bash
v-update-web-domain-traff admin example.com
```

此函数重新计算特定域的带宽使用情况。

命令: v-update-web-domains-disk

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-web-domains-disk)

更新域磁盘使用情况

**选项**: `USER`

**示例**:

```bash
v-update-web-domains-disk alice
```

此函数重新计算所有用户网站域的磁盘使用情况。

命令: v-update-web-domains-stat

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-web-domains-stat)

更新域统计信息

**选项**: `USER`

**示例**:

```bash
v-update-web-domains-stat admin
```

此函数运行所有用户 Web 域的日志分析器使用情况。

命令: v-update-web-domains-traff

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-web-domains-traff)

更新域带宽使用情况

**选项**: `USER`

**示例**:

```bash
v-update-web-domains-traff bob
```

此函数重新计算所有用户网域的带宽使用情况。

命令: v-update-web-templates

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-web-templates)

更新网页模板

**选项**: `[RESTART]` `[SKIP]`

此函数用于从 Hestia 包获取更新的 Web (Nginx/Apache2/PHP) 模板。

命令: v-update-white-label-logo

[github脚本查看](https://github.com/hestiacp/hestiacp/blob/release/bin/v-update-white-label-logo)

更新logo标志

**选项**: `[DOWNLOAD]`

用用户创建的徽标替换默认 Hestia 徽标
