# Changelog
All notable changes to this project will be documented in this file.

## [CURRENT] - Development
### Features
- Add read only/demo mode function if DEMO_MODE is set to yes in hestia.conf
- Add php-imagick module to installer and upgrade scripts

### Bugfixes
- Add a detection of web root for add .well-known acme challenge.
- Rework Let's Encrypt acme staging to use hestia conform standard.
- Fix if condition, use nginx for Let's Encrypt acme request if present.
- Rework v-add-sys-ip, remove centos/redhat support and rework conditions.
- Enable hsts and force ssl on v-add-letsencrypt-host.

## [1.0.4] - 2019-07-09 - Hotfix
### Bugfixes
- Delay start of services to prevent restart liimit

## [1.0.3] - 2019-07-09 - Hotfix
### Bugfixes
- Fix Let's Encrypt Mail SSL permission issue

## [1.0.1] - 2019-06-25
### Features
- Improved support for Let's Encrypt certificate generation
- Addition of Let's Encrypt support for Control Panel – see v-add-letsencrypt-host
- Enabled use of per-domain SSL certificates for inbound and outbound mail services
- Consolidated template structure, removing over 50% duplicate code
- Re-organized file system structure for domain configuration files
- Added the ability to change release branches through the user interface and command line
- Added the ability to update using Git from the command line - see v-sys-update-hestia-git
- Implemented support for SFTP chroot jails
- A newly redesigned user interface which features:
    - A softer color palette which better matches the Hestia Control Panel logo colors.
    - A consolidated overview of domains and other information
    - Improved navigation paths to make things easier to find
    - Improved compatibility when viewing the Control Panel interface from a mobile device
- Improved handling of mail domain DNS zone values
- Enabled OCSP stapling on SSL-enabled web domains
- Enabled support for HTTP Strict Transport Security on SSL-enabled web domains in the system backend– see v-change-web-domain-hsts
- Improved logging and console output during new installations and upgrades

### Bugfixes
- Fixed issues with HTTP-to-HTTPS redirecton
- Fixed an issue where another website would load if browsing to a non-SSL enabled domaing using HTTPS.

## [1.0.0-190618] - 2019-06-25
### Features
- 

### Bugfixes
- 

## [0.9.8-28] - 2019-05-16
### Features
- Implement force ssl function for web domains

### Bugfixes
- 


[CURRENT]: https://github.com/hestiacp/hestiacp
[1.0.4]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.4
[1.0.3]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.3
[1.0.1]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.1
[1.0.0-190618]: https://github.com/hestiacp/hestiacp/releases/tag/1.0.0-190618
[0.9.8-28]: https://github.com/hestiacp/hestiacp/releases/tag/0.9.8-28
