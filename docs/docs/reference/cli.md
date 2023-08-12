# CLI Reference

## v-acknowledge-user-notification

update user notification

**Options**: `USER` `NOTIFICATION`

This function updates user notification.

## v-add-access-key

generate access key

**Options**: `USER` `[PERMISSIONS]` `[COMMENT]` `[FORMAT]`

**Examples**:

```bash
v-add-access-key admin v-purge-nginx-cache,v-list-mail-accounts comment json
```

The "PERMISSIONS" argument is optional for the admin user only.
This function creates a key file in $HESTIA/data/access-keys/

## v-add-backup-host

add backup host

**Options**: `TYPE` `HOST` `USERNAME` `PASSWORD` `[PATH]` `[PORT]`

**Examples**:

```bash
v-add-backup-host sftp backup.acme.com admin p4$$w@Rd
v-add-backup-host b2 bucketName keyID applicationKey
```

Add a new remote backup location. Currently SFTP, FTP and Backblaze are supported

## v-add-cron-hestia-autoupdate

add cron job for hestia automatic updates

**Options**: `MODE`

This function adds a cronjob for hestia automatic updates
that can be downloaded from apt or git.

## v-add-cron-job

add cron job

**Options**: `USER` `MIN` `HOUR` `DAY` `MONTH` `WDAY` `COMMAND` `[JOB]` `[RESTART]`

**Examples**:

```bash
v-add-cron-job admin * * * * * sudo /usr/local/hestia/bin/v-backup-users
```

This function adds a job to cron daemon. When executing commands, any output
is mailed to user's email if parameter REPORTS is set to 'yes'.

## v-add-cron-letsencrypt-job

add cron job for Let's Encrypt certificates

**Options**: –

This function adds a new cron job for Let's Encrypt.

## v-add-cron-reports

add cron reports

**Options**: `USER`

**Examples**:

```bash
v-add-cron-reports admin
```

This function for enabling reports on cron tasks and administrative
notifications.

## v-add-cron-restart-job

add cron reports

**Options**: –

This function for enabling restart cron tasks

## v-add-database

add database

**Options**: `USER` `DATABASE` `DBUSER` `DBPASS` `[TYPE]` `[HOST]` `[CHARSET]`

**Examples**:

```bash
v-add-database admin wordpress_db matt qwerty123
```

This function creates the database concatenating username and user_db.
Supported types of databases you can get using v-list-sys-config script.
If the host isn't stated and there are few hosts configured on the server,
then the host will be defined by one of three algorithms. "First" will choose
the first host in the list. "Random" will chose the host by a chance.
"Weight" will distribute new database through hosts evenly. Algorithm and
types of supported databases is designated in the main configuration file.

## v-add-database-host

add new database server

**Options**: `TYPE` `HOST` `DBUSER` `DBPASS` `[MAX_DB]` `[CHARSETS]` `[TEMPLATE]` `[PORT]`

**Examples**:

```bash
v-add-database-host mysql localhost alice p@$$wOrd
```

This function add new database server to the server pool. It supports local
and remote database servers, which is useful for clusters. By adding a host
you can set limit for number of databases on a host. Template parameter is
used only for PostgreSQL and has an default value "template1". You can read
more about templates in official PostgreSQL documentation.

## v-add-database-temp-user

add temp database user

**Options**: `USER` `DATABASE` `[TYPE]` `[HOST]` `[TTL]`

**Examples**:

```bash
v-add-database-temp-user wordress wordpress_db mysql
```

This function creates an temporary database user mysql_sso_db_XXXXXXXX and a random password
The user has an limited validity and only granted access to the specific database
Returns json to be read SSO Script

## v-add-dns-domain

add dns domain

**Options**: `USER` `DOMAIN` `IP` `[NS1]` `[NS2]` `[NS3]` `[NS4]` `[NS5]` `[NS6]` `[NS7]` `[NS8]` `[RESTART]`

**Examples**:

```bash
v-add-dns-domain admin example.com ns1.example.com ns2.example.com '' '' '' '' '' '' yes
```

This function adds DNS zone with records defined in the template. If the exp
argument isn't stated, the expiration date value will be set to next year.
The soa argument is responsible for the relevant record. By default the first
user's NS server is used. TTL is set as common for the zone and for all of
its records with a default value of 14400 seconds.

## v-add-dns-on-web-alias

add dns domain or dns record after web domain alias

**Options**: `USER` `ALIAS` `IP` `[RESTART]`

**Examples**:

```bash
v-add-dns-on-web-alias admin www.example.com 8.8.8.8
```

This function adds dns domain or dns record based on web domain alias.

## v-add-dns-record

add dns record

**Options**: `USER` `DOMAIN` `RECORD` `TYPE` `VALUE` `[PRIORITY]` `[ID]` `[RESTART]` `[TTL]`

**Examples**:

```bash
v-add-dns-record admin acme.com www A 162.227.73.112
```

This function is used to add a new DNS record. Complex records of TXT, MX and
SRV types can be used by a filling in the 'value' argument. This function also
gets an ID parameter for definition of certain record identifiers or for the
regulation of records.

## v-add-domain

add web/dns/mail domain

**Options**: `USER` `DOMAIN` `[IP]` `[RESTART]`

**Examples**:

```bash
v-add-domain admin example.com
```

This function adds web/dns/mail domain to a server.

## v-add-fastcgi-cache

Enable FastCGI cache for nginx

**Options**: `USER` `DOMAIN` `[DURATION]` `[RESTART]`

**Examples**:

```bash
v-add-fastcgi-cache user domain.tld 30m
```

This function enables FastCGI cache for nginx
Acceptable values for duration is time in seconds (10s) minutes (10m) or days (10d)
Add "yes" as last parameter to restart nginx

## v-add-firewall-ban

add firewall blocking rule

**Options**: `IP` `CHAIN`

**Examples**:

```bash
v-add-firewall-ban 37.120.129.20 MAIL
```

This function adds new blocking rule to system firewall

## v-add-firewall-chain

add firewall chain

**Options**: `CHAIN` `[PORT]` `[PROTOCOL]` `[PROTOCOL]`

**Examples**:

```bash
v-add-firewall-chain CRM 5678 TCP
```

This function adds new rule to system firewall

## v-add-firewall-ipset

add firewall ipset

**Options**: `NAME` `[SOURCE]` `[IPVERSION]` `[AUTOUPDATE]` `[REFRESH]`

**Examples**:

```bash
v-add-firewall-ipset country-nl "https://raw.githubusercontent.com/ipverse/rir-ip/master/country/nl/ipv4-aggregated.txt"
```

This function adds new ipset to system firewall

## v-add-firewall-rule

add firewall rule

**Options**: `ACTION` `IP` `PORT` `[PROTOCOL]` `[COMMENT]` `[RULE]`

**Examples**:

```bash
v-add-firewall-rule DROP 185.137.111.77 25
```

This function adds new rule to system firewall

## v-add-fs-archive

archive directory

**Options**: `USER` `ARCHIVE` `SOURCE` `[SOURCE...]`

**Examples**:

```bash
v-add-fs-archive admin archive.tar readme.txt
```

This function creates tar archive

## v-add-fs-directory

add directory

**Options**: `USER` `DIRECTORY`

**Examples**:

```bash
v-add-fs-directory admin mybar
```

This function creates new directory on the file system

## v-add-fs-file

add file

**Options**: `USER` `FILE`

**Examples**:

```bash
v-add-fs-file admin readme.md
```

This function creates new files on file system

## v-add-letsencrypt-domain

check letsencrypt domain

**Options**: `USER` `DOMAIN` `[ALIASES]` `[MAIL]`

**Examples**:

```bash
v-add-letsencrypt-domain admin wonderland.com www.wonderland.com,demo.wonderland.com
example: v-add-letsencrypt-domain admin wonderland.com '' yes
```

This function check and validates domain with Let's Encrypt

## v-add-letsencrypt-host

add letsencrypt for host and backend

**Options**: –

This function check and validates the backend certificate and generate
a new let's encrypt certificate.

## v-add-letsencrypt-user

register letsencrypt user account

**Options**: `USER`

**Examples**:

```bash
v-add-letsencrypt-user bob
```

This function creates and register LetsEncrypt account

## v-add-mail-account

add mail domain account

**Options**: `USER` `DOMAIN` `ACCOUNT` `PASSWORD` `[QUOTA]`

**Examples**:

```bash
v-add-mail-account user example.com john P4$$vvOrD
```

This function add new email account.

## v-add-mail-account-alias

add mail account alias aka nickname

**Options**: `USER` `DOMAIN` `ACCOUNT` `ALIAS`

**Examples**:

```bash
v-add-mail-account-alias admin acme.com alice alicia
```

This function add new email alias.

## v-add-mail-account-autoreply

add mail account autoreply message

**Options**: `USER` `DOMAIN` `ACCOUNT` `MESSAGE`

**Examples**:

```bash
v-add-mail-account-autoreply admin example.com user Hello from e-mail!
```

This function add new email account.

## v-add-mail-account-forward

add mail account forward address

**Options**: `USER` `DOMAIN` `ACCOUNT` `FORWARD`

**Examples**:

```bash
v-add-mail-account-forward admin acme.com alice bob
```

This function add new email account.

## v-add-mail-account-fwd-only

add mail account forward-only flag

**Options**: `USER` `DOMAIN` `ACCOUNT`

**Examples**:

```bash
v-add-mail-account-fwd-only admin example.com user
```

This function adds fwd-only flag

## v-add-mail-domain

add mail domain

**Options**: `USER` `DOMAIN` `[ANTISPAM]` `[ANTIVIRUS]` `[DKIM]` `[DKIM_SIZE]` `[RESTART]` `[REJECT_SPAM]`

**Examples**:

```bash
v-add-mail-domain admin mydomain.tld
```

This function adds MAIL domain.

## v-add-mail-domain-antispam

add mail domain antispam support

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-add-mail-domain-antispam admin mydomain.tld
```

This function enables spamassasin for incoming emails.

## v-add-mail-domain-antivirus

add mail domain antivirus support

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-add-mail-domain-antivirus admin mydomain.tld
```

This function enables clamav scan for incoming emails.

## v-add-mail-domain-catchall

add mail domain catchall account

**Options**: `USER` `DOMAIN` `EMAIL`

**Examples**:

```bash
v-add-mail-domain-catchall admin example.com master@example.com
```

This function enables catchall account for incoming emails.

## v-add-mail-domain-dkim

add mail domain dkim support

**Options**: `USER` `DOMAIN` `[DKIM_SIZE]`

**Examples**:

```bash
v-add-mail-domain-dkim admin acme.com
```

This function adds DKIM signature to outgoing domain emails.

## v-add-mail-domain-reject

