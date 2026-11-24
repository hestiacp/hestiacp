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

## Debian 12 Bookworm to Debian 13 Trixie

### Important note (run as root)

All commands in this guide must be executed as **root user**.
You can either log in directly as root or use `sudo -i` before running the commands.

### Update the current system

First, make sure your current system is fully up to date.

```bash
apt-get update
apt-get -y full-upgrade
```

### Upgrade MariaDB

Before upgrading to Debian 13, make sure MariaDB has been upgraded to at least version 11.8, since the MariaDB repository does not support version 11.4 on Debian 13.

```bash
sed -i -E 's|mariadb-server/[0-9]+\.[0-9]+/repo/|mariadb-server/11.8/repo/|' /etc/apt/sources.list.d/mariadb.list
apt-get update
apt-get -y full-upgrade
```

### Update the APT sources

Once this is done, update your APT sources to Debian 13:

```bash
sed -i 's/bookworm/trixie/g' /etc/apt/sources.list
sed -i 's/bookworm/trixie/g' /etc/apt/sources.list.d/*.list
sed -i 's/bookworm/trixie/g' /etc/apt/sources.list.d/*.sources
apt-get update
```

### Check for locally modified configuration files

Before continuing, you may want to generate a list of configuration files for which newer versions are available but the system is currently using locally modified versions. This allows you to compare the list after the upgrade in case any manual changes are required.

```bash
find / -path /root -prune -o -type f -regex '.*\.\(dpkg\|ucf\)-dist$' -print
```

### Perform the system upgrade

Now start the upgrade:

```bash
DEBIAN_FRONTEND=noninteractive UCF_FORCE_CONFFOLD=1 apt-get -y full-upgrade -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold"
```

### Migrate service configuration files

Some services, such as Dovecot or BIND, may fail to start because they are still using configuration files that are no longer compatible. Even if no services appear to be failing, you should run the following script. It will automatically detect whether any configuration changes are required and apply them if necessary.

```bash
/usr/local/hestia/install/upgrade/manual/migrate_conf_to_debian_13.sh
```

### Review new configuration files

At this point, you can run the following command again to check for new configuration files:

```bash
find / -path /root -prune -o -type f -cmin -120 -regex '.*\.\(dpkg\|ucf\)-dist$' -print
```

You may see a few files listed, but you should ignore most of them. Configuration files managed or modified by Hestia (such as `sshd`, `dovecot`, `bind`, `exim4`, etc.) should always be ignored. If you are unsure whether a file needs to be reviewed or merged, it is safest to leave it unchanged.

### Reboot the system

Finally, reboot the system:

```bash
reboot
```

### Clean up

After the system starts again, update the package lists, install any remaining package upgrades, and remove packages that are no longer needed:

```bash
apt-get update
apt-get -y full-upgrade
apt-get autoremove
apt-get clean
```

The upgrade is now complete.

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

## Ubuntu 24.04 Noble to Ubuntu 26.04 Resolute

### Important note (run as root)

All commands in this guide must be executed as **root user**.
You can either log in directly as root or use `sudo -i` before running the commands.

### Update the current system

First, make sure your current system is fully up to date.

```bash
apt-get update
apt-get -y full-upgrade
```

### Upgrade MariaDB

Before upgrading to Ubuntu 26.04, make sure MariaDB has been upgraded to at least version 11.8, since the MariaDB repository does not support version 11.4 on Ubuntu 26.04.

```bash
sed -i -E 's|mariadb-server/[0-9]+\.[0-9]+/repo/|mariadb-server/11.8/repo/|' /etc/apt/sources.list.d/mariadb.list
apt-get update
apt-get -y full-upgrade
```

### Replace the Ondřej Surý PPAs

Once this is done, you must remove the Ondřej Surý PPAs. Ubuntu 26.04 is no longer supported through the Launchpad PPAs, and PHP packages are now distributed exclusively through the official Sury APT repository.

```bash
while read -r source; do rm -f "$source"; done < <(grep -rl ondrej /etc/apt/sources.list.d)
echo "deb [arch=$(arch | sed -e 's/x86_64/amd64/' -e 's/aarch64/arm64/') signed-by=/usr/share/keyrings/sury-keyring.gpg] https://packages.sury.org/php/ resolute main" > /etc/apt/sources.list.d/php.list
curl -s https://packages.sury.org/php/apt.gpg | gpg --dearmor | tee /usr/share/keyrings/sury-keyring.gpg > /dev/null 2>&1
```

### Update the APT sources

Now, update your APT sources to Ubuntu 26.04:

```bash
sed -i 's/noble/resolute/g' /etc/apt/sources.list
sed -i 's/noble/resolute/g' /etc/apt/sources.list.d/*.list
sed -i 's/noble/resolute/g' /etc/apt/sources.list.d/*.sources
apt-get update
```

### Check for locally modified configuration files

Before continuing, you may want to generate a list of configuration files for which newer versions are available but the system is currently using locally modified versions. This allows you to compare the list after the upgrade in case any manual changes are required.

```bash
find / -path /root -prune -o -type f -regex '.*\.\(dpkg\|ucf\)-dist$' -print
```

### Perform the system upgrade

Now start the upgrade:

```bash
DEBIAN_FRONTEND=noninteractive UCF_FORCE_CONFFOLD=1 apt-get -y full-upgrade -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold"
```

### Migrate service configuration files

Some services, such as Dovecot or BIND, may fail to start because they are still using configuration files that are no longer compatible. Even if no services appear to be failing, you should run the following script. It will automatically detect whether any configuration changes are required and apply them if necessary.

```bash
/usr/local/hestia/install/upgrade/manual/migrate_conf_to_ubuntu_26.04.sh
```

### Review new configuration files

At this point, you can run the following command again to check for new configuration files:

```bash
find / -path /root -prune -o -type f -cmin -120 -regex '.*\.\(dpkg\|ucf\)-dist$' -print
```

You may see a few files listed, but you should ignore most of them. Configuration files managed or modified by Hestia (such as `sshd`, `dovecot`, `bind`, `exim4`, etc.) should always be ignored. If you are unsure whether a file needs to be reviewed or merged, it is safest to leave it unchanged.

### Reboot the system

Finally, reboot the system:

```bash
reboot
```

### Clean up

After the system starts again, update the package lists, install any remaining package upgrades, and remove packages that are no longer needed:

```bash
apt-get update
apt-get -y full-upgrade
apt-get autoremove
apt-get clean
```

The upgrade is now complete.

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
