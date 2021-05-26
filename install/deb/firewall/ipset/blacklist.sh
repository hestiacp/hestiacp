#!/bin/bash

# Script and blacklist urls partially taken from:
# https://github.com/trick77/ipset-blacklist/blob/master/ipset-blacklist.conf
#

BLACKLISTS=(
    "https://www.projecthoneypot.org/list_of_ips.php?t=d&rss=1" # Project Honey Pot Directory of Dictionary Attacker IPs
    "https://check.torproject.org/cgi-bin/TorBulkExitList.py?ip=1.1.1.1"  # TOR Exit Nodes
    "https://www.maxmind.com/en/high-risk-ip-sample-list" # MaxMind GeoIP Anonymous Proxies
    "http://danger.rulez.sk/projects/bruteforceblocker/blist.php" # BruteForceBlocker IP List
    "https://www.spamhaus.org/drop/drop.lasso" # Spamhaus Don't Route Or Peer List (DROP)
    "https://cinsscore.com/list/ci-badguys.txt" # C.I. Army Malicious IP List
    "https://lists.blocklist.de/lists/all.txt" # blocklist.de attackers
    "https://blocklist.greensnow.co/greensnow.txt" # GreenSnow
    "https://raw.githubusercontent.com/firehol/blocklist-ipsets/master/firehol_level1.netset" # Firehol Level 1
    "https://raw.githubusercontent.com/firehol/blocklist-ipsets/master/stopforumspam_7d.ipset" # Stopforumspam via Firehol
)


IP_BLACKLIST_TMP=$(mktemp)
for i in "${BLACKLISTS[@]}"; do
    IP_TMP=$(mktemp)
    (( HTTP_RC=$(curl -L --connect-timeout 10 --max-time 10 -o "$IP_TMP" -s -w "%{http_code}" "$i") ))
    if (( HTTP_RC == 200 || HTTP_RC == 302 || HTTP_RC == 0 )); then # "0" because file:/// returns 000
        command grep -Po '^(?:\d{1,3}\.){3}\d{1,3}(?:/\d{1,2})?' "$IP_TMP" | sed -r 's/^0*([0-9]+)\.0*([0-9]+)\.0*([0-9]+)\.0*([0-9]+)$/\1.\2.\3.\4/' >> "$IP_BLACKLIST_TMP"
    elif (( HTTP_RC == 503 )); then
        echo >&2 -e "\\nUnavailable (${HTTP_RC}): $i"
    else
        echo >&2 -e "\\nWarning: curl returned HTTP response code $HTTP_RC for URL $i"
    fi
    rm -f "$IP_TMP"
done

sed -r -e '/^(0\.0\.0\.0|10\.|127\.|172\.1[6-9]\.|172\.2[0-9]\.|172\.3[0-1]\.|192\.168\.|22[4-9]\.|23[0-9]\.)/d' "$IP_BLACKLIST_TMP"|sort -n|sort -mu
rm -f "$IP_BLACKLIST_TMP"
