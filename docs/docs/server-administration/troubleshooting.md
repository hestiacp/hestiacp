# Troubleshooting

## Command not found when I try to run a v-command as root

Add to /root/.bashrc the following code:

```bash
if [ "${PATH#*/usr/local/hestia/bin*}" = "$PATH" ]; then
	. /etc/profile.d/hestia.sh
fi
```

And logout and login again.

After that you are able to run any v-command you want.

## Disabling “Use IP address allow list for login attempts” via command line

With the introduction of Hestia v1.4.0 we have added certain security features, including the possibility to limit login to certain IP addresses. If your IP address changes, you will not able to login anymore. To disable this feature, run the following commands:

```bash
# Disable the feature
v-change-user-config-value admin LOGIN_USE_IPLIST 'no'
# Remove listed IP addresses
v-change-user-config-value admin LOGIN_ALLOW_IPS ''
```

## Can I update my cronjobs via `crontab -e`?

No, you cannot. When you update HestiaCP, the crontab will simply get overwritten. The changes will not get saved in backups either.

## After update Apache2 I am not able to restart Apache2 or Nginx

The error message states (98) Address already in use: AG0072: make_sock: could not bind to address 0.0.0.0:80

When a package update sometimes comes with a new config and probally it has been overwritten...

```bash
Configuration file '/etc/apache2/apache2.conf'
 ==> Modified (by you or by a script) since installation.
 ==> Package distributor has shipped an updated version.
   What would you like to do about it ?  Your options are:
	Y or I  : install the package maintainer's version
	N or O  : keep your currently-installed version
	  D     : show the differences between the versions
	  Z     : start a shell to examine the situation
 The default action is to keep your current version.
*** apache2.conf (Y/I/N/O/D/Z) [default=N] ?
```

If you see this message **ALWAYS** press "N" or **ENTER** to select the default value!

How ever if you entered Y or I. Then replace the config that can be found in /root/hst_backups/xxxxx/conf/apache2/ folder and copy over apache2.conf and ports.conf to /etc/apache2/ folder

xxxxxx is the date/time the backup is made during the last update of HestiaCP

If you don't have have a backup made you can also copy the config in /usr/local/hestia/install/deb/apache2/apache2.conf to /etc/apache2.conf and also empty /etc/apache2/ports.conf

## Unable to bind adress

In rare cases the network service might be slower than Apache2 and or Nginx. In that case Nginx or Apache2 will refuse to start up successfully start.

```bash
systemctl status nginx
```

Will create the error an error

```bash
nginx: [emerg] bind to x.x.x.x:80 failed (99: cannot assign requested address)
```

or in case of Aapche2

```bash
(99)Cannot assign requested address: AH00072: make_sock: could not bind to address x.x.x.x:8443
```

The following command should allow services to assign to non existing ip addresses

```bash
sysctl -w net.ipv4.ip_nonlocal_bind=1
```

## Error: 24: Too many open files

```bash
2022/02/21 15:04:38 [emerg] 51772#51772: open() "/var/log/apache2/domains/<redactedforprivacy>.error.log" failed (24: Too many open files)
```

or

```bash
2022/02/21 15:04:38 [emerg] 2724394#2724394: open() "/var/log/nginx/domains/xxx.error.log" failed (24: Too many open files)
```

This error means that there are to many open files with Nginx. To resolve this issue:

/etc/systemd/system/nginx.service.d/override.conf

```bash
[Service]
LimitNOFILE=65536
```

Then run:

```bash
systemctl daemon-reload
```

Add this to the Nginx config file (Needs to be smaller or equal to LimitNOFILE!)

```bash
worker_rlimit_nofile 16384
```

And then restart nginx with systemctl restart nginx

To verifiy run:

```bash
cat /proc/ < nginx-pid > /limits
```