add mail domain reject spam support

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-add-mail-domain-reject admin mydomain.tld
```

The function enables spam rejection for incoming emails.

## v-add-mail-domain-smtp-relay

Add mail domain smtp relay support

**Options**: `USER` `DOMAIN` `HOST` `[USERNAME]` `[PASSWORD]` `[PORT]`

**Examples**:

```bash
v-add-mail-domain-smtp-relay user domain.tld srv.smtprelay.tld uname123 pass12345
```

This function adds mail domain smtp relay support.

## v-add-mail-domain-ssl

add mail SSL for $domain

**Options**: `USER` `DOMAIN` `SSL_DIR` `[RESTART]`

This function turns on SSL support for a mail domain. Parameter ssl_dir
is a path to a directory where 2 or 3 ssl files can be found. Certificate file
mail.domain.tld.crt and its key mail.domain.tld.key are mandatory. Certificate
authority mail.domain.tld.ca file is optional.

## v-add-mail-domain-webmail

add webmail support for a domain

**Options**: `USER` `DOMAIN` `[WEBMAIL]` `[RESTART]` `[QUIET]`

**Examples**:

```bash
v-add-sys-webmail user domain.com
example: v-add-sys-webmail user domain.com snappymail
example: v-add-sys-webmail user domain.com roundcube
```

This function enables webmail client for a mail domain.

## v-add-remote-dns-domain

add remote dns domain

**Options**: `USER` `DOMAIN` `[FLUSH]`

**Examples**:

```bash
v-add-remote-dns-domain admin mydomain.tld yes
```

This function synchronise dns domain with the remote server.

## v-add-remote-dns-host

add new remote dns host

**Options**: `HOST` `PORT` `USER` `PASSWORD` `[TYPE]` `[DNS_USER]`

**Examples**:

```bash
v-add-remote-dns-host slave.your_host.com 8083 admin your_passw0rd
v-add-remote-dns-host slave.your_host.com 8083 api_key ''
```

This function adds remote dns server to the dns cluster.
As alternative api_key generated on the slave server.
See v-generate-api-key can be used to connect the remote dns server

## v-add-remote-dns-record

add remote dns domain record

**Options**: `USER` `DOMAIN` `ID`

**Examples**:

```bash
v-add-remote-dns-record bob acme.com 23
```

This function synchronise dns domain with the remote server.

## v-add-sys-api-ip

add IP address to API allow list

**Options**: `IP`

**Examples**:

```bash
v-add-sys-api-ip 1.1.1.1
```

## v-add-sys-dependencies

**Options**:

Add php dependencies to Hestia
options: [MODE]

## v-add-sys-filemanager

add file manager functionality to Hestia Control Panel

**Options**: `[MODE]`

This function installs the File Manager on the server
for access through the Web interface.

## v-add-sys-firewall

add system firewall

**Options**: –

This function enables the system firewall.

## v-add-sys-ip

add system IP address

**Options**: `IP` `NETMASK` `[INTERFACE]` `[USER]` `[IP_STATUS]` `[IP_NAME]` `[NAT_IP]`

**Examples**:

```bash
v-add-sys-ip 203.0.113.1 255.255.255.0
```

This function adds IP address into a system. It also creates rc scripts. You
can specify IP name which will be used as root domain for temporary aliases.
For example, if you set a1.myhosting.com as name, each new domain created on
this IP will automatically receive alias $domain.a1.myhosting.com. Of course
you must have wildcard record \*.a1.myhosting.com pointed to IP. This feature
is very handy when customer wants to test domain before dns migration.

## v-add-sys-pma-sso

enables support for single sign on phpMyAdmin

**Options**: `[MODE]`

This function enables support for SSO to phpMyAdmin

## v-add-sys-quota

add system quota

**Options**: –

This function enables filesystem quota on /home partition
Some kernels do require additional packages to be installed first

## v-add-sys-roundcube

Install Roundcube webmail client

**Options**: `[MODE]`

This function installs the Roundcube webmail client.

## v-add-sys-sftp-jail

add system sftp jail

**Options**: `[RESTART]`

**Examples**:

```bash
v-add-sys-sftp-jail yes
```

This function enables sftp jailed environment.

## v-add-sys-smtp

Add SMTP Account for logging, notification and internal mail

**Options**: `DOMAIN` `PORT` `SMTP_SECURITY` `USERNAME` `PASSWORD` `EMAIL`

**Examples**:

```bash
v-add-sys-smtp example.com 587 STARTTLS test@domain.com securepassword test@example.com
```

This function allows configuring a SMTP account for the server to use
for logging, notification and warn emails etc.

## v-add-sys-smtp-relay

add system wide smtp relay support

**Options**: `HOST` `[USERNAME]` `[PASSWORD]` `[PORT]`

**Examples**:

```bash
v-add-sys-smtp-relay srv.smtprelay.tld uname123 pass12345
```

This function adds system wide smtp relay support.

## v-add-sys-snappymail

Install SnappyMail webmail client

**Options**: `[MODE]`

This function installs the SnappyMail webmail client.

## v-add-user

add system user

**Options**: `USER` `PASSWORD` `EMAIL` `[PACKAGE]` `[NAME]` `[LASTNAME]`

**Examples**:

```bash
v-add-user admin2 P4$$w@rD bgates@aol.com
```

This function creates new user account.

## v-add-user-2fa

add 2fa to existing user

**Options**: `USER`

**Examples**:

```bash
v-add-user-2fa admin
```

This function creates a new 2fa token for user.

## v-add-user-composer

add composer (php dependency manager) for a user

**Options**: `USER`

**Examples**:

```bash
v-add-user-composer user [version]
```

This function adds support for composer (php dependency manager)
Homepage: <https://getcomposer.org/>

## v-add-user-notification

add user notification

**Options**: `USER` `TOPIC` `NOTICE` `[TYPE]`

This function adds a new user notification to the panel.

## v-add-user-package

adding user package

**Options**: `TMPFILE` `PACKAGE` `[REWRITE]`

This function adds new user package to the system.

## v-add-user-sftp-jail

add user sftp jail

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-add-user-sftp-jail admin
```

This function enables sftp jailed environment

## v-add-user-sftp-key

add user sftp key

**Options**: `USER` `[TTL]`

This function creates and updates SSH keys for used with the File Manager.

## v-add-user-ssh-key

add ssh key

**Options**: `USER` `KEY`

**Examples**:

```bash
v-add-user-ssh-key user 'valid ssh key'
```

Function check if $user/.ssh/authorized_keys exists and create it.
After that it append the new key(s)

## v-add-user-wp-cli

add wp-cli for a user

**Options**: `USER`

**Examples**:

```bash
v-add-user-wp-cli user
```

This function adds support for wp-cli to the user account

## v-add-web-domain

add web domain

**Options**: `USER` `DOMAIN` `[IP]` `[RESTART]` `[ALIASES]` `[PROXY_EXTENSIONS]`

**Examples**:

```bash
v-add-web-domain admin wonderland.com 192.18.22.43 yes www.wonderland.com
```

This function adds virtual host to a server. In cases when ip is
undefined in the script, "default" template will be used. The alias of
`www.domain.tld` type will be automatically assigned to the domain unless
"none" is transmited as argument. If ip have associated dns name, this
domain will also get the alias domain-tpl.$ipname. An alias with the ip
name is useful during the site testing while dns isn't moved to server yet.

## v-add-web-domain-alias

add web domain alias

**Options**: `USER` `DOMAIN` `ALIASES` `[RESTART]`

**Examples**:

```bash
v-add-web-domain-alias admin acme.com www.acme.com yes
```

This function adds one or more aliases to a domain (it is also called
"domain parking"). This function supports wildcards \*.domain.tpl.

## v-add-web-domain-allow-users

Allow other users create subdomains

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-add-web-domain-allow-users admin admin.com
```

Bypass the rule check for Enforce subdomain ownership for a specific domain.
Enforce subdomain ownership setting in /edit/server/ set to no will always overwrite this behaviour
eg: admin adds admin.com
user can create user.admin.com

## v-add-web-domain-backend

add web domain backend

**Options**: `USER` `DOMAIN` `[TEMPLATE]` `[RESTART]`

**Examples**:

```bash
v-add-web-domain-backend admin example.com default yes
```

This function is used to add the web backend configuration.

## v-add-web-domain-ftp

add ftp account for web domain.

**Options**: `USER` `DOMAIN` `FTP_USER` `FTP_PASSWORD` `[FTP_PATH]`

**Examples**:

```bash
v-add-web-domain-ftp alice wonderland.com alice_ftp p4$$vvOrD
```

This function creates additional ftp account for web domain.

## v-add-web-domain-httpauth

add password protection for web domain

**Options**: `USER` `DOMAIN` `AUTH_USER` `AUTH_PASSWORD` `[RESTART]`

**Examples**:

```bash
v-add-web-domain-httpauth admin acme.com user02 super_pass
```

This function is used for securing web domain with http auth

## v-add-web-domain-proxy

add webdomain proxy support

**Options**: `USER` `DOMAIN` `[TEMPLATE]` `[EXTENTIONS]` `[RESTART]`

**Examples**:

```bash
v-add-web-domain-proxy admin example.com
```

This function enables proxy support for a domain. This can significantly
improve website speed.

## v-add-web-domain-redirect

Adding force redirect to domain

**Options**: `USER` `DOMAIN` `REDIRECT` `HTTPCODE` `[RESTART]`

**Examples**:

```bash
v-add-web-domain-redirect user domain.tld domain.tld
example: v-add-web-domain-redirect user domain.tld www.domain.tld
example: v-add-web-domain-redirect user domain.tld shop.domain.tld
example: v-add-web-domain-redirect user domain.tld different-domain.com
example: v-add-web-domain-redirect user domain.tld shop.different-domain.com
example: v-add-web-domain-redirect user domain.tld different-domain.com 302
```

Function creates a forced redirect to a domain

## v-add-web-domain-ssl

adding ssl for domain

**Options**: `USER` `DOMAIN` `SSL_DIR` `[SSL_HOME]` `[RESTART]`

**Examples**:

```bash
v-add-web-domain-ssl admin example.com /home/admin/conf/example.com/web
```

This function turns on SSL support for a domain. Parameter ssl_dir is a path
to directory where 2 or 3 ssl files can be found. Certificate file
domain.tld.crt and its key domain.tld.key are mandatory. Certificate
authority domain.tld.ca file is optional. If home directory parameter
(ssl_home) is not set, https domain uses public_shtml as separate
documentroot directory.

## v-add-web-domain-ssl-force

Adding force SSL for a domain

**Options**: `USER` `DOMAIN` `[RESTART]` `[QUIET]`

**Examples**:

```bash
v-add-web-domain-ssl-force admin acme.com
```

This function forces SSL for the requested domain.

## v-add-web-domain-ssl-hsts

Adding hsts to a domain

**Options**: `USER` `DOMAIN` `[RESTART]` `[QUIET]`

This function enables HSTS for the requested domain.

## v-add-web-domain-ssl-preset

Adding force SSL for a domain

**Options**: `USER` `DOMAIN` `[SSL]`

Up on creating an web domain set the SSL Force values due to the delay of LE due to DNS propergation over DNS cluster
When LE has been activated it will set the actions

## v-add-web-domain-stats

add log analyser to generate domain statistics

**Options**: `USER` `DOMAIN` `TYPE`

**Examples**:

```bash
v-add-web-domain-stats admin example.com awstats
```

This function is used for enabling log analyser system to a domain. For viewing
the domain statistics use <http://domain.tld/vstats/> link. Access this page
is not protected by default. If you want to secure it with passwords you
should use v-add-web-domain_stat_auth script.

## v-add-web-domain-stats-user

add password protection to web domain statistics

**Options**: `USER` `DOMAIN` `STATS_USER` `STATS_PASSWORD` `[RESTART]`

**Examples**:

```bash
v-add-web-domain-stats-user admin example.com watchdog your_password
```

This function is used for securing the web statistics page.

## v-add-web-php

add php fpm version

**Options**: `VERSION`

**Examples**:

```bash
v-add-web-php 8.0
```

Install php-fpm for provided version.

## v-backup-user

backup system user with all its objects

**Options**: `USER` `NOTIFY`

**Examples**:

```bash
v-backup-user admin yes
```

This function is used for backing up user with all its domains and databases.

## v-backup-users

backup all users

**Options**: –

This function backups all system users.

## v-change-cron-job

change cron job

**Options**: `USER` `JOB` `MIN` `HOUR` `DAY` `MONTH` `WDAY` `COMMAND`

**Examples**:

```bash
v-change-cron-job admin 7 * * * * * * /usr/bin/uptime
```

This function is used for changing existing job. It fully replace job
parameters with new one but with same id.

## v-change-database-host-password

change database server password

**Options**: `TYPE` `HOST` `USER` `PASSWORD`

**Examples**:

```bash
v-change-database-host-password mysql localhost wp_user pA$$w@rD
```

This function changes database server password.

## v-change-database-owner

change database owner

**Options**: `DATABASE` `USER`

**Examples**:

```bash
v-change-database-owner mydb alice
```

This function for changing database owner.

## v-change-database-password

change database password

**Options**: `USER` `DATABASE` `DBPASS`

**Examples**:

```bash
v-change-database-password admin wp_db neW_pAssWorD
```

This function for changing database user password to a database. It uses the
full name of database as argument.

## v-change-database-user

change database username

**Options**: `USER` `DATABASE` `DBUSER` `[DBPASS]`

**Examples**:

```bash
v-change-database-user admin my_db joe_user
```

This function for changing database user. It uses the

## v-change-dns-domain-dnssec

change dns domain dnssec status

**Options**: `USER` `DOMAIN` `STATUS`

**Examples**:

```bash
v-change-dns-domain-dnssec admin domain.pp.ua yes
```

## v-change-dns-domain-exp

change dns domain expiration date

**Options**: `USER` `DOMAIN` `EXP`

**Examples**:

```bash
v-change-dns-domain-exp admin domain.pp.ua 2020-11-20
```

This function of changing the term of expiration domain's registration. The
serial number will be refreshed automatically during update.

## v-change-dns-domain-ip

change dns domain ip address

**Options**: `USER` `DOMAIN` `IP` `[RESTART]`

**Examples**:

```bash
v-change-dns-domain-ip admin domain.com 123.212.111.222
```

This function for changing the main ip of DNS zone.

## v-change-dns-domain-soa

change dns domain soa record

**Options**: `USER` `DOMAIN` `SOA` `[RESTART]`

**Examples**:

```bash
v-change-dns-domain-soa admin acme.com d.ns.domain.tld
```

This function for changing SOA record. This type of records can not be
modified by v-change-dns-record call.

## v-change-dns-domain-tpl

change dns domain template

**Options**: `USER` `DOMAIN` `TEMPLATE` `[RESTART]`

**Examples**:

```bash
v-change-dns-domain-tpl admin example.com child-ns yes
```

This function for changing the template of records. By updating old records
will be removed and new records will be generated in accordance with
parameters of new template.

## v-change-dns-domain-ttl

change dns domain ttl

**Options**: `USER` `DOMAIN` `TTL` `[RESTART]`

**Examples**:

```bash
v-change-dns-domain-ttl alice example.com 14400
```

This function for changing the time to live TTL parameter for all records.

## v-change-dns-record

change dns domain record

**Options**: `USER` `DOMAIN` `ID` `RECORD` `TYPE` `VALUE` `[PRIORITY]` `[RESTART]` `[TTL]`

**Examples**:

```bash
v-change-dns-record admin domain.ua 42 192.18.22.43
```

This function for changing DNS record.

## v-change-dns-record-id

change dns domain record id

**Options**: `USER` `DOMAIN` `ID` `NEWID` `[RESTART]`

**Examples**:

```bash
v-change-dns-record-id admin acme.com 24 42 yes
```

This function for changing internal record id.

## v-change-domain-owner

change domain owner

**Options**: `DOMAIN` `USER`

**Examples**:

```bash
v-change-domain-owner www.example.com bob
```

This function of changing domain ownership.

## v-change-firewall-rule

change firewall rule

**Options**: `RULE` `ACTION` `IP` `PORT` `[PROTOCOL]` `[COMMENT]`

**Examples**:

```bash
v-change-firewall-rule 3 ACCEPT 5.188.123.17 443
```

This function is used for changing existing firewall rule.
It fully replace rule with new one but keeps same id.

## v-change-fs-file-permission

change file permission

**Options**: `USER` `FILE` `PERMISSIONS`

**Examples**:

```bash
v-change-fs-file-permission admin readme.txt 0777
```

This function changes file access permissions on the file system

## v-change-mail-account-password

change mail account password

**Options**: `USER` `DOMAIN` `ACCOUNT` `PASSWORD`

**Examples**:

```bash
v-change-mail-account-password admin mydomain.tld user p4$$vvOrD
```

This function changes email account password.

## v-change-mail-account-quota

change mail account quota

**Options**: `USER` `DOMAIN` `ACCOUNT` `QUOTA`

**Examples**:

```bash
v-change-mail-account-quota admin mydomain.tld user01 unlimited
```

This function changes email account disk quota.

## v-change-mail-account-rate-limit

change mail account rate limit

**Options**: `USER` `DOMAIN` `ACCOUNT` `RATE`

**Examples**:

```bash
v-change-mail-account-rate-limit admin mydomain.tld user01 100
```

This function changes email account rate limit. Use system to use domain or "server" setting

## v-change-mail-domain-catchall

change mail domain catchall email

**Options**: `USER` `DOMAIN` `EMAIL`

**Examples**:

```bash
v-change-mail-domain-catchall user01 mydomain.tld master@mydomain.tld
```

This function changes mail domain catchall.

## v-change-mail-domain-rate-limit

change mail domain rate limit

**Options**: `USER` `DOMAIN` `RATE`

**Examples**:

```bash
v-change-mail-domain-rate-limit admin mydomain.tld 100
```

This function changes email account rate limit for the domain. Account specific setting will overwrite domain setting!

## v-change-mail-domain-sslcert

change domain ssl certificate

**Options**: `USER` `DOMAIN` `SSL_DIR` `[RESTART]`

This function changes SSL domain certificate and the key. If ca file present
it will be replaced as well.

## v-change-remote-dns-domain-exp

change remote dns domain expiration date

**Options**: `USER` `DOMAIN`

This function synchronise dns domain with the remote server.

## v-change-remote-dns-domain-soa

change remote dns domain SOA

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-change-remote-dns-domain-soa admin example.org.uk
```

