#!/bin/bash
# info: enable GeoIP Awstats 
#
# The function enables resolving IP addresses  with the use of GeoIP database


#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Includes
source $HESTIA/func/main.sh
source $HESTIA/conf/hestia.conf


#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

#check if string already exists
if grep "geoip" $HESTIA/data/templates/web/awstats/awstats.tpl; then 
    echo "Plugin allready enabled"
    exit 0
fi

#----------------------------------------------------------#
#                       Action                             #
#----------------------------------------------------------#

if [ -d /etc/awstats ]; then
    perl -MCPAN -f -e "install Geo::IP::PurePerl"
    perl -MCPAN -f -e "install Geo::IP"
    sed -i '/LoadPlugin=\"geoip GEOIP_STANDARD \/usr\/share\/GeoIP\/GeoIP.dat\"/s/^#//g' /etc/awstats/awstats.conf
    echo "LoadPlugin=\"geoip GEOIP_STANDARD /usr/share/GeoIP/GeoIP.dat\"" >> $HESTIA/data/templates/web/awstats/awstats.tpl
    
    for user in $($BIN/v-list-sys-users plain); do
        $BIN/v-rebuild-web-domains $user no
    done
fi


#----------------------------------------------------------#
#                       Hestia                             #
#----------------------------------------------------------#

# Logging
log_history "Enabled GeoIP Awstats" '' 'admin'
log_event "$OK" "$ARGUMENTS"

exit 0
