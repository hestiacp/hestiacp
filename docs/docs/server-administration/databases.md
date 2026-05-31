# Database & phpMyAdmin SSO

## How to setup a remote database server

1. It is assumed you already have your second server up and running.
2. On your Hestia server run the following command (`mysql` may be replaced by `postgresql`):

```bash
v-add-database-host mysql new-server.com root password
```

To make sure the host has been added, run the following command:

```bash
v-list-database-hosts
```

## Why I can’t use `http://ip/phpmyadmin/`

For security reasons, we have decided to disable this option. Please use `https://host.domain.tld/phpmyadmin/` instead.

## How to create PhpMyAdmin root user credentials

Replace `myrootusername` & `myrootusername_password` with preferred credentials:

```bash
mysql -uroot
```

```sql
CREATE USER 'myrootusername'@'localhost' IDENTIFIED BY 'myrootusername_password';
GRANT ALL PRIVILEGES ON *.* TO 'myrootusername'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
QUIT;
```

## How can I enable access to `http://ip/phpmyadmin/`

### For Apache2

```bash
nano /etc/apache2/conf.d/ip.conf

# Add the following code before both </VirtualHost> closing tags
IncludeOptional /etc/apache2/conf.d/*.inc

# Restart apache2
systemctl restart apache2

# You can also add the following in /etc/apache2.conf instead
IncludeOptional /etc/apache2/conf.d/*.inc
```

### For Nginx

```bash
nano /etc/nginx/conf.d/ip.conf

# Replace the following
location /phpmyadmin/ {
  alias /var/www/document_errors/;
  return 404;
}
location /phppgadmin/ {
  alias /var/www/document_errors/;
  return 404;
}

# With the following
include     /etc/nginx/conf.d/phpmyadmin.inc*;
include     /etc/nginx/conf.d/phppgadmin.inc*;
```

## How can I connect from a remote location to the database

By default, connections to port 3306 are disabled in the firewall. Open
port 3306 in the firewall ([documentation](./firewall)), then edit `/etc/mysql/mariadb.conf.d/50-server.cnf`:

```bash
nano /etc/mysql/mariadb.conf.d/50-server.cnf

# Set bind-address to one of the following
bind-address = 0.0.0.0
bind-address = "your.server.ip.address"
```

## PhpMyAdmin Single Sign On

NOTE: PhpMyAdmin Single Sign On enabled only for individual databases. Primary "PhpMyAdmin" button for existing database credentials only.

### Unable to activate phpMyAdmin Single Sign on

Make sure the API is enabled and working properly. Hestia’s PhpMyAdmin Single Sign On function connects over the Hestia API.

### When clicking the phpMyAdmin Single Sign On button, I am forwarded to the login page of phpMyAdmin

Automated can sometimes cause issues. Login via SSH and open `/var/log/{webserver}/domains/{hostname.domain.tld.error.log` and look for one of the following error messages:

- `Unable to connect over API, please check API connection`
  1. Check if the api has been enabled.
  2. Add the public IP of your server to the allowed IPs in the **Server settings**.
- `Access denied: There is a security token mismatch`
  1. Disable and then enable the phpMyAdmin SSO. This will refresh both keys.
  2. If you are behind a firewall or proxy, you may want to disable it and try again.
- `Link has expired`
  1. Refresh the database page and try again.

## Remote databases

If needed you can simply host Mysql or Postgresql on a remote server.

To add a remote database:

```bash
v-add-database-host TYPE HOST DBUSER DBPASS [MAX_DB] [CHARSETS] [TPL] [PORT]
```

For example:

```bash
v-add-database-host mysql db.hestiacp.com root mypassword 500
```

## Multiple local database endpoints

Hestia can manage more than one database endpoint on the same host when each
endpoint uses a different port. This is useful when you run an additional local
MySQL or MariaDB instance yourself, for example on `127.0.0.1:3307`.

Hestia does not install or maintain the second MySQL/MariaDB service. Configure
the extra service with its own data directory, socket, and TCP port before
adding it to Hestia.

```bash
v-add-database-host mysql 127.0.0.1 root mypassword 500 UTF8,UTF8MB4 template1 3307
```

To create a database on a specific endpoint from the CLI, pass both the host
and port:

```bash
v-add-database admin app_db app_user strongpassword mysql 127.0.0.1 utf8mb4 3307
```

When more than one endpoint uses the same host, commands that target one
database host need the port:

```bash
v-list-database-host mysql 127.0.0.1 json 3307
v-suspend-database-host mysql 127.0.0.1 3307
v-delete-database-host mysql 127.0.0.1 3307
```

For MySQL-family endpoints, Hestia refreshes phpMyAdmin server entries when a
database host is added or removed. You can also refresh them manually:

```bash
v-update-sys-pma-hosts
```

The generated phpMyAdmin entries cover host and port selection. Advanced
phpMyAdmin storage features such as `pmadb`, `controluser`, bookmarks, and
designer tables are only configured for the default local phpMyAdmin setup
unless you customize phpMyAdmin separately.
