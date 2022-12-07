# Contributing to Hestia’s development

Hestia is an open-source project, and we welcome contributions from the community. Please read the [contributing guidelines](https://github.com/hestiacp/hestiacp/blob/main/CONTRIBUTING.md) for additional information.

Hestia is designed to be installed on a web server. To develop Hestia on your local machine, a virtual machine is recommend.

::: warning
Development builds are unstable. If you encounter a bug please [report it via GitHub](https://github.com/hestiacp/hestiacp/issues/new/choose) or [submit a Pull request](https://github.com/hestiacp/hestiacp/pulls).
:::

## Creating a virtual machine for development

These are example instructions for creating a virtual machine running Hestia for development.

These instructions use [Multipass](https://multipass.run/) to create the VM. Feel free to adapt the commands for any virtualization software you prefer.

1. [Install Multipass](https://multipass.run/install) for your OS.

1. [Fork Hestia](https://github.com/hestiacp/hestiacp/fork) and clone the repository to your local machine

   ```bash
   git clone https://github.com/YourUsername/hestiacp.git $HOME/projects
   ```

1. Create an Ubuntu VM with at least 2G of memory

   ```bash
   multipass launch --name hestia-dev --mem 2G
   ```

1. Map your cloned repository to the VM's home directory

   ```bash
   multipass mount $HOME/projects/hestiacp hestia-dev:/home/ubuntu/hestiacp
   ```

1. SSH into the VM as root and install some required packages

   ```bash
   multipass exec hestiacp-dev -- sudo bash
   sudo apt update && sudo apt install -y jq libjq1
   ```

1. Navigate to `/src` and build Hestia packages

   ```bash
   cd src
   ./hst_autocompile.sh --hestia --noinstall --keepbuild '~localsrc'
   ```

1. Navigate to `/install` and install Hestia

   _(update the [installation flags](../introduction/getting-started#list-of-installation-options) to your liking, note that login credentials are set here)_

   ```bash
   cd ../install
   bash hst-install.sh -D /tmp/hestiacp-src/deb/ --interactive no --email admin@example.com --password password123 --hostname demo.hestiacp.com -f
   ```

1. Reboot VM (and exit SSH session)

   ```bash
   reboot
   ```

1. Find the IP address of the VM

   ```bash
   multipass list
   ```

1. Visit the VM's IP address in your browser using the default Hestia port and login with `admin`/`password123`

   _(bypass any SSL errors you see when loading the page)_

   ```bash
   e.g. https://192.168.64.15:8083
   ```

Hestia is now running in a virtual machine. If you'd like to make changes to the source code and test them, please continue to the next section.

## Making changes to Hestia

After setting up Hestia in a VM you can now make changes to the source code in `$HOME/projects/hestiacp` on your local machine using your editor of choice.

The following are example instructions for making a change to Hestia's UI and testing it locally.

::: info
Please ensure you have [Yarn](https://yarnpkg.com) v3 installed and are using [Node.js](https://nodejs.org/en/) v16 or higher.
:::

1. At the root of the project on your local machine, install Node dependencies

   ```bash
   yarn install
   ```

1. Make a change to a file that we can later test, then build the UI assets

   _e.g. change the body background color to red in `web/css/src/base.css` then run:_

   ```bash
   yarn build
   ```

1. SSH into the VM as root and navigate to `/src`

   ```bash
   multipass exec hestia-dev -- sudo bash
   cd src
   ```

1. Run the Hestia build script

   ```bash
   ./hst_autocompile.sh --hestia --install '~localsrc'
   ```

1. Reload the page in your browser to see your changes

::: info
A backup is created each time the Hestia build script is run. If you run this a lot it can fill up your VM's disk space.
You can delete the backups by running `rm -rf /root/hst_backups` as root user on the VM.
:::

Please refer to the [contributing guidelines](https://github.com/hestiacp/hestiacp/blob/main/CONTRIBUTING.md) for more details on submitting code changes for review.

### Building packages

::: info
For building `hestia-nginx` or `hestia-php`, at least 2 GB of memory is required!
:::

Here is more detailed information about the build scripts that are run from `src`:

#### Build packages only

```bash
# Only Hestia
./hst_autocompile.sh --hestia --noinstall --keepbuild '~localsrc'
```

```bash
# Hestia + hestia-nginx and hestia-php
./hst_autocompile.sh --all --noinstall --keepbuild '~localsrc'
```

#### Build and install packages

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

## Installing Hestia from a branch

The following is useful for testing a Pull Request or a branch on a fork.

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

## Updating Hestia from GitHub

The following is useful for pulling the latest staging/beta changes from GitHub and compiling the changes.

::: info
The following method only supports building the `hestia` package. If you need to build `hestia-nginx` or `hestia-php`, use one of the previous commands.
:::

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

## Running automated tests

For automated tests we currently use [Bats](https://github.com/bats-core/bats-core).

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
