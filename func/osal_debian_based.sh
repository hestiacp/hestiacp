#!/bin/sh
# Debian / Ubuntu

#-------------------------------------------------------------------#
# Variables                                                         #
#-------------------------------------------------------------------#

# Apache
OSAL_SERVICE_APACHE=apache2
OSAL_USER_APACHE_DATA=www-data
OSAL_PKG_APACHE=apache2
OSAL_PKG_APACHE_EXTRA=
OSAL_PKG_APACHE_MOD_RUID2=libapache2-mod-ruid2
OSAL_PATH_APACHE_CONF=/etc/apache2
OSAL_PATH_APACHE_CONF_D=/etc/apache2/conf.d
OSAL_PATH_APACHE_MODS_ENABLED=/etc/apache2/mods-enabled
OSAL_PATH_APACHE_MODS_AVAILABLE=/etc/apache2/mods-available

# Awstats
OSAL_PKG_AWSTATS=awstats
OSAL_PATH_AWSTATS_CONF=/etc/awstats

# Bind
OSAL_SERVICE_BIND=bind9
OSAL_USER_BIND=bind
OSAL_PKG_BIND=bind9
OSAL_PATH_BIND_DATA=/var/cache/bind

# ClamAV
OSAL_SERVICE_CLAMAV=clamav-daemon
OSAL_USER_CLAMAV=clamav
OSAL_PKG_CLAMAV=clamav-daemon
OSAL_PATH_CLAMAV_CONF=/etc/clamav/clamd.conf
OSAL_PATH_CLAMAV_CONF_D=/etc/clamd.d

# Cron
OSAL_SERVICE_CRON=cron

# Dovecot
OSAL_SERVICE_DOVECOT=dovecot
OSAL_PKG_DOVECOT='dovecot-imapd dovecot-pop3d'
OSAL_PATH_DOVECOT_CONF=/etc/dovecot

# Exim
OSAL_SERVICE_EXIM=exim4
OSAL_USER_EXIM=Debian-exim
OSAL_PKG_EXIM='exim4 exim4-daemon-heavy'
OSAL_FILENAME_EXIM_CONF="exim4.conf.template"
OSAL_DIR_EXIM_CONF=/etc/exim4
OSAL_PATH_EXIM_CONF="${OSAL_DIR_EXIM_CONF}/${OSAL_FILENAME_EXIM_CONF}"

# MariaDB
OSAL_PKG_MARIADB=mariadb
OSAL_SERVICE_MARIADB=mariadb
OSAL_DIR_MARIADB_CONF=/etc/mysql
OSAL_DIR_MARIADB_CONF_D=/etc/mysql/mariadb.conf.d
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
PHP_DIR_POOL_D_BASE=/etc/php

# phpMyAdmin
OSAL_PKG_PHPMYADMIN=phpmyadmin

# RoundCube
OSAL_PKG_ROUNDCUBE=roundcube
OSAL_PATH_ROUNDCUBE_INSTALL_MYSQL=/usr/share/dbconfig-common/data/roundcubemail/install/mysql

# SpamAssassin
OSAL_SERVICE_SPAMASSASSIN=spamassassin
OSAL_PKG_SPAMASSASSIN=spamassassin
OSAL_PATH_SPAMASSASSIN_CONF=/etc/spamassassin

# vsftp
OSAL_PATH_VSFTPD_CONF=/etc/vsftpd.conf

# Misc. users
OSAL_USER_NOBODY=nobody
OSAL_USER_NOGROUP=nogroup

# Misc. commands
OSAL_CMD_PACKAGE_MANAGER=/usr/bin/apt-get

# Misc. paths
OSAL_PATH_LOGROTATE_CONF_D=/etc/logrotate.d
OSAL_PATH_RUN=/var/run

#-------------------------------------------------------------------#
# Functions                                                         #
#-------------------------------------------------------------------#

# osal_package_getversion 'package'
osal_package_getversion() {
   dpkg-query --showformat='${Version}\n' --show $1 | cut -d"~" -f1
}

# OS function wrappers

# package_preinstall
osal_package_preinstall() {
    $OSAL_CMD_PACKAGE_MANAGER -qq update
}

# package_install 'package' 'package' ...
osal_package_install() {
    [ "$HESTIA_DEBUG" ] && >&2 echo $OSAL_CMD_PACKAGE_MANAGER -y -qq install -o Dpkg::Options::="--force-confold" "$@"
    $OSAL_CMD_PACKAGE_MANAGER -y -qq install -o Dpkg::Options::="--force-confold" "$@"
}

# package_remoev 'package' 'package' ...
osal_package_remove() {
    [ "$HESTIA_DEBUG" ] && >&2 echo $OSAL_CMD_PACKAGE_MANAGER -y -qq purge "$@"
    $OSAL_CMD_PACKAGE_MANAGER -y -qq purge "$@"
}

# Software-specific

# apache_module_isenabled 'module_name' = (1|null)
osal_apache_module_isenabled() {
    /usr/sbin/a2query -q -m $1 && echo 1
}

# apache_module_enable 'module_name'
osal_apache_module_enable() {
    /usr/sbin/a2enmod $1
}

# apache_module_disable 'module_name'
osal_apache_module_disable() {
    /usr/sbin/a2dismod $1
}

# multiphp_php_package_prefix 7.3 = 'php7.3'
osal_php_package_prefix() {
    echo "php${1}"
}

# multiphp_fpm_isinstalled 7.3 = (1|null)
osal_php_fpm_isinstalled() {
    [ -f "/etc/init.d/php$1-fpm" ] && echo 1
}

# multiphp_etc_folder '7.3' = /etc/php/7.3
multiphp_etc_folder() {
    echo /etc/php/php${1}
}

# Returns PHP-FPM directory for a given PHP version
# multiphp_fpm_pool_d '7.3' = /etc/php/7.3/fpm/pool.d
osal_php_fpm_pool_d() {
    echo $PHP_DIR_POOL_D_BASE/$1/fpm/pool.d
}