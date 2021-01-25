#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.3.0

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Add NPM to the default writeable folder list
echo "[ * ] Updating default writable folders for all users..."
for user in $($HESTIA/bin/v-list-sys-users plain); do
    mkdir -p \
        $HOMEDIR/$user/.npm

    chown $user:$user \
        $HOMEDIR/$user/.npm
done

# Add default SSL Certificate config when ip is visited
if [ "$PROXY_SYSTEM" = "nginx" ]; then
    echo "[ * ] Updating nginx configuration..."
    while read IP; do
        rm /etc/nginx/conf.d/$IP.conf
        cat $WEBTPL/$PROXY_SYSTEM/proxy_ip.tpl |\
        sed -e "s/%ip%/$IP/g" \
            -e "s/%web_port%/$WEB_PORT/g" \
            -e "s/%proxy_port%/$PROXY_PORT/g" \
            -e "s/%proxy_ssl_port%/$PROXY_SSL_PORT/g" \
        > /etc/$PROXY_SYSTEM/conf.d/$IP.conf
    done < <(ls $HESTIA/data/ips/)
fi

if [ "$FTP_SYSTEM" == "proftpd" ]; then
    echo "[ * ] Updating ProFTPd TLS configuration..."
    if [ -e  /etc/proftpd/proftpd.conf ]; then
        rm /etc/proftpd/proftpd.conf
    fi
    if [ -e  /etc/proftpd/tls.conf ]; then
        rm /etc/proftpd/tls.conf
    fi
    
    cp -f $HESTIA_INSTALL_DIR/proftpd/proftpd.conf /etc/proftpd/
    cp -f $HESTIA_INSTALL_DIR/proftpd/tls.conf /etc/proftpd/
    
fi

# Update exim configuration
if [ -e "/etc/exim4/exim4.conf.template" ]; then
    echo "[ * ] Updating exim4 configuration..."
    sed -i 's/${if match {${lc:$mime_filename}}{\\N(\\.ade|\\.adp|\\.bat|\\.chm|\\.cmd|\\.com|\\.cpl|\\.exe|\\.hta|\\.ins|\\.isp|\\.jse|\\.lib|\\.lnk|\\.mde|\\.msc|\\.msp|\\.mst|\\.pif|\\.scr|\\.sct|\\.shb|\\.sys|\\.vb|\\.vbe|\\.vbs|\\.vxd|\\.wsc|\\.wsf|\\.wsh)$\\N}{1}{0}}/${if match {${lc:$mime_filename}}{\\N(\\.ace|\\.ade|\\.adp|\\.app|\\.arj|\\.asp|\\.aspx|\\.asx|\\.bas|\\.bat|\\.cab|\\.cer|\\.chm|\\.cmd|\\.cnt|\\.com|\\.cpl|\\.crt|\\.csh|\\.der|\\.diagcab|\\.dll|\\.efi|\\.exe|\\.fla|\\.fon|\\.fxp|\\.gadget|\\.grp|\\.hlp|\\.hpj|\\.hta|\\.htc|\\.img|\\.inf|\\.ins|\\.iso|\\.isp|\\.its|\\.jar|\\.jnlp|\\.js|\\.jse|\\.ksh|\\.lib|\\.lnk|\\.mad|\\.maf|\\.mag|\\.mam|\\.maq|\\.mar|\\.mas|\\.mat|\\.mau|\\.mav|\\.maw|\\.mcf|\\.mda|\\.mdb|\\.mde|\\.mdt|\\.mdw|\\.mdz|\\.msc|\\.msh|\\.msh1|\\.msh1xml|\\.msh2|\\.msh2xml|\\.mshxml|\\.msi|\\.msp|\\.mst|\\.msu|\\.ops|\\.osd|\\.pcd|\\.pif|\\.pl|\\.plg|\\.prf|\\.prg|\\.printerexport|\\.ps1|\\.ps1xml|\\.ps2|\\.ps2xml|\\.psc1|\\.psc2|\\.psd1|\\.psdm1|\\.pst|\\.py|\\.pyc|\\.pyo|\\.pyw|\\.pyz|\\.pyzw|\\.reg|\\.scf|\\.scr|\\.sct|\\.sfx|\\.shb|\\.shs|\\.swf|\\.sys|\\.theme|\\.tmp|\\.ttf|\\.url|\\.vb|\\.vba|\\.vbe|\\.vbp|\\.vbs|\\.vhd|\\.vhdx|\\.vsmacros|\\.vsw|\\.vxd|\\.webpnp|\\.website|\\.wim|\\.ws|\\.wsc|\\.wsf|\\.wsh|\\.xbap|\\.xll|\\.xnk)$\\N}{1}{0}}/g' /etc/exim4/exim4.conf.template
fi

# Restrict access to /proc fs
if [ ! -e "/etc/cron.d/hestia-proc" ]; then
    echo "[ * ] Restricting access to /proc fs..."
    mount -o remount,defaults,hidepid=2 /proc > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        echo "[ ! ] WARNING: Cannot remount /proc (LXC containers require additional permissions be added to host apparmor profile)"
    else
        echo "@reboot root sleep 5 && mount -o remount,defaults,hidepid=2 /proc" > /etc/cron.d/hestia-proc
    fi
fi

# Change backup mode to zstd.
echo "[ * ] Setting zstd backup compression type as default..."
$BIN/v-change-sys-config-value "BACKUP_MODE" "zstd"
 
# Set LOGIN_STYLE variable in hestia.conf
echo "[ * ] Updating configuration file: hestia.conf..."
$BIN/v-change-sys-config-value "LOGIN_STYLE" "default"

# Remove old lanugage files.
if [ -e $HESTIA/web/inc/i18n/en.php ]; then 
    echo "[ * ] Performing language library clean-up..."
    rm -fr $HESTIA/web/inc/i18n
fi