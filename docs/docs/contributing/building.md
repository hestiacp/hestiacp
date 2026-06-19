# Building packages

::: info
For building `hestia-nginx` or `hestia-php`, at least 2 GB of memory is required!
:::

Here is more detailed information about the build scripts that are run from `src`:

## Installing Hestia from a branch

The following is useful for testing a Pull Request or a branch on a fork.

1. Install Node.js [Download](https://nodejs.org/en/download) or use [Node Source APT](https://github.com/nodesource/distributions)

```bash
# Replace with https://github.com/username/hestiacp.git if you want to test a branch that you created yourself
git clone https://github.com/hestiacp/hestiacp.git
cd ./hestiacp/

# Replace main with the branch you want to test
git checkout main

cd ./src/

# Compile packages
./hst_autocompile.sh --all --noinstall --keepbuild '~localsrc'

cd ../install

bash hst-install-{os}.sh --with-debs /tmp/hestiacp-src/deb/
```

Any option can be appended to the installer command. [See the complete list](../introduction/getting-started#list-of-installation-options).

## Build packages only

```bash
# Only Hestia
./hst_autocompile.sh --hestia --noinstall --keepbuild '~localsrc'
```

```bash
# Hestia + hestia-nginx and hestia-php
./hst_autocompile.sh --all --noinstall --keepbuild '~localsrc'
```

## Build and install packages

::: info
Use if you have Hestia already installed, for your changes to take effect.
:::

```bash
# Only Hestia
./hst_autocompile.sh --hestia --install '~localsrc'
```

```bash
# Hestia + hestia-nginx and hestia-php
./hst_autocompile.sh --all --install '~localsrc'
```

## Updating Hestia from GitHub

The following is useful for pulling the latest staging/beta changes from GitHub and compiling the changes.

::: info
The following method only supports building the `hestia` package. If you need to build `hestia-nginx` or `hestia-php`, use one of the previous commands.
:::

1. Install Node.js [Download](https://nodejs.org/en/download) or use [Node Source APT](https://github.com/nodesource/distributions)

```bash
v-update-sys-hestia-git [USERNAME] [BRANCH]
```

**Note:** Sometimes dependencies will get added or removed when the packages are installed with `dpkg`. It is not possible to preload the dependencies. If this happens, you will see an error like this:

```bash
dpkg: error processing package hestia (–install):
dependency problems - leaving unconfigured
```

To solve this issue, run:

```bash
apt install -f
```

## Building for other architectures or OS releases on the same machine

`hst_autocompile.sh` only ever builds for the environment it's actually running in (its own `--cross` flag just makes the architecture-independent `hestia` package build for both AMD64 and ARM64 directly, with no emulation needed). To also build `hestia-nginx`, `hestia-php` or `hestia-web-terminal` (which contain compiled native code) for **other** architectures or OS releases on the same machine, use `chroot_build_all.sh` instead — it spins up the other environments and runs the unmodified `hst_autocompile.sh` inside each one.

Pass `--cross` to also build for the other architecture (AMD64<->ARM64), same OS release as the host:

```bash
./chroot_build_all.sh --all --noinstall --keepbuild --cross '~localsrc'
```

Pass `--all-os` to build for every supported OS release (Debian 12/13, Ubuntu 22.04/24.04/26.04) on both architectures — up to 10 combinations from one invocation:

```bash
./chroot_build_all.sh --all --noinstall --keepbuild --all-os '~localsrc'
```

In both cases, the combination matching the host's own OS/release/architecture is built directly; every other combination is built inside a QEMU-emulated chroot (debootstrap + `qemu-user-static`). The first run downloads/bootstraps a minimal root filesystem per combination under `/var/lib/hestiacp-build-chroot/<distro>-<release>-<arch>`; subsequent runs reuse it, so only the first build of a given combination is slow. `--all-os` always covers both architectures, so combining it with `--cross` has no extra effect.

Since a package can share the same name/version/arch across OS releases, `--all-os` writes each release's packages to their own subdirectory instead of the usual flat `deb/`:

```text
/tmp/hestiacp-src/deb/<distro>-<release>/<package>_<version>_<arch>.deb
# e.g.
/tmp/hestiacp-src/deb/debian-bookworm/hestia-nginx_1.2.3_amd64.deb
/tmp/hestiacp-src/deb/ubuntu-noble/hestia-nginx_1.2.3_arm64.deb
```
