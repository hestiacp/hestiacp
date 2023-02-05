# Backups

To manage your backups, navigate to the **Backups <i class="fas fa-fw fa-file-archive"></i>** tab.

## Manually creating a backup

Click the **<i class="fas fa-fw fa-plus-circle"></i> Create backup** button.

A popup will be shown with the following message:

**Task has been added to the queue. You will receive an email notification when your backup is ready for download.**

## Downloading a backup

1. Hover over the backup you want to download.
2. Click the <i class="fas fa-fw fa-file-download"><span class="visually-hidden">Download</span></i> icon on the right of the backup’s filename.

If the backup is stored on a remote server, the file is downloaded to the server and you will get notified by email when the download is available.

## Restoring a backup

1. Hover over the backup you want to restore.
2. Click the backup’s filename or the <i class="fas fa-fw fa-undo"><span class="visually-hidden">Restore</span></i> icon on the right of the backup’s filename.
3. Restore the backup in one of the following ways:
   1. You can restore the whole backup by clicking the **<i class="fas fa-fw fa-undo"></i> Restore backup** button on the top right.
   2. Restore multiple parts of the backup, by selecting them, then selecting **Restore** in the **Apply to selected** menu in the top right and clicking on the <i class="fas fa-fw fa-arrow-right"><span class="visually-hidden">Apply</span></i> button.
   3. Restore one part of the backup by hovering over it and clicking the <i class="fas fa-fw fa-undo"><span class="visually-hidden">Restore</span></i> icon on the right.

## Deleting a backup

1. Hover over the backup you want to delete.
2. Click the <i class="fas fa-fw fa-trash"><span class="visually-hidden">delete</span></i> icon on the right of the backup’s filename.

## Excluding components from backups

1. Click the **<i class="fas fa-fw fa-folder-minus"></i> Backup Exclusion** button.
2. Click the **<i class="fas fa-fw fa-pencil-alt"></i> Edit backup exclusions** button.

### Excluding a web domain

In the box labeled **Web Domains**, enter each domain you want to exclude, one per line.

To exclude a specific folder from a domain use the following syntax:

```bash
domain.tld:public_html/wp-content/uploads:public_html/cache
```

This will excude both `public_html/wp-content/uploads/` and `public_html/cache/` from that domain.

To exclude all domains, use `*`.

### Excluding a mail domain

In the box labeled **Mail Domains**, enter each domain you want to exclude, one per line.

To exclude only one or multiple mail account use the following syntax:

```bash
domain.tld:info:support
```

This will excude both `info@domain.tld` and `support@domain.tld`.

To exclude all domains, use `*`.

### Excluding a database

In the box labeled **Databases**, enter the name of each database you want to exclude, one per line.

To exclude all databases, use `*`.

### Excluding a user directory

In the box labeled **User Directories**, enter the name of each directory you want to exclude, one per line.

To exclude all directories, use `*`.

## Editing the number of backups

To edit the number of backups, please read the [Packages](../user-guide/packages.md) and [Users](../user-guide/users.md) documentation. You will need to create or edit a package, and assign it to the desired user.
