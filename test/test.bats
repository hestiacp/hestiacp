#!/usr/bin/env bats

load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'
load 'test_helper/bats-file/load'


function random() {
    head /dev/urandom | tr -dc 0-9 | head -c$1
}

function setup() {
    # echo "# Setup_file" > &3
    if [ $BATS_TEST_NUMBER = 1 ]; then
        echo 'user=test-5285' > /tmp/hestia-test-env.sh
        echo 'userbk=testbk-5285' >> /tmp/hestia-test-env.sh
        echo 'userpass1=test-5285' >> /tmp/hestia-test-env.sh
        echo 'userpass2=t3st-p4ssw0rd' >> /tmp/hestia-test-env.sh
        echo 'HESTIA=/usr/local/hestia' >> /tmp/hestia-test-env.sh
        echo 'domain=test-5285.hestiacp.com' >> /tmp/hestia-test-env.sh
    fi

    source /tmp/hestia-test-env.sh
    source $HESTIA/func/main.sh
    source $HESTIA/conf/hestia.conf
    source $HESTIA/func/ip.sh
}

function validate_web_domain() {
    local user=$1
    local domain=$2
    local webproof=$3
    local webpath=${4}

    refute [ -z "$user" ]
    refute [ -z "$domain" ]
    refute [ -z "$webproof" ]

    source $HESTIA/func/ip.sh

    run v-list-web-domain $user $domain
    assert_success

    USER_DATA=$HESTIA/data/users/$user
    local domain_ip=$(get_object_value 'web' 'DOMAIN' "$domain" '$IP')
    SSL=$(get_object_value 'web' 'DOMAIN' "$domain" '$SSL')
    domain_ip=$(get_real_ip "$domain_ip")

    if [ ! -z $webpath ]; then
        assert_file_exist $HOMEDIR/$user/web/$domain/public_html/$webpath
    fi

    # Test HTTP
    run curl --location --silent --show-error --insecure --resolve "${domain}:80:${domain_ip}" "http://${domain}/${webpath}"
    assert_success
    assert_output --partial "$webproof"

    # Test HTTPS
    if [ "$SSL" = "yes" ]; then
        run v-list-web-domain-ssl $user $domain
        assert_success

        run curl --location --silent --show-error --insecure --resolve "${domain}:443:${domain_ip}" "https://${domain}/${webpath}"
        assert_success
        assert_output --partial "$webproof"
    fi
}

function validate_mail_domain() {
    local user=$1
    local domain=$2

    refute [ -z "$user" ]
    refute [ -z "$domain" ]

    run v-list-mail-domain $user $domain
    assert_success

    assert_dir_exist $HOMEDIR/$user/mail/$domain
    assert_dir_exist $HOMEDIR/$user/conf/mail/$domain

    assert_file_exist $HOMEDIR/$user/conf/mail/$domain/aliases
    assert_file_exist $HOMEDIR/$user/conf/mail/$domain/antispam
    assert_file_exist $HOMEDIR/$user/conf/mail/$domain/antivirus
    assert_file_exist $HOMEDIR/$user/conf/mail/$domain/fwd_only
    assert_file_exist $HOMEDIR/$user/conf/mail/$domain/ip
    assert_file_exist $HOMEDIR/$user/conf/mail/$domain/passwd
}

function validate_webmail_domain() {
    local user=$1
    local domain=$2
    local webproof=$3
    local webpath=${4}

    refute [ -z "$user" ]
    refute [ -z "$domain" ]
    refute [ -z "$webproof" ]

    source $HESTIA/func/ip.sh

    USER_DATA=$HESTIA/data/users/$user
    local domain_ip=$(get_object_value 'web' 'DOMAIN' "$domain" '$IP')
    SSL=$(get_object_value 'mail' 'DOMAIN' "$domain" '$SSL')
    domain_ip=$(get_real_ip "$domain_ip")

    if [ ! -z "$webpath" ]; then
        assert_file_exist /var/lib/roundcube/$webpath
    fi

    # Test HTTP
    run curl --location --silent --show-error --insecure --resolve "webmail.${domain}:80:${domain_ip}" "http://webmail.${domain}/${webpath}"
    assert_success
    assert_output --partial "$webproof"

    # Test HTTP
    run curl --location --silent --show-error --insecure --resolve "mail.${domain}:80:${domain_ip}" "http://mail.${domain}/${webpath}"
    assert_success
    assert_output --partial "$webproof"

    # Test HTTPS
    if [ "$SSL" = "yes" ]; then
        run v-list-mail-domain-ssl $user $domain
        assert_success

        run curl --location --silent --show-error --insecure --resolve "webmail.${domain}:443:${domain_ip}" "https://webmail.${domain}/${webpath}"
        assert_success
        assert_output --partial "$webproof"

        run curl --location --silent --show-error --insecure --resolve "mail.${domain}:443:${domain_ip}" "https://mail.${domain}/${webpath}"
        assert_success
        assert_output --partial "$webproof"
    fi
}

