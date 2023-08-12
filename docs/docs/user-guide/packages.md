# Packages

To manage packages, log in as an **administrator** and navigate to the **Users <i class="fas fa-fw fa-users"></i>** tab.

## Adding a new package

1. Click the **<i class="fas fa-fw fa-plus-circle"></i> Add Package** button.
2. Fill out the form. When clicking on <i class="fas fa-fw fa-infinity"><span class="visually-hidden">unlimited</span></i> icon, the limits will be set to unlimited.
3. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## Copying a package

1. Hover over the package you want to copy.
2. Click the <i class="fas fa-fw fa-clone"><span class="visually-hidden">copy</span></i> icon on the right of the package name.

## Editing a package

::: info
The **system** package can’t be edited or renamed.
:::

1. Hover over the package you want to edit.
2. Click the <i class="fas fa-fw fa-pencil-alt"><span class="visually-hidden">edit</span></i> icon on the right of the package name.

## Deleting a package

::: info
The **system** package can’t be deleted.
:::

1. Hover over the package you want to delete.
2. Click the <i class="fas fa-fw fa-trash"><span class="visually-hidden">delete</span></i> icon on the right of the package name.

## Importing existing packages from a different server

Packages are stored in `/usr/local/hestia/data/packages` as `package-name.pkg`. Simply copy these package files to other servers.

## Package configuration

::: info
Some options may not be available depending on your setup.
:::

### Quota

Total available storage space including the websites, email accounts, databases and the home folder. If you have enabled **File Quotas** during install or in the server settings, this value enforces the quota limit on websites, email accounts and the home folder. Databases are excluded.

### Bandwidth

Allocated bandwidth. Only outgoing traffic over web is accounted for.

There’s currently no methods of auto-suspending available.

### Backups

Maximum number of backups that can be stored.

## Web domains

### Web domains

Maximum number of web domains that can be created.

### Aliases

Maximum number of aliases that can be added per domain.

### Proxy Template

Default proxy template for created domains.

### Web Template

Default web template for created domains.

## DNS

### DNS Template

Default DNS template that get assigned on domain creation.

### DNS Domains

Maximum number of DNS domains that can be created.

### DNS records

Maximum number of DNS records that can be added per domain.

### Name Servers

Default name servers to be used by the user. Up to 8 different name servers can be added.

## Mail

### Mail Domains

Maximum number of mail domains that can be created.

### Mail accounts

Maximum number of mail accounts that can be added per domain.

### Rate limit

Maximum number of emails that accounts can send per hour.

## Databases

Maximum number of databases that can be created.

## System

### Cron jobs

Maximum number of cron jobs that can be created.

### Shell access

Select the shell that will be shown to the user when logging in via SSH.

::: warning
When set to `nologin`, SSH access is disabled but SFTP access is still allowed.
:::
