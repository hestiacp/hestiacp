# Server configuration

## I am not able to login

For installing dependencies we use Composer. As are currently not able
to run it under hestia-php version. We install it via /usr/bin/php. Make
sure proc_open is allowed in the main php version. In the future we look
in methods to allow install via composer via hestia-php.

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

## Can I run the control panel behind a reverse proxy?

Yes. By default PHP sees the address of the proxy as the client IP. To make the panel use the real client IP, tell the panel's Nginx (`hestia-nginx`) which proxies to trust. Create a file in `/etc/hestiacp/nginx.d/` (the directory is not created by Hestia, and files in it survive updates):

```bash
mkdir -p /etc/hestiacp/nginx.d
```

```nginx
# /etc/hestiacp/nginx.d/realip.conf
set_real_ip_from  192.0.2.10;
set_real_ip_from  2001:db8::10;
real_ip_header    X-Forwarded-For;
real_ip_recursive on;
```

Check the configuration and restart the panel (this also restarts `hestia-php`):

```bash
/usr/local/hestia/nginx/sbin/hestia-nginx -t && systemctl restart hestia
```

Notes:

- Enabling this logs out existing panel sessions once, because the session is bound to the client IP and that changes from the proxy address to the real client address. Users just log in again. phpMyAdmin single sign-on is not fixed in every proxy setup by this alone: `hestia-sso.php` builds the client IP from the forwarding headers itself instead of using the panel's real IP handling (see upstream PR [#5526](https://github.com/hestiacp/hestiacp/pull/5526)).
- Only list the exact addresses of your own proxies in `set_real_ip_from`. Never trust a wide range or `0.0.0.0/0`, or any visitor can spoof their IP. Both IPv4 and IPv6 addresses are supported; add one `set_real_ip_from` line for each proxy address. Use a header that your proxy always overwrites (or removes and sets again). With `X-Forwarded-For` keep `real_ip_recursive on`; with a single-value header such as `X-Real-IP` you can use `real_ip_header X-Real-IP;`.
- This requires `hestia-nginx` from a Hestia release that includes `ngx_http_realip_module`. Older `hestia-nginx` builds ignore the file (their `nginx.conf` has no include), so `-t` passes but nothing changes. Check with `hestia-nginx -V 2>&1 | grep -o http_realip_module` and `grep nginx.d /usr/local/hestia/nginx/conf/nginx.conf`.

::: warning
If you trust `127.0.0.1`, `::1` or the server's own IP (a proxy on the same host, or Cloudflare Tunnel with `cloudflared` running on the server), every local process, including hosted users' PHP scripts and cron jobs, can connect to port 8083 and send any client IP. That can get arbitrary IPs banned by Fail2Ban and can satisfy `LOGIN_ALLOW_IPS` and `API_ALLOWED_IP`. Prefer a proxy on a separate host or address, or restrict who can connect to port 8083 (for example with an iptables `owner` match that only lets the `cloudflared` user connect to it).

Unless the proxy is Cloudflare, it must also remove the `CF-Connecting-IP` header from incoming requests, because the panel honors it for clients that appear to come from Cloudflare addresses.
:::

::: warning
Fail2Ban and the firewall cannot protect the panel behind a proxy, because the connections to port 8083 are made by the proxy. Failed logins are logged with the real client IP, so the `hestia-iptables` jail bans that IP, but this has no effect on connections coming from the proxy. Block abusive clients on the proxy itself (for example with an allow list, a rate limit or CrowdSec).

Without a working real IP setup (for example if the proxy does not send the header), failed logins are logged with the proxy address, and after 5 of them the jail bans the proxy and locks everybody out of the panel. As a safeguard, add the proxy to `ignoreip` for the Hestia jail only, in `/etc/fail2ban/jail.d/hestia-proxy.local`:

```ini
[hestia-iptables]
ignoreip = 127.0.0.1/8 ::1 192.0.2.10 2001:db8::10
```

Then reload Fail2Ban:

```bash
fail2ban-client reload
```

:::

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
