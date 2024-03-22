# 故障排除

## 当我尝试以 root 身份运行 v 命令时找不到命令

在/root/.bashrc中添加以下代码：

```bash
if [ "${PATH#*/usr/local/hestia/bin*}" = "$PATH" ]; then
	. /etc/profile.d/hestia.sh
fi
```

并注销并再次登录。

之后您就可以运行任何您想要的 v 命令。

## 通过命令行禁用“使用 IP 地址允许列表进行登录尝试”

随着 Hestia v1.4.0 的推出，我们添加了某些安全功能，包括限制登录某些 IP 地址的可能性。 如果您的 IP 地址发生变化，您将无法再登录。 要禁用此功能，请运行以下命令：

```bash
# 禁用该功能
v-change-user-config-value admin LOGIN_USE_IPLIST 'no'
# 删除列出的IP地址
v-change-user-config-value admin LOGIN_ALLOW_IPS ''
```

## 我可以通过 `crontab -e` 更新我的 cronjobs 吗？

你不能。 当您更新 HestiaCP 时，crontab 将被覆盖。 更改也不会保存在备份中。

## 更新 Apache2 后，我无法重新启动 Apache2 或 Nginx

错误消息指出 (98) 地址已在使用中：AG0072: make_sock: 无法绑定到地址 0.0.0.0:80

当软件包更新有时附带新配置并且可能已被覆盖时......

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

如果您看到此消息**始终**，请按`N`或**ENTER** 选择默认值！

但是，如果您输入` Y `或` I `。然后替换 `/root/hst_backups/xxxxx/conf/apache2/` 文件夹中的配置，并将 `apache2.conf` 和 `ports.conf` 复制到 `/etc/apache2/` 文件夹

如果您没有备份，您也可以将 `/usr/local/hestia/install/deb/apache2/apache2.conf` 中的配置复制到 `/etc/apache2.conf` 并清空 /`etc/apache2/ports .conf`

## 无法绑定地址

在极少数情况下，网络服务可能比 Apache2 和/或 Nginx 慢。 这种情况下Nginx或者Apache2会拒绝启动而无法成功启动。

```bash
systemctl status nginx
```

导致会造成错误的错误

```bash
nginx: [emerg] bind to x.x.x.x:80 failed (99: cannot assign requested address)
```

或者如果是 Aapche2

```bash
(99)Cannot assign requested address: AH00072: make_sock: could not bind to address x.x.x.x:8443
```

以下命令应允许服务分配给不存在的 IP 地址

```bash
sysctl -w net.ipv4.ip_nonlocal_bind=1
```

## 错误：24：打开的文件太多

```bash
2022/02/21 15:04:38 [emerg] 51772#51772: open() "/var/log/apache2/domains/<redactedforprivacy>.error.log" failed (24: Too many open files)
```

或者

```bash
2022/02/21 15:04:38 [emerg] 2724394#2724394: open() "/var/log/nginx/domains/xxx.error.log" failed (24: Too many open files)
```

这个错误意味着 Nginx 打开的文件过多。 为了解决这个问题:

/etc/systemd/system/nginx.service.d/override.conf

```bash
[Service]
LimitNOFILE=65536
```

然后运行:

```bash
systemctl daemon-reload
```

将其添加到 Nginx 配置文件中（需要小于或等于 LimitNOFILE！）

```bash
worker_rlimit_nofile 16384
```

然后用

```bash
systemctl restart nginx
```

重新启动nginx

要验证运行：

```bash
cat /proc/ < nginx-pid > /limits
```
