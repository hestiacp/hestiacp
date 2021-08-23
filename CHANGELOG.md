# Changelog
All notable changes to this project will be documented in this file.

## [Development]

### Features

- Add support for Debian 11  (Bullseye) #1611
- Add support for openssl in hestia-php 
- Replace old firewall system with systemd service / startup script #2064 @myrevery
- Add Quick installers for GravCMS, Docuwiki and Mediawiki (#2002) @PsychotherapistSam

### Bugfixes

- Improve handling upgrade of Roundcube #1917
- Fix an issue with sorting the update scripts when version goes higher then 1.x.10 
- Allow the use of multiple CAA records for domain. #2073
- Add missing group (www-data) to migrate_phpmyadmin script #2077 @bet0x

## [1.4.10] - Service release 

### Features

- Added v-delete-firewall-ban ip all #2031
- Include config tests for nginx/apache2 templates

### Bugfixes

- Fixed UI issues after upgrade jQuery + jQuery UI to last version (#2021 and #2032) + [forum](https://forum.hestiacp.com/t/confusion-about-send-welcome-email-checkbox/4259/11)
- Fixed security issues in caching templates of Nginx when used as Reverse Proxy
- Fixed an issue with deleting multiple mail accounts (#2047)
- Fixed an issue with phpmailer + non latin characters (#2050) thanks @Faymir 
- Fix Unable to load dynamic library 'pdo_mysql.so' after php reinstalling (#2069)

## [1.4.9] - Service release 

### Bugfixes

- Updated jQuery and jQuery UI to the latest version due to a vulnerability in jQuery. @dependabot
- Fixed bug in /etc/dovecot/conf.d/10-ssl.conf for new installs
- Fixed bug with notifications
- Fixed translation string @myrevery 

## [1.4.8] - Service release 

### Features

- Add support for automated testing for HestiaCP code with @drone
- Add support for SMTP server for internal email #1988 @Myself5 / #1165

### Bugfixes

- Updated jQuery and jQuery UI to the latest version due to a vulnerability in jQuery. @dependabot
- Resolve issue with double ENFORCE_SUBDOMAIN_OWNERSHIP keys in hestia.conf
- Resolve issue with create new user during install in some cases #2000
- Fixed an issue with Quick Install apps named Test123 (@PsychotherapistSam)
- Fix an issue with dovecot 2.3 ssl config (#1432)
- Load $HESTIA path during upgrade script (#1698)
- Remove TLS 1.1 from Proftpd config (#950)
- Don't remove postfix when Exim is not installed (#1995)
- Fix a bug in no-php Nginx FPM template (##2007)
- Update German translations
- Fixed a few minor error in Mail DMS records (#2005)


## [1.4.7] - Service release 

### Bugfixes

- Fixed #1984 phppgadmin not working on apache2 systems
- Fixed #1985 Restart service not working


## [1.4.6] - Service release 

### Features

- Add support for custom install hooks #1757
- Add template for CraftCMS #1973 @anvme
- Upgrade Filegator to 7.6.0

### Bugfixes

- Fixed #1961 Renewal Apache2 only SSL certificate fails
- Fixed #1956 to prevent reset of defined webmail client.
- Explicitly disable cron reports #1978 
- Fixed an issue where in rare cases certificate failed to install @dpeca and @myvesta
- Fixed an issue where composer failed to install when .composer folder is missing
- Fixed #1980 Lets Encrypt Auto Renewal Reverts Webmail Client back to Roundcube

## [1.4.5] - Service release

### Bugfixes

- Revert #1943 and rework it to fix possible errors occurring on v-rebuild-cron-jobs.
- Fixed #1956 to prevent reset of defined webmail client.
- Explicitly disable cron reports #1978

## [1.4.4] - Service release

### Features

- Add nginx user_agent separation to desktop/mobile (e.g. for fastcgi cache)
- Run phpmyadmin folder under www-data user instead of "user" improving security. (@bet0x)
- Added new template for mod php users to access phpmyadmin

### Bugfixes

- Add template for when webmail is disabled allowing to generate SSL. 
- Fixed PHP bug in /list/log/ 
- Fixed issue with time in /list/services as it was showing as 50 minute1 instead of minutes
- Add missing back buttons + fix behaviour of back buttons on login page. 
- Set "default" when WEB_TEMPLATE and PROXY_TEMPLATE is missing in user.conf 
- Add BACKEND_TEMPLATE to default package
- Fixed possible error occur for v-rebuild-cron-jobs #1943 (thanks @clarkchentw)
- Restrict access file manager when SSH is enabled for the user (@bet0x)
- Check for DNS domains when running v-change-sys-ip-nat (@clarkchentw)
- Fixed logical error in installer (@clarkchentw)

## [1.4.3] - Service release

### Features

- Include DMARC record in DNS record list #1836
- Enabled phpMyAdmin Single Sign On support #1460
- Add command to add / delete from API_ALLOWED_IP list (#1904)

### Bugfixes

- Improve the calculated disk size of a new backup estimated by excluding the exclude folders, mail accounts and database in backups (#1616) @Myself5
- Improve v-update-firewall / v-stop-firewall to make it self healing (#1892) @myrevery 
- Update phpMyAdmin version to 1.5.1 (See https://www.phpmyadmin.net/news/2021/6/4/phpmyadmin-511-released/)
- Fixed a bug after rebuilding mail with Exim4 and suspended domains (#1886)
- Fixed "Allowed IP addresses for API" field with strange behaviour #1866
- Fixed an issue where the "Saved confirmation" was not set due to a redirect #1879
- Increased minimal memory requirements for ClamD / ClamAV.  #1840
- Restore of backup did not rebuild the "Forced SSL" and "HSTS" config on new account #1862
- Keep changes made by /install/upgrade/manual/install_awstats_geopip.sh on update HestiaCP (via Discord)
- Refactor/improve PHP and HTML code @s0t (#1860)
- Fixed XSS vulnerability in login page and a few other locations @briansemrau / @numanturle
- Delete old session after session_regenerate_id() @briansemrau
- Improve error message when domain all ready exists on different account.
- Fixed an issue where phpmyadmin did not update when Postgresql was available.
- Webmail clients set to rainloop where not able to create a SSL certificate via LE #1913
- Fixed an issue where plugin-hestia-change-pasword did not change the port on v-change-sys-port (Rainloop) #1895
- Fixed an issue where HELO message was not set / error was created on NAT IP

## [1.4.2] - Service release

- **NOTE:** During the 1.4.1 / 1.4.0 release we have introduced a bug for Ubuntu 20.04 and 18.04 users with multiple network ports on the server. This release will solve the problems caused by this bug! If you are unable to download the Hestia packages via apt. Run the following command via CLI or SSH as root

```
    iptables -A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT
```

Then run the update via 

```
    apt update && apt upgrade
```

### Bugfixes

- Fixed issue wit startup script for iptables / network (#1849) (@myrevery)
- Fixed problem with accidentally replacing nginx.conf during upgrade nginx (#1878 / @myrevery)
- Fixed issue with installing Ubuntu 18.04
- Fixed issue with login into file manger as admin user
- Added proxy_extentions back to support older custom templates
- Added the possibility to skip the forced reboot when interactive is set to no
- Fixed an issue with modx template
- Updated translations (Croatian, Czech and Italian)  
- Fixed an issue where users where not able to save / update web domains when POLICY_USER_EDIT_WEB_TEMPLATES is enabled (#1872)
- Fixed an issue where admin users where not able to add new ssh key for users (#1870)
- Fixed an issue where domain.com was not affected as a valid domain (#1874)
- Fixed an issue where "development" icon was not removed on update to release (#1835)
  
## [1.4.1] - Bug fix

- Fixed bug with 2FA enabled logins 

## [1.4.0] - Major Release (Feature / Quality Update)

- **NOTE:** Ubuntu 16.04 (Xenial) is no longer supported as it has reached EOL (end-of-life) status. 
- **NOTE:** Apache in "standalone" mode is no longer actively supported and has been removed from installer options. Nginx (Proxy) + Apache2 will remain supported. 
- **NOTE:** Custom "quick installer apps" will not work anymore due to changes in how we handle quick installer apps. Minimal changes to the Quick installer apps are required! Please check https://github.com/hestiacp/hestia-quick-install for how to migrate!
- **NOTE:** Manual upgrade scripts are available to update Roundcube, Rainloop and PHPmyadmin to the last version they can be found in /usr/local/hestia/install/upgrade/manual/

### Features
- Introduced support for NGINX FastCGI cache.
- Introduced support for SMTP Relay / smarthosts (server-wide or per-domain).
- Introduced the ability to choose which webmail client to use per-domain (Roundcube or Rainloop).
- Added support for Rainloop (Run v-add-sys-rainloop to install it)
- Added B2 Backup Support for Remote Backup Location - thanks **@rez0n**!
- Added template support for osTicket - thanks **@madito**!
- Packages for phpMyAdmin, Roundcube, and Rainloop will be pulled directly from their upstream source instead of APT for new installations.
- Added DNS records view to mail domains which provides DKIM, SPF, and other entries to use with an external provider.
- Added an upgrade script to provide in-place upgrades to php7.4 (or any other version).
- Added Drupal and Nextcloud quick installer support (Removed placeholder Joomla)
- Added a new optional theme "Vestia"
- Added a switch to disable the API and also limit the api by default to 127.0.0.1 only. For current installs added the option "allow-all" on default 
- After first reboot of Hestia will try do 1 attempt to request / generate a valid Lets encrypt certificate
- Introduced multiple new security policies via WebUI. 
    - Allow users to edit Web / Proxy / DNS / Backend templates
    - Allow users to edit account details
    - Allow suspended users to login with "read-only" access
    - Allow users view / delete user history
    - Enforce sub domain ownership
    - Limit access to admin account when other users have the role "Administrator" assigned to them.
- Disable user to login via WebUI / Limit access to WebUI to certain IP address per user. 
- Discourage websites to be created under "admin" account and redirect users to create new users. 
- Added support for redirecting to www / non www domains (or custom)  #427 / #1638.
- Allow users to see failed login attempts on there account. 
- Introduced support for ARM based systems. Currently the packages are not available via ATP! 
- Force reboot of system after install

### Bugfixes
- Fixed an issue where user name was duplicated when editing FTP users. (#1411)
- Fixed an issue where the iptables service would appear to be in a stopped state when fail2ban is stopped. (#1374)
- Fixed an issue where the default language value was incorrectly set under Server Settings > Configure.
- Fixed an issue with the dark theme where available updates were incorrectly displayed.
- Fixed an issue where local and FTP backup files were not deleted when running `v-delete-user-backup`. (#1421)
- Fixed an issue where IP addresses could not be deleted. (#1423)
- Fixed an issue where `v-rebuild-user` would incorrectly rebuild domain items in addition to user account configuration.
- Fixed an issue which caused a web domain's custom document root value to be lost when restoring from backup.
- Fixed an issue which caused a `NSPOSIXErrorDomain:100` error when using Safari/iOS (thanks **@stsimb**).
- Fixed an issue where exim ignored the configured mail quota limit.
- Fixed an issue where invalid character validation was performed when editing mail auto replies.
- Fixed an issue which caused Let's Encrypt to fail when using the Moodle template (thanks **@ArturoBlanco**).
- Fixed an issue where the MySQL `wait_timeout` value was not saved due to wrong regexp attribute (thanks **@guicapanema**).
- Fixed an issue where nginx web statistics authorization file was placed in the wrong directory.
- Fixed several small issues that were reported when using PostgreSQL.
- Improved reliability of mail domains and webmail clients.
- Improved reliability of service restarts during upgrades.
- Improved compatibility with Blesta / WHMCS plugins.
- Improved API error handling routines - thanks **@danielalexis**!
- Improved backup performance through the use of multi-threading when creating archives using the `zstd` compression type.
- Improved error handling when creating firewall rules.
- Improved handling of suspended users and domains to allow deletion without unsuspension.
- Improved dependencies over package control to install `lsb-release` and `zstd`.
- Improved SFTP connection handling to be case insensitive (thanks **@lazzurs**).
- Improved domain validation to prevent creating subdomains when the top-level domain belongs to another account (thanks **@KuJoe** and **@sickcodes**).
- Improved IDN domain handling to resolve issues with Let's Encrypt SSL and mail domain services.
- Added private folder to openbasedir permissions for all main templates.
- Disabled changing backup folder via Web UI because it used symbolic link instead of mount causing issues with restore mail / user files.
- Fixed XSS vulnerability in `v-add-sys-ip` and user history log (thanks **@numanturle**).
- Fixed remote code execution vulnerability which could occur when deleting SSH keys (thanks **@numanturle**).
- Fixed vulnerability in v-update-sys-hestia (thanks **@numanturle**)
- Disabled the Update via WebUI due to timeout issues. Please update via ```apt update && apt upgrade``` in command line instead.
- Improve how Quick install of web apps are handled and allow users added apps to be maintained in list view. 
- Fixed an issue where the api was enabled after an update of HestiaCP
- Fixed an issue when the default php version got deleted webmail didn't work any more. #1477
- Limit access when "demo" mode is enabled. 
- Fixed an issue where limitations on aliases didn't work propperly
- Fixed an issue where "Exit to control pannel" link got changed to "Logout" #1669
- Allow packages to be deleted when in use. Current users are changed to "Default" package. 
- Fixed multiple bugs with in v-restore-users
- Redesign statics page
- Allow self signed certificates to be created with aliases. 
- Fixed issue where mail accounts where sorting incorrectly by size #1687
- Improve results v-search-command #1703
- Merge Codeiginiter / Drupal templates. 
- Prepare template for FastCGI support an improve security by allowing only .well-known for Let's encrypt requests
- Update Cloudflare Ips in nginx.conf
- Fixed an issue where emails where send to nobody when connection failed to database #1765
- Fixed an issue where no notifications where send on failure and save local backup if remote backup failed. 
- Fixed an issue where domains containing 2 dots in the top level domain could accidentally got removed #1763
- Fixed an issue where www could be created and after delete webmail doesn't work anymore #1746
- Standardize headers for upgrade scripts
- Improved how we handle custom themes
- Refactored HMTL / PHP code WebUI
- Updated ClamAV configuration
- Fixed issue where file manger key got the wrong permissions
- Update version Laveral @mariojgt

## [1.3.5] - Service Release
### Features
- No new features have been introduced in this release.

### Bugfixes
- Updated APT repository key for PHP from packages.sury.org (https://forum.hestiacp.com/t/apt-upgrade-failed-gpg-error-packages-sury-org)
- Updated phpMyAdmin to v5.1.0.

## [1.3.4] - Service Release
### Features
- No new features have been introduced in this release.

### Bugfixes
- Fixed xss vulnerability in v-add-sys-ip and user history log (thanks **@numanturle**)
- Fixed remote execution possibility when deleting ssh key (thanks **@numanturle**)

## [1.3.3] - Service Release
### Bugfixes
- Improved if web folder already exists and do not follow symlink on chmod (thanks @0xGsch and @kikoas1995).
- Improved api key authentification to prevent brute force attacks.
- Improved ssh keys folder permission to prevent unauthorized access.

## [1.3.2] - Service Release
### Features
- Added PHP v8.0 support for multiphp environment.

### Bugfixes
- Improved session token handling in login as function, thanks to Vulnerability Laboratory - [Evolution Security GmbH]™.
- Fixed an where fpm pool config was not deleted when changing backend template.
- Improved bats testing with multiphp (5.6-8.0) tests.
- Fixed an issue where full webmail path was loaded as default value.

## [1.3.1] - Service Release
### Features
- No new features have been introduced in this release.

### Bugfixes
- Fixed an issue where updates for `hestia-php` were incorrectly being marked as out-of-date in the UI due to a change in our servicing and package versioning scheme.
- Fixed an issue that occured on the Updates page where the table row color of available updates would be difficult to read.
- Fixed an issue where an administrator would get stuck in a loop trying to navigate back after adding a SSH key.
- Fixed an issue where long table entries which exceeded the table length would overlap other UI elements.
- Fixed an issue where the total amount of items on a page would fail to display correctly.
- Improved the accuracy and reliability of tooltips throughout the the Control Panel UI:
    - Removed unnecessary tooltips from buttons and other elements.
    - Fixed incorrect tags which prevented tooltips from being displayed.
    - Introduced tooltips to counter items on the Users, Packages, and Statistics pages to help better distinguish statistics.
- Improved the display of items, quotas, and suspended items in the Control Panel navigation header - thanks **@cmstew**!
- Fixed an issue which caused higher than normal CPU usage during an upgrade due to a duplicate condition in the rebuild process.
- Fixed minor spelling inconsistencies in command line script comments and output text.
- Fixed an issue where old configuration files were not cleaned up when moving domains with `v-change-domain-owner`.
- Fixed an issue where a `no backend template doesn't exist` could potentially would appear after upgrade with older templates (#1322).
- Introduced caching templates for nginx + php-fpm configurations  - thanks **@cmstew**!
- Fixed an issue where DNS cluster updates could fail due to the format of a DKIM record in an available zone - thanks **@jrohde**!
- Improved the quality of comment formatting in command line scripts - thanks **@bisubus**! 
- Fixed an issue where the logo was not displayed in the File Manager - thanks **@robothemes**!
- Fixed an issue in the Control Panel UI which caused databases and additional FTP accounts to be named incorrectly if manually prefaced with the username.
- Fixed an issue where custom document roots were not saved correctly.
- Improved the visibility of service availability in the Control Panel UI.
- Fixed an issue which let you unsuspend a cronjob on active demo mode.
- Updated DE, EN, ES, KO, NL and TR languages, thanks to @Wibol, Blackjack, @emrahkayihan, areo and @hahagu!
- Fixed an issue which let the auto compiler fail with local src builds.
- Added turkish language to system installers, thanks to @emrahkayihan!
- Fixed incorrect error message when using unknown domain with v-delete-domain.

## [1.3.0] - Major Release (Feature / Quality Update)
### Features
- Users can now choose to point a domain to a different document root location (similar to domain parking).
- The software update process will now perform a system health check before proceeding with installation.
- Administrators now have control over software update notifications through the following settings in `$HESTIA/conf/hestia.conf` and through the Control Panel web interface:
    - `UPGRADE_SEND_EMAIL` = Sends an email notification to primary admin account's email address
    - `UPGRADE_SEND_EMAIL_LOG` = Sends installation log output to the primary admin account's email address
- The upgrade process will now save installation logs to the `/root/hst_backups` directory by default for post-install troubleshooting.
    - **Note:** We may adjust this path in the future and will document such changes as they happen.
- We've introduced the ability to assign Administrator rights to other user accounts, enabling them to perform tasks under the Server Settings tab.
- We've introduced a more robust translation system which will allow us to provide higher quality translations in future releases.
    - **Note:** Some country codes have been updated, as a result your language setting may default back to English after upgrading.
- For new installations, MariaDB 10.5 is now the default version.
    - For existing installations, we've provided a manual post-install upgrade script. Please run `$HESTIA/install/upgrade/manual/upgrade_mariadb.sh` to migrate to MariaDB 10.5).
- The user interface theme has been set to "Dark" by default. This can be changed from **Server Settings > Configure > Basic Options > Appearance**.
    - **Note:** The name of the default theme has not been adjusted, and the change to the "dark" theme only applies to new installations at this time. This behavior may be changed in a future release.

### Bugfixes
- Fixed a security issue where user password reset keys could potentially be gleaned from system process list - thanks **RACK911 LABS**
- Fixed an issue with passwords containing "`'`" - [Forum](https://forum.hestiacp.com/t/two-factor-authentication-issue-with-standard-user/1652/)
- Fixed an issue with database backups when the port was not specified (#1068)
- Fixed an issue where websites without SSL enabled would display the content of the first valid SSL enabled website (#1103)
- Fixed an issue that would occur when using the `--with-debs` flag with the installer due to an incorrect version check routine (#1110)
- Fixed an issue with incorrect permissions which would occur when restoring email accounts (#1114)
- Fixed an issue where the File Manager would apply the wrong permissions on new directories
- Fixed an issue that prevented successful restoration of SSL-enabled mail domains from a backup archive (#1069)
- Fixed an issue where the phpMyAdmin button would not work in the Control Panel Web UI (#1078)
- Fixed an issue where passwords were generated incorrectly (#1184)
- Fixed an issue in `v-add-sys-ip` to ensure IP configuration is set to the correct port - thanks **@madito**
- Fixed an issue that resulted in an extended loop condition when running `v-rebuild-all`
- Improved support for API key usage with the `v-add-remote-dns-host` command (#1265)
- Improved validation of free disk space when executing backup routine (#1115)
- Improved support for SSH key types other than RSA / DSA
- Improved reliability of backup function when removing remote locations (#1083)
- Improved spam filtering by adding additional known-dangerous file extensions in exim's blacklist (#1138) - thanks **@kpapad904**
- Updated Apache2 configuration to use Include with IncludeOptional (#1072)
- Removed the ability to log in as "root" (whic logged to the admin account, deemed no longer necessary)
- Add ca-certificates, software-properties-common to the dependencies (#1073 + [Forum](https://forum.hestiacp.com/t/hestiscp-fails-on-new-debian-9-vps/1623/8)) - thanks **@daniel-eder**
- Create .npm directory by default when creating new user accounts (#1113) - thanks **@hahagu** 
- Improved accuracy of several UI translations (NL, DE, UK, RU, ES, IT, ZH-CN) - thanks **@myrevery** and other contributors for your work!
- Added `$restart` flag to `v-add-web-domain-backend` command (#1094) (#797) - thanks **@bright-soft**
- PostgreSQL: forbid the use of upper case (#1084) causing issues with backup / creating database or user
- Changed WordPress name in Quick Web App installer (#1074)
- Cleaned up entries used in the Google / Gmail DNS template - thanks **@madito**
- Enhanced ProFTPd support for TLS
- Refactored LXD compiler script
- Updated phpMyAdmin to version 5.0.4

## [1.2.4] - Service Release
### Features
- No new features have been introduced in this release.

### Bugfixes
- Fixes an issue on auto renewing let's encrypt certificates.

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
- Fixed incorrect MX record for DNS domains using the Office 365 template.

## [1.0.6] - 2019-09-24 - Hotfix
### Bugfixes
- Add support for HTTP/2 Let's Encrypt Server.

## [1.0.5] - 2019-08-06 - Hotfix
### Bugfixes
- Fixed several security issues, thanks to Andrea Cardaci (https://cardaci.xyz/)
- Rework Let's Encrypt ACME staging to use hestia conform standard.
- Fixed if condition, use nginx for Let's Encrypt ACME request if present.

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
