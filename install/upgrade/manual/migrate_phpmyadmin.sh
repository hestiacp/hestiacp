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

echo "For deleting PHPmyAdmin you will need confirm the removal with root password. Password can be found in /usr/local/hestia/conf/mysql.conf"
read -p 'Would you like to continue? [y/n]'
echo    # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    # Remove PMA SSO first 
    sso="no"
    if [ "$PHPMYADMIN_KEY" != "" ]; then
        sso="yes"
        $HESTIA/bin/v-delete-sys-pma-sso
    fi
    
   # Create an backup of current config
   echo "[ * ] Make backup old config files"
   mkdir -p /root/hst_backup_man/phmyadmin
   cp -r /etc/phpmyadmin/* /root/hst_backup_man/phmyadmin
   
   mkdir -p /root/hst_backup_man/var_phmyadmin
   cp -r /var/lib/phpmyadmin/* /root/hst_backup_man/var_phmyadmin
   
   echo '[ * ] Remove PHPmyAdmin via ATP'
   apt-mark hold phpmmyadmin
   
   echo '[ * ] Delete possible trail'
   # make sure everything is deleted 
   rm -f -r /usr/share/phpmyadmin
   rm -f -r /etc/phpmyadmin
   rm -f -r /var/lib/phpmyadmin/
   
   echo '[ * ] Create new folders'
   # Create folders
   mkdir -p  /usr/share/phpmyadmin
   mkdir -p /etc/phpmyadmin
   mkdir -p /etc/phpmyadmin/conf.d/  
   mkdir /usr/share/phpmyadmin/tmp
   chmod 777 /usr/share/phpmyadmin/tmp/
   mkdir -p /etc/phpmyadmin/conf.d/  
   
   # Configuring Apache2 for PHPMYADMIN
   if [ "$WEB_SYSTEM" == "apache2" ]; then
       cp -f $HESTIA_INSTALL_DIR/pma/apache.conf /etc/phpmyadmin/
       rm /etc/apache2/conf.d/phpmyadmin.conf
       ln -s /etc/phpmyadmin/apache.conf /etc/apache2/conf.d/phpmyadmin.conf
   fi
   
   PASS=$(generate_password)
      
   echo "[ * ] Installing phpMyAdmin version v$pma_v..."
   # Download latest phpmyadmin release
   wget --quiet https://files.phpmyadmin.net/phpMyAdmin/$pma_v/phpMyAdmin-$pma_v-all-languages.tar.gz
   # Unpack files
   tar xzf phpMyAdmin-$pma_v-all-languages.tar.gz
   
   # Overwrite old files
   cp -rf phpMyAdmin-$pma_v-all-languages/* /usr/share/phpmyadmin
   
   # Create copy of config file
   cp -f $HESTIA_INSTALL_DIR/phpmyadmin/config.inc.php /etc/phpmyadmin/
   mkdir -p /var/lib/phpmyadmin/tmp
   chmod 777 -R /var/lib/phpmyadmin/tmp
   
   # Set config and log directory
   sed -i "s|define('CONFIG_DIR', ROOT_PATH);|define('CONFIG_DIR', '/etc/phpmyadmin/');|" /usr/share/phpmyadmin/libraries/vendor_config.php
   sed -i "s|define('TEMP_DIR', ROOT_PATH . 'tmp/');|define('TEMP_DIR', '/var/lib/phpmyadmin/tmp');|" /usr/share/phpmyadmin/libraries/vendor_config.php
   
   # Generate blowfish
   blowfish=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 32)
   sed -i "s|%blowfish_secret%|$blowfish|" /etc/phpmyadmin/config.inc.php
   
   # Clear Up
   rm -fr phpMyAdmin-$pma_v-all-languages
   rm -f phpMyAdmin-$pma_v-all-languages.tar.gz
   
   if [ -z '$DB_PMA_ALIAS' ]; then
       echo "DB_PMA_ALIAS='phpmyadmin'" >> $HESTIA/conf/hestia.conf
   fi
   $HESTIA/bin/v-change-sys-db-alias 'pma' "phpmyadmin"

   # Special thanks to Pavel Galkin (https://skurudo.ru)
   # https://github.com/skurudo/phpmyadmin-fixer
   
   echo "[ * ] Createing localhost config"
   #ubuntu phpmyadmin path
   pmapath="/etc/phpmyadmin/conf.d/01-localhost.php"
   echo "<?php " >> $pmapath
   echo "\$cfg['Servers'][\$i]['host'] = 'localhost';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['port'] = '3306';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['favorite'] = 'pma__favorite';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['usergroups'] = 'pma__usergroups';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['central_columns'] = 'pma__central_columns';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['designer_settings'] = 'pma__designer_settings';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['export_templates'] = 'pma__export_templates';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['savedsearches'] = 'pma__savedsearches';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['navigationhiding'] = 'pma__navigationhiding';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['users'] = 'pma__users';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['usergroups'] = 'pma__usergroups';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['pmadb'] = 'phpmyadmin';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['controluser'] = 'pma';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['controlpass'] = '$PASS';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['bookmarktable'] = 'pma__bookmark';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['relation'] = 'pma__relation';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['userconfig'] = 'pma__userconfig';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['table_info'] = 'pma__table_info';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['column_info'] = 'pma__column_info';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['history'] = 'pma__history';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['recent'] = 'pma__recent';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['table_uiprefs'] = 'pma__table_uiprefs';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['tracking'] = 'pma__tracking';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['table_coords'] = 'pma__table_coords';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['pdf_pages'] = 'pma__pdf_pages';" >> $pmapath
   echo "\$cfg['Servers'][\$i]['designer_coords'] = 'pma__designer_coords';" >> $pmapath
   
   #SOME WORK with DATABASE (table / user)
   PMADB=phpmyadmin
   PMAUSER=pma
   
   echo '[ * ] Drop database could throw a error if successfull removal was preformed'
   # removed tabs due to here doc errors
   #DROP USER and TABLE
   mysql -uroot <<MYSQL_PMA1
DROP USER '$PMAUSER'@'localhost';
DROP DATABASE $PMADB;
FLUSH PRIVILEGES;
MYSQL_PMA1

   echo '[ * ] Create new user'
   #CREATE PMA USER
   mysql -uroot <<MYSQL_PMA2
CREATE USER '$PMAUSER'@'localhost' IDENTIFIED BY '$PASS';
CREATE DATABASE $PMADB;
MYSQL_PMA2
   
   echo '[ * ] Create new database'
   #GRANT PMA USE SOME RIGHTS
   mysql -uroot <<MYSQL_PMA3
USE $PMADB;
GRANT USAGE ON $PMADB.* TO '$PMAUSER'@'localhost' IDENTIFIED BY '$PASS';
GRANT ALL PRIVILEGES ON $PMADB.* TO '$PMAUSER'@'localhost';
FLUSH PRIVILEGES;
MYSQL_PMA3
   
   #MYSQL DB and TABLES ADDITION
   mysql -uroot < $HESTIA_INSTALL_DIR/phpmyadmin/create_tables.sql
      
    if [ "$sso" == "yes" ]; then
        $HESTIA/bin/v-add-sys-pma-sso
    fi
fi
