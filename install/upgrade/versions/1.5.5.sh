#!/bin/bash

# DevIT Control Panel upgrade script for target version 1.5.5

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
####### Pass through information to the end user in case of a issue or problem  #######
#######                                                                         #######
####### Use add_upgrade_message "My message here" to include a message          #######
####### in the upgrade notification email. Example:                             #######
#######                                                                         #######
####### add_upgrade_message "My message here"                                   #######
#######                                                                         #######
####### You can use \n within the string to create new lines.                   #######
#######################################################################################

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

# Update php-fpm.conf
for version in $($BIN/v-list-sys-php plain); do
	cp -f $DevIT_INSTALL_DIR/php-fpm/php-fpm.conf /etc/php/$version/fpm/
	sed -i "s/fpm_v/$version/g" /etc/php/$version/fpm/php-fpm.conf
done

echo "[ * ] Updating apt keyring configuration..."

mkdir -p /root/.gnupg && chmod 700 /root/.gnupg

if [ ! -f "/usr/share/keyrings/nginx-keyring.gpg" ]; then
	# Get Architecture
	architecture="$(arch)"
	case $architecture in
		x86_64)
			ARCH="amd64"
			;;
		aarch64)
			ARCH="arm64"
			;;
		*)
			echo "   [ ! ] Unsuported architectrue"
			;;
	esac

	#Get OS details
	os=$(grep "^ID=" /etc/os-release | cut -f 2 -d '=')
	codename="$(lsb_release -s -c)"
	release="$(lsb_release -s -r)"
	mariadb_v=$(mysql -V | awk 'NR==1{print $5}' | head -c 4)
	RHOST='apt.DevITcp.com'

	apt="/etc/apt/sources.list.d"

	if [ -f "$apt/nginx.list" ]; then
		rm $apt/nginx.list
		echo "   [ * ] NGINX"
		echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/nginx-keyring.gpg] https://nginx.org/packages/mainline/$os/ $codename nginx" > $apt/nginx.list
		curl -s https://nginx.org/keys/nginx_signing.key | gpg --dearmor | tee /usr/share/keyrings/nginx-keyring.gpg > /dev/null 2>&1
	fi
	if [ "$os" = "debian" ]; then
		if [ -f "$apt/php.list" ]; then
			rm $apt/php.list
			echo "   [ * ] PHP"
			echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/sury-keyring.gpg] https://packages.sury.org/php/ $codename main" > $apt/php.list
			curl -s https://packages.sury.org/php/apt.gpg | gpg --dearmor | tee /usr/share/keyrings/sury-keyring.gpg > /dev/null 2>&1
		fi
		if [ -f "$apt/apache2.list" ]; then
			rm $apt/apache2.list
			echo "   [ * ] Apache2"
			echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/apache2-keyring.gpg] https://packages.sury.org/apache2/ $codename main" > $apt/apache2.list
			curl -s https://packages.sury.org/apache2/apt.gpg | gpg --dearmor | tee /usr/share/keyrings/apache2-keyring.gpg > /dev/null 2>&1
		fi
	fi
	if [ -f "$apt/mariadb.list" ]; then
		rm $apt/mariadb.list
		echo "   [ * ] MariaDB"
		echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/mariadb-keyring.gpg] https://mirror.mva-n.net/mariadb/repo/$mariadb_v/$os $codename main" > $apt/mariadb.list
		curl -s https://mariadb.org/mariadb_release_signing_key.asc | gpg --dearmor | tee /usr/share/keyrings/mariadb-keyring.gpg > /dev/null 2>&1
	fi
	if [ -f "$apt/DevIT.list" ]; then
		rm $apt/DevIT.list
		echo "   [ * ] DevIT"
		echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/DevIT-keyring.gpg] https://$RHOST/ $codename main" > $apt/DevIT.list
		gpg --no-default-keyring --keyring /usr/share/keyrings/DevIT-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys A189E93654F0B0E5 > /dev/null 2>&1
		apt-key del A189E93654F0B0E5 > /dev/null 2>&1
	fi
	if [ -f "$apt/postgresql.list" ]; then
		rm $apt/postgresql.list
		echo "[ * ] PostgreSQL"
		echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/postgresql-keyring.gpg] https://apt.postgresql.org/pub/repos/apt/ $codename-pgdg main" > $apt/postgresql.list
		curl -s https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /usr/share/keyrings/postgresql-keyring.gpg > /dev/null 2>&1
	fi

fi

if [ ! -f "$DevIT/data/packages/system.pkg" ]; then
	echo "[ * ] Install default system package."
	cp -f $DevIT/install/deb/packages/system.pkg $DevIT/data/packages/system.pkg
fi
