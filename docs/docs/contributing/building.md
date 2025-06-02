# Building packages

::: info
For building `devcp-nginx` or `devcp-php`, at least 2 GB of memory is required!
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
./dst_autocompile.sh --all --noinstall --keepbuild '~localsrc'

cd ../install

bash dst-install-{os}.sh --with-debs /tmp/hestiacp-src/deb/
```

Any option can be appended to the installer command. [See the complete list](../introduction/getting-started#list-of-installation-options).

## Build packages only

```bash
# Only Hestia
./dst_autocompile.sh --devcp --noinstall --keepbuild '~localsrc'
```

```bash
# Hestia + devcp-nginx and devcp-php
./dst_autocompile.sh --all --noinstall --keepbuild '~localsrc'
```

## Build and install packages

::: info
Use if you have Hestia already installed, for your changes to take effect.
:::

```bash
# Only Hestia
./dst_autocompile.sh --devcp --install '~localsrc'
```

```bash
# Hestia + devcp-nginx and devcp-php
./dst_autocompile.sh --all --install '~localsrc'
```

## Updating Hestia from GitHub

The following is useful for pulling the latest staging/beta changes from GitHub and compiling the changes.

::: info
The following method only supports building the `devcp` package. If you need to build `devcp-nginx` or `devcp-php`, use one of the previous commands.
:::

1. Install Node.js [Download](https://nodejs.org/en/download) or use [Node Source APT](https://github.com/nodesource/distributions)

```bash
v-update-sys-devcp-git [USERNAME] [BRANCH]
```

**Note:** Sometimes dependencies will get added or removed when the packages are installed with `dpkg`. It is not possible to preload the dependencies. If this happens, you will see an error like this:

```bash
dpkg: error processing package devcp (–install):
dependency problems - leaving unconfigured
```

To solve this issue, run:

```bash
apt install -f
```
