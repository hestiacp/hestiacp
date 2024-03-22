# 防火墙

::: 警告
每次编辑或更新防火墙后，Hestia 都会清除当前的 iptables，除非通过 Hestia 和 [自定义脚本](# 如何自定义 iptables 规则？) 添加规则。
:::

## 如何打开或阻止端口或 IP？

1. 进入Hestia web 控制面版单击右上角的 <i class="fas fa-fw fa-cog"><span class="visually-hidden">服务器</span></i> 图标导航至服务器设置。
2. 单击 **<i class="fas fa-fw fa-shield-alt"></i> 防火墙** 按钮。
3. 单击**<i class="fas fa-fw fa-plus-circle"></i>添加规则**按钮。
4. 选择所需的操作。
5. 选择所需的协议。
6. 输入您希望应用此规则的端口（“0”表示所有端口）。
7. 设置此规则适用的 IP（所有 IP 为“0.0.0.0/0”）或选择一个 IP 地址。
8. （可选）描述规则的功能。
9. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

您还可以使用 [v-add-firewall-rule](../reference/cli#v-add-firewall-rule) 命令。

## 如何设置 IPSet 黑名单或白名单？

IPSet 是 IP 地址或子网的大型列表。 它们可用于黑名单和白名单。

1. 单击右上角的 <i class="fas fa-fw fa-cog"><span class="visually-hidden">服务器</span></i> 图标导航至服务器设置。
2. 单击 **<i class="fas fa-fw fa-shield-alt"></i> 防火墙** 按钮。
3. 单击**<i class="fas fa-fw fa-list"></i>管理 IP 列表**按钮。
4. 单击 **<i class="fas fa-fw fa-plus-circle"></i> 添加 IP 列表** 按钮。
5. 为您的 IP 列表命名。
6. 通过输入以下内容之一来选择数据源：
    - URL：`http://ipverse.net/ipblocks/data/countries/nl.zone`
    - 脚本（使用 `chmod 755`为脚本赋予权限）：`/usr/local/hestia/install/deb/firewall/ipset/blacklist.sh`
    - 文件：`file:/location/of/file`
    - 您还可以使用 Hestia 包含的来源之一。
7. 选择所需的 IP 版本（v4 或 v6）。
8. 选择是否自动更新列表。
9. 单击右上角的 **<i class="fas fa-fw fa-save"></i> 保存** 按钮。

## 如何自定义 iptables 规则？

::: 危险
这是危险的高级功能，请确保您了解自己在做什么。
:::

Hestia 支持使用脚本设置自定义规则、链或标志等。

脚本必须位于：`/usr/local/hestia/data/firewall/custom.sh`

1. 创建custom.sh：`touch /usr/local/hestia/data/firewall/custom.sh`
2. 使其可执行：`chmod +x /usr/local/hestia/data/firewall/custom.sh`
3. 使用您最喜欢的编辑器对其进行编辑。
4. 测试并确保其有效。
5. 要使自定义规则持久化，请运行：`v-update-firewall`

**隐式保护：** 在使规则生效之前，如果您搞砸了或将自己锁定在服务器之外，只需重新启动即可。

自定义.sh 示例：

```bash
#!/bin/bash

IPTABLES="$(command -v iptables)"

$IPTABLES -N YOURCHAIN
$IPTABLES -F YOURCHAIN
$IPTABLES -I YOURCHAIN -s 0.0.0.0/0 -j RETURN
$IPTABLES -I INPUT -p TCP -m multiport --dports 1:65535 -j YOURCHAIN
```

## 我的 IP 集群不起作用

 IP集群必须包含至少 10 个 IP 或 IP 范围。

## 我可以将多个源合并为一个吗？

如果您想将多个 IP 源组合在一起，可以使用以下脚本来实现：

```bash
#!/bin/bash

BEL=(
	"https://raw.githubusercontent.com/ipverse/rir-ip/master/country/be/ipv4-aggregated.txt"
	"https://raw.githubusercontent.com/ipverse/rir-ip/master/country/nl/ipv4-aggregated.txt"
	"https://raw.githubusercontent.com/ipverse/rir-ip/master/country/lu/ipv4-aggregated.txt"
)

IP_BEL_TMP=$(mktemp)
for i in "${BEL[@]}"; do
	IP_TMP=$(mktemp)
	((HTTP_RC = $(curl -L --connect-timeout 10 --max-time 10 -o "$IP_TMP" -s -w "%{http_code}" "$i")))
	if ((HTTP_RC == 200 || HTTP_RC == 302 || HTTP_RC == 0)); then # "0" because file:/// returns 000
		command grep -Po '^(?:\d{1,3}\.){3}\d{1,3}(?:/\d{1,2})?' "$IP_TMP" | sed -r 's/^0*([0-9]+)\.0*([0-9]+)\.0*([0-9]+)\.0*([0-9]+)$/\1.\2.\3.\4/' >> "$IP_BEL_TMP"
	elif ((HTTP_RC == 503)); then
		echo >&2 -e "\\nUnavailable (${HTTP_RC}): $i"
	else
		echo >&2 -e "\\nWarning: curl returned HTTP response code $HTTP_RC for URL $i"
	fi
	rm -f "$IP_TMP"
done

sed -r -e '/^(0\.0\.0\.0|10\.|127\.|172\.1[6-9]\.|172\.2[0-9]\.|172\.3[0-1]\.|192\.168\.|22[4-9]\.|23[0-9]\.)/d' "$IP_BEL_TMP" | sort -n | sort -mu
rm -f "$IP_BEL_TMP"
```
