#!/bin/sh
# RHEL / CentOS / Fedora

#-------------------------------------------------------------------#
# Variables                                                         #
#-------------------------------------------------------------------#

# Services
OSAL_SERVICE_APACHE=httpd
OSAL_SERVICE_BIND=named
OSAL_SERVICE_CLAMAV=clamd
OSAL_SERVICE_CRON=crond

# Users
OSAL_USER_APACHE_DATA=apache
OSAL_USER_BIND=named
OSAL_USER_EXIM=exim
OSAL_USER_NOBODY=nobody
OSAL_USER_NOGROUP=nobody

# Commands
OSAL_CMD_PACKAGE_MANAGER=/usr/bin/dnf

# Packages
OSAL_PKG_APACHE=httpd
OSAL_PKG_APACHE_MOD_RUID2=mod_ruid2
OSAL_PKG_BIND=bind
OSAL_PKG_CLAMAV=clamav clamav-update
OSAL_PKG_EXIM=exim
OSAL_PKG_PHPMYADMIN=phpMyAdmin
OSAL_PKG_ROUNDCUBE=roundcubemail

# Paths
OSAL_PATH_APACHE_CONF=/etc/httpd
OSAL_PATH_BIND_DATA=/var/named
OSAL_PATH_CLAMAV_CONF=/etc/clamd.conf /etc/clamd.d
OSAL_PATH_EXIM_CONF=/etc/exim
OSAL_PATH_ROUNDCUBE_INSTALL_MYSQL=/usr/share/roundcubemail/SQL/mysql
OSAL_PATH_VSFTPD_CONF=/etc/vsftpd

#-------------------------------------------------------------------#
# Functions                                                         #
#-------------------------------------------------------------------#

# OS function wrappers

# package_preinstall
osal_package_preinstall() {
    true    # Do nothing (on Debian: apt-get update)
}

# package_install 'package' 'package' ...
osal_package_install() {
    $OSAL_CMD_PACKAGE_MANAGER -q -y install "$@"
}

# package_remoev 'package' 'package' ...
osal_package_remove() {
    $OSAL_CMD_PACKAGE_MANAGER -y remove "$@"
}

# service_start 'service-name'
osal_service_start() {
    /usr/bin/systemctl start ${1}.service
}

# service_stop 'service-name'
osal_service_stop() {
    /usr/bin/systemctl stop ${1}.service
}

# service_restart 'service-name'
osal_service_restart() {
    /usr/bin/systemctl restart ${1}.service
}

# service_enable 'service-name'
osal_service_enable() {
    /usr/bin/systemctl enable ${1}.service > /dev/null
}

# service_disable 'service-name'
osal_service_disable() {
    /usr/bin/systemctl disable ${1}.service /dev/null
}

# Software-specific

# apache_module_isenabled 'module_name' = (1|null)
osal_apache_module_isenabled() {
    mod_enabled=$(/usr/sbin/httpd -M | grep $1)
    [ "$mod_enabled" ] && echo 1
}

# apache_module_enable 'module_name'
osal_apache_module_enable() {
    /usr/bin/sed -i "/LoadModule ${1}_module/ s/#*//" $OSAL_PATH_APACHE_CONF/conf.modules.d/*.conf
    grep "^$LoadModule\s*${1}_module" $OSAL_PATH_APACHE_CONF/conf.modules.d/*.conf > /dev/null
    if [ $? -gt 0 ]; then
        echo "LoadModule ${1}_module modules/mod_${1}.so" > $OSAL_PATH_APACHE_CONF/conf.modules.d/hestia-${1}.conf
    fi
}

# apache_module_disable 'module_name'
osal_apache_module_disable() {
    /usr/bin/sed -i "/LoadModule\s*${1}_module/ s/^/#/" $OSAL_PATH_APACHE_CONF/conf.modules.d/*.conf
}

# multiphp_php_package_prefix 7.3 = 'php73-php'
osal_multiphp_php_package_prefix() {
    echo php${1//.}-php
}

# multiphp_fpm_isinstalled 7.3 = (1|null)
osal_multiphp_fpm_isinstalled() {
    php_prefix=$(osal_multiphp_php_package_prefix $1)
    rpm -q --quiet ${php_prefix}-fpm && echo 1
}

# multiphp_etc_folder '7.3' = /etc/opt/remi/php73
osal_multiphp_etc_folder() {
    echo /etc/opt/remi/php${1//.}
}

# multiphp_fpm_pool_d '7.3' = /etc/opt/remi/php$73/php-fpm.d
osal_multiphp_fpm_pool_d() {
    echo /etc/opt/remi/php${1//.}/php-fpm.d
}