This function synchronise dns domain with the remote server.

## v-change-remote-dns-domain-ttl

change remote dns domain TTL

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-change-remote-dns-domain-ttl admin domain.tld
```

This function synchronise dns domain with the remote server.

## v-change-sys-api

Enable / Disable API access

**Options**: `STATUS`

**Examples**:

```bash
v-change-sys-api enable legacy
# Enable legacy api currently default on most of api based systems
example: v-change-sys-api enable api
# Enable api
v-change-sys-api disable
# Disable API
```

Enabled / Disable API

## v-change-sys-config-value

change sysconfig value

**Options**: `KEY` `VALUE`

**Examples**:

```bash
v-change-sys-config-value VERSION 1.0
```

This function is for changing main config settings such as COMPANY_NAME or
COMPANY_EMAIL and so on.

## v-change-sys-db-alias

change phpmyadmin/phppgadmin alias url

**Options**: `TYPE` `ALIAS`

**Examples**:

```bash
v-change-sys-db-alias pma phpmyadmin
# Sets phpMyAdmin alias to phpmyadmin
v-change-sys-db-alias pga phppgadmin
# Sets phpPgAdmin alias to phppgadmin
```

This function changes the database editor url in
apache2 or nginx configuration.

## v-change-sys-demo-mode

enable or disable demo mode

**Options**: `ACTIVE`

This function will set the demo mode variable,
which will prevent usage of certain v-scripts in the backend
and prevent modification of objects in the control panel.
It will also disable virtual hosts for Apache and NGINX
for domains which have been created.

## v-change-sys-hestia-ssl

change hestia ssl certificate

**Options**: `SSL_DIR` `[RESTART]`

**Examples**:

```bash
v-change-sys-hestia-ssl /home/new/dir/path yes
```

This function changes hestia SSL certificate and the key.

## v-change-sys-hostname

change hostname

**Options**: `HOSTNAME`

**Examples**:

```bash
v-change-sys-hostname mydomain.tld
```

This function for changing system hostname.

## v-change-sys-ip-name

change IP name

**Options**: `IP` `NAME`

**Examples**:

```bash
v-change-sys-ip-name 203.0.113.1 acme.com
```

This function for changing dns domain associated with IP.

## v-change-sys-ip-nat

change NAT IP address

**Options**: `IP` `NAT_IP` `[RESTART]`

**Examples**:

```bash
v-change-sys-ip-nat 10.0.0.1 203.0.113.1
```

This function for changing NAT IP associated with IP.

## v-change-sys-ip-owner

change IP owner

**Options**: `IP` `USER`

**Examples**:

```bash
v-change-sys-ip-owner 203.0.113.1 admin
```

This function of changing IP address ownership.

## v-change-sys-ip-status

change IP status

**Options**: `IP` `IP_STATUS`

**Examples**:

```bash
v-change-sys-ip-status 203.0.113.1 yourstatus
```

This function of changing an IP address's status.

## v-change-sys-language

change sys language

**Options**: `LANGUAGE` `[UPDATE_USERS]`

**Examples**:

```bash
v-change-sys-language ru
```

This function for changing system language.

## v-change-sys-php

Change default php version server wide

**Options**: `VERSION`

**Examples**:

```bash
v-change-sys-php 8.0
```

## v-change-sys-port

change system backend port

**Options**: `PORT`

**Examples**:

```bash
v-change-sys-port 5678
```

This function for changing the system backend port in NGINX configuration.

## v-change-sys-release

update web templates

**Options**: `[RESTART]`

This function for changing the release branch for the
Hestia Control Panel. This allows the user to switch between
stable and pre-release builds which will automaticlly update
based on the appropriate release schedule if auto-update is
turned on.

## v-change-sys-service-config

change service config

**Options**: `CONFIG` `SERVICE` `[RESTART]`

**Examples**:

```bash
v-change-sys-service-config /home/admin/dovecot.conf dovecot yes
```

This function for changing service confguration.

## v-change-sys-timezone

change system timezone

**Options**: `TIMEZONE`

**Examples**:

```bash
v-change-sys-timezone Europe/Berlin
```

This function for changing system timezone.

## v-change-sys-webmail

change webmail alias url

**Options**: `WEBMAIL`

**Examples**:

```bash
v-change-sys-webmail YourtrickyURLhere
```

This function changes the webmail url in apache2 or nginx configuration.

## v-change-user-config-value

changes user configuration value

**Options**: `USER` `KEY` `VALUE`

**Examples**:

```bash
v-change-user-config-value admin ROLE admin
```

Changes key/value for specified user.

## v-change-user-contact

change user contact email

**Options**: `USER` `EMAIL`

**Examples**:

```bash
v-change-user-contact admin admin@yahoo.com
```

This function for changing of e-mail associated with a certain user.

## v-change-user-language

change user language

**Options**: `USER` `LANGUAGE`

**Examples**:

```bash
v-change-user-language admin en
```

This function for changing language.

## v-change-user-name

change user full name

**Options**: `USER` `NAME` `[LAST_NAME]`

**Examples**:

```bash
v-change-user-name admin John Smith
```

This function allow to change user's full name.

## v-change-user-ns

change user name servers

**Options**: `USER` `NS1` `NS2` `[NS3]` `[NS4]` `[NS5]` `[NS6]` `[NS7]` `[NS8]`

**Examples**:

```bash
v-change-user-ns ns1.domain.tld ns2.domain.tld
```

This function for changing default name servers for specific user.

## v-change-user-package

change user package

**Options**: `USER` `PACKAGE` `[FORCE]`

**Examples**:

```bash
v-change-user-package admin yourpackage
```

This function changes user's hosting package.

## v-change-user-password

change user password

**Options**: `USER` `PASSWORD`

**Examples**:

```bash
v-change-user-password admin NewPassword123
```

This function changes user's password and updates RKEY value.

## v-change-user-php-cli

add php version alias to .bash_aliases

**Options**: `USER` `VERSION`

**Examples**:

```bash
v-change-user-php-cli user 7.4
```

add line to .bash_aliases to set default php command line
version when multi-php is enabled.

## v-change-user-rkey

change user random key

**Options**: `USER` `[HASH]`

This function changes user's RKEY value thats has been used for security value to be used forgot password function only.

## v-change-user-role

updates user role

**Options**: `USER` `ROLE`

**Examples**:

```bash
v-change-user-role user administrator
```

Give/revoke user administrator rights to manage all accounts as admin

## v-change-user-shell

change user shell

**Options**: `USER` `SHELL`

**Examples**:

```bash
v-change-user-shell admin nologin
```

This function changes system shell of a user. Shell gives ability to use ssh.

## v-change-user-sort-order

updates user role

**Options**: `USER` `SORT_ORDER`

**Examples**:

```bash
v-change-user-sort-order user date
```

Changes web UI display sort order for specified user.

## v-change-user-template

change user default template

**Options**: `USER` `TYPE` `TEMPLATE`

**Examples**:

```bash
v-change-user-template admin WEB wordpress
```

This function changes default user web template.

## v-change-user-theme

updates user theme

**Options**: `USER` `THEME`

**Examples**:

```bash
v-change-user-theme admin dark
example: v-change-user-theme peter vestia
```

Changes web UI display theme for specified user.

## v-change-web-domain-backend-tpl

change web domain backend template

**Options**: `USER` `DOMAIN` `TEMPLATE` `[RESTART]`

**Examples**:

```bash
v-change-web-domain-backend-tpl admin acme.com PHP-7_4
```

This function changes backend template

## v-change-web-domain-dirlist

enable/disable directory listing

**Options**: `USER` `DOMAIN` `MODE`

**Examples**:

```bash
v-change-web-domain-dirlist user demo.com on
```

This function is used for changing the directory list mode.

## v-change-web-domain-docroot

Changes the document root for an existing web domain

**Options**: `USER` `DOMAIN` `TARGET_DOMAIN` `[DIRECTORY]` `[PHP]`

**Examples**:

```bash
v-change-web-domain-docroot admin domain.tld otherdomain.tld
# add custom docroot
# points domain.tld to otherdomain.tld's document root.
v-change-web-domain-docroot admin test.local default
# remove custom docroot
# returns document root to default value for domain.
```

This call changes the document root of a chosen web domain
to another available domain under the user context.

## v-change-web-domain-ftp-password

change ftp user password.

**Options**: `USER` `DOMAIN` `FTP_USER` `FTP_PASSWORD`

**Examples**:

```bash
v-change-web-domain-ftp-password admin example.com ftp_usr ftp_qwerty
```

This function changes ftp user password.

## v-change-web-domain-ftp-path

change path for ftp user.

**Options**: `USER` `DOMAIN` `FTP_USER` `FTP_PATH`

**Examples**:

```bash
v-change-web-domain-ftp-path admin example.com /home/admin/example.com
```

This function changes ftp user path.

## v-change-web-domain-httpauth

change password for http auth user

**Options**: `USER` `DOMAIN` `AUTH_USER` `AUTH_PASSWORD` `[RESTART]`

**Examples**:

```bash
v-change-web-domain-httpauth admin acme.com alice white_rA$$bIt
```

This function is used for changing http auth user password

## v-change-web-domain-ip

change web domain ip

**Options**: `USER` `DOMAIN` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-change-web-domain-ip admin example.com 167.86.105.230 yes
```

