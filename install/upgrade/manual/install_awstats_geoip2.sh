#!/bin/bash
# info: enable GeoIP2 in Awstats
#
# This function enables GeoIP2 location lookup for
# IP addresses that are listed in awstats.

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Includes
# shellcheck source=/usr/local/hestia/func/main.sh
source $HESTIA/func/main.sh
# shellcheck source=/usr/local/hestia/conf/hestia.conf
source $HESTIA/conf/hestia.conf

#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

#check if string already exists
if grep "geoip2" $HESTIA/data/templates/web/awstats/awstats.tpl; then
    echo "Plugin allready enabled"
    exit 0
fi

#----------------------------------------------------------#
#                       Action                             #
#----------------------------------------------------------#

if [ -d /etc/awstats ]; then
    apt-get install make libssl-dev zlib1g-dev libdata-validate-ip-perl
    perl -MCPAN -f -e "GeoIP2::Database::Reader"
    sed -i '/LoadPlugin=\"geoip2_country \/pathto\/GeoLite2-Country.mmdb\"/s/^#//g;s/pathto/usr\/share\/GeoIP/g' /etc/awstats/awstats.conf
    echo "LoadPlugin=\"geoip2_country /usr/share/GeoIP/GeoLite2-Country.mmdb\"" >> $HESTIA/data/templates/web/awstats/awstats.tpl

    for user in $($BIN/v-list-sys-users plain); do
        $BIN/v-rebuild-web-domains $user no
    done
fi

#----------------------------------------------------------#
#                       Hestia                             #
#----------------------------------------------------------#

# Logging
log_history "Enabled GeoIP2 Awstats" '' 'admin'
log_event "$OK" "$ARGUMENTS"

exit 0
