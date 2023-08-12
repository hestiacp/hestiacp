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

## I changed SSH port and I cannot use the file manager anymore

The SSH port is loaded in a PHP session. Logging out and logging back in will reset the session, fixing the issue.
