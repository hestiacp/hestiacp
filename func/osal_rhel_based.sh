#!/bin/sh
# RHEL / CentOS / Fedora

#-------------------------------------------------------------------#
# Variables                                                         #
#-------------------------------------------------------------------#

# Services
SERVICE_NAME_APACHE=httpd
SERVICE_NAME_BIND=named
SERVICE_NAME_CRON=crond

# Users
USER_APACHE_DATA=apache
USER_BIND=named
USER_NOBODY=nobody
USER_NOGROUP=nobody

# Commands
CMD_PACKAGE_MANAGER=/usr/bin/dnf

# Packages
PKG_APACHE=httpd
PKG_APACHE_MOD_RUID2=mod_ruid2
PKG_BIND=bind
PKG_EXIM=exim
PKG_PHPMYADMIN=phpMyAdmin
PKG_ROUNDCUBE=roundcubemail

# Paths
PATH_BIND_DATA=/var/named
PATH_ROUNDCUBE_INSTALL_MYSQL=/usr/share/roundcubemail/SQL/mysql

#-------------------------------------------------------------------#
# Functions                                                         #
#-------------------------------------------------------------------#

# OS function wrappers

# package_preinstall
package_preinstall() {
    true    # Do nothing (on Debian: apt-get update)
}

# package_install 'package' 'package' ...
package_install() {
    $CMD_PACKAGE_MANAGER -q -y install "$@"
}

# package_remoev 'package' 'package' ...
package_remove() {
    $CMD_PACKAGE_MANAGER -y remove "$@"
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
    mod_enabled=$(/usr/sbin/httpd -M | grep $1)
    [ "$mod_enabled" ] && echo 1
}

# apache_module_enable 'module_name'
apache_module_enable() {
    /usr/bin/sed -i "/LoadModule ${1}_module/ s/#*//" /etc/httpd/conf.modules.d/*.conf
}

# apache_module_disable 'module_name'
apache_module_disable() {
    /usr/bin/sed -i "/LoadModule ${1}_module/ s/^/#/" /etc/httpd/conf.modules.d/*.conf
}

# multiphp_php_package_prefix 7.3 = 'php73-php'
multiphp_php_package_prefix() {
    echo php${1//.}-php
}

# multiphp_fpm_isinstalled 7.3 = (1|null)
multiphp_fpm_isinstalled() {
    php_prefix=$(multiphp_php_package_prefix $1)
    rpm -q --quiet ${php_prefix}-fpm && echo 1
}

# multiphp_etc_folder '7.3' = /etc/opt/remi/php73
multiphp_etc_folder() {
    echo /etc/opt/remi/php${1//.}
}

# multiphp_fpm_pool_d '7.3' = /etc/php/7.3/fpm/pool.d
multiphp_fpm_pool_d() {
    echo /etc/opt/remi/php${1//.}/php-fpm.d
}