#----------------------------------------------------------#
#                         MAIN                             #
#----------------------------------------------------------#

@test "Add new userXXX" {
    skip
    run v-add-user $user $user $user@hestiacp.com default "Super Test"
    assert_success
    refute_output
}

#----------------------------------------------------------#
#                           IP                             #
#----------------------------------------------------------#

@test "Check reverse Dns validation" {

    # 1. PTR record for a IP should return a hostname(reverse) which in turn must resolve to the same IP addr(forward). (Full circle)
    #  `-> not implemented in `is_ip_rdns_valid` yet and also not tested here
    # 2. Reject rPTR records that match generic dynamic IP pool patterns

    local ip="54.200.1.22"
    local rdns="ec2-54-200-1-22.us-west-2.compute.amazonaws.com"
    run is_ip_rdns_valid "$ip"
    assert_failure
    refute_output

    local rdns="ec2.54.200.1.22.us-west-2.compute.amazonaws.com"
    run is_ip_rdns_valid "$ip"
    assert_failure
    refute_output

    local rdns="ec2-22-1-200-54.us-west-2.compute.amazonaws.com"
    run is_ip_rdns_valid "$ip"
    assert_failure
    refute_output

    local rdns="ec2.22.1.200.54.us-west-2.compute.amazonaws.com"
    run is_ip_rdns_valid "$ip"
    assert_failure
    refute_output

    local rdns="ec2-200-54-1-22.us-west-2.compute.amazonaws.com"
    run is_ip_rdns_valid "$ip"
    assert_failure
    refute_output

    local rdns="panel-22.mydomain.tld"
    run is_ip_rdns_valid "$ip"
    assert_success
    assert_output "$rdns"

    local rdns="mail.mydomain.tld"
    run is_ip_rdns_valid "$ip"
    assert_success
    assert_output "$rdns"

    local rdns="mydomain.tld"
    run is_ip_rdns_valid "$ip"
    assert_success
    assert_output "$rdns"

}

#----------------------------------------------------------#
#                         User                             #
#----------------------------------------------------------#

@test "Add new user" {
    run v-add-user $user $user $user@hestiacp.com default "Super Test"
    assert_success
    refute_output
}

@test "Change user password" {
    run v-change-user-password "$user" t3st-p4ssw0rd
    assert_success
    refute_output
}

@test "Change user email" {
    run v-change-user-contact "$user" tester@hestiacp.com
    assert_success
    refute_output
}

@test "Change user contact invalid email " {
    run v-change-user-contact "$user" testerhestiacp.com
    assert_failure $E_INVALID
    assert_output --partial 'Error: invalid email format'
}

@test "Change user name" {
    run v-change-user-name "$user" "New name"
    assert_success
    refute_output
}

@test "Change user shell" {
    run v-change-user-shell $user bash
    assert_success
    refute_output
}

@test "Change user invalid shell" {
    run v-change-user-shell $user bashinvalid
    assert_failure $E_INVALID
    assert_output --partial 'shell bashinvalid is not valid'
}

@test "Change user default ns" {
    run v-change-user-ns $user ns0.com ns1.com ns2.com ns3.com
    assert_success
    refute_output

    run v-list-user-ns "$user" plain
    assert_success
    assert_output --partial 'ns0.com'
}

#----------------------------------------------------------#
#                         Cron                             #
#----------------------------------------------------------#

@test "Cron: Add cron job" {
    run v-add-cron-job $user 1 1 1 1 1 echo
    assert_success
    refute_output
}

@test "Cron: Suspend cron job" {
    run v-suspend-cron-job $user 1
    assert_success
    refute_output
}

@test "Cron: Unsuspend cron job" {
    run v-unsuspend-cron-job $user 1
    assert_success
    refute_output
}

@test "Cron: Delete cron job" {
    run v-delete-cron-job $user 1
    assert_success
    refute_output
}

@test "Cron: Add cron job (duplicate)" {
    run v-add-cron-job $user 1 1 1 1 1 echo 1
    assert_success
    refute_output

    run v-add-cron-job $user 1 1 1 1 1 echo 1
    assert_failure $E_EXISTS
    assert_output --partial 'JOB=1 is already exists'
}

@test "Cron: Second cron job" {
    run v-add-cron-job $user 2 2 2 2 2 echo 2
    assert_success
    refute_output
}

@test "Cron: Two cron jobs must be listed" {
    run v-list-cron-jobs $user csv
    assert_success
    assert_line --partial '1,1,1,1,1,"echo",no'
    assert_line --partial '2,2,2,2,2,"echo",no'
}

