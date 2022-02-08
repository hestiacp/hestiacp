#!/bin/bash
# info: Disconnect Roundcube from APT and solving issues with Roundcube accidental updates from ATP

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Includes
source $HESTIA/func/main.sh
# get current Roundcube version 
source $HESTIA/install/upgrade/upgrade.conf
source $HESTIA/conf/hestia.conf

#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

if [ ! -d "/usr/share/roundcube/" ]; then
    echo "ERROR: Roundcube is not managed by apt."
    exit 2;
fi

#----------------------------------------------------------#
#                       Action                             #
#----------------------------------------------------------#

echo "To remove Roundcube you will need use the root password. Password can be found in /usr/local/hestia/conf/mysql.conf"
read -p 'Would you like to continue? [y/n]' -n 1 -r
echo    # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    version=$(cat /usr/share/roundcube/index.php | grep -o -E '[0-9].[0-9].[0-9]+' | head -1);
    # Backup database 
    echo "#version $version" >> ~/roundcube.sql
    echo "SET FOREIGN_KEY_CHECKS = 0;" >> ~/roundcube.sql
    mysqldump  --add-drop-table roundcube >> ~/roundcube.sql
    echo "SET FOREIGN_KEY_CHECKS = 1;" >> ~/roundcube.sql
    echo '[ * ] Remove Roundcube via ATP'
    apt-get autoremove roundcube-core roundcube-mysql roundcube-plugins
    echo '[ * ] Delete possible trail'
    # make sure everything is deleted 
    rm -f -r /usr/share/roundcube
    rm -f -r /etc/roundcube
    rm -f -r /var/lib/roundcube/
    
    # Install Roundcube
    $HESTIA/bin/v-add-sys-roundcube
    # restore backup
    echo "SET FOREIGN_KEY_CHECKS = 0;" > ~/drop_all_tables.sql
    ( mysqldump --add-drop-table --no-data -u root roundcube | grep 'DROP TABLE' ) >> ./drop_all_tables.sql 
    echo "SET FOREIGN_KEY_CHECKS = 1;" >> ~/drop_all_tables.sql
    mysql -u root roundcube < ./drop_all_tables.sql
     
    mysql roundcube < ~/roundcube.sql
    /var/lib/roundcube/bin/update.sh --version "$version"
fi
