# 入门

本部分将帮助您在服务器上安装 Hestia。 如果您已经安装了 Hestia 并且只是寻找选项，则可以跳过此页面。

::: danger 警告
安装程序需要以 **root** 身份运行，可以直接从终端运行，也可以使用 SSH 远程运行。 如果您不这样做，安装程序将不会继续。
:::

## 要求

::: warning 注意
Hestia 必须安装在全新操作系统安装之上，以确保正常功能。
如果在 VPS/KVM 上，并且已有管理员帐户，请删除该默认管理员 ID，或使用“--force”继续安装。 有关更多详细信息，请参阅下面的自定义安装。
:::

|     名称             | 最低配置                                        | 推荐                                 |
| -------------------  | ---------------------------------------------- | ------------------------------------ |
| **CPU**              | 1 核 64 位                                     | 4 cores                              |
| **内存**             | 1 GB (不安装SpamAssassin 和 ClamAV)             | 4 GB                                 |
| **硬盘**             | 10 GB HDD                                      | 40 GB SSD                            |
| **操作系统**         | Debian 10, 11 or 12<br>Ubuntu 20.04, 22.04 LTS | Latest Debian <br> Latest Ubuntu LTS |

::: warning 注意
Hestia 仅运行在 AMD64 / x86_64 和 ARM64 / aarch64 处理器上。 它还需要64位操作系统！
我们目前不支持基于 i386 或 ARM7 的处理器。
:::

### 支持的操作系统

- Debian 10、11 或 12
- Ubuntu 20.04 或 22.04

::: warning 注意
Hestia 不支持非 LTS 操作系统。 例如，如果您将其安装在 Ubuntu 21.10 上，您将不会获得我们的支持。
:::

## 常规安装

交互式安装程序将安装默认的 Hestia 软件配置。

### 第 1 步：下载

下载最新版本的安装脚本：也可以点这里配置我们为你开发的[一键自动化安装命令脚本](/install)。

```bash
wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh
```

如果由于 SSL 验证错误而导致下载失败，请确保您已在系统上安装了 ca 证书包 - 您可以使用以下命令执行此操作：

````bash
apt-get update && apt-get install ca-certificates -y
````

### 第 2 步：运行

要开始安装过程，只需运行脚本并按照屏幕上的提示操作即可：

````bash
bash hst-install.sh
````

您将在安装期间指定的地址（如果适用）收到一封欢迎电子邮件，并在安装完成后收到屏幕上的说明，用于登录和访问您的服务器。

＃＃ 自定义安装

如果您想要自定义安装哪些软件，或者想要运行无人值守安装，则需要运行自定义安装。

要查看可用选项的列表，请运行以下命令

````bash
bash hst-install.sh -h
````

### 安装选项列表

：：： 提示
选择安装选项的一种更简单的方法是使用我们为你开发的[一键自动化安装命令脚本](/install)。
:::

要选择安装哪些软件，你可以勾选安装脚本的选项，选择你需要安装的模块。 您可以查看下面的完整选项列表。

```bash
-a, --apache Install Apache [yes | no] default: yes
-w, --phpfpm Install PHP-FPM [yes | no] default: yes
-o, --multiphp Install MultiPHP [yes | no] default: no
-v, --vsftpd Install VSFTPD [yes | no] default: yes
-j, --proftpd Install ProFTPD [yes | no] default: no
-k, --named Install BIND [yes | no] default: yes
-m, --mysql Install MariaDB [yes | no] default: yes
-M, --mysql8 Install Mysql8 [yes | no] default: no
-g, --postgresql Install PostgreSQL [yes | no] default: no
-x, --exim Install Exim [yes | no] default: yes
-z, --dovecot Install Dovecot [yes | no] default: yes
-Z, --sieve Install Sieve [yes | no] default: no
-c, --clamav Install ClamAV [yes | no] default: yes
-t, --spamassassin Install SpamAssassin [yes | no] default: yes
-i, --iptables Install Iptables [yes | no] default: yes
-b, --fail2ban Install Fail2ban [yes | no] default: yes
-q, --quota Filesystem Quota [yes | no] default: no
-d, --api Activate API [yes | no] default: yes
-r, --port Change Backend Port default: 8083
-l, --lang Default language default: en
-y, --interactive Interactive install [yes | no] default: yes
-s, --hostname Set hostname
-e, --email Set admin email
-p, --password Set admin password
-D, --with-debs Path to Hestia debs
-f, --force Force installation
-h, --help Print this help
```

#### 示例

此命令将使用以下软件安装法语版的最新Hestia版本，分别选择安装了以下模块。

- Nginx Web Server
- PHP-FPM Application Server
- MariaDB Database Server
- IPtables Firewall + Fail2Ban Intrusion prevention software
- Vsftpd FTP Server
- Exim Mail Server
- Dovecot POP3/IMAP Server

```bash
bash hst-install.sh \
	--interactive no \
	--hostname host.domain.tld \
	--email email@domain.tld \
	--password p4ssw0rd \
	--lang fr \
	--apache no \
	--named no \
	--clamav no \
	--spamassassin no
```

输入相关的信息后程序开始下载安装，等待约5-15分钟即可安装完成。安装完成后提示你按回车键重启。等待约1-3分钟即可重启完毕。

重新连接你的服务器即可登录Hestia的控制面板啦！安装完成后默认账户名为admin 密码为你设置的密码！

如你没有设置请查看在根目录下`/root/hst_install`的文件夹默认安装配置文件名称为 `hst_install-安装日期和随机编码.log`的文件或者浏览ssh窗口里面有显示默认生成的密码。

## 下一步做什么?

到目前为止，您的服务器上应该已经安装了 Hestia。 您已准备好添加新用户，以便您（或他们）可以在您的服务器上添加新网站或部署应用。

要访问您的控制面板，请导航至浏览器输入域名： `https://host.Example.com:8083` 或者 IP： `1.2.3.4:8083`
