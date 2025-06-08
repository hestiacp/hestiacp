<h1 align="center"><a href="https://github.com/Ghost-Dev9/DevIT">DevIT Panel</a></h1>
<p align="center">
  <img src="https://github.com/Ghost-Dev9/DevIT/blob/main/web/images/logo.png" alt="DevIT Panel Logo" width="320"/>
</p>
<h2 align="center">Modern, lightweight, and powerful control panel for the web</h2>
<p align="center"><strong>Latest stable release:</strong> Version 1.0.0 | <a href="https://github.com/Ghost-Dev9/DevIT/releases">View Changelog</a></p>
<p align="center">
	<a href="https://github.com/Ghost-Dev9/DevIT">DevIT Panel</a> |
	<a href="https://github.com/Ghost-Dev9/DevIT/wiki">Documentation</a> |
	<a href="https://github.com/Ghost-Dev9/DevIT/discussions">Community</a>
	<br/><br/>
	<a href="https://github.com/Ghost-Dev9/DevIT/actions/workflows/ci.yml">
		<img src="https://github.com/Ghost-Dev9/DevIT/actions/workflows/ci.yml/badge.svg" alt="CI Status"/>
	</a>
</p>

DevIT Panel is designed to provide administrators with an easy-to-use web and command line interface, enabling quick deployment and management of web domains, mail accounts, DNS zones, and databases from one central dashboard—no manual configuration required.
## Features and Services
- Apache2 and NGINX with PHP-FPM
- Multiple PHP versions (5.6 - 8.4, 8.3 as default)
- DNS Server (Bind) with clustering capabilities
- POP/IMAP/SMTP mail services with Anti-Virus, Anti-Spam, and Webmail (ClamAV, SpamAssassin, Sieve, Roundcube)
- MariaDB/MySQL and/or PostgreSQL databases
- Let's Encrypt SSL support with wildcard certificates
- Firewall with brute-force attack detection and IP lists (iptables, fail2ban, and ipset)
## Supported Platforms and Operating Systems
- **Debian:** 12, 11
- **Ubuntu:** 24.04 LTS, 22.04 LTS, 20.04 LTS
> **Note:** DevIT Panel does not support 32-bit operating systems! For best compatibility, use KVM or LXC-based VPS.
## Installing DevIT Panel
> **Important:** Install DevIT Panel on a fresh OS for best results.
### Step 1: Log in
Log in as **root** or a user with super-user privileges:

ssh root@your.server
### Step 2: Download
Download the installation script for the latest release:
wget https://raw.githubusercontent.com/Ghost-Dev9/DevIT/release/install/devit-install.sh

text
If you encounter SSL errors, ensure `ca-certificates` is installed:
apt-get update && apt-get install ca-certificates

text
### Step 3: Run
Start the installation and follow the prompts:
bash devit-install.sh

text
After installation, follow the on-screen instructions to log in and access your server.
### Custom Installation
For advanced options, run:
bash devit-install.sh -h

text
Or use the [installation wizard](https://github.com/Ghost-Dev9/DevIT/wiki/Install) to generate your command.
## Upgrading DevIT Panel
Automatic updates are enabled by default. To upgrade manually:
apt-get update
apt-get upgrade

text
## Issues & Support
- For help, visit the [community discussions](https://github.com/Ghost-Dev9/DevIT/discussions).
- To report bugs, [open an issue](https://github.com/Ghost-Dev9/DevIT/issues).
## Contributions
Want to contribute? Please read our [Contribution Guidelines](https://github.com/Ghost-Dev9/DevIT/blob/main/CONTRIBUTING.md).
## License
DevIT Panel is licensed under [GPL v3](https://github.com/Ghost-Dev9/DevIT/blob/main/LICENSE).
