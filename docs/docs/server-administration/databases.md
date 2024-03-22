# PostgreSQL数据库和 MySQL 单点登录

## 如何设置远程数据库服务器

1. 假设您已经启动并运行第二台服务器。
2. 在 Hestia 服务器上运行以下命令（`mysql` 可以替换为 `postgresql`）:

```bash
v-add-database-host mysql new-server.com root password
```

查看数据库名称是否已添加，请运行以下命令：

```bash
v-list-database-hosts
```

## 为什么我不能使用 `http://ip/phpmyadmin/`

出于安全原因，我们决定禁用此选项。 请改用`https://host.domain.tld/phpmyadmin/`。

## 如何创建 PhpMyAdmin 管理账户

将 `myrootusername` 和 `myrootusername_password` 替换为你想设置的名字和密码

```bash
mysql -uroot
mysql > CREATE USER 'myrootusername'@'localhost' IDENTIFIED BY 'myrootusername_password'
mysql > GRANT ALL PRIVILEGES ON *.* TO 'myrootusername'@'localhost' WITH GRANT OPTION
mysql > FLUSH PRIVILEGES
```

## 如何启用对 `http://ip/phpmyadmin/` 的访问

### 对于 Apache2

```bash
nano /etc/apache2/conf.d/ip.conf

# 在两个标签 </VirtualHost> 结束标记之间添加以下代码
IncludeOptional /etc/apache2/conf.d/*.inc

# 重新启动apache2
systemctl restart apache2

# 你也可以在/etc/apache2.conf中添加以下内容
IncludeOptional /etc/apache2/conf.d/*.inc
```

顺便更新添加以下内容，方便你使用配置。

```bash
# 启动apache2
systemctl start apache2

# 停止apache2服务
systemctl stop apache2

# 查看apache2运行状态 运行完按 ctrl + z 结束查看
systemctl status apache2

# 查看apache2版本 
apache2 -v

# 你也可以在/etc/apache2.conf中添加以下内容
IncludeOptional /etc/apache2/conf.d/*.inc
```

### 对于 Nginx

```bash
nano /etc/nginx/conf.d/ip.conf

# 替换以下内容
location /phpmyadmin/ {
  alias /var/www/document_errors/;
  return 404;
}
location /phppgadmin/ {
  alias /var/www/document_errors/;
  return 404;
}

# 插入有以下内容的代码段
include     /etc/nginx/conf.d/phpmyadmin.inc*;
include     /etc/nginx/conf.d/phppgadmin.inc*;
```

## 如何从远程位置连接到数据库

默认情况下，防火墙中禁用到端口 3306 的连接。 打开
防火墙中的端口 3306（[文档](./firewall)），然后编辑 `/etc/mysql/mariadb.conf.d/50-server.cnf`

```bash
nano /etc/mysql/mariadb.conf.d/50-server.cnf

# 将绑定地址设置为以下示例
bind-address = 0.0.0.0
bind-address = "your.server.ip.address"
```

## PhpMyAdmin 单点登录

注意：仅对单个数据库启用 PhpMyAdmin 单点登录。 仅适用于现有数据库凭据的主“PhpMyAdmin”按钮。

### 无法激活 phpMyAdmin 单点登录

确保 API 已启用并正常工作。 Hestia 的 PhpMyAdmin 单点登录功能通过 Hestia API 进行连接。

### 单击 phpMyAdmin 单点登录按钮时，我将转到 phpMyAdmin 的登录页面

自动化有时会导致问题。 通过 SSH 登录并打开 `/var/log/{webserver}/domains/{hostname.domain.tld.error.log` 并查找以下错误消息之一：

- `无法通过 API 连接，请检查 API 连接`
   1. 检查api是否已开启。
   2. 将服务器的公共IP 添加到**服务器设置**中允许的IP。
- `访问被拒绝：安全令牌不匹配`
   1. 禁用然后启用 phpMyAdmin 单点登录。 这将刷新两个密钥。
   2. 如果您位于防火墙或代理后面，您可能需要禁用它并重试。
- `链接已过期`
   1.刷新数据库页面并重试。

## 远程数据库

如果需要，您可以简单地将 Mysql 或 Postgresql 托管在远程服务器上。

添加远程数据库:

```bash
v-add-database-host TYPE HOST DBUSER DBPASS [MAX_DB] [CHARSETS] [TPL] [PORT]
```

例如:

```bash
v-add-database-host mysql db.hestiacp.com root mypassword 500
```

如果您愿意，可以在主机服务器上设置 phpMyAdmin 以允许连接到数据库。 在`/etc/phpmyadmin/conf.d`中创建`01-localhost`文件的副本并更改

```php
$cfg["Servers"][$i]["host"] = "localhost";
$cfg["Servers"][$i]["port"] = "3306";
$cfg["Servers"][$i]["pmadb"] = "phpmyadmin";
$cfg["Servers"][$i]["controluser"] = "pma";
$cfg["Servers"][$i]["controlpass"] = "random password";
$cfg["Servers"][$i]["bookmarktable"] = "pma__bookmark";
```

请确保还创建 phpmyadmin 用户和数据库。

参考 `/usr/local/hestia/install/deb/phpmyadmin/pma.sh`
