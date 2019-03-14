[Hestia Control Panel](https://www.hestiacp.com/)
==================================================

Disclaimer
----------------------------
**Hestia Control Panel is in active development and has been made available as a pre-release beta.**<br>
**Please use caution if you choose to use this software in a production environment.**

What is Hestia Control Panel?
----------------------------
* An open source web server control panel with an easy-to-use interface.
* A lightweight alternative to cPanel, Plesk, etc.

What features does Hestia Control Panel support?
----------------------------
* Web Server (Apache/Nginx) with PHP
* DNS Server (Bind)
* Mail Server (Exim/Dovecot) with Anti-Virus and Spam Filtering (ClamAV and SpamAssassin)
* Database Server (MariaDB/PostgreSQL)

System Requirements:
----------------------------
* Debian 8 or 9
* Ubuntu 16.04 LTS or Ubuntu 18.04 LTS
* **NOTE:** Hestia Control Panel must be installed on a "clean" operating system to ensure proper functionality.

How to install:
----------------------------
Log in to your server as root, either directly or via SSH:
```bash
ssh root@your.server
```
Download the installation script:
```bash
wget https://raw.githubusercontent.com/hestiacp/hestiacp/master/install/hst-install.sh
```
Run the installation script and follow the on-screen instructions:
```bash
bash hst-install.sh
```
To perform an unattended installation using the default options:
```bash
bash hst-install.sh -f -y no -e <email> -p <password> -s <hostname>
```
For additional installation options:
```bash
bash hst-install.sh -h
```
Reporting bugs & issues:
----------------------------
If you've run into an issue with Hestia Control Panel, please let us know as soon as possible so that we may investigate further and resolve any issues in a timely manner.

Bug reports can be filed using GitHub's [Issues](https://github.com/hestiacp/hestiacp/issues) feature located at the top of our repository.

License:
----------------------------
Hestia Control Panel is licensed under [GPL v3](https://github.com/hestiacp/hestiacp/blob/master/LICENSE) license, and is based on VestaCP.<br>

How to support Hestia Control Panel:
----------------------------
Hestia Control Panel is open source and completely free for everyone to use.

If you would like to help our developers cover their time and infrastucture costs, or to support the Hestia Control Panel project as a whole, please consider making a donation via PayPal.

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ST87LQH2CHGLA)
