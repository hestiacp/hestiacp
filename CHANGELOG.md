# Changelog
All notable changes to this project will be documented in this file.

## [CURRENT] - Development
### Features
- Added support for custom user interface themes.
- Adjusted default font size for improved readability.
- Added read only/demo mode function if DEMO_MODE is set to yes in hestia.conf.
- Added php-imagick module to installer and upgrade scripts.
- Added recidive filter function to fail2ban.
- Refactored MultiPHP functionality. MultiPHP will be enabled by default on new installations.
- Allowed admin user to add or remove PHP versions from webui (edit/server->"Web Server" page).
- Extended v-extract-fs-archive to allow archive testing and extracting only specific paths (for tar)
- Allow renaming of existing packages from console (v-rename-package).
- Webmail IP address is now inherited from web domain when using multiple IPs.
- Exim now uses the web domain IP if it exists.
- Public IP is now used when updating webmail DNS record.
- Added PHP 7.4 to MultiPHP.
- Add Support for Debian 10 (Buster).

### Bugfixes
- Added a detection of web root for add .well-known ACME challenge.
- Reworked Let's Encrypt ACME staging to use Hestia code standards.
- Fixed issues with incorrect font rendering on Windows and Linux.
- Fixed issues with Let's Encrypt - use Nginx for Let's Encrypt ACME request if present.
- Reworked v-add-sys-ip, removed CentOS/Red Hat support and reworked conditions.
- Enabled HSTS and force SSL on v-add-letsencrypt-host.
- Prevented login action for webmail in list user view.
- Removed hardcoded mail in HELO data (cosmetic fix).
- Fixed SFTP server validation check - thanks @dbannik.
- Implemented security warning message when creating web domains with the default admin account.
- Fixed wrong quotes used for default keys folder location in v-generate-api-key backend script.
- Fixed permissions to allow access for FTP users created in web domains under admin account.
- Check if user home directory exists before setting permissions on SFTP fail2ban jail.
- Fixed several reported security issues, thanks to Andrea Cardaci (https://cardaci.xyz/)
- Security fix: Command line arguments arguments were glob expanded when written to log file.
- Ensure that SFTP accounts remain configured in sshd when removing web domains/
- Improved security by ensuring that file operations in user home folder will be executed as the real user.
- Added a confirmation dialog when deleting user logs.
- Fixed an issue where the SFTP fail2ban jail was not working correctly for user accounts which were restored from backup archives.
- Enhanced input validation in backend command line scripts.
- Improved page load performance by optimizing how the notifications list is loaded (in some cases, improvement measured from 1sec to under 100ms).
- Improved page load performance when loading IP ban rules in the Control Panel.
- Updated panel framework to use jQuery to 3.4.1.
- Fixed an issue with SFTP fail2ban jail due to missing user.
- Fixed an issue where remote backup hostname would reject an IP address without reverse DNS (PTR record). (#569)
- Create default writable folders in user home directory (#580).
- Added gnupg/gnupg2 check to prevent issues with pubkey installation.
- Fixed DNS nameserver validation when adding new packages.
- Implemented additional debug information for Let's Encrypt validation - thanks @shakaran.
- Disabled alerts for successful cronjob backups.
- Fixed an issue with suspending resources when logged in as a normal (non admin) user.
- Fixed an issue with unsuspending a user, PHP-FPM website pool configuration was being deleted.
- Fixed potential upgrade issue when using v-update-sys-hestia-git.
- Fixed corruption of global user stats when rebuilding a mail domain.
- Fixed formatting of backup exclusions textbox.
- Fixed MultiPHP upgrade script to update all web templates.
- Fixed report issue link in installer scripts.
- Fixed database user authentification on backup restore.
- Added robots.txt for roundcube webmail to prevent search bot crawling.
- Re-Enable force ssl function on let's encrypt certification renew.
- Added official postgresql repository to be up to date.
- Hardening MySQL configuration, prevent local infile.
- Fixed lograte bug and cleans up the messed up nginx/apache2 log permissions.
- Fixed IfModule mpm_itk.c for apache2 templates.
- Added mpm_itk for Deb10 single php installation only.
- Hardening nginx configuration, drop TLSv1.1 support.
- Fixed excluding folders named "logs" from restore backup, thanks to @davidgolsen.

## [1.0.6] - 2019-09-24 - Hotfix
### Bugfixes
- Add support for HTTP/2 Let's Encrypt Server.

## [1.0.5] - 2019-08-06 - Hotfix
### Bugfixes
- Fix several security issues, thanks to Andrea Cardaci (https://cardaci.xyz/)
- Rework Let's Encrypt ACME staging to use hestia conform standard.
- Fix if condition, use nginx for Let's Encrypt ACME request if present.

## [1.0.4] - 2019-07-09 - Hotfix
### Bugfixes
- Delayed start of services to prevent restart limit.

## [1.0.3] - 2019-07-09 - Hotfix
### Bugfixes
- Fixed Let's Encrypt Mail SSL permission issue.

## [1.0.1] - 2019-06-25
### Features
- Improved support for Let's Encrypt certificate generation.
- v-add-letsencrypt-host: Added Let's Encrypt support for Control Panel's own SSL.
- Enabled use of per-domain SSL certificates for inbound and outbound mail services.
- Consolidated template structure, removing over 50% duplicate code.
- Re-organised file system structure for domain configuration files.
- Added the ability to change release branches through the user interface and the command line.
- v-sys-update-hestia-git: Added the ability to update using Git from the command line.
- Implemented support for SFTP chroot jails.
- A newly redesigned user interface which features:
    - A softer color palette which better matches the Hestia Control Panel logo colors.
    - A consolidated overview of domains and other information.
    - Improved navigation paths to make things easier to find.
    - Improved compatibility when viewing the Control Panel interface from a mobile device.
- Improved handling of mail domain DNS zone values.
- Enabled OCSP stapling on SSL-enabled web domains.
- v-change-web-domain-hsts: Enabled support for HTTP Strict Transport Security (HSTS) on SSL.
- Improved logging and console output during new installations and upgrades.

### Bugfixes
- Fixed issues with HTTP-to-HTTPS redirecton.
- Fixed an issue where another website would load if browsing to a non-SSL enabled domaing using HTTPS.

## [1.0.0-190618] - 2019-06-25
### Features
- 

### Bugfixes
- 

## [0.9.8-28] - 2019-05-16
### Features
- Implemented force SSL function for web domains.

### Bugfixes
- 


[CURRENT]: https://github.com/hestiacp/hestiacp
[1.0.4]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.4
[1.0.3]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.3
[1.0.1]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.1
[1.0.0-190618]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.0-190618
[0.9.8-28]: https://github.com/hestiacp/hestiacp/releases/tag/0.9.8-28
