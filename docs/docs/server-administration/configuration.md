# Server configuration

## I am not able to login

For installing dependencies we use Composer. As are currently not able
to run it under hestia-php version. We install it via /usr/bin/php. Make
sure proc_open is allowed in the main php version. In the future we look
in methods to allow install via composer via hestia-php.

## Can MD5 password hashes be upgraded automatically?

Enable **Security → System → Login → Upgrade MD5 password hashes to yescrypt on login**
in Server Settings, or run:

```bash
v-change-sys-config-value POLICY_SYSTEM_UPGRADE_MD5 yes
```

After a successful control-panel login, including IP restrictions and two-factor
authentication, a legacy MD5-crypt (`$1$`) password hash is replaced with a
randomly salted yescrypt (`$y$`) hash. The password itself and its ageing settings
are unchanged. The Hestia password-hash cache is synchronized and password-reset
tokens are invalidated. This requires `mkpasswd` from the `whois` package with
yescrypt support. If the upgrade fails, no panel session is created.

The option is provisionally disabled by default; an absent setting also means
disabled. Disabling it preserves existing authentication behavior and does not
downgrade passwords already upgraded:

```bash
v-change-sys-config-value POLICY_SYSTEM_UPGRADE_MD5 no
```

Only interactive control-panel login triggers migration. API authentication,
mail accounts, DES hashes, locked accounts, and existing SHA-512/yescrypt hashes
are not migrated.

## Can Dovecot upgrade MD5 mailbox passwords on login?

This is a separate option under **Server Settings → Mail Server → Upgrade MD5
mailbox password hashes on login**. It is provisionally disabled by default.
Enable or disable it with:

```bash
v-change-sys-mail-password-upgrade yes
v-change-sys-mail-password-upgrade no
```

Use this dedicated command rather than setting `POLICY_MAIL_UPGRADE_MD5`
directly: it installs/removes the authentication hook, validates the Dovecot
configuration, restarts Dovecot, and rolls back if applying the change fails.
Changing the setting can disconnect existing mail sessions. On systemd,
it also manages `/etc/systemd/system/dovecot.service.d/hestia-password-upgrade.conf`.
This grants writes only to Hestia's user metadata and stable migration lock
below `/usr/local/hestia/data`, leaving `ProtectSystem` enabled and panel code
read-only. A service restart, not just a reload, is required to apply or remove
this write exception.
It requires local Dovecot 2.3 or 2.4 with Lua passdb support, a root auth-worker,
and the Hestia PHP runtime (8.1+ with `fsync` and `proc_open`). It does not install
additional packages when the setting is changed. New installations include
`dovecot-auth-lua` when Dovecot is enabled and the distribution provides that
separate package. Existing installations may need to install it before enabling
migration; older distributions may link Lua authentication into `dovecot-core`.

For a Hestia-managed, unsuspended mailbox with a correct MD5-crypt password, the
hook replaces the old hash in both the mailbox password file and Hestia's mail
metadata. It prefers Argon2id when supported by both Dovecot and Hestia PHP,
otherwise bcrypt. It never falls back to MD5 or silently truncates passwords
to bcrypt's 72-byte limit. Existing modern hashes are left alone.

The hook runs for PLAIN/LOGIN authentication through Dovecot, including IMAP,
POP3, webmail, and Exim SMTP AUTH when using Hestia's Dovecot authenticator.
Master-user logins and non-authenticating lookups do not migrate passwords.
After the preflight, the normal Dovecot passwd-file backend still performs
authentication and its usual checks. Custom passdb chains are not supported.
Plaintext passwords travel only over a bounded pipe, not command arguments,
environment variables, logs, or temporary files.

This feature does **not** globally enable `auth_allow_weak_schemes`. On Dovecot
2.4, disabling migration therefore leaves its normal weak-hash rejection in
place; on 2.3 it preserves existing behavior. Disabling does not downgrade
already migrated hashes. If migration fails, authentication fails rather than
accepting a weak hash without upgrading it.

Dovecot's passwd-file backend checks file changes only once per second, even
with its authentication cache disabled. After a migration, and briefly for other
logins using a recently changed mailbox file, the preflight waits about 1.1
seconds before continuing to that backend. This prevents a successful migration
from being rejected against an older cached hash. No migration locks are held
during this wait; unchanged older mailbox files add no such delay.

