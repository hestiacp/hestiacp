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

```batch
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
