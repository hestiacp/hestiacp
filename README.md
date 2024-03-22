<h1 align="center"><a href="https://www.hestiacp.com/">Hestia控制面板</a></h1>

![HestiaCP Web 界面屏幕截图](https://s11.ax1x.com/2024/01/13/pFPkUQs.png)

<h2 align="center">重量轻、功能强大的控制面板，适用于现代网络</h2>

<p align="center"><strong>最新稳定版本:</strong> 1.9.0 | <a href="https://github.com/hestiacp/hestiacp/blob/release/CHANGELOG.md">查看更新日志</a></p>

<p align="center">
	<a href="https://www.hestiacp.com/">Hestia控制面板官网</a> |
	<a href="https://docs.hestiacp.com/">文档</a> |
	<a href="https://forum.hestiacp.com/">论坛</a>
	<br/><br/>
	<a href="https://drone.hestiacp.com/hestiacp/hestiacp">
		<img src="https://drone.hestiacp.com/api/badges/hestiacp/hestiacp/status.svg?ref=refs/heads/main" alt="Drone Status"/>
	</a>
	<a href="https://github.com/hestiacp/hestiacp/actions/workflows/lint.yml">
		<img src="https://github.com/hestiacp/hestiacp/actions/workflows/lint.yml/badge.svg" alt="Lint Status"/>
	</a>
</p>

## **欢迎您!**

Hestia控制面板旨在为管理员提供易于使用的web和命令行界面，使他们能够从一个中央面板快速部署和管理web域、邮件帐户、DNS区域和数据库，而无需手动部署和配置单个组件或服务。

## 官方捐赠方式

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ST87LQH2CHGLA)
比特币 : bc1q48jt5wg5jaj8g9zy7c3j03cv57j2m2u5anlutu<br>
以太坊 : 0xfF3Dd2c889bd0Ff73d8085B84A314FC7c88e5D51<br>
币安: bnb1l4ywvw5ejfmsgjdcx8jn5lxj7zsun8ktfu7rh8<br>
BNB智能链数字钱包: 0xfF3Dd2c889bd0Ff73d8085B84A314FC7c88e5D51<br>

## 功能和服务

-Apache2和NGINX与PHP-FPM

-多个PHP版本（默认为5.6-8.2、8.1）

-具有群集功能的DNS服务器（绑定）

-具有防病毒、防垃圾邮件和网络邮件功能的POP/IMAP/SMTP邮件服务（ClamAV、SpamAssassin、Sieve、Roundcube）

-MariaDB/MMySQL和/或PostgreSQL数据库

-让我们使用通配符证书加密SSL支持

-带有暴力攻击检测和IP列表（iptables、fail2ban和ipset）的防火墙。

## 支持的平台和操作系统

- **Debian:** 12, 11, or 10

- **Ubuntu:** 22.04 LTS, 20.04 LTS

**注意:**

-Hestia控制面板不支持32位操作系统！

-Hestia控制面板与OpenVZ 7或更低版本结合使用可能存在DNS和/或防火墙问题。如果您使用虚拟专用服务器，我们强烈建议您使用基于KVM或LXC的服务器！

## 安装Hestia控制面板

- **注意:** 您必须将Hestia控制面板安装在新的操作系统安装之上，以确保功能正常。

虽然我们已经尽一切努力使安装过程和控制面板界面尽可能友好（即使是对新用户），但在继续之前，假设您已经具备了一些关于如何设置Linux服务器的基本知识和理解。

### 步骤1：登录

要开始安装，您需要以**root**或具有超级用户权限的用户身份登录。您可以直接从命令行控制台或通过SSH远程登录服务器执行安装：

```bash
ssh root@123.123.123.123
```

### 步骤2：下载

下载最新版本的安装脚本：

```bash

wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh`

```

如果下载由于SSL验证错误而失败，请确保您已在系统上安装了ca证书包-您可以使用以下命令执行此操作：

```bash
apt-get-update&&apt-get-install ca证书
```

### 第三步：脚本

要开始安装过程，只需运行脚本并按照屏幕上的提示进行操作：

```bash
bash hst-install.sh
```

您将收到一封欢迎电子邮件，地址为安装期间指定的地址（如果适用），并在安装完成后在屏幕上指示您登录和访问服务器。

### 自定义安装

您可以在安装过程中指定许多不同的标志，以仅安装所需的功能。要查看可用选项的列表，请运行：

```bash
bash hst-install.sh-h
```

或者，您可以使用<https://hestiacp.com/install.html>这允许您通过GUI轻松生成安装命令。

## 如何升级现有安装

默认情况下，在新安装的Hestia控制面板上启用自动更新，可以通过**服务器设置>更新**进行管理。要手动检查和安装可用的更新，请使用apt软件包管理器：

```bash
apt-get update
apt-get upgrade
```

## 问题和支持请求

-如果您在使用Hestia控制面板时遇到一般问题并需要帮助，请[访问我们的论坛](https://forum.hestiacp.com/)以搜索潜在的解决方案或发布社区成员可以提供帮助的新帖子。

-Bug和其他可复制的问题应通过GitHub提交[创建新的问题报告](https://github.com/hestiacp/hestiacp/issues)以便我们的开发人员能够进一步调查。请注意，支持请求将重定向到我们的论坛。

**重要提示：我们为未描述已执行的故障排除步骤的请求或与Hestia控制面板无关的第三方应用程序（如WordPress）提供支持（_cannot_）。请确保您在论坛帖子或发布报告中包含尽可能多的信息**

## 贡献

如果您想为该项目捐款，请[阅读我们的捐款指南](https://github.com/hestiacp/hestiacp/blob/release/CONTRIBUTING.md)简要概述我们的开发过程和标准。

## 版权所有

“Hestia控制面板”、“HestiaCP”和Hestia标志是hestiap.com的原始版权，适用以下限制：

**您可以：**

-在与应用程序或项目直接相关的任何上下文中使用名称“Hestia Control Panel”、“HestiaCP”或Hestia徽标。这包括应用程序本身、本地社区以及新闻或博客文章。

**您不允许：**

-以“Hestia Control Panel”、“HestiaCP”或类似衍生产品的名义出售或重新分发应用程序，包括在与创收活动相关的任何品牌或营销材料中使用Hestia标志，

-在与项目无关的任何上下文中使用名称“Hestia控制面板”、“HestiaCP”或Hestia标志，

-以任何方式更改名称“Hestia控制面板”、“HestiaCP”或Hestia标志。

## 许可证

Hestia控制面板根据[GPL v3](https://github.com/hestiacp/hestiacp/blob/release/LICENSE)许可证获得许可  并且基于[VestaCP](https://vestacp.com/)项目。
