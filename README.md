[Hestia Control Panel](https://www.hestiacp.com/)
==================================================
**Current stable release:** Version 1.0.6, released on September 26th, 2019.<br>
**Current development release:** Version 1.1.0, release date yet to be determined.

**Please read this, before you start!**
---------------------------- 
HestiaCP is not an autopilot for your server. Hestia is a tool to enable various hosting related settings via a web interface. We assume that you are familiar with the basics of server administration, as well as knowledge about setting up and managing hosting applications. This means we assume that even without Hestia you would be able to set up a LAMP stack and manage your email, but are looking for a tool to simplify these tasks. We cannot provide support for requests that do not describe in detail the troubleshooting you have already done or that clearly show a lack of will and knowledge to solve the problem.

**We're looking for some extra hands on deck!**
---------------------------- 
Are you a software developer, tester, or have experience in writing documentation or guides? Do you have some spare time to contribute and want to help further the development of Hestia Control Panel?<br><br>Please send an email to info@hestiacp.com with a quick outline of your previous experience in any of these areas or where you think you could help on the project and we'll reach out to discuss with you further. 

As always we welcome all feedback and contributions!

Sincerely,<br>
The Hestia Control Panel development team

What is Hestia Control Panel?
----------------------------
* An open source web server control panel with an easy-to-use interface.
* A lightweight alternative to cPanel, Plesk, etc.

What does Hestia Control Panel support?
----------------------------
* Standard Web Server (Apache/NGINX) with PHP
* PHP Web Application Server (NGINX + PHP-FPM)
* Multiple PHP versions (5.6 - 7.3)
* DNS Server (Bind) with clustering capabilities
* Mail Server (Exim/Dovecot) with Anti-Virus and Anti-Spam (ClamAV and SpamAssassin)
* Database functionality (MariaDB/PostgreSQL)
* Let's Encrypt SSL with wildcard certificates

Supported operating systems:
----------------------------
* Debian 8 or 9
* Ubuntu 16.04 LTS or Ubuntu 18.04 LTS (the latest LTS release is recommended)
* **NOTE:** Hestia Control Panel must be installed on top of a fresh operating system installation to ensure proper functionality.

Installing Hestia Control Panel
============================
## Step 1: Log in
To install Hestia Control Panel on your server, you will need to be logged in as **root** or a user with super-user privileges in order to continue. You can perform the installation either directly from the command line console or remotely via SSH:
```bash
ssh root@your.server
```
## Step 2: Download
Download the installation script for the latest release:
```bash
wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh
```
If the download fails due to an SSL validation error, please be sure you've installed the ca-certificate package on your system - you can do this with the following command:
```bash
apt-get update && apt-get install ca-certificates
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

Installing & testing development builds
=============================
In order to install a development build based on the latest published code, you should first have an instance of Hestia Control Panel set up. If you do not have a server configured, please install the latest stable build using the instructions above before continuing.

**PLEASE NOTE: Development builds should not be installed on systems with live production data without understanding the potential risks involved.**

To install a development build, first ensure that you have the latest Git upgrade script installed which handles new dependencies added after the 1.0.x branch:
```bash
wget -O $HESTIA/bin/v-update-sys-hestia-git https://raw.githubusercontent.com/hestiacp/hestiacp/master/bin/v-update-sys-hestia-git
chmod +x $HESTIA/bin/v-update-sys-hestia-git
```

Then run the following command:
```bash
v-update-sys-hestia-git branchname
```
Replace *branchname* with the name of the branch you wish to install from, such as **release** or **master** (stable and current development branches, respectively). 

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

If you would like to help our developers cover their time and infrastucture costs, or to support the Hestia Control Panel project as a whole, please consider making a donation via PayPal or become a sponsor.

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ST87LQH2CHGLA)

License
=============================
Hestia Control Panel is licensed under [GPL v3](https://github.com/hestiacp/hestiacp/blob/master/LICENSE) license, and is based on the [VestaCP](https://www.vestacp.com/) project.<br>