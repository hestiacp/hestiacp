#!/bin/bash
# info: Disconnect PHPmyadmin from APT and solving issues with PHPMyadmin accidental updates from ATP


#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Includes
source $HESTIA/func/main.sh
# get current phpmyadmin version 
source $HESTIA/install/upgrade/upgrade.conf
source $HESTIA/conf/hestia.conf


#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

if [ ! -d "/usr/share/phpmyadmin/" ]; then
    echo "Install Roundcube not done via APT"
    exit 2;
fi


echo "For deleting Roudcube you will need confirm the removal with root password. Password can be found in /usr/local/hestia/conf/mysql.conf"
read -p "Please enter Y to continue" -n 1 -r
echo    # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    version=$(cat /usr/share/roundcube/index.php | grep -o -E '[0-9].[0-9].[0-9]+' | head -1);
    # Backup database 
    echo "SET FOREIGN_KEY_CHECKS = 0;" >> ~/roundcube.sql
    mysqldump -U root --add-drop-table roundcube >> ~/roundcube.sql
    echo "SET FOREIGN_KEY_CHECKS = 1;" >> ~/roundcube.sql
    echo '[ * ] Remove Roundcube via ATP'
    apt-get autoremove roundcube-core roundcube-mysql roundcube-plugins
    echo '[ * ] Delete possible trail'
    # make sure everything is deleted 
    rm -f -r /usr/share/roundcube
    rm -f -r /etc/roundcube
    rm -f -r /var/lib/roundcube/
    
    # Install roundcube
    $HESTIA/bin/v-add-sys-roundcube
    # restore backup
    mysql roundcube < ~/roundcube.sql
    /var/lib/roundcube/bin/update.sh --version "$version"
fi