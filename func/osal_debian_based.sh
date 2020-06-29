#!/bin/sh
# Debian / Ubuntu

#-------------------------------------------------------------------#
# Variables                                                         #
#-------------------------------------------------------------------#

# Services
SERVICE_NAME_APACHE=apache2
SERVICE_NAME_BIND=bind9
SERVICE_NAME_CRON=cron

# Users
USER_APACHE_DATA=www-data
USER_BIND=bind
USER_NOBODY=nobody
USER_NOGROUP=nogroup

# Commands
CMD_PACKAGE_MANAGER=/usr/bin/apt-get

# Packages
PKG_APACHE=apache2
PKG_APACHE_MOD_RUID2=libapache2-mod-ruid2
PKG_BIND=bind9
PKG_EXIM=exim4
PKG_PHPMYADMIN=phpmyadmin
PKG_ROUNDCUBE=roundcube

# Paths
PATH_BIND_DATA=/var/cache/bind
PATH_ROUNDCUBE_INSTALL_MYSQL=/usr/share/dbconfig-common/data/roundcubemail/install/mysql

#-------------------------------------------------------------------#
# Functions                                                         #
#-------------------------------------------------------------------#

# OS function wrappers

# package_preinstall
package_preinstall() {
    $CMD_PACKAGE_MANAGER -qq update
}

# package_install 'package' 'package' ...
package_install() {
    $CMD_PACKAGE_MANAGER -y -qq install -o Dpkg::Options::="--force-confold" "$@"
}

# package_remoev 'package' 'package' ...
package_remove() {
    $CMD_PACKAGE_MANAGER -y purge "$@"
}

# service_start 'service-name'
service_start() {
    /usr/bin/systemctl start ${1}.service
}

# service_stop 'service-name'
service_stop() {
    /usr/bin/systemctl stop ${1}.service
}

# service_restart 'service-name'
service_restart() {
    /usr/bin/systemctl restart ${1}.service
}

# service_enable 'service-name'
service_enable() {
    /usr/bin/systemctl enable ${1}.service
}

# service_disable 'service-name'
service_disable() {
    /usr/bin/systemctl disable ${1}.service
}

# Software-specific

# apache_module_isenabled 'module_name' = (1|null)
apache_module_isenabled() {
    /usr/sbin/a2query -q -m $1 && echo 1
}

# apache_module_enable 'module_name'
apache_module_enable() {
    /usr/sbin/a2enmod $1
}

# apache_module_disable 'module_name'
apache_module_disable() {
    /usr/sbin/a2dismod $1
}

# multiphp_php_package_prefix 7.3 = 'php7.3'
multiphp_php_package_prefix() {
    echo "php${1}"
}

# multiphp_fpm_isinstalled 7.3 = (1|null)
multiphp_fpm_isinstalled() {
    [ -f "/etc/init.d/php$1-fpm" ] && echo 1
}

# multiphp_etc_folder '7.3' = /etc/php/7.3
multiphp_etc_folder() {
    echo /etc/php/php${1}
}

# multiphp_fpm_pool_d '7.3' = /etc/php/7.3/fpm/pool.d
multiphp_fpm_pool_d() {
    echo /etc/php/$1/fpm/pool.d
}