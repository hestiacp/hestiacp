# Hestia command line interface

The hestia command

## Modules

### List available modules

Lists modules, indicating what each module provides and installation status.

`hestia module list`

```
Module           Provides     Inst Description
apache           web          No   Apache web server
clamav           antivirus    Yes  ClamAV antivirus
dovecot          imap         Yes  Dovecot IMAP/POP3 server
exim             mta          No   Exim mail transport agent
nginx-web        web          No   Nginx web server
nginx-proxy      rproxy       No   Nginx reverse proxy
nginx-web        web          No   Nginx web server
php-fpm          php          Yes  PHP language FPM
phpmyadmin       phpmyadmin   Yes  phpMyAdmin MariaDB/MySQL web admin
spamassassin     antispam     Yes  SpamAssassin antispam
vsftpd           ftp          No   Vsftpd FTP server
```

### Get module information

`hestia module info web`

```
Module name     : apache
Installed       : yes
Description     : Hestia Apache module
Variant         : apache
Version         : 1
```

### Query module provides

`hestia module what-provides web`

```
apache
```

`hestia module what-provides exim`

```
exim
```

`hestia module what-provides nonexistent`

(exit code 1)