This function is used for changing domain ip

## v-change-web-domain-name

change web domain name

**Options**: `USER` `DOMAIN` `NEW_DOMAIN` `[RESTART]`

**Examples**:

```bash
v-change-web-domain-name alice wonderland.com lookinglass.com yes
```

This function is used for changing the domain name.

## v-change-web-domain-proxy-tpl

change web domain proxy template

**Options**: `USER` `DOMAIN` `TEMPLATE` `[EXTENTIONS]` `[RESTART]`

**Examples**:

```bash
v-change-web-domain-proxy-tpl admin domain.tld hosting
```

This function changes proxy template

## v-change-web-domain-sslcert

change domain ssl certificate

**Options**: `USER` `DOMAIN` `SSL_DIR` `[RESTART]`

**Examples**:

```bash
v-change-web-domain-sslcert admin example.com /home/admin/tmp
```

This function changes SSL domain certificate and the key. If ca file present
it will be replaced as well.

## v-change-web-domain-sslhome

changing domain ssl home

**Options**: `USER` `DOMAIN` `SSL_HOME` `[RESTART]`

**Examples**:

```bash
v-change-web-domain-sslhome admin acme.com single
example: v-change-web-domain-sslhome admin acme.com same
```

This function changes SSL home directory. Single will separate the both public_html / public_shtml. Same will always point to public_shtml

## v-change-web-domain-stats

change web domain statistics

**Options**: `USER` `DOMAIN` `TYPE`

**Examples**:

```bash
v-change-web-domain-stats admin example.com awstats
```

This function of deleting site's system of statistics. Its type is
automatically chooses from client's configuration file.

## v-change-web-domain-tpl

change web domain template

**Options**: `USER` `DOMAIN` `TEMPLATE` `[RESTART]`

**Examples**:

```bash
v-change-web-domain-tpl admin acme.com opencart
```

This function changes template of the web configuration file. The content
of webdomain directories remains untouched.

## v-check-access-key

check access key

**Options**: `ACCESS_KEY_ID` `SECRET_ACCESS_KEY` `COMMAND` `[IP]` `[FORMAT]`

**Examples**:

```bash
v-check-access-key key_id secret v-purge-nginx-cache 127.0.0.1 json
```

- Checks if the key exists;
- Checks if the secret belongs to the key;
- Checks if the key user is suspended;
- Checks if the key has permission to run the command.

## v-check-api-key

check api key

**Options**: `KEY` `[IP]`

**Examples**:

```bash
v-check-api-key random_key 127.0.0.1
```

This function checks a key file in $HESTIA/data/keys/

## v-check-fs-permission

open file

**Options**: `USER` `FILE`

**Examples**:

```bash
v-check-fs-permission admin readme.txt
```

This function opens/reads files on the file system

## v-check-mail-account-hash

check user password

**Options**: `TYPE` `PASSWORD` `HASH`

**Examples**:

```bash
v-check-mail-account-hash ARGONID2 PASS HASH
```

This function verifies email account password hash

## v-check-user-2fa

check user token

**Options**: `USER` `TOKEN`

**Examples**:

```bash
v-check-user-2fa admin 493690
```

This function verifies user 2fa token.

## v-check-user-hash

check user hash

**Options**: `USER` `HASH` `[IP]`

**Examples**:

```bash
v-check-user-hash admin CN5JY6SMEyNGnyCuvmK5z4r7gtHAC4mRZ...
```

This function verifies user hash

## v-check-user-password

check user password

**Options**: `USER` `PASSWORD` `[IP]` `[RETURN_HASH]`

**Examples**:

```bash
v-check-user-password admin qwerty1234
```

This function verifies user password from file

## v-copy-fs-directory

copy directory

**Options**: `USER` `SRC_DIRECTORY` `DST_DIRECTORY`

**Examples**:

```bash
v-copy-fs-directory alice /home/alice/dir1 /home/bob/dir2
```

This function copies directory on the file system

## v-copy-fs-file

copy file

**Options**: `USER` `SRC_FILE` `DST_FILE`

**Examples**:

```bash
v-copy-fs-file admin readme.txt readme_new.txt
```

This function copies file on the file system

## v-copy-user-package

duplicate existing package

**Options**: `PACKAGE` `NEW_PACKAGE`

**Examples**:

```bash
v-copy-user-package default new
```

This function allows the user to duplicate an existing
package file to facilitate easier configuration.

## v-delete-access-key

delete access key

**Options**: `ACCESS_KEY_ID`

**Examples**:

```bash
v-delete-access-key mykey
```

This function removes a key from in $HESTIA/data/access-keys/

## v-delete-backup-host

delete backup ftp server

**Options**: `TYPE` `[HOST]`

**Examples**:

```bash
v-delete-backup-host sftp
```

This function deletes ftp backup host

## v-delete-cron-hestia-autoupdate

delete hestia autoupdate cron job

**Options**: –

This function deletes hestia autoupdate cron job.

## v-delete-cron-job

delete cron job

**Options**: `USER` `JOB`

**Examples**:

```bash
v-delete-cron-job admin 9
```

This function deletes cron job.

## v-delete-cron-reports

delete cron reports

**Options**: `USER`

**Examples**:

```bash
v-delete-cron-reports admin
```

This function for disabling reports on cron tasks and administrative
notifications.

## v-delete-cron-restart-job

delete restart job

**Options**: –

This function for disabling restart cron tasks

## v-delete-database

delete database

**Options**: `USER` `DATABASE`

**Examples**:

```bash
v-delete-database admin wp_db
```

This function for deleting the database. If database user have access to
another database, he will not be deleted.

## v-delete-database-host

delete database server

**Options**: `TYPE` `HOST`

**Examples**:

```bash
v-delete-database-host pgsql localhost
```

This function for deleting the database host from hestia configuration. It will
be deleted if there are no databases created on it only.

## v-delete-database-temp-user

deletes temp database user

**Options**: `USER` `DBUSER` `[TYPE]` `[HOST]`

**Examples**:

```bash
v-add-database-temp-user wordress hestia_sso_user mysql
```

Revokes "temp user" access to a database and removes the user
To be used in combination with v-add-database-temp-user

## v-delete-databases

delete user databases

**Options**: `USER`

**Examples**:

```bash
v-delete-databases admin
```

This function deletes all user databases.

## v-delete-dns-domain

delete dns domain

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-dns-domain alice acme.com
```

This function for deleting DNS domain. By deleting it all records will also be
deleted.

## v-delete-dns-domains

delete dns domains

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-delete-dns-domains bob
```

This function for deleting all users DNS domains.

## v-delete-dns-domains-src

delete dns domains based on SRC field

**Options**: `USER` `SRC` `[RESTART]`

**Examples**:

```bash
v-delete-dns-domains-src admin '' yes
```

This function for deleting DNS domains related to a certain host.

## v-delete-dns-on-web-alias

delete dns domain or dns record based on web domain alias

**Options**: `USER` `DOMAIN` `ALIAS` `[RESTART]`

**Examples**:

```bash
v-delete-dns-on-web-alias admin example.com www.example.com
```

This function deletes dns domain or dns record based on web domain alias.

## v-delete-dns-record

delete dns record

**Options**: `USER` `DOMAIN` `ID` `[RESTART]`

**Examples**:

```bash
v-delete-dns-record bob acme.com 42 yes
```

This function for deleting a certain record of DNS zone.

## v-delete-domain

delete web/dns/mail domain

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-domain admin domain.tld
```

This function deletes web/dns/mail domain.

## v-delete-fastcgi-cache

Disable FastCGI cache for nginx

**Options**: `USER` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-delete-fastcgi-cache user domain.tld
```

This function disables FastCGI cache for nginx

## v-delete-firewall-ban

delete firewall blocking rule

**Options**: `IP` `CHAIN`

**Examples**:

```bash
v-delete-firewall-ban 198.11.130.250 MAIL
```

This function deletes blocking rule from system firewall

## v-delete-firewall-chain

delete firewall chain

**Options**: `CHAIN`

**Examples**:

```bash
v-delete-firewall-chain WEB
```

This function adds new rule to system firewall

## v-delete-firewall-ipset

delete firewall ipset

**Options**: `NAME`

**Examples**:

```bash
v-delete-firewall-ipset country-nl
```

This function removes ipset from system and from hestia

## v-delete-firewall-rule

delete firewall rule

**Options**: `RULE`

**Examples**:

```bash
v-delete-firewall-rule SSH_BLOCK
```

This function deletes firewall rule.

## v-delete-fs-directory

delete directory

**Options**: `USER` `DIRECTORY`

**Examples**:

```bash
v-delete-fs-directory admin report1
```

This function deletes directory on the file system

## v-delete-fs-file

delete file

**Options**: `USER` `FILE`

**Examples**:

```bash
v-delete-fs-file admin readme.txt
```

This function deletes file on the file system

## v-delete-letsencrypt-domain

deleting letsencrypt ssl cetificate for domain

**Options**: `USER` `DOMAIN` `[RESTART]` `[MAIL]`

**Examples**:

```bash
v-delete-letsencrypt-domain admin acme.com yes
```

This function turns off letsencrypt SSL support for a domain.

## v-delete-mail-account

delete mail account

**Options**: `USER` `DOMAIN` `ACCOUNT`

**Examples**:

```bash
v-delete-mail-account admin acme.com alice
```

This function deletes email account.

## v-delete-mail-account-alias

delete mail account alias aka nickname

**Options**: `USER` `DOMAIN` `ACCOUNT` `ALIAS`

**Examples**:

```bash
v-delete-mail-account-alias admin example.com alice alicia
```

This function deletes email account alias.

## v-delete-mail-account-autoreply

delete mail account autoreply message

**Options**: `USER` `DOMAIN` `ACCOUNT` `ALIAS`

**Examples**:

```bash
v-delete-mail-account-autoreply admin mydomain.tld bob
```

This function deletes an email accounts autoreply.

## v-delete-mail-account-forward

delete mail account forward

**Options**: `USER` `DOMAIN` `ACCOUNT` `EMAIL`

**Examples**:

```bash
v-delete-mail-account-forward admin acme.com tony bob@acme.com
```

This function deletes an email accounts forwarding address.

## v-delete-mail-account-fwd-only

delete mail account forward-only flag

**Options**: `USER` `DOMAIN` `ACCOUNT`

**Examples**:

```bash
v-delete-mail-account-fwd-only admin example.com jack
```

This function deletes fwd-only flag

## v-delete-mail-domain

delete mail domain

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-mail-domain admin mydomain.tld
```

This function for deleting MAIL domain. By deleting it all accounts will
also be deleted.

## v-delete-mail-domain-antispam

delete mail domain antispam support

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-mail-domain-antispam admin mydomain.tld
```

This function disable spamassasin for incoming emails.

## v-delete-mail-domain-antivirus

delete mail domain antivirus support

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-mail-domain-antivirus admin mydomain.tld
```

This function disables clamav scan for incoming emails.

## v-delete-mail-domain-catchall

delete mail domain catchall email

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-mail-domain-catchall admin mydomain.tld
```

This function disables mail domain cathcall.

## v-delete-mail-domain-dkim

delete mail domain dkim support

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-mail-domain-dkim admin mydomain.tld
```

This function delete DKIM domain pem.

## v-delete-mail-domain-reject

delete mail domain reject spam support

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-mail-domain-reject admin mydomain.tld
```

The function disables spam rejection for incoming emails.

## v-delete-mail-domain-smtp-relay

Remove mail domain smtp relay support

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-mail-domain-smtp-relay user domain.tld
```

This function removes mail domain smtp relay support.

## v-delete-mail-domain-ssl

delete mail domain ssl support

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-mail-domain-ssl user demo.com
```

This function delete ssl certificates.

## v-delete-mail-domain-webmail

delete webmail support for a domain

**Options**: `USER` `DOMAIN` `[RESTART]` `[QUIET]`

**Examples**:

```bash
v-delete-mail-domain-webmail user demo.com
```

This function removes support for webmail from
a specified mail domain.

## v-delete-mail-domains

delete mail domains