@test "Cron: rebuild" {
    run v-rebuild-cron-jobs $user
    assert_success
    refute_output
}

#----------------------------------------------------------#
#                          IP                              #
#----------------------------------------------------------#

@test "Ip: Add new ip on first interface" {
    interface=$(v-list-sys-interfaces plain | head -n 1)
    run ip link show dev $interface
    assert_success

    local a2_rpaf="/etc/$WEB_SYSTEM/mods-enabled/rpaf.conf"
    local a2_remoteip="/etc/$WEB_SYSTEM/mods-enabled/remoteip.conf"

    # Save initial state
    echo "interface=${interface}" >> /tmp/hestia-test-env.sh
    [ -f "$a2_rpaf" ]     && file_hash1=$(cat $a2_rpaf     |md5sum |cut -d" " -f1) && echo "a2_rpaf_hash='${file_hash1}'"     >> /tmp/hestia-test-env.sh
    [ -f "$a2_remoteip" ] && file_hash2=$(cat $a2_remoteip |md5sum |cut -d" " -f1) && echo "a2_remoteip_hash='${file_hash2}'" >> /tmp/hestia-test-env.sh


    local ip="198.18.0.12"
    run v-add-sys-ip $ip 255.255.255.255 $interface $user
    assert_success
    refute_output

    assert_file_exist /etc/$WEB_SYSTEM/conf.d/$ip.conf
    assert_file_exist $HESTIA/data/ips/$ip
    assert_file_contains $HESTIA/data/ips/$ip "OWNER='$user'"
    assert_file_contains $HESTIA/data/ips/$ip "INTERFACE='$interface'"

    if [ -n "$PROXY_SYSTEM" ]; then
        assert_file_exist /etc/$PROXY_SYSTEM/conf.d/$ip.conf
        [ -f "$a2_rpaf" ] && assert_file_contains "$a2_rpaf" "RPAFproxy_ips.*$ip\b"
        [ -f "$a2_remoteip" ] && assert_file_contains "$a2_remoteip" "RemoteIPInternalProxy $ip\$"
    fi

}

@test "Ip: Add ip (duplicate)" {
    run v-add-sys-ip 198.18.0.12 255.255.255.255 $interface $user
    assert_failure $E_EXISTS
}

@test "Ip: Add extra ip" {
    local ip="198.18.0.121"
    run v-add-sys-ip $ip 255.255.255.255 $interface $user
    assert_success
    refute_output

    assert_file_exist /etc/$WEB_SYSTEM/conf.d/$ip.conf
    assert_file_exist $HESTIA/data/ips/$ip
    assert_file_contains $HESTIA/data/ips/$ip "OWNER='$user'"
    assert_file_contains $HESTIA/data/ips/$ip "INTERFACE='$interface'"

    if [ -n "$PROXY_SYSTEM" ]; then
        assert_file_exist /etc/$PROXY_SYSTEM/conf.d/$ip.conf
        local a2_rpaf="/etc/$WEB_SYSTEM/mods-enabled/rpaf.conf"
        [ -f "$a2_rpaf" ] && assert_file_contains "$a2_rpaf" "RPAFproxy_ips.*$ip\b"

        local a2_remoteip="/etc/$WEB_SYSTEM/mods-enabled/remoteip.conf"
        [ -f "$a2_remoteip" ] && assert_file_contains "$a2_remoteip" "RemoteIPInternalProxy $ip\$"
    fi
}

@test "Ip: Delete ips" {
    local ip="198.18.0.12"
    run v-delete-sys-ip $ip
    assert_success
    refute_output

    assert_file_not_exist /etc/$WEB_SYSTEM/conf.d/$ip.conf
    assert_file_not_exist $HESTIA/data/ips/$ip


    ip="198.18.0.121"
    run v-delete-sys-ip $ip
    assert_success
    refute_output

    assert_file_not_exist /etc/$WEB_SYSTEM/conf.d/$ip.conf
    assert_file_not_exist $HESTIA/data/ips/$ip

    if [ -n "$PROXY_SYSTEM" ]; then
        assert_file_not_exist /etc/$PROXY_SYSTEM/conf.d/$ip.conf
    fi

    # remoteip and rpaf config hashes must match the initial one
    if [ ! -z "$a2_rpaf_hash" ]; then
        local a2_rpaf="/etc/$WEB_SYSTEM/mods-enabled/rpaf.conf"
        file_hash=$(cat $a2_rpaf |md5sum |cut -d" " -f1)
        assert_equal "$file_hash" "$a2_rpaf_hash"
    fi
    if [ ! -z "$a2_remoteip_hash" ]; then
        local a2_remoteip="/etc/$WEB_SYSTEM/mods-enabled/remoteip.conf"
        file_hash=$(cat $a2_remoteip |md5sum |cut -d" " -f1)
        assert_equal "$file_hash" "$a2_remoteip_hash"
    fi
}