The two password files are replaced atomically individually, with Hestia's
metadata committed first so a rebuild cannot restore the obsolete hash. An
interrupted migration between the two replacements can recover on a subsequent
login with the same password. Concurrent migrations are serialized, and changes
observed during hashing cause the attempt to fail without overwriting them.
Hestia mail-changing commands (including password resets, suspension, rebuild,
restore, import, and ownership transfer) hold a shared administrative lock.
Migration takes that lock exclusively for its final state check and both file
replacements, so it cannot overwrite a completed administrative update. Hashing
does not hold the administrative lock. Lock waits are bounded: a busy system
can reject that login attempt rather than write stale data. Custom scripts that
edit mailbox metadata or runtime password files must use the same coordination
protocol; direct file edits are not covered.
The administrative lock file is `$HESTIA/data/.mail-password-upgrade.lock`;
do not replace or remove it while Hestia commands or authentication are running.

## Where can I find more information about the config files?

A good starting point for every software is to check the official docs:

- For Nginx: [NGINX Docs](https://nginx.org/en/docs/)
- For Apache2: [Apache Docs](http://httpd.apache.org/docs/2.4/)
- For PHP-FPM: [PHP Docs](https://www.php.net/manual/en/install.fpm.configuration.php)

You could also try [our Forum](https://forum.hestiacp.com)

## Can I use HestiaCP behind Cloudflare CDN?

By default the [Cloudflare Proxy](https://developers.cloudflare.com/fundamentals/get-started/reference/network-ports/) supports only a limited number of ports. This means that Cloudflare will not forward port 8083, which is the default port for Hestia. To change the Hestia port to one that Cloudflare will forward, run this command:

```bash
v-change-sys-port 2083
```

You can also disable Cloudflare proxy feature.

## How to remove unused ethernet ports from RRD?

```bash
nano /usr/local/hestia/conf/hestia.conf
```

Add the following line:

```bash
RRD_IFACE_EXCLUDE='lo'
```

Add network ports as comma separated list

```bash
rm /usr/local/hestia/web/rrd/net/*
systemctl restart hestia
```

## What does the “Enforce subdomain ownership” policy mean?

In Hestia <=1.3.5 and Vesta, it was possible for users to create subdomains from domains that were owned by other users. For example, user Bob could create `bob.alice.com`, even if `alice.com` is owned by Alice. This could cause security issues and therefore we have decided to add a policy to control this behaviour. By default, the policy is enabled.

You can tweak the policy for a specific domain and user, for example for a domain that has been used for testing:

```bash
# to enable
v-add-web-domain-allow-users user domain.tld
# to disable
v-delete-web-domain-allow-users user domain.tld
```

## Can I restrict access to the `admin` account?

In Hestia 1.3, we have made it possible to give another user Administrator access. In 1.4, we have given system administrators the option to limit access to the main **System Administrator** account to improve security.

## My server IP has changed, what do I need to do?

When a server IP changes, you need to run the following command, which will rebuild all config files:

```bash
v-update-sys-ip
```

## Unable to bind address

In rare cases the network service might be slower than Apache2 and or Nginx. In that case, Nginx or Apache2 will refuse to successfully start. You can verify that this is the case by looking at the service’s status:

```bash
systemctl status nginx

# Output
nginx: [emerg] bind to x.x.x.x:80 failed (99: cannot assign requested address)
```

Or, in case of Apache2:

```bash
systemctl status httpd

# Output
(99)Cannot assign requested address: AH00072: make_sock: could not bind to address x.x.x.x:8443
```

The following command should allow services to assign to non existing IP addresses:

```bash
sysctl -w net.ipv4.ip_nonlocal_bind=1
```

## I am unable to monitor processes with Zabbix

For security reasons, users are not allowed to monitor processes from other users by default.

To solve the issue if you use monitoring via Zabbix, edit `/etc/fstab` and modify it to the following, then reboot the server or remount `/proc`:

```bash
proc /proc proc defaults,hidepid=2,gid=zabbix 0 0
```

## Error: 24: Too many open files

If you see an error similar to this:

```bash
2022/02/21 15:04:38 [emerg] 51772#51772: open() "/var/log/apache2/domains/domain.tld.error.log" failed (24: Too many open files)
```

It means that there are too many open files with Nginx. To resolve this issue, edit the Nginx daemon config, then reload the daemons by running `systemctl daemon-reload`:

```bash
# /etc/systemd/system/nginx.service.d/override.conf
[Service]
LimitNOFILE=65536
```

Add this to the Nginx config file (Needs to be smaller or equal to `LimitNOFILE`)

```bash
# /etc/nginx/nginx.conf
worker_rlimit_nofile 16384
```

Restart Nginx with `systemctl restart nginx`, and verify the new limits by running:

```bash
cat /proc/ < nginx-pid > /limits.
```