**Options**: `USER`

**Examples**:

```bash
v-delete-mail-domains admin
```

This function for deleting all users mail domains.

## v-delete-remote-dns-domain

delete remote dns domain

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-remote-dns-domain admin example.tld
```

This function synchronise dns with the remote server.

## v-delete-remote-dns-domains

delete remote dns domains

**Options**: `[HOST]`

This function deletes remote dns domains.

## v-delete-remote-dns-host

delete remote dns host

**Options**: `HOST`

**Examples**:

```bash
v-delete-remote-dns-host example.org
```

This function for deleting the remote dns host from hestia configuration.

## v-delete-remote-dns-record

delete remote dns domain record

**Options**: `USER` `DOMAIN` `ID`

**Examples**:

```bash
v-delete-remote-dns-record user07 acme.com 44
```

This function synchronise dns with the remote server.

## v-delete-sys-api-ip

delete ip adresss from allowed ip list api

**Options**: `IP`

**Examples**:

```bash
v-delete-sys-api-ip 1.1.1.1
```

## v-delete-sys-filemanager

remove file manager functionality from Hestia Control Panel

**Options**: `[MODE]`

This function removes the File Manager and its entry points

## v-delete-sys-firewall

delete system firewall

**Options**: –

This function disables firewall support

## v-delete-sys-ip

delete system IP

**Options**: `IP`

**Examples**:

```bash
v-delete-sys-ip 203.0.113.1
```

This function for deleting a system IP. It does not allow to delete first IP
on interface and do not allow to delete IP which is used by a web domain.

## v-delete-sys-mail-queue

delete exim mail queue

**Options**: –

This function checks for messages stuck in the exim mail queue
and prompts the user to clear the queue if desired.

## v-delete-sys-pma-sso

disables support for single sign on PHPMYADMIN

**Options**: `[MODE]`

Disables support for SSO to phpMyAdmin

## v-delete-sys-quota

delete system quota

**Options**: –

This function disables filesystem quota on /home partition

## v-delete-sys-sftp-jail

delete system sftp jail

**Options**: –

This function disables sftp jailed environment

## v-delete-sys-smtp

Remove SMTP Account for logging, notification and internal mail

**Options**: –

This function allows configuring a SMTP account for the server to use
for logging, notification and warn emails etc.

## v-delete-sys-smtp-relay

disable system wide smtp relay support

**Options**:

options:

## v-delete-user

delete user

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-delete-user whistler
```

This function deletes a certain user and all his resources such as domains,
databases, cron jobs, etc.

## v-delete-user-2fa

delete 2fa of existing user

**Options**: `USER`

**Examples**:

```bash
v-delete-user-2fa admin
```

This function deletes 2fa token of a user.

## v-delete-user-auth-log

Delete auth log file for user

**Options**:

This function for deleting a users auth log file

## v-delete-user-backup

delete user backup

**Options**: `USER` `BACKUP`

**Examples**:

```bash
v-delete-user-backup admin admin.2012-12-21_00-10-00.tar
```

This function deletes user backup.

## v-delete-user-backup-exclusions

delete backup exclusion

**Options**: `USER` `[SYSTEM]`

**Examples**:

```bash
v-delete-user-backup-exclusions admin
```

This function for deleting backup exclusion

## v-delete-user-ips

delete user ips

**Options**: `USER`

**Examples**:

```bash
v-delete-user-ips admin
```

This function deletes all user's ip addresses.

## v-delete-user-log

Delete log file for user

**Options**: `USER`

**Examples**:

```bash
v-delete-user-log user
```

This function for deleting a users log file

## v-delete-user-notification

delete user notification

**Options**: `USER` `NOTIFICATION`

**Examples**:

```bash
v-delete-user-notification admin 1
```

This function deletes user notification.

## v-delete-user-package

delete user package

**Options**: `PACKAGE`

**Examples**:

```bash
v-delete-user-package admin palegreen
```

This function for deleting user package.

## v-delete-user-sftp-jail

delete user sftp jail

**Options**: `USER`

**Examples**:

```bash
v-delete-user-sftp-jail whistler
```

This function disables sftp jailed environment for USER

## v-delete-user-ssh-key

add ssh key

**Options**: `USER` `KEY`

**Examples**:

```bash
v-delete-user-ssh-key user unique_id
```

Delete user ssh key from authorized_keys

## v-delete-user-stats

delete user usage statistics

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-user-stats user
example: v-delete-user-stats admin overall
```

This function deletes user statistics data.

## v-delete-web-domain

delete web domain

**Options**: `USER` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-delete-web-domain admin wonderland.com
```

The call of function leads to the removal of domain and all its components
(statistics, folders contents, ssl certificates, etc.). This operation is
not fully supported by "undo" function, so the data recovery is possible
only with a help of reserve copy.

## v-delete-web-domain-alias

delete web domain alias

**Options**: `USER` `DOMAIN` `ALIAS` `[RESTART]`

**Examples**:

```bash
v-delete-web-domain-alias admin example.com www.example.com
```

This function of deleting the alias domain (parked domain). By this call
default www aliase can be removed as well.

## v-delete-web-domain-allow-users

disables other users create subdomains

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-web-domain-allow-users admin admin.com
```

Enable the rule check for Enforce subdomain ownership for a specific domain.
Enforce subdomain ownership setting in /edit/server/ set to no will always overwrite this behaviour
eg: admin adds admin.com
user can create user.admin.com

## v-delete-web-domain-backend

deleting web domain backend configuration

**Options**: `USER` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-delete-web-domain-backend admin acme.com
```

This function of deleting the virtualhost backend configuration.

## v-delete-web-domain-ftp

delete webdomain ftp account

**Options**: `USER` `DOMAIN` `FTP_USER`

**Examples**:

```bash
v-delete-web-domain-ftp admin wonderland.com bob_ftp
```

This function deletes additional ftp account.

## v-delete-web-domain-httpauth

delete http auth user

**Options**: `USER` `DOMAIN` `AUTH_USER` `[RESTART]`

**Examples**:

```bash
v-delete-web-domain-httpauth admin example.com alice
```

This function is used for deleting http auth user

## v-delete-web-domain-proxy

deleting web domain proxy configuration

**Options**: `USER` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-delete-web-domain-proxy alice lookinglass.com
```

This function of deleting the virtualhost proxy configuration.

## v-delete-web-domain-redirect

Delete force redirect to domain

**Options**: `USER` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-add-web-domain-redirect user domain.tld
```

Function delete a forced redirect to a domain

## v-delete-web-domain-ssl

delete web domain SSL support

**Options**: `USER` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-delete-web-domain-ssl admin acme.com
```

This function disable https support and deletes SSL certificates.

## v-delete-web-domain-ssl-force

remove ssl force from domain

**Options**: `USER` `DOMAIN` `[RESTART]` `[QUIET]`

**Examples**:

```bash
v-delete-web-domain-ssl-force admin domain.tld
```

This function removes force SSL configurations.

## v-delete-web-domain-ssl-hsts

remove ssl force from domain

**Options**: `USER` `DOMAIN` `[RESTART]` `[QUIET]`

**Examples**:

```bash
v-delete-web-domain-ssl-hsts user domain.tld
```

This function removes force SSL configurations.

## v-delete-web-domain-stats

delete web domain statistics

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-delete-web-domain-stats user02 h1.example.com
```

This function of deleting site's system of statistics. Its type is
automatically chooses from client's configuration file.

## v-delete-web-domain-stats-user

disable web domain stats authentication support

**Options**: `USER` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-delete-web-domain-stats-user admin acme.com
```

This function removes authentication of statistics system. If the script is
called without naming a certain user, all users will be removed. After
deleting all of them statistics will be accessible for view without an
authentication.

## v-delete-web-domains

delete web domains

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-delete-web-domains admin
```

This function deletes all user's webdomains.

## v-delete-web-php

delete php fpm version

**Options**: `VERSION`

**Examples**:

```bash
v-delete-web-php 7.3
```

This function checks and delete a fpm php version if not used by any domain.

## v-download-backup

Download backup

**Options**: `USER` `BACKUP`

**Examples**:

```bash
v-download-backup admin admin.2020-11-05_05-10-21.tar
```

This function download back-up from remote server

## v-dump-database

Dumps database contents in STDIN / file

**Options**: `USER` `DATABASE` `[FILE]`

**Examples**:

```bash
v-dump-database user user_databse > test.sql
example: v-dump-database user user_databse file
```

Dumps database in STDIN or /backup/user.database.type.sql

## v-export-rrd

export rrd charts as json

**Options**: `[CHART]` `[TIMESPAN]`

**Examples**:

```bash
v-export-rrd chart format
```

## v-extract-fs-archive

archive to directory

**Options**: `USER` `ARCHIVE` `DIRECTORY` `[SELECTED_DIR]` `[STRIP]` `[TEST]`

**Examples**:

```bash
v-extract-fs-archive admin latest.tar.gz /home/admin
```

This function extracts archive into directory on the file system

## v-generate-api-key

generate api key

**Options**: –

This function creates a key file in $HESTIA/data/keys/

## v-generate-debug-report

**Options**:

Includes
shellcheck source=/etc/hestiacp/hestia.conf

## v-generate-password-hash

generate password hash

**Options**: `HASH_METHOD` `SALT` `PASSWORD`

**Examples**:

```php
		v-generate-password-hash sha-512 rAnDom_string yourPassWord
```

This function generates password hash

## v-generate-ssl-cert

generate self signed certificate and CSR request

**Options**: `DOMAIN` `EMAIL` `COUNTRY` `STATE` `CITY` `ORG` `UNIT` `[ALIASES]` `[FORMAT]`

**Examples**:

```bash
v-generate-ssl-cert example.com mail@yahoo.com USA California Monterey ACME.COM IT
```

This function generates self signed SSL certificate and CSR request

## v-get-dns-domain-value

get dns domain value

**Options**: `USER` `DOMAIN` `KEY`

**Examples**:

```bash
v-get-dns-domain-value admin example.com SOA
```

This function for getting a certain DNS domain parameter.

## v-get-fs-file-type

get file type

**Options**: `USER` `FILE`

**Examples**:

```bash
v-get-fs-file-type admin index.html
```

This function shows file type

## v-get-mail-account-value

get mail account value

**Options**: `USER` `DOMAIN` `ACCOUNT` `KEY`

**Examples**:

```bash
v-get-mail-account-value admin example.tld tester QUOTA
```

This function for getting a certain mail account parameter.

## v-get-mail-domain-value

get mail domain value

**Options**: `USER` `DOMAIN` `KEY`

**Examples**:

```bash
v-get-mail-domain-value admin example.com DKIM
```

This function for getting a certain mail domain parameter.

## v-get-sys-timezone

get system timezone

**Options**: `[FORMAT]`

This function to get system timezone

## v-get-sys-timezones

list system timezone

**Options**: `[FORMAT]`

**Examples**:

```bash
v-get-sys-timezones json
```

This function checks system timezone settings

## v-get-user-salt

get user salt

**Options**: `USER` `[IP]` `[FORMAT]`

**Examples**:

```bash
v-get-user-salt admin
```

This function provides users salt

## v-get-user-value

get user value

**Options**: `USER` `KEY`

**Examples**:

```bash
v-get-user-value admin FNAME
```

This function for obtaining certain user's parameters.

## v-import-cpanel

Import Cpanel backup to a new user

**Options**: `BACKUP` `[MX]`

**Examples**:

```bash
v-import-cpanel /backup/backup.tar.gz yes
```

Based on sk-import-cpanel-backup-to-vestacp
Credits: Maks Usmanov (skamasle) and contributors:
Thanks to <https://github.com/Skamasle/sk-import-cpanel-backup-to-vestacp/graphs/contributors>

## v-insert-dns-domain

insert dns domain

**Options**: `USER` `DATA` `[SRC]` `[FLUSH]` `#`

This function inserts raw record to the dns.conf

## v-insert-dns-record

insert dns record

**Options**: `USER` `DOMAIN` `DATA`

This function inserts raw dns record to the domain conf

## v-insert-dns-records

inserts dns records

**Options**: `USER` `DOMAIN` `DATA_FILE`

This function copy dns record to the domain conf

## v-list-access-key

list all API access keys

**Options**: `ACCESS_KEY_ID` `[FORMAT]`

**Examples**:

```bash
v-list-access-key 1234567890ABCDefghij json
```

## v-list-access-keys

list all API access keys

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-access-keys json
```

## v-list-api

list api

**Options**: `API` `[FORMAT]`

**Examples**:

```bash
v-list-api mail-accounts json
```

## v-list-apis

list available APIs

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-apis json
```

## v-list-backup-host

list backup host