@test "Ip: Add IP for rest of the test" {
    local ip="198.18.0.125"
    run v-add-sys-ip $ip 255.255.255.255 $interface $user
    assert_success
    refute_output

    assert_file_exist /etc/$WEB_SYSTEM/conf.d/$ip.conf
    assert_file_exist $HESTIA/data/ips/$ip
    assert_file_contains $HESTIA/data/ips/$ip "OWNER='$user'"
    assert_file_contains $HESTIA/data/ips/$ip "INTERFACE='$interface'"

    if [ -n "$PROXY_SYSTEM" ]; then
        assert_file_exist /etc/$PROXY_SYSTEM/conf.d/$ip.conf
        local a2_rpaf="/etc/$WEB_SYSTEM/mods-enabled/rpaf.conf"
        [ -f "$a2_rpaf" ] && assert_file_contains "$a2_rpaf" "RPAFproxy_ips.*$ip\b"

        local a2_remoteip="/etc/$WEB_SYSTEM/mods-enabled/remoteip.conf"
        [ -f "$a2_remoteip" ] && assert_file_contains "$a2_remoteip" "RemoteIPInternalProxy $ip\$"
    fi
}


#----------------------------------------------------------#
#                         WEB                              #
#----------------------------------------------------------#

@test "WEB: Add web domain" {
    run v-add-web-domain $user $domain 198.18.0.125
    assert_success
    refute_output

    echo -e "<?php\necho 'Hestia Test:'.(4*3);" > $HOMEDIR/$user/web/$domain/public_html/php-test.php
    validate_web_domain $user $domain 'Hestia Test:12' 'php-test.php'
    rm $HOMEDIR/$user/web/$domain/public_html/php-test.php
}

@test "WEB: Add web domain (duplicate)" {
    run v-add-web-domain $user $domain 198.18.0.125
    assert_failure $E_EXISTS
}

@test "WEB: Add web domain alias" {
    run v-add-web-domain-alias $user $domain v3.$domain
    assert_success
    refute_output
}

@test "WEB: Add web domain alias (duplicate)" {
    run v-add-web-domain-alias $user $domain v3.$domain
    assert_failure $E_EXISTS
}

@test "WEB: Add web domain stats" {
    run v-add-web-domain-stats $user $domain awstats
    assert_success
    refute_output
}

@test "WEB: Add web domain stats user" {
    skip
    run v-add-web-domain-stats-user $user $domain test m3g4p4ssw0rd
    assert_success
    refute_output
}

@test "WEB: Suspend web domain" {
    run v-suspend-web-domain $user $domain
    assert_success
    refute_output

    validate_web_domain $user $domain 'This site is currently suspended'
}

@test "WEB: Unsuspend web domain" {
    run v-unsuspend-web-domain $user $domain
    assert_success
    refute_output

    echo -e "<?php\necho 'Hestia Test:'.(4*3);" > $HOMEDIR/$user/web/$domain/public_html/php-test.php
    validate_web_domain $user $domain 'Hestia Test:12' 'php-test.php'
    rm $HOMEDIR/$user/web/$domain/public_html/php-test.php
}

@test "WEB: Add ssl" {
    cp -f $HESTIA/ssl/certificate.crt /tmp/$domain.crt
    cp -f $HESTIA/ssl/certificate.key /tmp/$domain.key

    run v-add-web-domain-ssl $user $domain /tmp
    assert_success
    refute_output
}

@test "WEB: Rebuild web domain" {
    run v-rebuild-web-domains $user
    assert_success
    refute_output
}


#----------------------------------------------------------#
#                      MULTIPHP                            #
#----------------------------------------------------------#

@test "Multiphp: Default php Backend version" {
    def_phpver=$(multiphp_default_version)
    multi_domain="multiphp.${domain}"

    run v-add-web-domain $user $multi_domain 198.18.0.125
    assert_success
    refute_output

    echo -e "<?php\necho PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" > "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
    validate_web_domain $user $multi_domain "$def_phpver" 'php-test.php'
    rm "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"

}

@test "Multiphp: Change backend version - PHP v5.6" {
    test_phpver='5.6'
    multi_domain="multiphp.${domain}"

    if [ ! -d "/etc/php/${test_phpver}/fpm/pool.d/" ]; then
        skip "PHP ${test_phpver} not installed"
    fi

    run v-change-web-domain-backend-tpl $user $multi_domain 'PHP-5_6' 'yes'
    assert_success
    refute_output

    # Changing web backend will create a php-fpm pool config in the corresponding php folder
    assert_file_exist "/etc/php/${test_phpver}/fpm/pool.d/${multi_domain}.conf"

    # A single php-fpm pool config file must be present
    num_fpm_config_files="$(find -L /etc/php/ -name "${multi_domain}.conf" | wc -l)"
    assert_equal "$num_fpm_config_files" '1'

    echo -e "<?php\necho 'hestia-multiphptest:'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" > "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
    validate_web_domain $user $multi_domain "hestia-multiphptest:$test_phpver" 'php-test.php'
    rm "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
}

