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
OSAL_FILENAME_EXIM_CONF="${OSAL_PKG_EXIM}.conf"
OSAL_DIR_EXIM_CONF=/etc/exim
OSAL_PATH_EXIM_CONF="${OSAL_DIR_EXIM_CONF}/${OSAL_FILENAME_EXIM_CONF}"

# MariaDB
OSAL_PKG_MARIADB=MariaDB
OSAL_SERVICE_MARIADB=mariadb
OSAL_DIR_MARIADB_CONF=/etc
OSAL_DIR_MARIADB_CONF_D=/etc/my.cnf.d
OSAL_FILENAME_MARIADB_CONF=my.cnf
OSAL_PATH_MARIADB_CONF="$OSAL_DIR_MARIADB_CONF/$OSAL_FILENAME_MARIADB_CONF"
OSAL_PATH_MARIADB_DATA=/var/lib/mysql

# Nginx
OSAL_SERVICE_NGINX=nginx
OSAL_USER_NGINX=nginx
OSAL_PKG_NGINX=nginx
OSAL_PATH_NGINX_CONF=/etc/nginx
OSAL_PATH_NGINX_CONF_D=/etc/nginx/conf.d

# PHP
PHP_DIR_POOL_D_BASE=/etc/opt/remi

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
OSAL_PATH_RUN=/var/run

#-------------------------------------------------------------------#
# Functions                                                         #
#-------------------------------------------------------------------#

# osal_package_getversion 'package'
osal_package_getversion() {
    rpm --queryformat="%{VERSION}" -q $1 | cut -d"~" -f1
}

# OS function wrappers

# package_preinstall
osal_package_preinstall() {
    true    # Do nothing (on Debian: apt-get update)
}

# package_install 'package' 'package' ...
osal_package_install() {
    [ "$HESTIA_DEBUG" ] && >&2 echo $OSAL_CMD_PACKAGE_MANAGER -q -y install "$@"
    $OSAL_CMD_PACKAGE_MANAGER -q -y install "$@"
}

# package_remoev 'package' 'package' ...
osal_package_remove() {
    [ "$HESTIA_DEBUG" ] && >&2 $OSAL_CMD_PACKAGE_MANAGER -q -y remove "$@"
    $OSAL_CMD_PACKAGE_MANAGER -q -y remove "$@"
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
osal_php_package_prefix() {
    echo php${1//.}-php
}

# multiphp_fpm_isinstalled 7.3 = (1|null)
osal_php_fpm_isinstalled() {
    php_prefix=$(osal_php_package_prefix $1)
    rpm -q --quiet ${php_prefix}-fpm && echo 1
}

# multiphp_etc_folder '7.3' = /etc/opt/remi/php73
osal_multiphp_etc_folder() {
    echo /etc/opt/remi/php${1//.}
}

# Returns PHP-FPM directory for a given PHP version
# multiphp_fpm_pool_d '7.3' = /etc/opt/remi/php73/php-fpm.d
osal_php_fpm_pool_d() {
    local numbersonly=${1//.}       # Remove . in 7.3
    numbersonly=${numbersonly#php}  # Remove php in php73
    echo $PHP_DIR_POOL_D_BASE/php${numbersonly}/php-fpm.d
}