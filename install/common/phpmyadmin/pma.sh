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
echo "\$cfg['Servers'][\$i]['hide_db'] = 'information_schema';" >> $pmapath

#SOME WORK with DATABASE (table / user)
PMADB=phpmyadmin
PMAUSER=pma

#DROP USER and TABLE
#mysql -uroot <<MYSQL_PMA1
#DROP USER '$PMAUSER'@'localhost';
#DROP DATABASE $PMADB;
#FLUSH PRIVILEGES;
#MYSQL_PMA1

#CREATE PMA USER
if [ -f '/usr/bin/mariadb' ]; then
	mysql_server="mariadb"
else
	mysql_server="mysql"
fi
mysql_out=$(mktemp)
$mysql -e 'SELECT VERSION()' > $mysql_out
mysql_ver=$(cat $mysql_out | tail -n1 | cut -f 1 -d -)
mysql_ver_sub=$(echo $mysql_ver | cut -d '.' -f1)
mysql_ver_sub_sub=$(echo $mysql_ver | cut -d '.' -f2)

if [ "$mysql" = "mysql" ] && [ "$mysql_ver_sub" -ge 8 ]; then
	query="CREATE USER '$PMAUSER'@'localhost' IDENTIFIED BY '$PASS';"
	$mysql_server -uroot -e "$query" > /dev/null

	query="CREATE DATABASE $PMADB;"
	$mysql_server -uroot -e "$query" > /dev/null

	query="GRANT USAGE ON $PMADB.* TO '$PMAUSER'@'localhost';"
	$mysql_server -uroot -e "$query" > /dev/null

	query="GRANT ALL PRIVILEGES ON $PMADB.* TO '$PMAUSER'@'localhost';"
	$mysql_server -uroot -e "$query" > /dev/null

	query="FLUSH PRIVILEGES;"
	$mysql_server -uroot -e "$query" > /dev/null

else
	query="CREATE USER '$PMAUSER'@'localhost' IDENTIFIED BY '$PASS';"
	$mysql_server -uroot -e "$query" > /dev/null

	query="CREATE DATABASE $PMADB;"
	$mysql_server -uroot -e "$query" > /dev/null

	query="GRANT USAGE ON $PMADB.* TO '$PMAUSER'@'localhost' IDENTIFIED BY '$PASS';"
	$mysql_server -uroot -e "$query" > /dev/null

	query="GRANT ALL PRIVILEGES ON $PMADB.* TO '$PMAUSER'@'localhost';"
	$mysql_server -uroot -e "$query" > /dev/null

	query="FLUSH PRIVILEGES;"
	$mysql_server -uroot -e "$query" > /dev/null
fi

#MYSQL DB and TABLES ADDITION
$mysql_server -uroot < "$HESTIA_INSTALL_DIR/phpmyadmin/create_tables.sql"
