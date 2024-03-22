# DNS 集群和 DNSSEC

::: 信息
随着1.7.0版本的发布，我们实现了对DNSSEC的支持。 DNSSEC 需要主站 -> 从站设置。 如果现有实现是主<->主设置，则不支持。 DNSSEC 还至少需要 Ubuntu 22.04 或 Debian 11！
:::

## 在 Hestia 上托管您的域的 DNS

先决条件

这些步骤要求您配置域的 DNS 服务器以使用 Hestia 服务器。

- 请注意，大多数域名提供商需要配置两个或更多 DNS 服务器。
- 名称服务器很可能需要注册为“粘合记录”
- 您可能需要等待最多 24 小时，名称服务器才可用

准备域名和 DNS

1. 在您的 Hestia master 上，使用 **child-ns** 模板[创建一个 DNS 区域](../user-guide/dns#adding-a-dns-zone)
2. 在您的域名注册商面板上，将域的名称服务器设置为 Hestia 服务器

如果您正在寻找最小化与 DNS 相关的停机时间的选项，或者寻找一种在所有服务器之间自动同步 DNS 区域的方法，您可以考虑设置 DNS 集群。

如果 DNSSEC 对您很重要，那么您必须使用 Master -> Slave。 但是，如果您想将区域添加到任一服务器并将它们复制到另一台服务器，则配置为主<->主。

::: 提示
如果您刚刚设置从站，请检查主机名是否解析以及您是否拥有有效的 SSL 证书
:::

## DNS 集群设置

主服务器是创建 DNS 区域的地方，从服务器通过 API 接收区域。 Hestia 可以配置为 Master <-> Master 或 Master -> Slave。 对于 Master <-> Master 配置，每个 Master 也是一个 Slave，因此可以将其视为 Master/Slave <-> Master/Slave。

在每个从属服务器上，需要一个唯一的用户来分配区域，并且必须分配“同步 DNS 用户”或“dns-cluster”角色。

::: 信息
随着 1.6.0 的发布，我们实施了新的 API 访问密钥身份验证系统。 我们强烈建议使用此方法而不是以前的用户名/密码系统，因为由于访问密钥和秘密密钥的长度，它更安全！

如果您仍想使用旧版 API 通过 **admin** 用户名和密码进行身份验证，请确保**启用旧版 API** 访问权限设置为 **yes**。
:::

### 主服务器和附服务器 DNS 集群（默认设置）与 Hestia API

::: 警告
此方法不支持 DNSSEC！
:::

1. 在每台 Hestia 服务器上创建一个新用户，充当“从属”。 确保它使用“dns-cluster”用户名或具有“dns-cluster”角色
2. 运行以下命令启用DNS服务器.

```bash
v-add-remote-dns-host slave.yourhost.com 8083 'accesskey:secretkey' '' 'api' 'username'
```

或者如果您仍然想使用管理员和密码身份验证（不推荐）

```bash
v-add-remote-dns-host slave.yourhost.com 8083 'admin' 'strongpassword' 'api' 'username'
```

这样您就可以设置 Master -> Slave 或 Master <-> Master <-> Master 集群。

对于如何链接 DNS 服务器没有限制。

### 主服务器 -> 使用 Hestia API 的附主机 DNS 集群

准备您的**从属**服务器：

1. 在**配置服务器** -> **安全** -> **API 允许的 IP 地址**中将您的主服务器 IP 列入白名单
2. 为管理员（或所有用户）启用 API 访问。
3. 在至少具有 **sync-dns-cluster** 权限的 **admin** 用户下创建 API 密钥。
4. 创建一个新的 DNS 同步用户，如下所示：
    - 有电子邮件地址（通用的）
    - 具有“dns-cluster”角色
    - 如果他们不是普通用户，您可能需要设置“不允许用户登录控制面板”
5. 编辑 `/usr/local/hestia/conf/hestia.conf`，将 `DNS_CLUSTER_SYSTEM='hestia'` 更改为 `DNS_CLUSTER_SYSTEM='hestia-zone'`。
6. 编辑 `/etc/bind/named.conf.options`，进行以下更改，然后使用 `systemctl restart bin9` 重新启动bind9:

   ```bash
   # 修改这一行
   allow-recursion { 127.0.0.1; ::1; };
   # 例如
   allow-recursion { 127.0.0.1; ::1; your.master.ip.address; };
   # 添加这一行
   allow-notify{ your.master.ip.address; };
   ```

准备您的**主**服务器：

1. 在 **Master** 服务器上，打开 `/etc/bind/named.conf.options`，执行以下更改，然后使用 `systemctl restart bind9` 重新启动 bind9.

   ```bash
   # 修改这一行
   allow-transfer { "none"; };
   # 例如
   allow-transfer { your.slave.ip.address; };
   # 或者这样，假如添加多个附站
   allow-transfer { first.slave.ip.address; second.slave.ip.address; };
   # 假如添加多个附站，请添加此行
   also-notify { second.slave.ip.address; };
   ```

2. 运行以下命令启用每个 Slave DNS 服务器，并等待一段时间以使其完成区域传输：

   ```bash
   v-add-remote-dns-host <your slave host name> <port number> '<accesskey>:<secretkey>' '' 'api' '<your chosen slave user name>'
   ```

   如果您仍想使用管理员和密码身份验证（不推荐）:

   ```bash
   v-add-remote-dns-host slave.yourhost.com 8083 'admin' 'strongpassword' 'api' 'user-name'
   ```

3. 通过使用 CLI 命令 `v-list-dns-domains dns-user` 列出 dns-user 的 **Slave** 上的 DNS 区域或以 dns-user 身份连接到 Web iterface 来检查其是否正常工作 检查 DNS 区域。

### 将现有 DNS 集群转换为 Master -> Slave

1. 在 `/usr/local/hestia/conf/hestia.conf` 中，将 `DNS_CLUSTER_SYSTEM='hestia'` 更改为 `DNS_CLUSTER_SYSTEM='hestia-zone'`。
2. 在主服务器上，打开`/etc/bind/named.options`，执行以下更改，然后使用`systemctl restart bind9`重新启动bind9.

   ```bash
   # 修改这一行
   allow-transfer { "none"; };
   # 例如
   allow-transfer { your.slave.ip.address; };
   # 或者这样，假如添加多个附站
   allow-transfer { first.slave.ip.address; second.slave.ip.address; };
   # 假如添加多个附站，请添加此行
   also-notify { second.slave.ip.address; };
   ```

3. 在从服务器上，打开`/etc/bind/named.options`，进行以下更改，然后使用`systemctl restart bin9`重新启动bind9:

   ```bash
   # 修改这一行
   allow-recursion { 127.0.0.1; ::1; };
   # 例如
   allow-recursion { 127.0.0.1; ::1; your.master.ip.address; };
   # 请添加此行
   allow-notify{ your.master.ip.address; };
   ```

4. 使用`v-sync-dns-cluster`更新 DNS

## 启用 DNSSEC

::: 警告
当 Hestia Cluster 作为 Master <-> Master 处于活动状态时，无法使用 DNSSEC
:::

要启用 DNSSEC，请选中 DNSSEC 前面的复选框并保存。

查看公钥。 进入 DNS 域列表并单击 <i class="fas fas-key"></i> 图标。

您可以根据 DNSKEY 或 DS 密钥创建新记录，具体取决于您的注册商。 将 DNSSEC 公钥添加到注册商后，DNSSEC 就会启用并生效。

：：： 危险
删除或禁用 Hestia 中的私钥将使域无法访问。
:::

## 常见问题解答和故障排除

### 我可以按用户分隔 DNS 帐户吗

您可以在命令末尾提供用户变量。

````bash
v-add-remote-dns-host slave.yourhost.com 8083 'access_key:secret_key' '' '' 'username'```
````

or

```bash
v-add-remote-dns-host slave.yourhost.com 8083 admin p4sw0rd '' 'username'
```

使用新的API系统，您还可以将`api_key`替换为`access_key:secret_key`

::: 信息
默认情况下，用户“dns-cluster”或具有“dns-cluster”角色的用户可以免于同步到其他 DNS 服务器！
:::

### 我无法添加服务器作为 DNS 主机

尝试为集群添加 DNS 服务器时出现以下错误:

```bash
/usr/local/hestia/func/remote.sh: line 43: return: Error:: numeric argument required
Error: api connection to slave.domain.tld failed
```

默认情况下，禁用非本地 IP 地址的 API 访问。 在您的 **附站** 上，将 **主站** 的 IP 地址添加到服务器设置 -> 配置 -> 安全 -> 系统 -> API 允许的 IP 地址中的 **API 允许的 IP 地址** 字段 然后按保存.