@test "Multiphp: Change backend version - PHP v7.0" {
    test_phpver='7.0'
    multi_domain="multiphp.${domain}"

    if [ ! -d "/etc/php/${test_phpver}/fpm/pool.d/" ]; then
        skip "PHP ${test_phpver} not installed"
    fi

    run v-change-web-domain-backend-tpl $user $multi_domain 'PHP-7_0' 'yes'
    assert_success
    refute_output

    # Changing web backend will create a php-fpm pool config in the corresponding php folder
    assert_file_exist "/etc/php/${test_phpver}/fpm/pool.d/${multi_domain}.conf"

    # A single php-fpm pool config file must be present
    num_fpm_config_files="$(find -L /etc/php/ -name "${multi_domain}.conf" | wc -l)"
    assert_equal "$num_fpm_config_files" '1'

    echo -e "<?php\necho 'hestia-multiphptest:'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" > "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
    validate_web_domain $user $multi_domain "hestia-multiphptest:$test_phpver" 'php-test.php'
    rm "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
}

@test "Multiphp: Change backend version - PHP v7.1" {
    test_phpver='7.1'
    multi_domain="multiphp.${domain}"

    if [ ! -d "/etc/php/${test_phpver}/fpm/pool.d/" ]; then
        skip "PHP ${test_phpver} not installed"
    fi

    run v-change-web-domain-backend-tpl $user $multi_domain 'PHP-7_1' 'yes'
    assert_success
    refute_output

    # Changing web backend will create a php-fpm pool config in the corresponding php folder
    assert_file_exist "/etc/php/${test_phpver}/fpm/pool.d/${multi_domain}.conf"

    # A single php-fpm pool config file must be present
    num_fpm_config_files="$(find -L /etc/php/ -name "${multi_domain}.conf" | wc -l)"
    assert_equal "$num_fpm_config_files" '1'

    echo -e "<?php\necho 'hestia-multiphptest:'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" > "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
    validate_web_domain $user $multi_domain "hestia-multiphptest:$test_phpver" 'php-test.php'
    rm "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
}

@test "Multiphp: Change backend version - PHP v7.2" {
    test_phpver='7.2'
    multi_domain="multiphp.${domain}"

    if [ ! -d "/etc/php/${test_phpver}/fpm/pool.d/" ]; then
        skip "PHP ${test_phpver} not installed"
    fi

    run v-change-web-domain-backend-tpl $user $multi_domain 'PHP-7_2' 'yes'
    assert_success
    refute_output

    # Changing web backend will create a php-fpm pool config in the corresponding php folder
    assert_file_exist "/etc/php/${test_phpver}/fpm/pool.d/${multi_domain}.conf"

    # A single php-fpm pool config file must be present
    num_fpm_config_files="$(find -L /etc/php/ -name "${multi_domain}.conf" | wc -l)"
    assert_equal "$num_fpm_config_files" '1'

    echo -e "<?php\necho 'hestia-multiphptest:'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" > "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
    validate_web_domain $user $multi_domain "hestia-multiphptest:$test_phpver" 'php-test.php'
    rm "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
}

@test "Multiphp: Change backend version - PHP v7.3" {
    test_phpver='7.3'
    multi_domain="multiphp.${domain}"

    if [ ! -d "/etc/php/${test_phpver}/fpm/pool.d/" ]; then
        skip "PHP ${test_phpver} not installed"
    fi

    run v-change-web-domain-backend-tpl $user $multi_domain 'PHP-7_3' 'yes'
    assert_success
    refute_output

    # Changing web backend will create a php-fpm pool config in the corresponding php folder
    assert_file_exist "/etc/php/${test_phpver}/fpm/pool.d/${multi_domain}.conf"

    # A single php-fpm pool config file must be present
    num_fpm_config_files="$(find -L /etc/php/ -name "${multi_domain}.conf" | wc -l)"
    assert_equal "$num_fpm_config_files" '1'

    echo -e "<?php\necho 'hestia-multiphptest:'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" > "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
    validate_web_domain $user $multi_domain "hestia-multiphptest:$test_phpver" 'php-test.php'
    rm "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
}

