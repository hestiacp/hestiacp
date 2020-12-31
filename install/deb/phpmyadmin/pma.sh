#!/bin/bash
# 
# phpmyadmin-fixer
#
# Fixes for phpmyadmin (configuration storage and some extended features)
#
# Original Version by Pavel Galkin (https://skurudo.ru)
# https://github.com/skurudo/phpmyadmin-fixer
#
# Changed some lines to fit to Hestia Configuration.
#

PASS=$(gen_pass)

#ubuntu phpmyadmin path
pmapath1="/etc/phpmyadmin/conf.d/01-localhost.php"

echo "\$cfg['Servers'][\$i]['favorite'] = 'pma__favorite';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['usergroups'] = 'pma__usergroups';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['central_columns'] = 'pma__central_columns';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['designer_settings'] = 'pma__designer_settings';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['export_templates'] = 'pma__export_templates';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['savedsearches'] = 'pma__savedsearches';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['navigationhiding'] = 'pma__navigationhiding';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['users'] = 'pma__users';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['usergroups'] = 'pma__usergroups';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['pmadb'] = 'phpmyadmin';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['controluser'] = 'pma';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['controlpass'] = '$PASS';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['bookmarktable'] = 'pma__bookmark';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['relation'] = 'pma__relation';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['userconfig'] = 'pma__userconfig';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['table_info'] = 'pma__table_info';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['column_info'] = 'pma__column_info';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['history'] = 'pma__history';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['recent'] = 'pma__recent';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['table_uiprefs'] = 'pma__table_uiprefs';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['tracking'] = 'pma__tracking';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['table_coords'] = 'pma__table_coords';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['pdf_pages'] = 'pma__pdf_pages';" >> $pmapath1
echo "\$cfg['Servers'][\$i]['designer_coords'] = 'pma__designer_coords';" >> $pmapath1

#SOME WORK with DATABASE (table / user)
PMADB=phpmyadmin
PMAUSER=pma

#DROP USER and TABLE
# mysql -uroot <<MYSQL_PMA1
# DROP USER '$PMAUSER'@'localhost';
# DROP DATABASE $PMADB;
# FLUSH PRIVILEGES;
# MYSQL_PMA1

#CREATE PMA USER
mysql -uroot <<MYSQL_PMA2
CREATE USER '$PMAUSER'@'localhost' IDENTIFIED BY '$PASS';
CREATE DATABASE $PMADB;
MYSQL_PMA2

#GRANT PMA USE SOME RIGHTS
mysql -uroot <<MYSQL_PMA3
USE $PMADB;
GRANT USAGE ON $PMADB.* TO '$PMAUSER'@'localhost' IDENTIFIED BY '$PASS';
GRANT ALL PRIVILEGES ON $PMADB.* TO '$PMAUSER'@'localhost';
FLUSH PRIVILEGES;
MYSQL_PMA3

#MYSQL DB and TABLES ADDITION
mysql -uroot < $HESTIA_INSTALL_DIR/phpmyadmin/create_tables.sql