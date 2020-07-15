#!/bin/sh
# Debian / Ubuntu

#-------------------------------------------------------------------#
# Variables                                                         #
#-------------------------------------------------------------------#

# Services
OSAL_SERVICE_APACHE=apache2
OSAL_SERVICE_BIND=bind9
OSAL_SERVICE_CLAMAV=clamav-daemon
OSAL_SERVICE_CRON=cron
OSAL_SERVICE_DOVECOT=dovecot
OSAL_SERVICE_EXIM=exim4
OSAL_SERVICE_SPAMASSASSIN=spamassassin

# Users
OSAL_USER_APACHE_DATA=www-data
OSAL_USER_BIND=bind
OSAL_USER_CLAMAV=clamav
OSAL_USER_EXIM=Debian-exim
OSAL_USER_NOBODY=nobody
OSAL_USER_NOGROUP=nogroup

# Commands
OSAL_CMD_PACKAGE_MANAGER=/usr/bin/apt-get

# Packages
OSAL_PKG_APACHE=apache2
OSAL_PKG_APACHE_MOD_RUID2=libapache2-mod-ruid2
OSAL_PKG_BIND=bind9
OSAL_PKG_CLAMAV=clamav-daemon
OSAL_PKG_DOVECOT='dovecot-imapd dovecot-pop3d'
OSAL_PKG_EXIM='exim4 exim4-daemon-heavy'
OSAL_PKG_PHPMYADMIN=phpmyadmin
OSAL_PKG_ROUNDCUBE=roundcube
OSAL_PKG_SPAMASSASSIN=spamassassin

# Paths
OSAL_PATH_APACHE_CONF=/etc/apache2
OSAL_PATH_BIND_DATA=/var/cache/bind
OSAL_PATH_CLAMAV_CONF=/etc/clamav/clamd.conf
OSAL_PATH_CLAMAV_CONF_D=/etc/clamd.d
OSAL_PATH_DOVECOT_CONF=/etc/dovecot
OSAL_PATH_EXIM_CONF=/etc/exim4
OSAL_PATH_LOGROTATE_CONF=/etc/logrotate.d
OSAL_PATH_ROUNDCUBE_INSTALL_MYSQL=/usr/share/dbconfig-common/data/roundcubemail/install/mysql
OSAL_PATH_SPAMASSASSIN_CONF=/etc/spamassassin
OSAL_PATH_VSFTPD_CONF=/etc/vsftpd.conf

#-------------------------------------------------------------------#
# Functions                                                         #
#-------------------------------------------------------------------#

# OS function wrappers

# package_preinstall
osal_package_preinstall() {
    $OSAL_CMD_PACKAGE_MANAGER -qq update
}

# package_install 'package' 'package' ...
osal_package_install() {
    $OSAL_CMD_PACKAGE_MANAGER -y -qq install -o Dpkg::Options::="--force-confold" "$@"
}

# package_remoev 'package' 'package' ...
osal_package_remove() {
    $OSAL_CMD_PACKAGE_MANAGER -y -qq purge "$@"
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
    /usr/bin/systemctl enable ${1}.service
}

# service_disable 'service-name'
osal_service_disable() {
    /usr/bin/systemctl disable ${1}.service
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
osal_multiphp_php_package_prefix() {
    echo "php${1}"
}

# multiphp_fpm_isinstalled 7.3 = (1|null)
osal_multiphp_fpm_isinstalled() {
    [ -f "/etc/init.d/php$1-fpm" ] && echo 1
}

# multiphp_etc_folder '7.3' = /etc/php/7.3
multiphp_etc_folder() {
    echo /etc/php/php${1}
}

# multiphp_fpm_pool_d '7.3' = /etc/php/7.3/fpm/pool.d
osal_multiphp_fpm_pool_d() {
    echo /etc/php/$1/fpm/pool.d
}