@test "Multiphp: Change backend version - PHP v7.4" {
    test_phpver='7.4'
    multi_domain="multiphp.${domain}"

    if [ ! -d "/etc/php/${test_phpver}/fpm/pool.d/" ]; then
        skip "PHP ${test_phpver} not installed"
    fi

    run v-change-web-domain-backend-tpl $user $multi_domain 'PHP-7_4' 'yes'
    assert_success
    refute_output

    # Changing web backend will create a php-fpm pool config in the corresponding php folder
    assert_file_exist "/etc/php/${test_phpver}/fpm/pool.d/${multi_domain}.conf"

    # A single php-fpm pool config file must be present
    num_fpm_config_files="$(find -L /etc/php/ -name "${multi_domain}.conf" | wc -l)"
    assert_equal "$num_fpm_config_files" '1'

    echo -e "<?php\necho 'hestia-multiphptest:'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" > "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
    validate_web_domain $user $multi_domain "hestia-multiphptest:$test_phpver" 'php-test.php'
    rm "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
}

@test "Multiphp: Change backend version - PHP v8.0" {
    test_phpver='8.0'
    multi_domain="multiphp.${domain}"

    if [ ! -d "/etc/php/${test_phpver}/fpm/pool.d/" ]; then
        skip "PHP ${test_phpver} not installed"
    fi

    run v-change-web-domain-backend-tpl $user $multi_domain 'PHP-8_0' 'yes'
    assert_success
    refute_output

    # Changing web backend will create a php-fpm pool config in the corresponding php folder
    assert_file_exist "/etc/php/${test_phpver}/fpm/pool.d/${multi_domain}.conf"

    # A single php-fpm pool config file must be present
    num_fpm_config_files="$(find -L /etc/php/ -name "${multi_domain}.conf" | wc -l)"
    assert_equal "$num_fpm_config_files" '1'

    echo -e "<?php\necho 'hestia-multiphptest:'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" > "$HOMEDIR/$user/web/$multi_domain/public_html/php-test.php"
    validate_web_domain $user $multi_domain "hestia-multiphptest:$test_phpver" 'php-test.php'
    rm $HOMEDIR/$user/web/$multi_domain/public_html/php-test.php
}

@test "Multiphp: Cleanup" {
    multi_domain="multiphp.${domain}"

    run v-delete-web-domain $user $multi_domain 'yes'
    assert_success
    refute_output

    # No php-fpm pool config file must be present
    num_fpm_config_files="$(find -L /etc/php/ -name "${multi_domain}.conf" | wc -l)"
    assert_equal "$num_fpm_config_files" '0'
}


#----------------------------------------------------------#
#                         DNS                              #
#----------------------------------------------------------#

@test "DNS: Add domain" {
    run v-add-dns-domain $user $domain 198.18.0.125
    assert_success
    refute_output
}

@test "DNS: Add domain (duplicate)" {
    run v-add-dns-domain $user $domain 198.18.0.125
    assert_failure $E_EXISTS
}

@test "DNS: Add domain record" {
    run v-add-dns-record $user $domain test A 198.18.0.125 20
    assert_success
    refute_output
}

@test "DNS: Delete domain record" {
    run v-delete-dns-record $user $domain 20
    assert_success
    refute_output
}

@test "DNS: Delete missing domain record" {
    run v-delete-dns-record $user $domain 20
    assert_failure $E_NOTEXIST
}

@test "DNS: Change domain expire date" {
    run v-change-dns-domain-exp $user $domain 2020-01-01
    assert_success
    refute_output
}

@test "DNS: Change domain ip" {
    run v-change-dns-domain-ip $user $domain 127.0.0.1
    assert_success
    refute_output
}

@test "DNS: Suspend domain" {
    run v-suspend-dns-domain $user $domain
    assert_success
    refute_output
}

@test "DNS: Unsuspend domain" {
    run v-unsuspend-dns-domain $user $domain
    assert_success
    refute_output
}

@test "DNS: Rebuild" {
    run v-rebuild-dns-domains $user
    assert_success
    refute_output
}

#----------------------------------------------------------#
#                         MAIL                             #
#----------------------------------------------------------#

@test "MAIL: Add domain" {
    run v-add-mail-domain $user $domain
    assert_success
    refute_output

    validate_mail_domain $user $domain

    # echo -e "<?php\necho 'Server: ' . \$_SERVER['SERVER_SOFTWARE'];" > /var/lib/roundcube/check_server.php
    validate_webmail_domain $user $domain 'Welcome to Roundcube Webmail'
    # rm /var/lib/roundcube/check_server.php
}

@test "MAIL: Add domain (duplicate)" {
    run v-add-mail-domain $user $domain
    assert_failure $E_EXISTS
}

@test "MAIL: Add account" {
    run v-add-mail-account $user $domain test t3st-p4ssw0rd
    assert_success
    refute_output
}

@test "MAIL: Add account (duplicate)" {
    run v-add-mail-account $user $domain test t3st-p4ssw0rd
    assert_failure $E_EXISTS
}

