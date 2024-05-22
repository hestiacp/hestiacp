<h1 align="center"><a href="https://www.hestiacp.com/">Hestia 服务器控制面板</a></h1>

![HestiaCP Web Interface screenshot](https://hestiamb.org/520.png)

<h2 align="center">适用于现代网络的轻量级且功能强大的服务器控制面板</h2>

<p align="center"><strong>最新稳定版本:</strong> 1.8.10 | <a href="https://github.com/hestiacp/hestiacp/blob/release/CHANGELOG.md">查看更新日志</a></p>

<p align="center">
	<a href="https://hestiamb.org">中文官网</a> |
	<a href="https://hestiacp.com/docs/introduction/getting-started.html">英文文档</a> |
	<a href="https://forum.hestiacp.com">英文论坛</a>
	<br/><br/>
	<a href="https://drone.hestiacp.com/hestiacp/hestiacp">
		<img src="https://drone.hestiacp.com/api/badges/hestiacp/hestiacp/status.svg?ref=refs/heads/main" alt="Drone Status"/>
	</a>
	<a href="https://github.com/hestiacp/hestiacp/actions/workflows/lint.yml">
		<img src="https://github.com/hestiacp/hestiacp/actions/workflows/lint.yml/badge.svg" alt="Lint Status"/>
	</a>
</p>

## **欢迎！**

Hestia 控制面板旨在为管理员提供一个易于使用的网页和命令行界面，使他们能够迅速部署和管理网站域名、邮件账户、DNS 区域和数据库，所有操作均通过一个中心仪表板进行，无需手动部署和配置各个组件或服务。

## 赞助

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ST87LQH2CHGLA)<br /><br />
Bitcoin : bc1q48jt5wg5jaj8g9zy7c3j03cv57j2m2u5anlutu<br>
Ethereum : 0xfF3Dd2c889bd0Ff73d8085B84A314FC7c88e5D51<br>
Binance: bnb1l4ywvw5ejfmsgjdcx8jn5lxj7zsun8ktfu7rh8<br>
Smart Chain: 0xfF3Dd2c889bd0Ff73d8085B84A314FC7c88e5D51<br>

## 功能和服务

- Apache2 和 NGINX 搭配 PHP-FPM
- 多个 PHP 版本（5.6 - 8.2，默认使用 8.1）
- 支持集群功能的 DNS 服务器（Bind）
- POP/IMAP/SMTP 邮件服务，包含防病毒、防垃圾邮件和网页邮件（ClamAV、SpamAssassin、Sieve、Roundcube）
- MariaDB/MySQL 和/或 PostgreSQL 数据库
- 支持 Let's Encrypt SSL 的通配符证书
- 防火墙具备暴力攻击检测和 IP 列表功能（iptables、fail2ban 和 ipset）。

## 支持的平台和操作系统

- **Debian:** 12, 11, 或 10
- **Ubuntu:** 22.04 LTS, 20.04 LTS

**注意：**

- Hestia 控制面板不支持 32 位操作系统！
- 如果使用 OpenVZ 7 或更低版本，Hestia 控制面板在 DNS 和/或防火墙方面可能会出现问题。如果您使用虚拟私有服务器，我们强烈建议您使用基于 KVM 或 LXC 的方案！

## 安装 Hestia 控制面板

- **注意：** 您必须在全新的操作系统安装上安装 Hestia 控制面板，以确保其正常功能。

虽然我们已经尽力使安装过程和控制面板界面尽可能友好（即使对新用户也是如此），但假设您在继续之前已经具备了一些关于如何设置 Linux 服务器的基础知识。

### 步骤 1: 登录

要开始安装，您需要以 **root** 用户或具有超级用户权限的用户身份登录。您可以直接从命令行控制台执行安装，也可以通过 SSH 远程执行：

```bash
ssh root@your.server
```

### 步骤 3: 运行安装脚本

要开始安装过程，只需运行脚本并按照屏幕上的提示操作：

```bash
bash hst-install.sh
```

安装完成后，您将在指定的电子邮件地址（如果适用）收到一封欢迎邮件，并在屏幕上看到登录和访问服务器的说明。

### 自定义安装

在安装过程中，您可以通过指定不同的标志来仅安装您需要的特性。要查看可用选项的列表，请运行：

```bash
bash hst-install.sh -h
```

另外，您还可以使用 <https://hestiacp.com/install.html>，它允许您通过图形用户界面（GUI）轻松生成安装命令。

## 如何升级现有安装

在 Hestia 控制面板的新安装中，默认启用自动更新，并可以在“服务器设置”>“更新”中管理。要手动检查和安装可用的更新，请使用 apt 包管理器：

```bash
apt-get update
apt-get upgrade
```

## 问题与支持请求

- 如果在使用 Hestia 控制面板时遇到一般性问题并需要帮助，请[访问我们的论坛](https://forum.hestiacp.com/)以搜索潜在解决方案或发布新帖子，社区成员将提供帮助。
- 遇到的错误和其他可重现的问题应通过 GitHub 提交，通过[创建新的问题报告](https://github.com/hestiacp/hestiacp/issues)以便我们的开发人员进一步调查。请注意，支持请求将被重定向到我们的论坛。

**重要提示：对于未描述已执行的故障排除步骤的请求，或者与 Hestia 控制面板无关的第三方应用程序（如 WordPress）的请求，我们**无法**提供支持。请确保在论坛帖子或问题报告中包含尽可能多的信息！**

## 贡献

对于希望为项目做出贡献的开发者，请务必先阅读我们的[贡献指南](https://github.com/hestiacp/hestiacp/blob/release/CONTRIBUTING.md)，以了解我们的开发流程和标准概述。这有助于确保您的贡献能够顺利合并到项目中。

## 版权

“Hestia Control Panel”（简称“HestiaCP”）及其标志是hestiacp.com的原创版权作品，并适用以下限制：

**您被允许：**

- 在与应用程序或项目直接相关的任何上下文中使用“Hestia Control Panel”、“HestiaCP”或Hestia标志。这包括应用程序本身、本地社区、新闻或博客文章。

**您不被允许：**

- 以“Hestia Control Panel”、“HestiaCP”或类似名称出售或重新分发该应用程序，包括在与产生收入的活动相关的品牌或营销材料中使用Hestia标志。
- 在与项目不相关的任何上下文中使用“Hestia Control Panel”、“HestiaCP”或Hestia标志。
- 以任何方式更改“Hestia Control Panel”、“HestiaCP”或Hestia标志的名称。

## 开源许可

Hestia Control Panel是在[GPL v3](https://github.com/hestiacp/hestiacp/blob/release/LICENSE)许可证下发布的，该项目基于[VestaCP](https://vestacp.com/)项目。这意味着您可以在遵守GPL v3许可证条款的前提下自由地使用、修改和分发Hestia Control Panel。
