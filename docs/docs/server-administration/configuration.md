# 服务器配置

## 我无法登录

对于安装依赖项，我们使用Composer。由于目前无法

以在hestia-php版本下运行它。我们通过/usr/bin.php安装它。制作

确保php主版本中允许proc_open。未来我们展望

在方法中，允许通过hestia-php通过composer进行安装。

## 在哪里可以找到有关配置文件的更多信息？

每个软件的一个好的起点是检查官方文档：

-对于Nginx:[Nginx文档](https://nginx.org/en/docs/)

-对于Apache2：[Apache Docs](http://httpd.apache.org/docs/2.4/)

-对于PHP-FPM：[PHP文档](https://www.php.net/manual/en/install.fpm.configuration.php)

你也可以试试[我们的论坛](https://forum.hestiacp.com)

## 我可以在Cloudflare CDN后面使用HestiaCP吗？

默认情况下，[Cloudflare Proxy](https://developers.cloudflare.com/fundamentals/get-started/reference/network-ports/)仅支持有限数量的端口。这意味着Cloudflare不会转发端口8083，这是Hestia的默认端口。要将Hestia端口更改为Cloudflare将转发的端口，请运行以下命令：

```bash
v-change-sys-port 2083
```

您还可以禁用Cloudflare代理功能。

## 如何从RRD中删除未使用的以太网端口？

```bash
nano /usr/local/hestia/conf/hestia.conf
```

添加以下行：

```bash
RRD_IFACE_EXCLUDE='lo'
```

添加网络端口以逗号,分隔的列表形式

```bash
rm /usr/local/hestia/web/rrd/net/*
systemctl restart hestia
```

## “强制执行子域所有权”政策是什么意思？

在 Hestia <=1.3.5 和 Vesta 中，用户可以从其他用户拥有的域创建子域。 例如，用户 Bob 可以创建“bob.alice.com”，即使“alice.com”归 Alice 所有。 这可能会导致安全问题，因此我们决定添加一项策略来控制这种行为。 默认情况下，该策略处于启用状态。

您可以针对特定域和用户调整策略，例如针对已用于测试的域：

````bash
＃ 启用
v-add-web-domain-allow-users domain.tld
# 禁用
v-delete-web-domain-allow-users domain.tld
````

## 我可以限制对“admin”帐户的访问吗？

在 Hestia 1.3 中，我们可以授予另一个用户管理员访问权限。 在 1.4 中，我们为系统管理员提供了限制对主 **系统管理员** 帐户的访问的选项，以提高安全性。

## 我的服务器IP已更改，我需要做什么？

当服务器IP更改时，您需要运行以下命令，这将重建所有配置文件：

```bash
v-update-sys-ip
```

## 无法绑定地址

在极少数情况下，网络服务可能比 Apache2 和/或 Nginx 慢。 在这种情况下，Nginx或Apache2将拒绝成功启动。 您可以通过查看服务的状态来验证情况是否如此：

```bash
systemctl status nginx

# 输出
nginx: [emerg] bind to x.x.x.x:80 failed (99: cannot assign requested address)
```

或者，对于 Apache2：

```bash
systemctl status httpd

# 输出
(99)Cannot assign requested address: AH00072: make_sock: could not bind to address x.x.x.x:8443
```

以下命令应允许服务分配给不存在的 IP 地址：

```bash
sysctl -w net.ipv4.ip_nonlocal_bind=1
```

## 我无法使用 Zabbix 监控进程

出于安全原因，默认情况下不允许用户监视其他用户的进程。

如果您通过 Zabbix 使用监控来解决该问题，请编辑 `/etc/fstab`

```bash
nano /etc/fstab
```

并将其修改为以下内容，然后重新启动服务器或重新挂载 `/proc`：

```bash
proc /proc proc defaults,hidepid=2,gid=zabbix 0 0
```

## 错误：24：打开的文件太多

如果您看到类似以下的错误：

```bash
2022/02/21 15:04:38 [emerg] 51772#51772: open() "/var/log/apache2/domains/domain.tld.error.log" failed (24: Too many open files)
```

这意味着 Nginx 打开的文件太多。 要解决此问题，请编辑 Nginx 守护进程配置，然后通过运行“systemctl daemon-reload”重新加载守护进程：

```bash
# 复制以下命令打开配置文件/etc/systemd/system/nginx.service.d/override.conf

nano /etc/systemd/system/nginx.service.d/override.conf
```

然后添加以下内容到配置按`ctrl + x`保存`y`确认更新配置文件`n`为不修改保存

```bash
[Service]
LimitNOFILE=65536
```

将其添加到 Nginx 配置文件中(需要小于或等于`LimitNOFILE`)

```bash
# /etc/nginx/nginx.conf
worker_rlimit_nofile 16384
```

使用 `systemctl restart nginx`重新启动 Nginx。

```bash
# 重启nginx服务
systemctl restart nginx
# 停止Nginx服务
systemctl stop nginx
# 启动Nginx服务
systemctl start nginx
# 重新加载Nginx服务
systemctl reload nginx
```

并通过运行验证新的限制：

```bash
cat /proc/ < nginx-pid > /limits.
```
