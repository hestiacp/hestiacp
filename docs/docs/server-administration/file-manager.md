# File manager

## How can I enable or disable the file manager

In a new install, the file manager will be enabled by default.

To enable or update the file manager, please run the following command:

```bash
v-add-sys-filemanager
```

To disable the file manager, please run the following command:

```bash
v-delete-sys-filemanager
```

## How the file manager works

Hestia installs FileGator under `/usr/local/hestia/web/fm` and configures it to use the active Hestia web session. Users do not log in to FileGator separately.

When a user opens the file manager, Hestia creates a temporary SFTP key at `/home/<user>/.ssh/hst-filemanager-key` if it does not already exist. The key is limited to local SFTP access from `127.0.0.1` and is scheduled for removal after 30 minutes. FileGator then connects to `127.0.0.1` over SFTP and uses `/home/<user>` as the file manager root.

Administrators can update the installed file manager files and configuration by running:

```bash
v-add-sys-filemanager
```

## Safe configuration changes

The packaged Hestia template for FileGator is stored at:

```bash
/usr/local/hestia/install/deb/filemanager/filegator/configuration.php
```

The installed runtime configuration is stored at:

```bash
/usr/local/hestia/web/fm/configuration.php
```

For upstream contributions, change the packaged template in the Hestia source tree. On installed servers, `v-add-sys-filemanager` copies the packaged template over the runtime file, and Hestia upgrades can replace both files. Keep a record of any local-only changes so you can reapply them after updates.

Hestia sets FileGator's `overwrite_on_upload` option to `true`, so uploading a file with the same name can replace the existing file. Tell users to keep backups before uploading over important website files.

## Permissions behavior

FileGator exposes permission changes through Hestia's file-system helpers. Permission changes are only expected to work for files and folders owned by the Hestia user. Files owned by `root`, web server users, or other system users should be fixed from the command line by an administrator.

For typical website files, use:

- `644` for files.
- `755` for folders.

Avoid broad recursive permission changes unless you have checked the target path. When FileGator sends a recursive permission change, it can target all entries, folders only, or files only. Without a recursive option, Hestia changes only the selected item.

## File manager gives “Unknown Error” message

This seems to occur specifically when the line `Subsystem sftp /usr/lib/openssh/sftp-server` is removed or changed in `/etc/ssh/sshd_config` in such a way that the install script cannot update it to `Subsystem sftp internal-sftp`.

Short answer: add `Subsystem sftp internal-sftp` to `/etc/ssh/sshd_config`.

Long answer: Refer to the install script `./install/hst-install-{distro}.sh` for all the changes made to `/etc/ssh/sshd_config`. For Debian, the changes can be summarised as follows:

```bash
# HestiaCP Changes to the default /etc/ssh/sshd_config in Debian 10 Buster

# Forced default yes
PasswordAuthentication yes

# Changed from default 2m to 1m
LoginGraceTime 1m

# Changed from default /usr/lib/openssh/sftp-server to internal-sftp
Subsystem sftp internal-sftp

# Changed from default yes
DebianBanner no
```

Changing all of the other parameters to their defaults and also changing to `PasswordAuthentication no` did not reproduce the error, thus it would seem to be isolated to the `Subsystem sftp internal-sftp` parameter.

For more information regarding debugging, inspect the Hestia Nginx log:

```bash
tail -f -s0.1 /var/log/hestia/nginx-error.log
```

Other common causes to check:

- The user's `/home/<user>/.ssh/authorized_keys` file is missing, damaged, or not writable by the user.
- `/home/<user>/.ssh/hst-filemanager-key` has stale ownership or permissions. It should be owned by `hestiaweb`.
- `PubkeyAuthentication` or `AuthorizedKeysFile` in the SSH server configuration prevents the temporary key from being used.
- The SSH service was moved to a new port after the user logged in. Log out and log in again to refresh the stored SFTP port.
- The protected system administrator policy is hiding the file manager while impersonating the protected `admin` account.

After changing SSH configuration, validate it and restart SSH:

```bash
sshd -t
systemctl restart ssh
```

## I changed SSH port and I cannot use the file manager anymore

The SSH port is loaded in a PHP session. Logging out and logging back in will reset the session, fixing the issue.
