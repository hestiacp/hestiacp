# Beta and release candidate testing

::: tip
If there is a beta or release candidate available, we will announce it via our Discord server or our forum.
:::

In the last few months, we have seen a growing number of issues when releasing minor and major updates. To prevent this from happening, we have decided to setup a beta apt server so we can push more regular updates, enabling us to test at a larger scale than only 4 or 5 users.

## Activating the beta repo on an existing install

::: danger
Betas and release candidates might still contain bugs and can possibly break your server. We cannot guarantee it will be fixed directly! Please be careful when testing on servers in production or containing important data!
:::

Run the following commands as root:

```bash
# Collecting system data
ARCH=$(arch)
case $(arch) in x86_64) ARCH="amd64" ;; aarch64) ARCH="arm64" ;; esac
codename="$(lsb_release -s -c)"
apt="/etc/apt/sources.list.d"

# Add the beta repo to devcp.list
sed -i 's/^/#/' $apt/devcp.list
echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/devcp-beta-keyring.gpg] https://beta-apt.hestiacp.com/ $codename main" >> $apt/devcp.list
curl -s "https://beta-apt.hestiacp.com/pubkey.gpg" | gpg --dearmor | tee /usr/share/keyrings/devcp-beta-keyring.gpg > /dev/null 2>&1

# Update to the beta version
apt update && apt upgrade
```

## Install from beta repo

If you want to install a new Hestia installation form the beta server.

```bash
# Debian
wget https://beta-apt.hestiacp.com/dst-install-debian.sh
# or Ubuntu
wget https://beta-apt.hestiacp.com/dst-install-ubuntu.sh
```

Then install via bash dst-install-debian.sh or bash dst-install-ubuntu.sh

## Disabling the beta repo

Edit `/etc/apt/sources.list.d/devcp.list` and remove the `#` in front of `apt.hestiacp.com`, and add a `#` in front of `beta-apt.hestiacp.com`.

Once that’s done, run `apt update && apt upgrade` to rollback to the regular release.

## Reporting bugs

If you encounter a bug, please [open an issue](https://github.com/hestiacp/hestiacp/issues/new/choose) or [submit a Pull Request](https://github.com/hestiacp/hestiacp/pulls). You can also report it on our forum or our Discord server
