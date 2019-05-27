[Hestia Control Panel](https://www.hestiacp.com/)
==================================================

**Pre-Release Software Information**
----------------------------
**Hestia Control Panel is undergoing rapid changes in development. As such, it is important to note that:**
* Current builds are released as a beta and are intended for development and testing purposes only.
* This software should not be used on production systems without understanding the risks.
* By using this software, you acknowledge and accept that bugs or issues could occur at any time.
* By using this software, you acknowledge and accept that changes to the functionality or user interface are likely during the course of development.

What is Hestia Control Panel?
----------------------------
* An open source web server control panel with an easy-to-use interface.
* A lightweight alternative to cPanel, Plesk, etc.

What does Hestia Control Panel support?
----------------------------
* Standard Web Server (apache2/nginx) with PHP
* PHP Web Application Server (nginx + php-fpm)
* Multiple PHP versions (5.6 - 7.3)
* DNS Server (Bind) with clustering capabilities
* Mail Server (Exim/Dovecot) with Anti-Virus and Anti-Spam (ClamAV and SpamAssassin)
* Database functionality (MariaDB/PostgreSQL)
* Let's Encrypt SSL with wildcard certificates

Supported operating systems:
----------------------------
* Debian 8 or 9
* Ubuntu 16.04 LTS or Ubuntu 18.04 LTS
* **NOTE:** Hestia Control Panel must be installed on top of a fresh operating system installation to ensure proper functionality.

Installing Hestia Control Panel
============================
## Step 1: Log in
To install Hestia Control Panel on your server, you will need to be logged in as **root** either directly from the command line console or remotely via SSH:
```bash
ssh root@your.server
```
## Step 2: Download
Download the installation script for the latest release:
```bash
wget https://raw.githubusercontent.com/hestiacp/hestiacp/master/install/hst-install.sh
```
## Step 3: Run
To begin the installation process, simply run the script and follow the on-screen prompts:
```bash
bash hst-install.sh
```
You will receive a welcome email at the address specified during installation (if applicable) and on-screen instructions after the installation is completed to log in and access your server.

## Additional installation notes:
To perform an unattended installation using the default options:
```bash
bash hst-install.sh -f -y no -e <email> -p <password> -s <hostname>
```
## Custom installation:
You may specify a number of various flags during installation to only install the features in which you need. To view a list of available options, run:
```bash
bash hst-install.sh -h
```
Alternatively, @gabizz has made available a command-line script generator at https://gabizz.github.io/hestiacp-scriptline-generator/ which allows you to easily generate the installation command via GUI.

Installing development builds
=============================
To install a development build based on the latest published code, you must first have an existing installation of Hestia available. If you do not have a server configured, please install the latest stable build using the instructions above before continuing.

**Development builds should not be installed on systems with live production data.**

## Step 1: Download the compiler script
```bash
wget https://raw.githubusercontent.com/hestiacp/hestiacp/develop/src/hst_autocompile.sh
```
## Step 2: Compile and install the desired build:
```bash
bash hst_autocompile.sh --packageset <branchname> <yes|no>
```
**Valid options for *packageset* flag include:**
* all
* hestia
* nginx
* php

For example, to install only the Control Panel itself built from the main development branch (**develop**): 
```bash
bash hst_autocompile.sh --hestia develop yes
```

**Important:** Updates which have been released via the Hestia package repositories and upgraded through **apt** will replace installations which use the above method. 

Reporting Issues
=============================
If you've run into an issue with Hestia Control Panel, please let us know as soon as possible so that we may investigate further and resolve any issues in a timely manner.

Bug reports can be filed using GitHub's [Issues](https://github.com/hestiacp/hestiacp/issues) feature.

Contributions
=============================
If you would like to contribute to the project, please [read our submission guidelines](https://github.com/hestiacp/hestiacp/blob/master/CONTRIBUTING.md) for a brief overview of our development processes and standards.

Donations
=============================
Hestia Control Panel is open source and completely free for everyone to use.

If you would like to help our developers cover their time and infrastucture costs, or to support the Hestia Control Panel project as a whole, please consider making a donation via PayPal.

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ST87LQH2CHGLA)

License
=============================
Hestia Control Panel is licensed under [GPL v3](https://github.com/hestiacp/hestiacp/blob/master/LICENSE) license, and is based on the [VestaCP](https://www.vestacp.com/) project.<br>