**Options**: `TYPE` `[FORMAT]`

**Examples**:

```bash
v-list-backup-host local
```

This function for obtaining the list of backup host parameters.

## v-list-cron-job

list cron job

**Options**: `USER` `JOB` `[FORMAT]`

**Examples**:

```bash
v-list-cron-job admin 7
```

This function of obtaining cron job parameters.

## v-list-cron-jobs

list user cron jobs

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-cron-jobs admin
```

This function for obtaining the list of all users cron jobs.

## v-list-database

list database

**Options**: `USER` `DATABASE` `[FORMAT]`

**Examples**:

```bash
v-list-database wp_db
```

This function for obtaining of all database's parameters.

## v-list-database-host

list database host

**Options**: `TYPE` `HOST` `[FORMAT]`

**Examples**:

```bash
v-list-database-host mysql localhost
```

This function for obtaining database host parameters.

## v-list-database-hosts

list database hosts

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-database-hosts json
```

This function for obtaining the list of all configured database hosts.

## v-list-database-types

list supported database types

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-database-types json
```

This function for obtaining the list of database types.

## v-list-databases

listing databases

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-databases user json
```

This function for obtaining the list of all user's databases.

## v-list-default-php

list default PHP version used by default.tpl

**Options**: `[FORMAT]`

List the default version used by de the default template

## v-list-dns-domain

list dns domain

**Options**: `USER` `DOMAIN` `[FORMAT]`

**Examples**:

```bash
v-list-dns-domain alice wonderland.com
```

This function of obtaining the list of dns domain parameters.

## v-list-dns-domains

list dns domains

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-dns-domains admin
```

This function for obtaining all DNS domains of a user.

## v-list-dns-records

list dns domain records

**Options**: `USER` `DOMAIN` `[FORMAT]`

**Examples**:

```bash
v-list-dns-records admin example.com
```

This function for getting all DNS domain records.

## v-list-dns-template

list dns template

**Options**: `TEMPLATE` `[FORMAT]`

**Examples**:

```bash
v-list-dns-template zoho
```

This function for obtaining the DNS template parameters.

## v-list-dns-templates

list dns templates

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-dns-templates json
```

This function for obtaining the list of all DNS templates available.

## v-list-dnssec-public-key

list public dnssec key

**Options**: `USER` `DOMAIN` `[FROMAT]`

**Examples**:

```bash
v-list-dns-public-key admin acme.com
```

This function list the public key to be used with DNSSEC and needs to be added to the domain register.

## v-list-firewall

list iptables rules

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-firewall json
```

This function of obtaining the list of all iptables rules.

## v-list-firewall-ban

list firewall block list

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-firewall-ban json
```

This function of obtaining the list of currently blocked ips.

## v-list-firewall-ipset

List firewall ipset

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-firewall-ipset json
```

This function prints defined ipset lists

## v-list-firewall-rule

list firewall rule

**Options**: `RULE` `[FORMAT]`

**Examples**:

```bash
v-list-firewall-rule 2
```

This function of obtaining firewall rule parameters.

## v-list-fs-directory

list directory

**Options**: `USER` `DIRECTORY`

**Examples**:

```bash
v-list-fs-directory /home/admin/web
```

This function lists directory on the file system

## v-list-letsencrypt-user

list letsencrypt key

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-letsencrypt-user admin
```

This function for obtaining the letsencrypt key thumbprint

## v-list-mail-account

list mail domain account

**Options**: `USER` `DOMAIN` `ACCOUNT` `[FORMAT]`

**Examples**:

```bash
v-list-mail-account admin domain.tld tester
```

This function of obtaining the list of account parameters.

## v-list-mail-account-autoreply

list mail account autoreply

**Options**: `USER` `DOMAIN` `ACCOUNT` `[FORMAT]`

**Examples**:

```bash
v-list-mail-account-autoreply admin example.com testing
```

This function of obtaining mail account autoreply message.

## v-list-mail-accounts

list mail domain accounts

**Options**: `USER` `DOMAIN` `[FORMAT]`

**Examples**:

```bash
v-list-mail-accounts admin acme.com
```

This function of obtaining the list of all user domains.

## v-list-mail-domain

list mail domain

**Options**: `USER` `DOMAIN` `[FORMAT]`

**Examples**:

```bash
v-list-mail-domain user01 mydomain.com
```

This function of obtaining the list of domain parameters.

## v-list-mail-domain-dkim

list mail domain dkim

**Options**: `USER` `DOMAIN` `[FORMAT]`

**Examples**:

```bash
v-list-mail-domain-dkim admin maildomain.tld
```

This function of obtaining domain dkim files.

## v-list-mail-domain-dkim-dns

list mail domain dkim dns records

**Options**: `USER` `DOMAIN` `[FORMAT]`

**Examples**:

```bash
v-list-mail-domain-dkim-dns admin example.com
```

This function of obtaining domain dkim dns records for proper setup.

## v-list-mail-domain-ssl

list mail domain ssl certificate

**Options**: `USER` `DOMAIN` `[FORMAT]`

**Examples**:

```bash
v-list-mail-domain-ssl user acme.com json
```

This function of obtaining domain ssl files.

## v-list-mail-domains

list mail domains

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-mail-domains admin
```

This function of obtaining the list of all user domains.

## v-list-remote-dns-hosts

list remote dns host

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-remote-dns-hosts json
```

This function for obtaining the list of remote dns host.

## v-list-sys-clamd-config

list clamd config parameters

**Options**: `[FORMAT]`

This function for obtaining the list of clamd config parameters.

## v-list-sys-config

list system configuration

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-sys-config json
```

This function for obtaining the list of system parameters.

## v-list-sys-cpu-status

list system cpu info

**Options**:

options:

## v-list-sys-db-status

list db status

**Options**:

options:

## v-list-sys-disk-status

list disk information

**Options**:

options:

## v-list-sys-dns-status

list dns status

**Options**:

options:

## v-list-sys-dovecot-config

list dovecot config parameters

**Options**: `[FORMAT]`

This function for obtaining the list of dovecot config parameters.

## v-list-sys-hestia-autoupdate

list hestia autoupdate settings

**Options**: `[FORMAT]`

This function for obtaining autoupdate settings.

## v-list-sys-hestia-ssl

list hestia ssl certificate

**Options**: `[FORMAT]`

This function of obtaining hestia ssl files.

## v-list-sys-hestia-updates

list system updates

**Options**: `[FORMAT]`

This function checks available updates for hestia packages.

## v-list-sys-info

list system os

**Options**: `[FORMAT]`

This function checks available updates for hestia packages.

## v-list-sys-interfaces

list system interfaces

**Options**: `[FORMAT]`

This function for obtaining the list of network interfaces.

## v-list-sys-ip

list system IP

**Options**: `IP` `[FORMAT]`

**Examples**:

```bash
v-list-sys-ip 203.0.113.1
```

This function for getting the list of system IP parameters.

## v-list-sys-ips

list system IPs

**Options**: `[FORMAT]`

This function for obtaining the list of system IP addresses.

## v-list-sys-languages

list system languages

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-sys-languages json
```

This function for obtaining the available languages for HestiaCP
Output is always in the ISO language code

## v-list-sys-mail-status

list mail status

**Options**:

options:

## v-list-sys-memory-status

list virtual memory info

**Options**:

options:

## v-list-sys-mysql-config

list mysql config parameters

**Options**: `[FORMAT]`

This function for obtaining the list of mysql config parameters.

## v-list-sys-network-status

list system network status

**Options**:

options:

## v-list-sys-nginx-config

list nginx config parameters

**Options**: `[FORMAT]`

This function for obtaining the list of nginx config parameters.

## v-list-sys-pgsql-config

list postgresql config parameters

**Options**: `[FORMAT]`

This function for obtaining the list of postgresql config parameters.

## v-list-sys-php

listing available PHP versions installed

**Options**: `[FORMAT]`

List /etc/php/\* version check if folder fpm is available

## v-list-sys-php-config

list php config parameters

**Options**: `[FORMAT]`

This function for obtaining the list of php config parameters.

## v-list-sys-proftpd-config

list proftpd config parameters

**Options**: `[FORMAT]`

This function for obtaining the list of proftpd config parameters.

## v-list-sys-rrd

list system rrd charts

**Options**: `[FORMAT]`

List available rrd graphics, its titles and paths.

## v-list-sys-services

list system services

**Options**: `[FORMAT]`

**Examples**:

```bash
v-list-sys-services json
```

This function for obtaining the list of configured system services.

## v-list-sys-shells

list system shells

**Options**: `[FORMAT]`

This function for obtaining the list of system shells.

## v-list-sys-spamd-config

list spamassassin config parameters

**Options**: `[FORMAT]`

This function for obtaining the list of spamassassin config parameters.

## v-list-sys-sshd-port

list sshd port

**Options**: `[FORMAT]`

This function for obtainings the port of sshd listens to

## v-list-sys-themes

list system themes

**Options**: `[FORMAT]`

This function for obtaining the list of themes in the theme
library and displaying them in the backend or user interface.

## v-list-sys-users

list system users

**Options**: `[FORMAT]`

This function for obtaining the list of system users without
detailed information.

## v-list-sys-vsftpd-config

list vsftpd config parameters

**Options**: `[FORMAT]`

This function for obtaining the list of vsftpd config parameters.

## v-list-sys-web-status

list web status

**Options**:

options:

## v-list-sys-webmail

listing available webmail clients

**Options**: `[FORMAT]`

List available webmail clients

## v-list-user

list user parameters

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-user admin
```

This function to obtain user parameters.

## v-list-user-auth-log

list user log

**Options**: `USER` `[FORMAT]`

This function of obtaining the list of 10 last users commands.

## v-list-user-backup

list user backup

**Options**: `USER` `BACKUP` `[FORMAT]`

**Examples**:

```bash
v-list-user-backup admin admin.2019-05-19_03-31-30.tar
```

This function of obtaining the list of backup parameters. This call, just as
all v*list*\* calls, supports 3 formats - json, shell and plain.

## v-list-user-backup-exclusions

list backup exclusions

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-user-backup-exclusions admin
```

This function for obtaining the backup exclusion list

## v-list-user-backups

list user backups

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-user-backups admin
```

This function for obtaining the list of available user backups.

## v-list-user-ips

list user IPs

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-user-ips admin
```

This function for obtaining the list of available IP addresses.

## v-list-user-log

list user log

**Options**: `USER` `[FORMAT]`

This function of obtaining the list of 100 last users commands.

## v-list-user-notifications

list user notifications

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-user-notifications admin
```

This function for getting the list notifications

## v-list-user-ns

list user nameservers

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-user-ns admin
```

Function for obtaining the list of user's DNS servers.

## v-list-user-package

list user package

**Options**: `PACKAGE` `[FORMAT]`

This function for getting the list of system ip parameters.

## v-list-user-packages

list user packages

**Options**: `[FORMAT]`

This function for obtaining the list of available hosting packages.

## v-list-user-ssh-key

add ssh key

**Options**: `USER` `[FORMAT]`

Lists $user/.ssh/authorized_keys

## v-list-user-stats

