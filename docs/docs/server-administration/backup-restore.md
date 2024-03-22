# 备份与恢复

## 如何将用户移至新服务器？

当前的恢复功能接受由 VestaCP 生成的备份
和赫斯提亚CP。

1. 在旧服务器上创建用户备份。

   ```bash
   v-backup-user username
   ```

2. 将生成的 tar.gz备份文件复制到新服务器并将其放置在 `/backup`中.

   ```bash
   scp /backup/username.2020.01.01-00-00.tar root@host.domain.tld:/backup/
   ```

3. 在新服务器上恢复备份。 您可以通过更改命令中的用户名来恢复到其他用户。

   ```bash
   v-restore-user username username.2020.01.01-00-00.tar
   ```

## 可以恢复哪些类型的备份？

目前 HestiaCP 仅支持恢复使用以下方式创建的备份：

1. HestiaCP
2. VestaCP

## 如何编辑备份数量？

要编辑备份数量，请阅读 [Packages](../user-guide/packages) 和 [Users](../user-guide/users) 文档。

您将需要创建或编辑包，并将其分配给所需的用户。

## 没有足够的磁盘空间来执行备份

出于安全原因，Hestia 在创建备份时会考虑 2 倍的用户磁盘使用量。 因此，在开始备份之前，我们会检查用户剩余的磁盘使用量。

如果遇到此错误，您可以执行以下操作之一来解决问题：

- 减少每个用户保存的备份量。
- 将备份移动到远程存储。
- 将备份文件夹移动到其他驱动器。
- 将用户拆分为多个用户。
- 从备份中排除某些文件夹或邮件帐户。

## zstd 和 gzip 有什么区别

zstd 是 Facebook 开发的，作为 gzip 的替代品。 在我们的测试过程中，我们发现与 gzip 相比，速度显着提高，磁盘空间使用量降低。

有关更多信息，请参阅 [zstd 存储库](https://github.com/facebook/zstd)。

## 最佳压缩比是多少

数字越高，压缩比越好。 在我们的测试过程中，我们发现 zstd 级别 3 与磁盘空间级别 9 类似，但速度要快得多。 zstd level 11 花费了大约相同的时间，但给了我们更小的尺寸。 切勿使用高于 19 的级别，因为 zstd 会变得非常慢。

## 目前支持哪些协议

目前支持的备份协议有：

- 文件传输协议
- SFTP
- Rclone，支持多达 50 个不同的云提供商。 [参见Rclone文档](https://rclone.org)

## 如何设置FTP备份服务器

通过 SSH 登录并以 root 身份运行以下命令：

```bash
v-add-backup-host 'ftp' 'remote.ftp-host.tld' 'backup-user' 'p4ssw0rd' '/path-backups/' 'port'
```

### 如何设置SFTP备份服务器

::: 警告
请注意，密码以 **纯文本** 形式存储在服务器上。 它们只能由 root 用户访问，但如果您想使用更安全的身份验证方法，请使用公钥和私钥。
:::

通过 SSH 登录并以 root 身份运行以下命令：

```bash
v-add-backup-host 'sftp' 'remote.ftp-host.tld' 'backup-user' 'p4ssw0rd' '/path-backups/' 'port'
```

如果使用公钥和私钥（推荐）：

```bash
v-add-backup-host 'sftp' 'remote.ftp-host.tld' 'backup-user' '/root/id_rsa' '/path-backups/' 'port'
```

## 如何设置 Rclone

::: 提示
初始配置只能通过 CLI 完成。 之后，您可以通过网络面板更新设置。
:::

首先，[下载 Rclone](https://rclone.org/downloads/)。 最简单的方法是运行以下命令：

```bash
sudo -v 
curl https://rclone.org/install.sh | sudo bash
```

下载和安装完成后，以`root`用户身份运行`rclone config`，然后选择选项`n`。 按照屏幕上的说明进行操作，完成后保存。
要验证它是否按预期运行：

```bash
echo "test" > /tmp/backuptest.txt
rclone cp /tmp/backuptest.txt $HOST:$FOLDER/backuptest.txt
rclone lsf $HOST:$FOLDER
```

并且看到文件已经上传了

```bash
rclone delete $HOST:$FOLDER/backuptest.txt
```

保存配置后，您可以使用以下命令设置 Hestia面板：

```bash
v-add-backup-host 'rclone' 'remote-name' '' '' 'Bucket or Folder name' ''
```

::: 提示
每个服务器的配置可能不同！ 在备份它之前，请确保测试它是否正常工作。 要验证它是否有效，请运行

```bash
v-backup-user admin
```

:::

例如：

```bash
rclone config

Current remotes:

Name Type
==== ====
r2 s3
```

要使用“R2”端点备份，请使用

```bash
v-add-backup-host 'rclone' 'r2' '' '' 'folder'
```

供 [Backblaze B2云存储](https://www.backblaze.com) 服务使用

```bash
v-add-backup-host 'rclone' 'b2' '' '' 'hestiacp'
```

## 如何更改默认备份文件夹

出于安全原因，不允许使用符号链接。 要更改默认备份文件夹，您可以执行以下操作：

1. 确保备份文件夹当前设置为`/backup`。
2. 如果其中有内容，请将其删除并重新创建。 您可以使用 FTP 客户端或在控制台中输入`mkdir /backup`。
3. 使用“mount”将所需文件夹安装到`/backup`：

   ```bash
   mount --bind /path/to/new/backup/folder /backup
   ```

对于永久修复，您应该向`fstab`中添加一条记录，以便在系统启动时安装此文件夹：

1. 打开`/etc/fstab`。

   ```bash
   nano /etc/fstab
   ```

2. 在末尾添加以下行：

   ```bash
   /path/to/new/backup/folder /backup none defaults,bind 0 0
   ```

3. 保存文件。

## 如何提取.zstd文件

按照以下说明进行操作，或使用WinRAR 6.10或更高版本来解压缩.zst文件。

### 如何使用zstd.exe在windows中提取domain_data.tar.zst

1. 下载并解压zstd.exe。可在[zstd GitHub](https://github.com/facebook/zstd/releases/)上找到.

2. 要解压缩备份，请使用以下命令：

   ```batch
   {dir_to_zstd}\zstd.exe -o {dir_to_file}\{file}.tar.zst
   ```

   例如

   ```batch
   C:\Users\{user}\Downloads\zstd-v1.4.4-win64\zstd.exe -d c:\Users\{user}\Downloads\admin.2021-06-27_05-48-23\web\{domain}\domain_data.tar.zst
   ```

   输出

   ```batch
   C:\Users\{user}\Downloads\admin.2021-06-27_05-48-23\web\{domain}\domain_data.tar.zst: 61440 bytes
   ```

3. 使用您喜欢的程序来解开生成的tarball，您就完成了。在本例中，tar 被输出到示例中

   ```batch
   C:\Users\{user}\Downloads\admin.2021-06-27_05-48-23\web\{domain}\domain_data.tar
   ```