@test "MAIL: Delete account" {
    run v-delete-mail-account $user $domain test
    assert_success
    refute_output
}

@test "MAIL: Delete missing account" {
    run v-delete-mail-account $user $domain test
    assert_failure $E_NOTEXIST
}


#----------------------------------------------------------#
#                         DB                               #
#----------------------------------------------------------#


#----------------------------------------------------------#
#                     Backup / Restore                     #
#----------------------------------------------------------#

#Test backup
#  Hestia v1.1.1 archive contains:
#    user: hestia111
#    web:
#      - test.hestia.com (+SSL self-signed)
#    dns:
#      - test.hestia.com
#    mail:
#      - test.hestia.com
#    mail acc:
#      - testaccount@test.hestia.com
#    db:
#      - hestia111_db
#    cron:
#      - 1: /bin/true
#
#  Vesta 0.9.8-23 archive contains:
#    user: vesta09823
#    web:
#      - vesta09823.tld (+SSL self-signed)
#    dns:
#      - vesta09823.tld
#    mail:
#      - vesta09823.tld
#    mail acc:
#      - testaccount@vesta09823.tld
#    db:
#      - vesta09823_db
#    cron:
#      - 1: /bin/true
#

# Testing Hestia backups
@test "Restore[1]: Hestia archive for a non-existing user" {
    if [ -d "$HOMEDIR/$userbk" ]; then
        run v-delete-user $userbk
        assert_success
        refute_output
    fi

    mkdir -p /backup

    local archive_name="hestia111.2020-03-26"
    run wget --quiet --tries=3 --timeout=15 --read-timeout=15 --waitretry=3 --no-dns-cache "https://hestiacp.com/testing/data/${archive_name}.tar" -O "/backup/${archive_name}.tar"
    assert_success

    run v-restore-user $userbk "${archive_name}.tar"
    assert_success

    rm "/backup/${archive_name}.tar"
}

@test "Restore[1]: From Hestia [WEB]" {
    local domain="test.hestia.com"
    validate_web_domain $userbk $domain 'Hello Hestia'
}

@test "Restore[1]: From Hestia [DNS]" {
    local domain="test.hestia.com"

    run v-list-dns-domain $userbk $domain
    assert_success

    run nslookup $domain 127.0.0.1
    assert_success
}

@test "Restore[1]: From Hestia [MAIL]" {
    local domain="test.hestia.com"

    run v-list-mail-domain $userbk $domain
    assert_success
}

@test "Restore[1]: From Hestia [MAIL-Account]" {
    local domain="test.hestia.com"

    run v-list-mail-account $userbk $domain testaccount
    assert_success
}

@test "Restore[1]: From Hestia [DB]" {
    run v-list-database $userbk "${userbk}_db"
    assert_success
}

@test "Restore[1]: From Hestia [CRON]" {
    run v-list-cron-job $userbk 1
    assert_success
}

@test "Restore[1]: From Hestia Cleanup" {
    run v-delete-user $userbk
    assert_success
    refute_output
}


@test "Restore[2]: Hestia archive over a existing user" {
    if [ -d "$HOMEDIR/$userbk" ]; then
        run v-delete-user $userbk
        assert_success
        refute_output
    fi

    if [ ! -d "$HOMEDIR/$userbk" ]; then
        run v-add-user $userbk $userbk test@hestia.com
        assert_success
    fi

    mkdir -p /backup

    local archive_name="hestia111.2020-03-26"
    run wget --quiet --tries=3 --timeout=15 --read-timeout=15 --waitretry=3 --no-dns-cache "https://hestiacp.com/testing/data/${archive_name}.tar" -O "/backup/${archive_name}.tar"
    assert_success

    run v-restore-user $userbk "${archive_name}.tar"
    assert_success

    rm "/backup/${archive_name}.tar"
}

@test "Restore[2]: From Hestia [WEB]" {
    local domain="test.hestia.com"
    validate_web_domain $userbk "${domain}" 'Hello Hestia'
}

@test "Restore[2]: From Hestia [DNS]" {
    local domain="test.hestia.com"

    run v-list-dns-domain $userbk $domain
    assert_success

    run nslookup $domain 127.0.0.1
    assert_success
}

@test "Restore[2]: From Hestia [MAIL]" {
    local domain="test.hestia.com"

    run v-list-mail-domain $userbk $domain
    assert_success
}

@test "Restore[2]: From Hestia [MAIL-Account]" {
    local domain="test.hestia.com"

    run v-list-mail-account $userbk $domain testaccount
    assert_success
}

@test "Restore[2]: From Hestia [DB]" {
    run v-list-database $userbk "${userbk}_db"
    assert_success
}