list user stats

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-user-stats admin
```

This function for listing user statistics

## v-list-users

list users

**Options**: `[FORMAT]`

This function to obtain the list of all system users.

## v-list-users-stats

list overall user stats

**Options**: `[FORMAT]`

This function for listing overall user statistics

## v-list-web-domain

list web domain parameters

**Options**: `USER` `DOMAIN` `[FORMAT]`

**Examples**:

```bash
v-list-web-domain admin example.com
```

This function to obtain web domain parameters.

## v-list-web-domain-accesslog

list web domain access log

**Options**: `USER` `DOMAIN` `[LINES]` `[FORMAT]`

**Examples**:

```bash
v-list-web-domain-accesslog admin example.com
```

This function of obtaining raw access web domain logs.

## v-list-web-domain-errorlog

list web domain error log

**Options**: `USER` `DOMAIN` `[LINES]` `[FORMAT]`

**Examples**:

```bash
v-list-web-domain-errorlog admin acme.com
```

This function of obtaining raw error web domain logs.

## v-list-web-domain-ssl

list web domain ssl certificate

**Options**: `USER` `DOMAIN` `[FORMAT]`

**Examples**:

```bash
v-list-web-domain-ssl admin wonderland.com
```

This function of obtaining domain ssl files.

## v-list-web-domains

list web domains

**Options**: `USER` `[FORMAT]`

**Examples**:

```bash
v-list-web-domains alice
```

This function to obtain the list of all user web domains.

## v-list-web-stats

list web statistics

**Options**: `[FORMAT]`

This function for obtaining the list of web statistics analyzer.

## v-list-web-templates

list web templates

**Options**: `[FORMAT]`

This function for obtaining the list of web templates available to a user.

## v-list-web-templates-backend

listing backend templates

**Options**: `[FORMAT]`

This function for obtaining the list of available backend templates.

## v-list-web-templates-proxy

listing proxy templates

**Options**: `[FORMAT]`

This function for obtaining the list of proxy templates available to a user.

## v-log-action

adds action event to user or system log

**Options**: `LOG_TYPE` `USER`

Event Levels:
info, warning, error

## v-log-user-login

add user login

**Options**: `USER` `IP` `STATUS` `[FINGERPRINT]`

## v-log-user-logout

Log User logout event

**Options**: `USER` `FINGERPRINT`

## v-move-fs-directory

move file

**Options**: `USER` `SRC_DIRECTORY` `DST_DIRECTORY`

**Examples**:

```bash
v-move-fs-directory admin /home/admin/web /home/user02/
```

This function moved file or directory on the file system. This function
can also be used to rename files just like normal mv command.

## v-move-fs-file

move file

**Options**: `USER` `SRC_FILE` `DST_FILE`

**Examples**:

```bash
v-move-fs-file admin readme.txt new_readme.txt
```

This function moved file or directory on the file system. This function
can also be used to rename files just like normal mv command.

## v-open-fs-config

open config

**Options**: `CONFIG`

**Examples**:

```bash
v-open-fs-config /etc/mysql/my.cnf
```

This function opens/reads config files on the file system

## v-open-fs-file

open file

**Options**: `USER` `FILE`

**Examples**:

```bash
v-open-fs-file admin README.md
```

This function opens/reads files on the file system

## v-purge-nginx-cache

Purge nginx cache

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-purge-nginx-cache user domain.tld
```

This function purges nginx cache.

## v-rebuild-all

rebuild all assets for a specified user

**Options**: `USER` `[RESTART]`

This function rebuilds all assets for a user account:

## v-rebuild-cron-jobs

rebuild cron jobs

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-rebuild-cron-jobs admin yes
```

This function rebuilds system cron config file for specified user.

## v-rebuild-database

rebuild databases

**Options**: `USER` `DATABASE`

**Examples**:

```bash
v-rebuild-database user user_wordpress
```

This function for rebuilding a single database for a user

## v-rebuild-databases

rebuild databases

**Options**: `USER`

**Examples**:

```bash
v-rebuild-databases admin
```

This function for rebuilding of all databases of a single user.

## v-rebuild-dns-domain

rebuild dns domain

**Options**: `USER` `DOMAIN` `[RESTART]` `[UPDATE_SERIAL]`

**Examples**:

```bash
v-rebuild-dns-domain alice wonderland.com
```

This function rebuilds DNS configuration files.

## v-rebuild-dns-domains

rebuild dns domains

**Options**: `USER` `[RESTART]` `[UPDATE_SERIAL]`

**Examples**:

```bash
v-rebuild-dns-domains alice
```

This function rebuilds DNS configuration files.

## v-rebuild-mail-domain

rebuild mail domain

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-rebuild-mail-domain user domain.tld
```

This function rebuilds configuration files for a single domain.

## v-rebuild-mail-domains

rebuild mail domains

**Options**: `USER`

**Examples**:

```bash
v-rebuild-mail-domains admin
```

This function rebuilds EXIM configuration files for all mail domains.

## v-rebuild-user

rebuild system user

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-rebuild-user admin yes
```

This function rebuilds system user account.

## v-rebuild-users

rebuild system users

**Options**: `[RESTART]`

This function rebuilds user configuration for all users.

## v-rebuild-web-domain

rebuild web domain

**Options**: `USER` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-rebuild-web-domain user domain.tld
```

This function rebuilds web configuration files.

## v-rebuild-web-domains

rebuild web domains

**Options**: `USER` `[RESTART]`

This function rebuilds web configuration files.

## v-refresh-sys-theme

change active system theme

**Options**: –

This function for changing the currently active system theme.

## v-rename-user-package

change package name

**Options**: `OLD_NAME` `NEW_NAME` `[MODE]`

**Examples**:

```bash
v-rename-package package package2
```

This function changes the name of an existing package.

## v-repair-sys-config

Restore system configuration

**Options**: `[SYSTEM]`

This function repairs or restores the system configuration file.

## v-restart-cron

restart cron service

**Options**: –

This function tells crond service to reread its configuration files.

## v-restart-dns

restart dns service

**Options**: –

This function tells BIND service to reload dns zone files.

## v-restart-ftp

restart ftp service

**Options**: –

This function tells ftp server to reread its configuration.

## v-restart-mail

restart mail service

**Options**: `[RESTART]`

This function tells exim or dovecot services to reload configuration files.

## v-restart-proxy

restart proxy server

**Options**: –

**Examples**:

```bash
v-restart-proxy [RESTART]
```

This function reloads proxy server configuration.

## v-restart-service

restart service

**Options**: `SERVICE` `[RESTART]`

**Examples**:

```bash
v-restart-service apache2
```

This function restarts system service.

## v-restart-system

restart operating system

**Options**: `RESTART` `[DELAY]`

**Examples**:

```bash
v-restart-system yes
```

This function restarts operating system.

## v-restart-web

restart web server

**Options**: `[RESTARRT]`

This function reloads web server configuration.

## v-restart-web-backend

restart backend server

**Options**: –

This function reloads backend server configuration.

## v-restore-cron-job

restore single cron job

**Options**: `USER` `BACKUP` `DOMAIN` `[NOTIFY]`

**Examples**:

```bash
v-restore-cron-job USER BACKUP CRON [NOTIFY]
```

This function allows the user to restore a single cron job
from a backup archive.

## v-restore-database

restore single database

**Options**: `USER` `BACKUP` `DATABASE` `[NOTIFY]`

**Examples**:

```bash
v-restore-database USER BACKUP DATABASE [NOTIFY]
```

This function allows the user to restore a single database
from a backup archive.

## v-restore-dns-domain

restore single dns domain

**Options**: `USER` `BACKUP` `DOMAIN` `[NOTIFY]`

**Examples**:

```bash
v-restore-dns-domain USER BACKUP DOMAIN [NOTIFY]
```

This function allows the user to restore a single DNS domain
from a backup archive.

## v-restore-mail-domain

restore single mail domain

**Options**: `USER` `BACKUP` `DOMAIN` `[NOTIFY]`

**Examples**:

```bash
v-restore-mail-domain USER BACKUP DOMAIN [NOTIFY]
```

This function allows the user to restore a single mail domain
from a backup archive.

## v-restore-user

restore user

**Options**: `USER` `BACKUP` `[WEB]` `[DNS]` `[MAIL]` `[DB]` `[CRON]` `[UDIR]` `[NOTIFY]`

**Examples**:

```bash
v-restore-user admin 2019-04-22_01-00-00.tar
```

This function for restoring user from backup. To be able to restore the backup,
the archive needs to be placed in /backup.

## v-restore-web-domain

restore single web domain

**Options**: `USER` `BACKUP` `DOMAIN` `[NOTIFY]`

**Examples**:

```bash
v-restore-web-domain USER BACKUP DOMAIN [NOTIFY]
```

This function allows the user to restore a single web domain
from a backup archive.

## v-revoke-api-key

revokes api key

**Options**: `[HASH]`

**Examples**:

```bash
v-revoke-api-key mykey
```

This function removes a key from in $HESTIA/data/keys/

## v-run-cli-cmd

run cli command

**Options**: `USER` `CMD` `[ARG...]`

**Examples**:

```bash
v-run-cli-cmd user composer require package
```

This function runs a limited list of cli commands with dropped privileges as the specific hestia user

## v-schedule-letsencrypt-domain

adding cronjob for letsencrypt cetificate installation

**Options**: `USER` `DOMAIN` `[ALIASES]`

**Examples**:

```bash
v-schedule-letsencrypt-domain admin example.com www.example.com
```

This function adds cronjob for letsencrypt ssl certificate installation

## v-schedule-user-backup

schedule user backup creation

**Options**: `USER`

**Examples**:

```bash
v-schedule-user-backup admin
```

This function for scheduling user backup creation.

## v-schedule-user-backup-download

Schedule a backup

**Options**: `USER` `BACKUP`

**Examples**:

```bash
v-schedule-user-backup-download admin 2019-04-22_01-00-00.tar
```

This function for scheduling user backup creation.

## v-schedule-user-restore

schedule user backup restoration

**Options**: `USER` `BACKUP` `[WEB]` `[DNS]` `[MAIL]` `[DB]` `[CRON]` `[UDIR]`

**Examples**:

```bash
v-schedule-user-restore 2019-04-22_01-00-00.tar
```

This function for scheduling user backup restoration.

## v-search-command

search for available commands

**Options**: `ARG1` `[ARG...]`

**Examples**:

```bash
v-search-command web
```

This function searches for available Hestia Control Panel commands
and returns results based on the specified criteria.
Originally developed for VestaCP by Federico Krum
<https://github.com/FastDigitalOceanDroplets/VestaCP/blob/master/files/v-search-command>

## v-search-domain-owner

search domain owner

**Options**: `DOMAIN` `[TYPE]`

**Examples**:

```bash
v-search-domain-owner acme.com
```

This function that allows to find user objects.

## v-search-fs-object

search file or directory

**Options**: `USER` `OBJECT` `[PATH]`

**Examples**:

```bash
v-search-fs-object admin hello.txt
```

This function search files and directories on the file system

## v-search-object

search objects

**Options**: `OBJECT` `[FORMAT]`

**Examples**:

```bash
v-search-object example.com json
```

This function that allows to find system objects.

## v-search-user-object

search objects

**Options**: `USER` `OBJECT` `[FORMAT]`

**Examples**:

```bash
v-search-user-object admin example.com json
```

This function that allows to find user objects.

## v-start-service

start service

**Options**: `SERVICE`

**Examples**:

```bash
v-start-service mysql
```

This function starts system service.

## v-stop-firewall

stop system firewall

**Options**: –

This function stops iptables

## v-stop-service

stop service

**Options**: `SERVICE`

**Examples**:

```bash
v-stop-service apache2
```

This function stops system service.

## v-suspend-cron-job

suspend cron job

**Options**: `USER` `JOB` `[RESTART]`

**Examples**:

```bash
v-suspend-cron-job admin 5 yes
```

This function suspends a certain job of the cron scheduler.

## v-suspend-cron-jobs

Suspending sys cron jobs

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-suspend-cron-jobs admin
```

This function suspends all user cron jobs.

## v-suspend-database

suspend database

**Options**: `USER` `DATABASE`

**Examples**:

```bash
v-suspend-database admin admin_wordpress_db
```

This function for suspending a certain user database.

## v-suspend-database-host

suspend database server

**Options**: `TYPE` `HOST`

**Examples**:

```bash
v-suspend-database-host mysql localhost
```

This function for suspending a database server.

## v-suspend-databases

suspend databases

**Options**: `USER`

**Examples**:

```bash
v-suspend-databases admin
```

This function for suspending of all databases of a single user.

## v-suspend-dns-domain

suspend dns domain

**Options**: `USER` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-suspend-dns-domain alice acme.com
```

This function suspends a certain user's domain.

## v-suspend-dns-domains

suspend dns domains

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-suspend-dns-domains admin yes
```

This function suspends all user's DNS domains.

## v-suspend-dns-record

suspend dns domain record

**Options**: `USER` `DOMAIN` `ID` `[RESTART]`

**Examples**:

```bash
v-suspend-dns-record alice wonderland.com 42 yes
```

This function suspends a certain domain record.

## v-suspend-domain

suspend web/dns/mail domain

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-suspend-domain admin example.com
```

This function suspends web/dns/mail domain.

## v-suspend-firewall-rule

suspend firewall rule

**Options**: `RULE`

**Examples**:

```bash
v-suspend-firewall-rule 7
```

This function suspends a certain firewall rule.

## v-suspend-mail-account

suspend mail account

**Options**: `USER` `DOMAIN` `ACCOUNT`

**Examples**:

```bash
v-suspend-mail-account admin acme.com bob
```

This function suspends mail account.

## v-suspend-mail-accounts

suspend all mail domain accounts

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-suspend-mail-accounts admin example.com
```

This function suspends all mail domain accounts.

## v-suspend-mail-domain

suspend mail domain

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-suspend-mail-domain admin domain.com
```

This function suspends mail domain.

## v-suspend-mail-domains

suspend mail domains

**Options**: `USER`

**Examples**:

```bash
v-suspend-mail-domains admin
```

This function suspends all user's MAIL domains.

## v-suspend-remote-dns-host

suspend remote dns server

