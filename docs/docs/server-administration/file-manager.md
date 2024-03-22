# 文件管理器

## 如何启用或禁用文件管理器

在新安装中，文件管理器将默认启用。

要启用或更新文件管理器，请运行以下命令：

```bash
v-add-sys-filemanager
```

要禁用文件管理器，请运行以下命令：

```bash
v-delete-sys-filemanager
```

## 文件管理器给出“未知错误”消息

当`/etc/ssh/sshd_config`中的`Subsystem sftp /usr/lib/openssh/sftp-server`行被删除或更改时，尤其会发生这种情况，
导致安装脚本无法将其更新为`Subsystem sftp internal-sftp`。将`Subsystem sftp internal-sftp`添加到`/etc/ssh/sshd_config`。
请参阅安装脚本`./install/hst-install-{distro}.sh`，了解对`/etc/ssh/sshd_config`所做的所有更改。 对于 Debian，变化可以总结如下：

```bash
# HestiaCP 更改 Debian 12 中的默认配置 
nano /etc/ssh/sshd_config

# 强制默认yes
PasswordAuthentication yes

# 从默认的2m改为1m
LoginGraceTime 1m

# 从默认的/usr/lib/openssh/sftp-server更改为internal-sftp
Subsystem sftp internal-sftp

# 从默认值更改为 yes
DebianBanner no
```

将所有其他参数更改为默认值并更改为`PasswordAuthentication no`不会重现错误，因此它似乎与`Subsystem sftp internal-sftp`参数隔离。

有关调试的更多信息，请检查 Hestia Nginx 日志：

```bash
tail -f -s0.1 /var/log/hestia/nginx-error.log
```

## 我更改了 SSH 端口，并且无法再使用文件管理器

SSH 端口在 PHP 会话中加载。 注销并重新登录将重置会话，从而解决问题。
