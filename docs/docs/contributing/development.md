# Contributing to Hestia’s development

[View the current guidelines](https://github.com/hestiacp/hestiacp/blob/main/CONTRIBUTING.md).

::: warning
Development builds are always unstable. If you encounter a bug please [report it via GitHub](https://github.com/hestiacp/hestiacp/issues/new/choose) or [submit a Pull request](https://github.com/hestiacp/hestiacp/pulls).
:::

## Compiling

::: info
For building `hestia-nginx` or `hestia-php`, at least 2 GB of memory is required!
:::

Go into the `src` folder and run one of these commands:

### Compile only

```bash
# Only Hestia
./hst_autocompile.sh --hestia --noinstall --keepbuild '~localsrc'
```

```bash
# Hestia + hestia-nginx and hestia-php
./hst_autocompile.sh --all --noinstall --keepbuild '~localsrc'
```

### Compile and install

::: info
Use this only if you have Hestia already installed.
:::

```bash
# Only Hestia
./hst_autocompile.sh --hestia --install '~localsrc'
```

```bash
# Hestia + hestia-nginx and hestia-php
./hst_autocompile.sh --all --install '~localsrc'
```

## Install Hestia from packages

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

## Update Hestia from GitHub

::: info
The following method only supports building the `hestia` package. If you need to build `hestia-nginx` or `hestia-php`, use one of the previous commands.
:::

```bash
v-update-sys-hestia-git [USERNAME] [BRANCH]
```

Sometimes dependencies will get added or removed when the packages are installed with `dpkg`. It is not possible to preload the dependencies. If this happens, you will see an error like:

```bash
dpkg: error processing package hestia (–install):
dependency problems - leaving unconfigured
```

To solve this issue, run:

```bash
apt install -f
```

## Automated testing

For automated testing, we currently use [Bats](https://github.com/bats-core/bats-core).

### Install

```bash
# Clone Hestia repo with testing submodules
git clone --recurse-submodules https://github.com/hestiacp/hestiacp
# Or, using an existing local repo with an up-to-date main branch
git submodule update --init --recursive

# Install Bats
test/test_helper/bats-core/install.sh /usr/local
```

### Run

::: danger
Do not run any testing script on a live server. It might cause issues or downtime!
:::

```bash
# Run Hestia tests
test/test.bats
```
