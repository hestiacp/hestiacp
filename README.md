[Hestia Control Panel](https://www.hestiacp.com/)
==================================================

Disclaimer
----------------------------
**Hestia Control Panel is in active development and has been made available as a pre-release beta.**<br>
**Please use caution if you choose to use this software in a production environment.**

What is Hestia Control Panel?
----------------------------
* HestiaCP is a fork of VestaCP.
* Main purpose of HestiaCP is to be more secure, better optimized and up to date.
* An open source web server control panel with an easy-to-use interface.
* A lightweight alternative to cPanel, Plesk, etc.

What Hestia Control Panel supports?
----------------------------
* Web Server (Apache/Nginx) with PHP
* DNS Server (Bind)
* Mail Server (Exim/Dovecot) with Antivirus and Spam Filter (ClamAV and SpamAssassin)
* Database Server (MariaDB/PostgreSQL)

System Requirements:
----------------------------
* Debian 8 or 9
* Ubuntu 16.04 LTS or Ubuntu 18.04 LTS
* **NOTE:** Hestia Control Panel must be installed on a "clean" Operating System to ensure proper functionality.

How to install:
----------------------------
Connect to your server as root via SSH
```bash
ssh root@your.server
```
Download the installation script:
```bash
wget https://raw.githubusercontent.com/hestiacp/hestiacp/master/install/hst-install.sh
```
Then run it:
```bash
bash hst-install.sh
```
To perform an unattended installation with the default options:
```bash
bash hst-install-ubuntu.sh -f -y no -e <email> -p <password> -s <hostname>
```
For additional installation options:
```bash
bash hst-install.sh -h
```

License:
----------------------------
Hestia Control Panel is licensed under [GPL v3](https://github.com/hestiacp/hestiacp/blob/master/LICENSE) license.

Donations:
----------------------------
Hestia Control Panel is open source and completely free for everyone!<br>If you like to make a donation in order to help cover development and infrastructure costs, you may do so via PayPal:

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ST87LQH2CHGLA)