@test "Restore[2]: From Hestia [CRON]" {
    run v-list-cron-job $userbk 1
    assert_success
}

@test "Restore[2]: From Hestia Cleanup" {
    run v-delete-user $userbk
    assert_success
    refute_output
}


# Testing Vesta Backups
@test "Restore[1]: Vesta archive for a non-existing user" {
    if [ -d "$HOMEDIR/$userbk" ]; then
        run v-delete-user $userbk
        assert_success
        refute_output
    fi

    mkdir -p /backup

    local archive_name="vesta09823.2018-10-18"
    run wget --quiet --tries=3 --timeout=15 --read-timeout=15 --waitretry=3 --no-dns-cache "https://hestiacp.com/testing/data/${archive_name}.tar" -O "/backup/${archive_name}.tar"
    assert_success

    run v-restore-user $userbk "${archive_name}.tar"
    assert_success

    rm "/backup/${archive_name}.tar"
}

@test "Restore[1]: From Vesta [WEB]" {
    local domain="vesta09823.tld"
    validate_web_domain $userbk $domain 'Hello Vesta'
}

@test "Restore[1]: From Vesta [DNS]" {
    local domain="vesta09823.tld"

    run v-list-dns-domain $userbk $domain
    assert_success

    run nslookup $domain 127.0.0.1
    assert_success
}

@test "Restore[1]: From Vesta [MAIL]" {
    local domain="vesta09823.tld"

    run v-list-mail-domain $userbk $domain
    assert_success
}

@test "Restore[1]: From Vesta [MAIL-Account]" {
    local domain="vesta09823.tld"

    run v-list-mail-account $userbk $domain testaccount
    assert_success
}

@test "Restore[1]: From Vesta [DB]" {
    run v-list-database $userbk "${userbk}_db"
    assert_success
}

@test "Restore[1]: From Vesta [CRON]" {
    run v-list-cron-job $userbk 1
    assert_success
}

@test "Restore[1]: From Vesta Cleanup" {
    run v-delete-user $userbk
    assert_success
    refute_output
}


@test "Restore[2]: Vesta archive over a existing user" {
    if [ -d "$HOMEDIR/$userbk" ]; then
        run v-delete-user $userbk
        assert_success
        refute_output
    fi

    if [ ! -d "$HOMEDIR/$userbk" ]; then
        run v-add-user $userbk $userbk test@hestia.com
        assert_success
    fi

    mkdir -p /backup

    local archive_name="vesta09823.2018-10-18"
    run wget --quiet --tries=3 --timeout=15 --read-timeout=15 --waitretry=3 --no-dns-cache "https://hestiacp.com/testing/data/${archive_name}.tar" -O "/backup/${archive_name}.tar"
    assert_success

    run v-restore-user $userbk "${archive_name}.tar"
    assert_success

    rm "/backup/${archive_name}.tar"
}

@test "Restore[2]: From Vesta [WEB]" {
    local domain="vesta09823.tld"
    validate_web_domain $userbk "${domain}" 'Hello Vesta'
}

@test "Restore[2]: From Vesta [DNS]" {
    local domain="vesta09823.tld"

    run v-list-dns-domain $userbk $domain
    assert_success

    run nslookup $domain 127.0.0.1
    assert_success
}

@test "Restore[2]: From Vesta [MAIL]" {
    local domain="vesta09823.tld"

    run v-list-mail-domain $userbk $domain
    assert_success
}

@test "Restore[2]: From Vesta [MAIL-Account]" {
    local domain="vesta09823.tld"

    run v-list-mail-account $userbk $domain testaccount
    assert_success
}

@test "Restore[2]: From Vesta [DB]" {
    run v-list-database $userbk "${userbk}_db"
    assert_success
}

@test "Restore[2]: From Vesta [CRON]" {
    run v-list-cron-job $userbk 1
    assert_success
}

@test "Restore[2]: From Vesta Cleanup" {
    run v-delete-user $userbk
    assert_success
    refute_output
}


#----------------------------------------------------------#
#                         CLEANUP                          #
#----------------------------------------------------------#

@test "Mail: Delete domain" {
    # skip
    run v-delete-mail-domain $user $domain
    assert_success
    refute_output
}

@test "DNS: Delete domain" {
    # skip
    run v-delete-dns-domain $user $domain
    assert_success
    refute_output
}

@test "WEB: Delete domain" {
    # skip
    run v-delete-web-domain $user $domain
    assert_success
    refute_output
}

@test "Delete user" {
    # skip
    run v-delete-user $user
    assert_success
    refute_output
}

@test "Ip: Delete the test IP" {
    # skip
    run v-delete-sys-ip 198.18.0.125
    assert_success
    refute_output
}

@test 'assert()' {
  touch '/var/log/test.log'
  assert [ -e '/var/log/test.log' ]
}
