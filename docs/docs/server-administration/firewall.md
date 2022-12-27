# Firewall

::: warning
After every edit or update the firewall, Hestia will clear the current iptables unless the rules are added via Hestia and [custom script](#how-can-i-customize-iptables-rules).
:::

## How can I open or block a port or IP?

1. Navigate to the server settings by clicking the <i class="fas fa-fw fa-cog"><span class="visually-hidden">Server</span></i> icon in the top right.
2. Click the **<i class="fas fa-fw fa-shield-alt"></i> Firewall** button.
3. Click the **<i class="fas fa-fw fa-plus-circle"></i> Add Rule** button.
4. Select the desired action.
5. Select the desired protocol.
6. Enter the port(s) you want this rule to apply to (`0` for all ports).
7. Set the IP this rule applies to (`0.0.0.0/0` for all IPs) or select an IPSet.
8. Optionally describe the rule’s function.
9. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

You can also use the [v-add-firewall-rule](../reference/cli.md#v-add-firewall-rule) command.

## How do I setup an IPSet blacklist or whitelist?

IPSet are large lists of IP addresses or subnets. They can be used for blacklists and whitelists.

1. Navigate to the server settings by clicking the <i class="fas fa-fw fa-cog"><span class="visually-hidden">Server</span></i> icon in the top right.
2. Click the **<i class="fas fa-fw fa-shield-alt"></i> Firewall** button.
3. Click the **<i class="fas fa-fw fa-list"></i> Manage IP lists** button.
4. Click the **<i class="fas fa-fw fa-plus-circle"></i> Add IP list** button.
5. Name your IP list.
6. Select the data source by entering one of the following:
   - URL: `http://ipverse.net/ipblocks/data/countries/nl.zone`
   - Script (with `chmod 755`): `/usr/local/hestia/install/deb/firewall/ipset/blacklist.sh`
   - File: `file:/location/of/file`
   - You can also use one of Hestia’s included sources.
7. Selected the desired IP version (v4 or v6).
8. Choose whether to auto-update the list or not.
9. Click the **<i class="fas fa-fw fa-save"></i> Save** button in the top right.

## How can I customize iptables rules?

::: danger
This is dangerously advanced feature, please make sure you understand what you are doing.
:::

Hestia supports setting custom rules, chains or flags, etc. using script.

Script must be here: `/usr/local/hestia/data/firewall/custom.sh`

1. Create custom.sh: `touch /usr/local/hestia/data/firewall/custom.sh`
2. Make it executable: `chmod +x /usr/local/hestia/data/firewall/custom.sh`
3. Edit it with your favorite editor.
4. Test and make sure it works.
5. To make custom rules persistent, run: `v-update-firewall`

**IMPLICIT PROTECTION:** Before making the rules persistent, if you screw up or lock yourself out of the server, just reboot.

custom.sh example:

```bash
#!/bin/bash

IPTABLES="$(command -v iptables)"

$IPTABLES -N YOURCHAIN
$IPTABLES -F YOURCHAIN
$IPTABLES -I YOURCHAIN -s 0.0.0.0/0 -j RETURN
$IPTABLES -I INPUT -p TCP -m multiport --dports 1:65535 -j YOURCHAIN
```

## My IPSet doesn’t work

An IPSet must contain at least 10 IP or IP ranges.

## Can I combine multiple sources in one?

If you want to combine multiple IP sources together, you can do so by using the following script:

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
