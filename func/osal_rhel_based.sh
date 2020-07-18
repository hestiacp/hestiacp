#!/bin/sh
# RHEL / CentOS / Fedora

#-------------------------------------------------------------------#
# Variables                                                         #
#-------------------------------------------------------------------#

# Apache
OSAL_SERVICE_APACHE=httpd
OSAL_USER_APACHE_DATA=apache
OSAL_PKG_APACHE=httpd
OSAL_PKG_APACHE_EXTRA=mod_ssl
OSAL_PKG_APACHE_MOD_RUID2=mod_ruid2
OSAL_PATH_APACHE_CONF=/etc/httpd/conf
OSAL_PATH_APACHE_CONF_D=/etc/httpd/conf.d
OSAL_PATH_APACHE_MODS_ENABLED=/etc/httpd/conf.modules.d

# Awstats
OSAL_PKG_AWSTATS=awstats
OSAL_PATH_AWSTATS_CONF=/etc/awstats

# Bind
OSAL_SERVICE_BIND=named
OSAL_USER_BIND=named
OSAL_PKG_BIND=bind
OSAL_PATH_BIND_DATA=/var/named

# ClamAV
OSAL_SERVICE_CLAMAV=clamd
OSAL_USER_CLAMAV=clamav
OSAL_PKG_CLAMAV='clamd clamav-update'
OSAL_PATH_CLAMAV_CONF=/etc/clamd.conf
OSAL_PATH_CLAMAV_CONF_D=/etc/clamd.d

# Cron
OSAL_SERVICE_CRON=crond

# Dovecot
OSAL_SERVICE_DOVECOT=dovecot
OSAL_PKG_DOVECOT=dovecot
OSAL_PATH_DOVECOT_CONF=/etc/dovecot

# Exim
OSAL_SERVICE_EXIM=exim
OSAL_USER_EXIM=exim
OSAL_PKG_EXIM=exim
OSAL_PATH_EXIM_CONF=/etc/exim

# Nginx
OSAL_SERVICE_NGINX=nginx
OSAL_USER_NGINX=nginx
OSAL_PKG_NGINX=nginx
OSAL_PATH_NGINX_CONF=/etc/nginx
OSAL_PATH_NGINX_CONF_D=/etc/nginx/conf.d

# phpMyAdmin
OSAL_PKG_PHPMYADMIN=phpMyAdmin

# RoundCube
OSAL_PKG_ROUNDCUBE=roundcubemail
OSAL_PATH_ROUNDCUBE_INSTALL_MYSQL=/usr/share/roundcubemail/SQL/mysql

# SpamAssassin
OSAL_SERVICE_SPAMASSASSIN=spamassassin
OSAL_PKG_SPAMASSASSIN=spamassassin
OSAL_PATH_SPAMASSASSIN_CONF=/etc/mail/spamassassin

# vsftpd
OSAL_PATH_VSFTPD_CONF=/etc/vsftpd

# Misc. users
OSAL_USER_NOBODY=nobody
OSAL_USER_NOGROUP=nobody

# Misc. commands
OSAL_CMD_PACKAGE_MANAGER=/usr/bin/dnf

# Misc. paths
OSAL_PATH_LOGROTATE_CONF_D=/etc/logrotate.d

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
    $OSAL_CMD_PACKAGE_MANAGER -q -y remove "$@"
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
    /usr/bin/sed -i "/^#*LoadModule\s*${1}_module/ s/#*//" $OSAL_PATH_APACHE_MODS_ENABLED/*.conf
    grep "^LoadModule\s*${1}_module" $OSAL_PATH_APACHE_MODS_ENABLED/*.conf > /dev/null
    if [ $? -gt 0 ]; then
        echo "LoadModule ${1}_module modules/mod_${1}.so" >> $OSAL_PATH_APACHE_MODS_ENABLED/${1}.conf
    fi
}

# apache_module_disable 'module_name'
osal_apache_module_disable() {
    /usr/bin/sed -i "/^LoadModule\s*${1}_module/ s/^/#/" $OSAL_PATH_APACHE_MODS_ENABLED/*.conf
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