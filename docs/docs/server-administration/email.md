# 电子邮件和邮件服务器

## 如何设置通过 SMTP 发送内部邮件？

默认情况下，Hestia 生成的电子邮件（通知、忘记密码、更新日志等）将通过内部邮件发送。 如果需要，您可以设置通过 SMTP 帐户发送邮件。

执行以下脚本并按照说明进行操作：

````bash
bash /usr/local/hestia/install/upgrade/manual/configure-server-smtp.sh
````

## 我无法发送电子邮件

首先，检查端口 25 是否对传出流量开放。 许多提供商默认阻止端口 25 以打击垃圾邮件。

为此，请运行以下命令：

```bash
telnet ASPMX.L.GOOGLE.COM 25
```

如果连接成功，您将看到类似以下内容：

```bash
Trying 2a00:1450:400c:c00::1b...
Connected to ASPMX.L.GOOGLE.COM.
Escape character is '^]'.
220 mx.google.com ESMTP a7si1253985wrr.455 - gsmtp
```

如果没有，您有 2 个选择：

1. 联系您的提供商并要求他们为传出流量打开端口 25。
2. 在邮件域设置下设置邮件中继或在系统设置中为服务器一般设置。 为此，您需要使用 SMTP 中继服务，例如：
    - [亚马逊 SES](https://aws.amazon.com/ses/)
    - [SMTP2GO](https://www.smtp2go.com)
    - [Sendinblue](https://www.sendinblue.com)

## 什么是 SMTP 中继服务以及如何设置它

SMTP 邮件中继是将电子邮件从一台服务器传输到另一台服务器进行传递的过程。 由于担心垃圾邮件，来自服务器的电子邮件通常会被服务提供商阻止。 或者 IP 声誉太低，所有电子邮件都会直接进入垃圾邮件箱。 为了防止此类问题，许多公司提供 SMTP 中继来处理传递部分。 由于他们通过相同的 IP 地址发送大量电子邮件，因此拥有更好的声誉。

要进行设置，请通过您想要或使用的提供商创建一个帐户，然后按照他们的说明更新您的 DNS。 完成后，您可以在“全局 SMTP”下或“编辑邮件域”->“SMTP 中继”下的设置中输入他们提供的 SMTP 用户帐户

## 我无法接收电子邮件

如果您无法接收电子邮件，请确保您已正确设置 DNS。 如果您使用的是 Cloudflare，请禁用“mail.domain.tld”代理。

完成后，您可以通过[MXToolBox](https://mxtoolbox.com/MXLookup.aspx)检查配置。

## 被拒绝， 打开解析器时出错：是因为你的[ip] 位于 [zen.spamhaus.org](https://www.spamhaus.org/returnc/pub/65.1.174.102)的黑名单中

1. 前往[Spamhaus免费数据查询账户](https://www.spamhaus.com/free-Trial/sign-up-for-a-free-data-query-service-account/)
2. 填写表格并通过您收到的电子邮件中的链接验证您的电子邮件地址。
3. 登录后，转到产品 → DQS，您将看到您的查询密钥，在下面您将看到使用 Zen Spamhaus 黑名单所需的确切 FQDN。 类似于：`HereYourQueryKey.zen.dq.spamhaus.net`
4. 编辑 /etc/exim4/dnsbl.conf 并将 `zen.spamhaus.org` 替换为 `HereYourQueryKey.zen.dq.spamhaus.net`
5. 另请编辑 /etc/exim4/exim4.conf.template 行： `deny message = Rejectedbecause $sender_host_address is in a black list at $dnslist_domain\n$dnslist_text` 改为 `deny message = Rejectedbecause $sender_host_address is in 黑名单`以防止您的查询密钥泄露
6. 使用systemctl restart exim4重新启动exim4

## 如何禁用电子邮件的内部查找

如果您使用 SMTP 中继或想要在 Web 服务器上使用 DKIM，但在 Gmail 上托管电子邮件，则需要在 Exim4 中禁用内部查找。

```bash
nano /etc/exim4/exim4.conf.template
```

```bash
dnslookup:
driver = dnslookup
domains = !+local_domains
transport = remote_smtp
no_more
```

Replace with:

```bash
dnslookup:
driver = dnslookup
domains = *
transport = remote_smtp
no_more
```

## 如何安装 SnappyMail？

您可以通过运行以下命令来安装 SnappyMail：

```bash
v-add-sys-snappymail
```

## 我可以登录 SnappyMail 后端吗

在根文件夹中，有一个名为`.snappymail`的文件，其中包含用户名和密码：

```bash
Username: admin_f0e5a5aa
Password: D0ung4naLOptuaa
Secret key: admin_f0e5a5aa
```

您可以通过导航到 `https://webmail.domain.tld/?admin_f0e5a5aa`来访问管理员，并使用您在文件中找到的数据登录。 出于安全原因，一旦不再需要该文件，请删除该文件。

## 我可以通过电子邮件使用 Cloudflare 代理吗

不可以，Cloudflare 的代理不适用于电子邮件。 如果您使用服务器上托管的电子邮件，请确保 A 记录“mail.domain.tld”的代理已关闭。 否则，您将无法接收电子邮件。 如果您想使用 Hestia 作为邮件服务器，建议记录以下记录：

- 名为 **mail** 的记录指向您的服务器 IP。
- 名为 **webmail** 的记录指向您的服务器 IP。
- 名称为 **@** 的 MX 记录，指向`mail.domain.tld`。
- 名称为 **@** 的 TXT 记录包含`v=spf1 a mx ip4:your ip; \~all`
- 名称为 **_domainkey** 的 TXT 记录包含 `t=y; o=~;`
- 名称为 **mail._domainkey** 的 TXT 记录包含`t=y; o=~DKIM key;`
- 名称为 **_dmarc** 的 TXT 记录包含`v=DMARC1; p=quarantine; sp=quarantine; adkim=s; aspf=s;`

The DKIM key and SPF record can be found in the **Mail Domains** list ([documentation](../user-guide/mail-domains#get-dns-records)).

DKIM 密钥和 SPF 记录可以在 **邮件域** 列表中找到（[文档](../user-guide/mail-domains#get-dns-records)）。

## 当从我的服务器发送电子邮件时，它们最终会进入垃圾邮件文件夹

确保您已设置正确的 RDNS、SPF 记录和 DKIM 记录。

如果这不起作用，则您的 IP 地址可能位于一个或多个黑名单中。 您可以尝试自行解除阻止，但通常更简单的方法是将 SMTP 和 SMTP 中继与 Amazon SES 或其他 SMTP 提供商结合使用。

## 如何启用 ManageSieve？

在 Hestia 安装期间，使用 `--sieve` 标志。 如果已安装 Hestia，则在以下路径中提供了升级脚本：`/usr/local/hestia/install/upgrade/manual/install_sieve.sh`

## 我可以允许通过外部邮件客户端访问 ManageSieve 吗？

在防火墙中打开端口 4190。 [阅读防火墙文档](./firewall)。

## 如何为 Snappymail 启用 ManageSieve？

编辑配置文件

```bash
nano /etc/snappymail/data/_data_/_default_/domains/default.ini
```

并修改以下设置：

```bash
sieve_use = On
sieve_allow_raw = Off
sieve_host = "localhost"
sieve_port = 4190
sieve_secure = "None"
```

## Oracle 云 + SMTP 中继

如果您想使用来自 Oracle Cloud 的 SMTP，您需要对 Exim4 配置进行以下更改：

打开/etc/exim4/exim4.conf.template并替换以下代码：

```bash
smtp_relay_login:
driver = plaintext
public_name = LOGIN
hide client_send = : SMTP_RELAY_USER : SMTP_RELAY_PASS
```

如:

```bash
smtp_relay_login:
driver = plaintext
public_name = PLAIN
hide client_send = ^SMTP_RELAY_USER^SMTP_RELAY_PASS
```

[请参阅论坛主题以获取更多信息](https://forum.hestiacp.com/t/oracle-cloud-email-as-relay-doesnt-works/11304/19?)
