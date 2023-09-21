# Getting Started

This section will help you get Hestia installed on your server. If you already have Hestia installed and are just looking for options, you can skip this page.

::: warning
The installer needs to be run as **root**, either directly from the terminal or remotely, using SSH. If you do not do this, the installer will not proceed.
:::

## Requirements

::: warning
Hestia must be installed on top of a fresh operating system installation to ensure proper functionality.
If on a VPS/KVM, and there is already an admin account, either delete that default admin ID, or use `--force` to continue with the installation. See custom installation below for further details.
:::

|                      | Minimum                                        | Recommended                          |
| -------------------- | ---------------------------------------------- | ------------------------------------ |
| **CPU**              | 1 core, 64-bit                                 | 4 cores                              |
| **Memory**           | 1 GB (no SpamAssassin and ClamAV)              | 4 GB                                 |
| **Disk**             | 10 GB HDD                                      | 40 GB SSD                            |
| **Operating System** | Debian 10, 11 or 12<br>Ubuntu 20.04, 22.04 LTS | Latest Debian <br> Latest Ubuntu LTS |

::: warning
Hestia only runs on AMD64 / x86_64 and ARM64 / aarch64 processors. It also requires a 64bit operating system!
We currently do not support i386 or ARM7-based processors.
:::

### Supported operating systems

- Debian 10, 11 or 12
- Ubuntu 20.04 or 22.04

::: warning
Hestia does not support non-LTS Operating systems. If you install it on, for example, Ubuntu 21.10, you will not receive support from us.
:::

## Regular installation

Interactive installer that will install the default Hestia software configuration.

### Step 1: Download

Download the installation script for the latest release:

```bash
wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh
```

If the download fails due to an SSL validation error, please be sure you've installed the ca-certificate package on your system - you can do this with the following command:

```bash
apt-get update && apt-get install ca-certificates
```

### Step 2: Run

To begin the installation process, simply run the script and follow the on-screen prompts:

```bash
bash hst-install.sh
```

You will receive a welcome email at the address specified during installation (if applicable) and on-screen instructions after the installation is completed to log in and access your server.

## Custom installation

If you want to customise which software gets installed, or want to run an unattended installation, you will need to run a custom installation.

To view a list of available options, run

```bash
bash hst-install.sh -h
```

### List of installation options

::: tip
An easier way to choose your installation options is by using the [Install string generator](/install.md).
:::

To choose what software gets installed, you can provide flags to the installation script. You can view the full list of options below.

```bash
-a, --apache Install Apache [yes | no] default: yes
-w, --phpfpm Install PHP-FPM [yes | no] default: yes
-o, --multiphp Install Multi-PHP [yes | no] default: no
-v, --vsftpd Install Vsftpd [yes | no] default: yes
-j, --proftpd Install ProFTPD [yes | no] default: no
-k, --named Install Bind [yes | no] default: yes
-m, --mysql Install MariaDB [yes | no] default: yes
-M, --mysql-classic Install Mysql8 [yes | no] default: no
-g, --postgresql Install PostgreSQL [yes | no] default: no
-x, --exim Install Exim [yes | no] default: yes
-z, --dovecot Install Dovecot [yes | no] default: yes
-Z, --sieve Install Sieve [yes | no] default: no
-c, --clamav Install ClamAV [yes | no] default: yes
-t, --spamassassin Install SpamAssassin [yes | no] default: yes
-i, --iptables Install Iptables [yes | no] default: yes
-b, --fail2ban Install Fail2ban [yes | no] default: yes
-q, --quota Filesystem Quota [yes | no] default: no
-d, --api Activate API [yes | no] default: yes
-r, --port Change Backend Port default: 8083
-l, --lang Default language default: en
-y, --interactive Interactive install [yes | no] default: yes
-s, --hostname Set hostname
-e, --email Set admin email
-p, --password Set admin password
-D, --with-debs Path to Hestia debs
-f, --force Force installation
-h, --help Print this help
```

#### Example

```bash
bash hst-install.sh \
	--interactive no \
	--hostname host.domain.tld \
	--email email@domain.tld \
	--password p4ssw0rd \
	--lang fr \
	--apache no \
	--named no \
	--clamav no \
	--spamassassin no
```

This command will install Hestia in French with the following software:

- Nginx Web Server
- PHP-FPM Application Server
- MariaDB Database Server
- IPtables Firewall + Fail2Ban Intrusion prevention software
- Vsftpd FTP Server
- Exim Mail Server
- Dovecot POP3/IMAP Server

## What’s next?

By now, you should have a Hestia installation on your server. You are ready to add new users, so that you (or they) can add new websites on your server.

To access your control panel, navigate to `https://host.domain.tld:8083` or `http://your.public.ip.address:8083`
