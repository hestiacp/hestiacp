# 操作系统升级

::: 危险
在升级操作系统之前，请务必进行备份！ 我们不为损坏的操作系统安装提供支持。 我们仅提供此页面来提供有关升级过程中可能出现的 Hestia 问题的信息。
:::

## debian & ubautu

::: 信息
确保验证 MariaDB 是否在新操作系统支持的版本上运行。 如果不是这种情况，请在升级操作系统之前将 MariaDB 版本更新到支持的版本！
:::

完成备份后，将 Hestia 更新到最新支持的版本：

```bash
apt update && apt upgrade -y
```

按照系统说明升级您的操作系统。 完成后，请确保检查`/etc/apt/sources.list.d`中的更新源配置是否正确。 如果是，请保存退出并再次运行`apt update && apt Upgrade -y`

## Debian 10 Buster 到 Debian 11 Bullseye

### SHA512密码加密

```bash
sed -i "s/obscure yescrypt/obscure sha512/g" /etc/pam.d/common-password
```

### Exim4 配置

```bash
rm -f /etc/exim4/exim4.conf.template
cp -f /usr/local/hestia/install/deb/exim/exim4.conf.4.94.template /etc/exim4/exim4.conf.template
```

### ProFTPD

注释掉`/etc/profpd/prodtpd.conf`中的[第29行](https://github.com/hestiacp/hestiacp/blob/1ff8a4e5207aae1e241954a83b7e8070bcdca788/install/deb/proftpd/proftpd.conf#L29)。

## Debian 9 延伸到 Debian 10 Buster

过去没有发现任何问题。

## Ubuntu 20.04 Focal 到 Ubuntu 22.04 Jammy

:::提示
验证 MariaDB 运行的版本至少为 10.6。 如果没有，请先在您当前的操作系统中升级到此版本！ 之后，注释掉 `/etc/apt/sources.list.d/mariadb.list` 中的配置源，然后升级您的操作系统。
:::

### SHA512 密码加密

```bash
sed -i "s/obscure yescrypt/obscure sha512/g" /etc/pam.d/common-password
```

### Exim4 配置

```bash
rm -f /etc/exim4/exim4.conf.template
cp -f /usr/local/hestia/install/deb/exim/exim4.conf.4.94.template /etc/exim4/exim4.conf.template
```

### ProFTPD

Comment out [line 29](https://github.com/hestiacp/hestiacp/blob/1ff8a4e5207aae1e241954a83b7e8070bcdca788/install/deb/proftpd/proftpd.conf#L29) in `/etc/profpd/prodtpd.conf`.

## Ubuntu 18.04 Bionic 到 Ubuntu 20.04 Focal

过去没有发现任何问题。

## 旧版本

我们尚未测试从 Ubuntu 16.04 到 Ubuntu 20.04 或 Debian 8 Jessy 到 Debian 10 的升级路径
