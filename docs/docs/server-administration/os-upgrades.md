# Operating system upgrades

::: danger
Before upgrading your operating system make sure to make a backup! We do not provide support for broken operating system installations. We only provide this page for information about Hestia issues that may come up while upgrading.
:::

## General

::: info
Make sure to verify that MariaDB is running on a supported version for the new operating system. If that is not the case, update MariaDB version to a supported version before upgrading your OS!
:::

Once a backup has been made, update Hestia to the last supported version:

```bash
apt update && apt upgrade
```

Follow system instructions to upgrade your OS. When done, make sure to check that the files in `/etc/apt/sources.list.d` are not hashed out. If they are, remove the hash and run `apt update && apt upgrade` again.

## Debian 11 Bullseye to Debian 12 Bookworm

### Exim config

```bash
rm -f /etc/exim4/exim4.conf.template
cp -f /usr/local/hestia/install/deb/exim/exim4.conf.4.95.template /etc/exim4/exim4.conf.template
```

## Debian 10 Buster to Debian 11 Bullseye

### SHA512 password encryption

```bash
sed -i "s/obscure yescrypt/obscure sha512/g" /etc/pam.d/common-password
```

### Exim4 config

```bash
rm -f /etc/exim4/exim4.conf.template
cp -f /usr/local/hestia/install/deb/exim/exim4.conf.4.94.template /etc/exim4/exim4.conf.template
```

### ProFTPD

Comment out [line 29](https://github.com/hestiacp/hestiacp/blob/1ff8a4e5207aae1e241954a83b7e8070bcdca788/install/deb/proftpd/proftpd.conf#L29) in `/etc/profpd/prodtpd.conf`.

## Debian 9 Stretch to Debian 10 Buster

No issues have been found in the past.

## Ubuntu 22.04 Jammy to Ubuntu 24.04 Noble

::: tip
Verify that MariaDB is running at least version 11.4. If not, first upgrade to this version in your current operating system! After that, comment out the line in `/etc/apt/sources.list.d/mariadb.list` and then upgrade your OS.
:::

### Exim4 config

```bash
rm -f /etc/exim4/exim4.conf.template
cp -f /usr/local/hestia/install/deb/exim/exim4.conf.4.95.template /etc/exim4/exim4.conf.template
```

## Ubuntu 20.04 Focal to Ubuntu 22.04 Jammy

::: tip
Verify that MariaDB is running at least version 10.6. If not, first upgrade to this version in your current operating system! After that, comment out the line in `/etc/apt/sources.list.d/mariadb.list` and then upgrade your OS.
:::

### SHA512 password encryption

```bash
sed -i "s/obscure yescrypt/obscure sha512/g" /etc/pam.d/common-password
```

### Exim4 config

```bash
rm -f /etc/exim4/exim4.conf.template
cp -f /usr/local/hestia/install/deb/exim/exim4.conf.4.94.template /etc/exim4/exim4.conf.template
```

### ProFTPD

Comment out [line 29](https://github.com/hestiacp/hestiacp/blob/1ff8a4e5207aae1e241954a83b7e8070bcdca788/install/deb/proftpd/proftpd.conf#L29) in `/etc/profpd/prodtpd.conf`.

## Ubuntu 18.04 Bionic to Ubuntu 20.04 Focal

No issues have been found in the past.

## Older versions

We haven’t tested the upgrade paths from Ubuntu 16.04 to Ubuntu 20.04 or Debian 8 Jessy to Debian 10
