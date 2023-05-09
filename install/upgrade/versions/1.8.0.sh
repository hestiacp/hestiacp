#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.8.0

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
####### upgrade_config_set_value only accepts true or false.                    #######
#######                                                                         #######
####### Pass through information to the end user in case of a issue or problem  #######
#######                                                                         #######
####### Use add_upgrade_message "My message here" to include a message          #######
####### in the upgrade notification email. Example:                             #######
#######                                                                         #######
####### add_upgrade_message "My message here"                                   #######
#######                                                                         #######
####### You can use \n within the string to create new lines.                   #######
#######################################################################################

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

# Apply the update for existing users to enable the "Enhanced and Optimized TLS" feature
echo '[ * ] Enable the "Enhanced and Optimized TLS" feature...'

# Configuring global OpenSSL options
cp -f $HESTIA/install/common/openssl/hestia-openssl.cnf /etc/ssl

if grep -qw "^#.include filename$" /etc/ssl/openssl.cnf 2> /dev/null; then
	sed -i 's|^#.include filename|#.include filename\n\n# Hestia OpenSSL configuration\n.include /etc/ssl/hestia-openssl.cnf|' /etc/ssl/openssl.cnf
elif [ -s "/etc/ssl/openssl.cnf" ]; then
	sed -i '1i# Hestia OpenSSL configuration\n.include /etc/ssl/hestia-openssl.cnf\n' /etc/ssl/openssl.cnf
else
	echo -e "# Hestia OpenSSL configuration\n.include /etc/ssl/hestia-openssl.cnf" > /etc/ssl/openssl.cnf
fi

# Update server configuration files
if [ "$IMAP_SYSTEM" = "dovecot" ]; then
    if grep -qw "^ssl_min_protocol = TLSv1.2$" /etc/dovecot/conf.d/10-ssl.conf 2> /dev/null; then
        sed -i '/^ssl_cipher_list = .\+$/d;s/^ssl_min_protocol = TLSv1.2/ssl_cipher_list = ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA384:DHE-RSA-AES256-SHA256\nssl_min_protocol = TLSv1.2/' /etc/dovecot/conf.d/10-ssl.conf
    elif grep -qw "^ssl_protocols = \!SSLv3 \!TLSv1 \!TLSv1.1$" /etc/dovecot/conf.d/10-ssl.conf 2> /dev/null; then
        sed -i '/^ssl_cipher_list = .\+$/d;s/^ssl_protocols = !SSLv3 !TLSv1 !TLSv1.1/ssl_cipher_list = ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA384:DHE-RSA-AES256-SHA256\nssl_protocols = !SSLv3 !TLSv1 !TLSv1.1/' /etc/dovecot/conf.d/10-ssl.conf
    fi
fi

if [ "$MAIL_SYSTEM" = "exim4" ]; then
    if grep -qw "^tls_on_connect_ports = 465$" /etc/exim4/exim4.conf.template 2> /dev/null; then
        sed -i '/^tls_require_ciphers = .\+$/d;s/^tls_on_connect_ports = 465/tls_on_connect_ports = 465\ntls_require_ciphers = PERFORMANCE:-RSA:-VERS-ALL:+VERS-TLS1.2:+VERS-TLS1.3:%SERVER_PRECEDENCE/' /etc/exim4/exim4.conf.template
    fi
fi

if [ "$FTP_SYSTEM" = "proftpd" ]; then
    if grep -qw "^TLSProtocol                             TLSv1.2$" /etc/proftpd/tls.conf 2> /dev/null; then
        sed -i '/^TLSCipherSuite .\+$/d;/^TLSServerCipherPreference .\+$/d;s/^TLSProtocol                             TLSv1.2/TLSCipherSuite                          ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA384:DHE-RSA-AES256-SHA256\nTLSProtocol                             TLSv1.2 TLSv1.3\nTLSServerCipherPreference               on/;s/^#TLSOptions                                                     AllowClientRenegotiations/#TLSOptions                      AllowClientRenegotiations/;s/^TLSOptions                       NoSessionReuseRequired AllowClientRenegotiations/TLSOptions                      NoSessionReuseRequired AllowClientRenegotiations/' /etc/proftpd/tls.conf
    fi
fi

if [ "$FTP_SYSTEM" = "vsftpd" ]; then
    if grep -q "^ssl_ciphers=.\+$" /etc/vsftpd/vsftpd.conf 2> /dev/null; then
        sed -i 's/^ssl_ciphers=.\+$/ssl_ciphers=ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA384:DHE-RSA-AES256-SHA256/' /etc/vsftpd/vsftpd.conf
    fi
fi

if [ "$WEB_SYSTEM" = "nginx" ] || [ "$PROXY_SYSTEM" = "nginx" ]; then
fi