**Options**: `HOST`

**Examples**:

```bash
v-suspend-remote-dns-host hostname.tld
```

This function for suspending remote dns server.

## v-suspend-user

suspend user

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-suspend-user alice yes
```

This function suspends a certain user and all his objects.

## v-suspend-web-domain

suspend web domain

**Options**: `USER` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-suspend-web-domain admin example.com yes
```

This function for suspending the site's operation. After blocking it all
visitors will be redirected to a web page explaining the reason of suspend.
By blocking the site the content of all its directories remains untouched.

## v-suspend-web-domains

suspend web domains

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-suspend-web-domains bob
```

This function of suspending all user's sites.

## v-sync-dns-cluster

synchronize dns domains

**Options**: `HOST`

This function synchronise all dns domains.

## v-unsuspend-cron-job

unsuspend cron job

**Options**: `USER` `JOB` `[RESTART]`

**Examples**:

```bash
v-unsuspend-cron-job admin 7 yes
```

This function unsuspend certain cron job.

## v-unsuspend-cron-jobs

unsuspend sys cron

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-unsuspend-cron-jobs admin no
```

This function unsuspends all suspended cron jobs.

## v-unsuspend-database

unsuspend database

**Options**: `USER` `DATABASE`

**Examples**:

```bash
v-unsuspend-database admin mydb
```

This function for unsuspending database.

## v-unsuspend-database-host

unsuspend database server

**Options**: `TYPE` `HOST`

**Examples**:

```bash
v-unsuspend-database-host mysql localhost
```

This function for unsuspending a database server.

## v-unsuspend-databases

unsuspend databases

**Options**: `USER`

This function for unsuspending all user's databases.

## v-unsuspend-dns-domain

unsuspend dns domain

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-unsuspend-dns-domain alice wonderland.com
```

This function unsuspends a certain user's domain.

## v-unsuspend-dns-domains

unsuspend dns domains

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-unsuspend-dns-domains alice
```

This function unsuspends all user's DNS domains.

## v-unsuspend-dns-record

unsuspend dns domain record

**Options**: `USER` `DOMAIN` `ID` `[RESTART]`

**Examples**:

```bash
v-unsuspend-dns-record admin example.com 33
```

This function unsuspends a certain domain record.

## v-unsuspend-domain

unsuspend web/dns/mail domain

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-unsuspend-domain admin acme.com
```

This function unsuspends web/dns/mail domain.

## v-unsuspend-firewall-rule

unsuspend firewall rule

**Options**: `RULE`

**Examples**:

```bash
v-unsuspend-firewall-rule 7
```

This function unsuspends a certain firewall rule.

## v-unsuspend-mail-account

unsuspend mail account

**Options**: `USER` `DOMAIN` `ACCOUNT`

**Examples**:

```bash
v-unsuspend-mail-account admin acme.com tester
```

This function unsuspends mail account.

## v-unsuspend-mail-accounts

unsuspend all mail domain accounts

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-unsuspend-mail-accounts admin acme.com
```

This function unsuspends all mail domain accounts.

## v-unsuspend-mail-domain

unsuspend mail domain

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-unsuspend-mail-domain user02 acme.com
```

This function unsuspends mail domain.

## v-unsuspend-mail-domains

unsuspend mail domains

**Options**: `USER`

**Examples**:

```bash
v-unsuspend-mail-domains admin
```

This function unsuspends all user's MAIL domains.

## v-unsuspend-remote-dns-host

unsuspend remote dns server

**Options**: `HOST`

**Examples**:

```bash
v-unsuspend-remote-dns-host hosname.com
```

This function for unsuspending remote dns server.

## v-unsuspend-user

unsuspend user

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-unsuspend-user bob
```

This function unsuspends user and all his objects.

## v-unsuspend-web-domain

unsuspend web domain

**Options**: `USER` `DOMAIN` `[RESTART]`

**Examples**:

```bash
v-unsuspend-web-domain admin acme.com
```

This function of unsuspending the domain.

## v-unsuspend-web-domains

unsuspend web domains

**Options**: `USER` `[RESTART]`

**Examples**:

```bash
v-unsuspend-web-domains admin
```

This function of unsuspending all user's sites.

## v-update-database-disk

update database disk usage

**Options**: `USER` `DATABASE`

**Examples**:

```bash
v-update-database-disk admin wp_db
```

This function recalculates disk usage for specific database.

## v-update-databases-disk

update databases disk usage

**Options**: `USER`

**Examples**:

```bash
v-update-databases-disk admin
```

This function recalculates disk usage for all user databases.

## v-update-dns-templates

update dns templates

**Options**: `[RESTART]`

This function for obtaining updated dns templates from Hestia package.

## v-update-firewall

update system firewall rules

**Options**: –

This function updates iptables rules

## v-update-firewall-ipset

update firewall ipset

**Options**: `[REFRESH]`

This function creates ipset lists and updates the lists if they are expired or ondemand

## v-update-host-certificate

update host certificate for hestia

**Options**: `USER` `HOSTNAME`

**Examples**:

```bash
v-update-host-certificate admin example.com
```

This function updates the SSL certificate used for Hestia Control Panel.

## v-update-letsencrypt-ssl

update letsencrypt ssl certificates

**Options**: –

This function for renew letsencrypt expired ssl certificate for all users

## v-update-mail-domain-disk

update mail domain disk usage

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-update-mail-domain-disk admin example.com
```

This function updates domain disk usage.

## v-update-mail-domain-ssl

updating ssl certificate for domain

**Options**: `USER` `DOMAIN` `SSL_DIR` `[RESTART]`

**Examples**:

```bash
v-update-mail-domain-ssl admin domain.com /home/admin/tmp
```

This function updates the SSL certificate for a domain. Parameter ssl_dir is a path
to directory where 2 or 3 ssl files can be found. Certificate file
domain.tld.crt and its key domain.tld.key are mandatory. Certificate
authority domain.tld.ca file is optional.

## v-update-mail-domains-disk

calculate disk usage for all mail domains

**Options**: `USER`

**Examples**:

```bash
v-update-mail-domains-disk admin
```

This function calculates disk usage for all mail domains.

## v-update-mail-templates

update mail templates

**Options**: `[RESTART]` `[SKIP]`

This function for obtaining updated webmail templates from Hestia package.

## v-update-sys-defaults

update default key database

**Options**: `[SYSTEM]`

**Examples**:

```bash
v-update-sys-defaults
example: v-update-sys-defaults user
```

This function updates the known key/value pair database

## v-update-sys-hestia

update hestia package/configs

**Options**: `PACKAGE`

**Examples**:

```bash
v-update-sys-hestia hestia-php
```

This function runs as apt update trigger. It pulls shell script from hestia
server and runs it. (hestia, hestia-nginx and hestia-php are valid options)

## v-update-sys-hestia-all

update all hestia packages

**Options**: –

This function of updating all hestia packages

## v-update-sys-hestia-git

Install update from Git repository

**Options**: `REPOSITORY` `BRANCH` `INSTALL`

**Examples**:

```bash
v-update-sys-hestia-git hestiacp staging/beta install
# Will download from the hestiacp repository
# Pulls code from staging/beta branch
# install: installs package immediately
# install-auto: installs package and schedules automatic updates from Git
```

Downloads and compiles/installs packages from GitHub repositories

## v-update-sys-ip

update system IP

**Options**: –

**Examples**:

```bash
v-update-sys-ip
# Intended for internal usage
```

This function scans configured IP in the system and register them with Hestia
internal database. This call is intended for use on vps servers, where IP is
set by hypervisor.

## v-update-sys-ip-counters

update IP usage counters

**Options**: `IP`

Function updates usage U_WEB_ADOMAINS and U_SYS_USERS counters.

## v-update-sys-queue

update system queue

**Options**: `PIPE`

This function is responsible queue processing. Restarts of services,
scheduled backups, web log parsing and other heavy resource consuming
operations are handled by this script. It helps to optimize system behaviour.
In a nutshell Apache will be restarted only once even if 10 domains are
added or deleted.

## v-update-sys-rrd

update system rrd charts

**Options**: –

This function is wrapper for all rrd functions. It updates all
v-update-sys-rrd\_\* at once.

## v-update-sys-rrd-apache2

update apache2 rrd

**Options**: `PERIOD`

This function is for updating apache rrd database and graphic.

## v-update-sys-rrd-ftp

update ftp rrd

**Options**: `PERIOD`

This function is for updating ftpd rrd database and graphic.

## v-update-sys-rrd-httpd

update httpd rrd

**Options**: `PERIOD`

This function is for updating apache rrd database and graphic.

## v-update-sys-rrd-la

update load average rrd

**Options**: `PERIOD`

This function is for updating load average rrd database and graphic.

## v-update-sys-rrd-mail

update mail rrd

**Options**: `PERIOD`

This function is for updating mail rrd database and graphic.

## v-update-sys-rrd-mem

update memory rrd

**Options**: `PERIOD`

This function is for updating memory rrd database and graphic.

## v-update-sys-rrd-mysql

update MySQL rrd

**Options**: `PERIOD`

This function is for updating mysql rrd database and graphic.

## v-update-sys-rrd-net

update network rrd

**Options**: `PERIOD`

This function is for updating network usage rrd database and graphic.

## v-update-sys-rrd-nginx

update nginx rrd

**Options**: `PERIOD`

This function is for updating nginx rrd database and graphic.

## v-update-sys-rrd-pgsql

update PostgreSQL rrd

**Options**: `PERIOD`

This function is for updating postgresql rrd database and graphic.

## v-update-sys-rrd-ssh

update ssh rrd

**Options**: `PERIOD`

This function is for updating ssh rrd database and graphic.

## v-update-user-backup-exclusions

update backup exclusion list

**Options**: `USER` `FILE`

**Examples**:

```bash
v-update-user-backup-exclusions admin /tmp/backup_exclusions
```

This function for updating backup exclusion list

## v-update-user-counters

update user usage counters

**Options**: `USER`

**Examples**:

```bash
v-update-user-counters admin
```

Function updates usage counters like U_WEB_DOMAINS, U_MAIL_ACCOUNTS, etc.

## v-update-user-disk

update user disk usage

**Options**: `USER`

**Examples**:

```bash
v-update-user-disk admin
```

The functions recalculates disk usage and updates database.

## v-update-user-package

update user package

**Options**: `PACKAGE`

**Examples**:

```bash
v-update-user-package default
```

This function propagates package to connected users.

## v-update-user-quota

update user disk quota

**Options**: `USER`

**Examples**:

```bash
v-update-user-quota alice
```

The functions upates disk quota for specific user

## v-update-user-stats

update user statistics

**Options**: `USER`

**Examples**:

```bash
v-update-user-stats admin
```

Function logs user parameters into statistics database.

## v-update-web-domain-disk

update disk usage for domain

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-update-web-domain-disk alice wonderland.com
```

This function recalculates disk usage for specific webdomain.

## v-update-web-domain-ssl

updating ssl certificate for domain

**Options**: `USER` `DOMAIN` `SSL_DIR` `[RESTART]`

**Examples**:

```bash
v-update-web-domain-ssl admin domain.com /home/admin/tmp
```

This function updates the SSL certificate for a domain. Parameter ssl_dir is a path
to directory where 2 or 3 ssl files can be found. Certificate file
domain.tld.crt and its key domain.tld.key are mandatory. Certificate
authority domain.tld.ca file is optional.

## v-update-web-domain-stat

update domain statistics

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-update-web-domain-stat alice acme.com
```

This function runs log analyser for specific webdomain.

## v-update-web-domain-traff

update domain bandwidth usage

**Options**: `USER` `DOMAIN`

**Examples**:

```bash
v-update-web-domain-traff admin example.com
```

This function recalculates bandwidth usage for specific domain.

## v-update-web-domains-disk

update domains disk usage

**Options**: `USER`

**Examples**:

```bash
v-update-web-domains-disk alice
```

This function recalculates disk usage for all user webdomains.

## v-update-web-domains-stat

update domains statistics

**Options**: `USER`

**Examples**:

```bash
v-update-web-domains-stat admin
```

This function runs log analyser usage for all user webdomains.

## v-update-web-domains-traff

update domains bandwidth usage

**Options**: `USER`

**Examples**:

```bash
v-update-web-domains-traff bob
```

This function recalculates bandwidth usage for all user webdomains.

## v-update-web-templates

update web templates

**Options**: `[RESTART]` `[SKIP]`

This function for obtaining updated web (Nginx/Apache2/PHP) templates from the Hestia package.

## v-update-white-label-logo

update white label logo's

**Options**: `[DOWNLOAD]`

Replace Hestia logos with User created logo's
