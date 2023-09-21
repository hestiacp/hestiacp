# Contributing to Hestia’s development

Hestia is an open-source project, and we welcome contributions from the community. Please read the [contributing guidelines](https://github.com/hestiacp/hestiacp/blob/main/CONTRIBUTING.md) for additional information.

Hestia is designed to be installed on a web server. To develop Hestia on your local machine, a virtual machine is recommend.

::: warning
Development builds are unstable. If you encounter a bug please [report it via GitHub](https://github.com/hestiacp/hestiacp/issues/new/choose) or [submit a Pull Request](https://github.com/hestiacp/hestiacp/pulls).
:::

## Creating a virtual machine for development

These are example instructions for creating a virtual machine running Hestia for development.

These instructions use [Multipass](https://multipass.run/) to create the VM. Feel free to adapt the commands for any virtualization software you prefer.

::: warning
Sometimes the mapping between the source code directory on your local machine to the directory in the VM can be lost with a "failed to obtain exit status for remote process" error. If this happens simply unmount and remount e.g.

```bash
multipass unmount hestia-dev
multipass mount $HOME/projects/hestiacp hestia-dev:/home/ubuntu/hestiacp
```

:::

1. [Install Multipass](https://multipass.run/install) for your OS.

1. [Fork Hestia](https://github.com/hestiacp/hestiacp/fork) and clone the repository to your local machine

   ```bash
   git clone https://github.com/YourUsername/hestiacp.git $HOME/projects
   ```

1. Create an Ubuntu VM with at least 2G of memory and 15G of disk space

   ```bash
   multipass launch --name hestia-dev --memory 2G --disk 15G
   ```

1. Map your cloned repository to the VM's home directory

   ```bash
   multipass mount $HOME/projects/hestiacp hestia-dev:/home/ubuntu/hestiacp
   ```

1. SSH into the VM as root and install some required packages

   ```bash
   multipass exec hestia-dev -- sudo bash
   sudo apt update && sudo apt install -y jq libjq1
   ```

1. Outside of the VM (in a new terminal) ensure [Node.js](https://nodejs.org/)
   16 or later is installed

   ```bash
   node --version
   ```

1. Install dependencies and build the theme files:

   ```bash
   npm install
   npm run build
   ```

1. Back in the VM terminal, navigate to `/src` and build Hestia packages

   ```bash
   cd src
   ./hst_autocompile.sh --hestia --noinstall --keepbuild '~localsrc'
   ```

1. Navigate to `/install` and install Hestia

   _(update the [installation flags](../introduction/getting-started#list-of-installation-options) to your liking, note that login credentials are set here)_

   ```bash
   cd ../install
   bash hst-install-ubuntu.sh -D /tmp/hestiacp-src/deb/ --interactive no --email admin@example.com --password Password123 --hostname demo.hestiacp.com -f
   ```

1. Reboot the VM (and exit SSH session)

   ```bash
   reboot
   ```

1. Find the IP address of the VM

   ```bash
   multipass list
   ```

1. Visit the VM's IP address in your browser using the default Hestia port and login with `admin`/`Password123`

   _(proceed past any SSL errors you see when loading the page)_

   ```bash
   e.g. https://192.168.64.15:8083
   ```

Hestia is now running in a virtual machine. If you'd like to make changes to the source code and test them in your browser, please continue to the next section.

## Making changes to Hestia

After setting up Hestia in a VM you can now make changes to the source code at `$HOME/projects/hestiacp` on your local machine (outside of the VM) using your editor of choice.

The following are example instructions for making a change to Hestia's UI and testing it locally.

1. At the root of the project on your local machine, ensure the latest packages are installed

   ```bash
   npm install
   ```

1. Make a change to a file that we can later test, then build the UI assets

   _e.g. change the body background color to red in `web/css/src/base.css` then run:_

   ```bash
   npm run build
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

## Running automated tests

We currently use [Bats](https://github.com/bats-core/bats-core) to run our automated tests.

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
