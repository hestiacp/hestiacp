# Changelog
All notable changes to this project will be documented in this file.

## [1.3.0] - Major Release (Feature / Quality Update)
### Features
- Users can now choose to point a domain to a different document root (similar to domain parking).
- The software update procedure will now perform a system health check prior to installation and repair missing environment variables.
- Administrators now have control over software update notifications through the following settings in `$HESTIA/conf/hestia.conf` and through the Control Panel web interface:
    - `UPGRADE_SEND_EMAIL` = Sends an email notification to admin email address
    - `UPGRADE_SEND_EMAIL_LOG` = Sends installation log output to admin email address
- Upgrade process will now save logs to the `hst_backups` directory.
- Support for removing backup remote location (#1083)
- Add support Proftpd TLS Support
- Add the possibility to assign user "Administrators" rights on login. Replaces "root" login. Notifications are only send towards the "admin" account email
- Updated translations system with the use of Gettext. Modified / Updated all translated strings

## Bugfixes
- Removed root login (root / root password )
- Update apache2.conf replace Include with IncludeOptional (#1072)
- Add ca-certificates, software-properties-common to the dependencies (#1073 + [Forum](https://forum.hestiacp.com/t/hestiscp-fails-on-new-debian-9-vps/1623/8) ) @daniel-eder
- Fixed issues with database port during backup when port was missing (#1068)
- Postqresql: forbid the use of upper case (#1084) causing issues with backup / creating database or user
- Fixed permissions email account during restore (#1114)
- Create .npm on creating new user (#1113) @hahagu 
- Fixed Access to a website without a ssl certificate on https shows the content of the first, valid ssl website (#1103)
- Fixed an issue when installing --with-debs and version check (#1110)
- Improved Translations Chinese @myrevery
- File manager create directory with proper permissions 
- Removed loop ad v-rebuild-all (#1096)
- Add $restart flag to v-add-web-domain-backend call (#1094) (#797) @bright-soft
- Fixed an issue with Restore Failed on Domains with Mail Setups using SSL (#1069)
- Fixed an issue with PHPMyAdmin button (#1078)
- Changed WordPress name in Webapp installer (#1074)
- Add a free disk space validation during backup routine (#1115)
- Removed PHP validation SSH keys allowing support other types then RSA / DSA
- Fixed issue with v-add-sys-ip and saving the ip configuration to correct port (@madito)
- Updated Exim black list for extensions (@kpapad904 / #1138)
- Fixed multiple bugs due to translations 
- Fixed bug with passwords containing "'" [Forum](https://forum.hestiacp.com/t/two-factor-authentication-issue-with-standard-user/1652/)
- Refactor LXD  Complier script
- Set default theme to "Dark"
- Clean up gmail.tpl (DNS) (@madito)


## [1.2.3] - Service Release
### Features
- No new features have been introduced in this release.

### Bugfixes
- Fixes an issue where non-ASCII characters were rejected in the password field.

## [1.2.2] - Service Release
### Features
- No new features have been introduced in this release.

### Bugfixes
- Create mailhelo.conf if it doesnt exist to prevent a error message during grep.
- Corrected the display of DNS record types to appear in alphabetical order.
- Fixed an issue where the DNS record type field would reset if an error occurred while adding a new DNS record. (#992)
- Fixed an issue where the DNS domain hint would not appear correctly when editing a DNS record. (#993)
- Fixed an issue where a DNS record would become malformed if changed from A to CNAME. (#988)
- Fixed an issue with the back button on the DNS records page. (#989)
- Fixed an issue where phpMyAdmin/phpPgAdmin would not load correctly due to an incorrect vhost configuration. (#970)
- Fixed an issue where malformed JSON output was returned when custom theme files are present. (#967)
- Fixed an error that would occur when running `v-change-user-php-cli` for the first time if .bash_aliases did not exist. (#960)
- Corrected an issue where tooltips were not displayed when hovering over the top level menu items.
- Improved handling of APT repository keys during installation.
- Reworked the Let's Encrypt renew functionality to skip removed aliases.
- Improved reliability of list handling when using IP lists.
- Enforce minimum password requirements with visual indication of password strength.
- Fixed an issue where user display name value was incorrectly set when changing packages.
- Improved installer version detection.
- Improved detection of MariaDB and MySQL services.

## [1.2.1] - Service Release
### Features
- Consolidated First and Last Name fields to a singular name field to simply input.
    - v-change-user-name will now accept both "First Last" (single argument) and First Last (two arguments) for backward compatibility.
- Removed ntpdate from new installations and enable systemd timesync daemon instead (thanks **@braewoods**)

### Bugfixes
- Fixed an issue where Composer would fail to install due to missing default directory.
- Corrected an issue where two-factor authentication validation was causing high CPU load during the login process. The login screen has been re-designed as a multi-step process (Username > Password > OTP PIN).
- Corrected an issue where text entry fields on the login screen were not automatically focused by default.
- Fixed an issue where RDNS value was incorrectly set if dig failed to resolve the server name.
- Fixed an issue where icons were pushed down in the header when using Bulgarian as the display language. (#932)
- Fixed an issue where new backups were not created when running v-schedule-user-backup-download. (#918)
- Fixed an issue where default configuration files and templates were not backed up correctly.
- Improved quality of default web domain templates for Drupal. (#916)
- Added missing strings to translation files (translations to follow).
- Corrected an issue where toolbars were out of place on the Mail and Firewall pages when using Bulgarian or Greek languages due to string length.
- Improved Spanish translations (thanks **@Wibol**)
- Improved German translations (thanks **@ronald-at**)
- Improved Russian translations (thanks **@Pleskan**)

## [1.2.0] - Major Release (Feature / Quality Update)
### Features
- **NOTE:** Debian 8 is no longer supported as it has reached EOL (end-of-life) status.
- Added support for Ubuntu Server 20.04 LTS.
- Added File Manager functionality (with File Gator backend) with the ability to add or remove at any time (`v-add-sys-filemanager` and `v-delete-sys-filemanager`)
- Extended built-in firewall to support allowing or blocking traffic using IP lists.
- Improved Apache2 performance by switching to mpm_event instead mod_prefork by default for new installations.
- Added support for configuring individual TTL per DNS record. Thanks to **@jaapmarcus**!
- Updated translations for Polish (thanks to @RejectPL!), Dutch, French, German, and Italian (WIP).
- Added the ability to set the default PHP command line version per-user.
- Added geolocation support to awstats to improve traffic reports.
- Enabled Roundcube plugins newmail_notifier and zipdownload by default.
- Added HELO support for multiple domains and IPs.
- Added the ability to manage SSH keys from CLI and web interface.
- Added a manual migration script for apache2 mpm_event for existing installations/upgrades (`$HESTIA/install/upgrade/manual/migrate_mpm_event.sh`).
- Added BATS system for testing the functionality of Bash scripts (WIP).
- Added **v-change-sys-db-alias** to change phpMyAdmin and phpPgAdmin access points (`v-change-sys-db-alias pma/pga myCustomURL`).

### Bugfixes
- Prevent ability to change the password of a non-Hestia user account. Thanks to **Alexandre Zanni**!
- Adjust Let's Encrypt validation check for IDN domains, thanks to **@zanami**!
- Set backup download location on FTP/SFTP restore, thanks to **@Daniyal-Javani**!
- Stop trying to renew Let's Encrypt certificates after multiple consecutive failed attempts. Thanks to **@dpeca**!
- Fixed an issue with auto-logout when used behind Cloudflare proxy and reworked 2FA authentication part. Thanks to **@rmj-s**!
- Fixed an issue where changing an email account password would fail if similar account names were present.
- Fixed an issue where e-mail quota was not preserved when (un)suspending or rebuilding a mail account.
- Fixed an issue where SSH configuration was not saved currently when edited from the Web interface.
- Fixed an issue where DNS IP did not use NAT/Public IP when available after changing web domain IP.
- Fixed an issue that would occur when a user would attempt to recover their account when two-factor authentication is enabled.
- Fixed permission issues that were presented when restoring a user backup.
- Improved page load performance of Control Panel web interface.
- Use Sury.org repository for Apache2 packages.
- Improved compatibility with Roundcube and PHP 7.4.
- Restrict the ability to edit crontab service via Control Panel web interface.
- Check whether Nginx, Apache2 and MariaDB are selected for installation prior to adding third party repositories.
- Restrict public access to Apache2 server-status page.
- Remove duplicated set-cookie line in default fpm config.
- Ignore empty lines when listing firewall rules.
- Improved top-level navigation in the Control Panel web interface (Server tab has been moved next to the Notification icon).
- Corrected various minor user interface and theme issues.
- Cleanup temporary files when uploading custom SSL certificate from Web interface.
- Cleanup temporary files when adding/renewing Let's Encrypt SSL certificate.
- Cleanup temporary files after running v-list-sys-services.
- Removed some legacy code and unused assets.
- Don't calculate /home folder size in v-list-sys-info.
- Adjust v-list-sys-services to honor the changed fail2ban service name.
- Rework busy port validation in v-change-sys-port.
- Implement a validation function to verify the correct version in hestia.conf prior to installation.
- Introduced a delay when an incorrect username, password, or 2FA code has been entered repeatedly.
- Improved "Forgot password" function prevent brute force attacks.
- Fixed an issue where the backup update counter was not updated properly when v-delete-user-backup was executed.
- Fixed an issue with public_(s)html file ownership.
- Fixed an issue with phpPgAdmin access.
- Fixed an issue where the wrong port was set for www.conf on certain configurations.
- Fixed an issue where Composer would fail to install.
- Fixed an issue where the selected theme was not immediately applied.
- Fixed an issue where HTTP-to-HTTPS redirection and HTTP Strict Transport Security (HSTS) events were not shown in the user history log.
- Fixed an issue where the web domain access log page was incorrectly formatted.
- Fixed an issue where awstats would show a HostAliases error if a web domain did not have any aliases.
- Fixed an issue where awstats configuration was not updated if web domain aliases were added or removed.
- Fixed an issue where user interface elements would overlap or display in the wrong place when using non-English locales.
- Fixed an issue where phpMyAdmin and phpPgAdmin were inaccessible from the Web UI if custom URLs had been set.
- Fixed an issue where mail SSL certificates were not restored properly from a backup archive.
- Fixed an issue where mail domain configuration files were not removed when the domain was deleted.
- Improved the functionality of `v-change-domain-owner` to correctly move mail domains and provide status output and logging/notifications.
- Improved the functionality of `v-update-sys-hestia-git` to allow user to specify GitHub repository and whether to build only core package or core and dependencies.
- Corrected the behavior of phpMyAdmin and phpPgAdmin so that alias dialogs accept custom word only and not full URL, aligns with webmail alias behavior.
- Corrected the behavior of the installer so that APT repositories are not added if installation is aborted due to version mismatch.
- Fixed an issue where upgrade procedures were not executed correctly when skipping between versions (e.g. 1.0.6 > 1.2.0).

### Known issues and notes
- **NOTE:** Custom phpMyAdmin and phpPgAdmin URL's will be reset once during this upgrade to correct a legacy code issue.
- Let's Encrypt renewal fails when removing alias from web domain (#856)
- Some translation strings need to be updated for accuracy (#746)
- v-restore-user only works with backup archives stored in /backup mount point (#641)

## [1.1.1] - 2020-03-24 - Hotfix
### Features
- No new features introduced with v1.1.1, this is strictly a security/bug fix release.

### Bugfixes
- Fixed phpMyAdmin blowfish and tmp directory issues.
- Added additional verification of host domain in password reset. Thanks to @FalzoMAD and @mmetince!
- Fixed issue with rc.local not executing properly.
- Rework of Let's Encrypt routine to use progressive delay between validation retries.
- Fixed syntax issue in v-list-sys-db-status which prevented main functions from loading.
- Removed /home size reporting when running v-list-sys-info due to performance issues.
- Updated installer to use Ubuntu key server for Hestia APT repository.
- Fixed duplicate demo mode check in v-change-user-password.

## [1.1.0] - 2020-03-11 - Major Release (Feature / Quality Update)
### Features
- Added support for custom user interface themes.
- Introduced official Dark and Flat themes.
- Added read-only/demo mode - DEMO_MODE must be set to yes in hestia.conf to enable.
- Added php-imagick module to installer and upgrade scripts.
- Added recidive filter function to fail2ban.
- Improved and refactored Multi-PHP functionality. 
- Multi-PHP will be enabled by default on new installations.
- Allow admin user to add/remove PHP versions from Web UI (Server -> Configure -> Web Server).
- Extended v-extract-fs-archive to allow archive testing and extracting only specific paths (for tar)
- Allow renaming of existing packages from console (v-rename-package).
- Added PHP 7.4 to Multi-PHP.
- Addded official support for Debian 10 (Buster).

### Bugfixes
- Added a detection of web root for add .well-known ACME challenge.
- Reworked Let's Encrypt ACME staging to use Hestia code standards.
- Fixed issues with incorrect font rendering on Windows and Linux.
- Fixed issues with Let's Encrypt - use Nginx for Let's Encrypt ACME request if present.
- Reworked v-add-sys-ip, removed deprecated CentOS/Red Hat code and reworked conditions.
- Enabled HSTS and force SSL on v-add-letsencrypt-host.
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
- Added robots.txt for Roundcube webmail to prevent search bot crawling.
- Re-Enable force ssl function on let's encrypt certification renew.
- Added official PostgreSQL repository so system stays up-to-date with latest available upstream packages.
- Hardening MySQL configuration, prevent local infile.
- Fixed lograte bug and cleans up the messed up nginx/apache2 log permissions.
- Fixed IfModule mpm_itk.c for apache2 templates.
- Added mpm_itk for Debian 10 (non Multi-PHP installations only.)
- Hardening nginx configuration, dropped support for TLSv1.1.
- Fixed excluding folders named "logs" from restore backup, thanks to @davidgolsen.
- Fixed typo in delete psql database part, thanks to @joshbmarshall.
- Split long txt records to 255 chunks to prevent bind issues, thanks to @setiseta.
- Fixed missing restart routine for vsftp on v-add-letsencrypt-host.
- Show amount of disk space consumed by /home when running v-list-sys-info.
- Remove broken /webmail alias from previous versions.
- Webmail IP address is now inherited from web domain when using multiple IPs.
- Exim now uses the web domain IP if it exists.
- Fix incorrect MX record for DNS domains using the Office 365 template.

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
