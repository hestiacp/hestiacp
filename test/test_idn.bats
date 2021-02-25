#!/usr/bin/env bats

# Extension for tests.bats to check for issues with IDN domains

load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'
load 'test_helper/bats-file/load'


function random() {
    head /dev/urandom | tr -dc 0-9 | head -c$1
}

function setup() {
    # echo "# Setup_file" > &3
    if [ $BATS_TEST_NUMBER = 1 ]; then
        echo 'user=testidn-5285' > /tmp/hestia-test-env.sh
        echo 'userbk=testbk-5285' >> /tmp/hestia-test-env.sh
        echo 'userpass1=testidn-5285' >> /tmp/hestia-test-env.sh
        echo 'userpass2=t3st-p4ssw0rd' >> /tmp/hestia-test-env.sh
        echo 'HESTIA=/usr/local/hestia' >> /tmp/hestia-test-env.sh
        echo 'domain=смместро.рф' >> /tmp/hestia-test-env.sh
        echo 'domain2=xn--e1anajjcdi.xn--p1ai' >> /tmp/hestia-test-env.sh
        echo 'ip=198.18.0.125' >> /tmp/hestia-test-env.sh
        echo 'ip2=198.18.0.126' >> /tmp/hestia-test-env.sh
        
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
    local domain_ip=$(get_object_value 'WEB' 'DOMAIN' "$domain" '$IP')
    SSL=$(get_object_value 'WEB' 'DOMAIN' "$domain" '$SSL')
    domain_ip=$(get_real_ip "$domain_ip")

    if [ ! -z $webpath ]; then
        domain_docroot=$(get_object_value 'WEB' 'DOMAIN' "$domain" '$CUSTOM_DOCROOT')
        if [ -n "$domain_docroot" ] && [ -d "$domain_docroot" ]; then
            assert_file_exist "${domain_docroot}/${webpath}"
        else
            assert_file_exist "${HOMEDIR}/${user}/WEB/${domain}/public_html/${webpath}"
        fi
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
    local domain=$(idn -t --quiet -a "$2" )

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

    domain_idn=$(idn -t --quiet -a "$domain" )
    source $HESTIA/func/ip.sh

    USER_DATA=$HESTIA/data/users/$user
    local domain_ip=$(get_object_value 'web' 'DOMAIN' "$domain_idn" '$IP')
    SSL=$(get_object_value 'mail' 'DOMAIN' "$domain_idn" '$SSL')
    domain_ip=$(get_real_ip "$domain_ip")

    if [ ! -z "$webpath" ]; then
        assert_file_exist /var/lib/roundcube/$webpath
    fi
    
    # Test HTTP
    run curl --location --silent --show-error --insecure --resolve "webmail.${domain_idn}:80:${domain_ip}" "http://webmail.${domain_idn}/${webpath}"
    assert_success
    assert_output --partial "$webproof"

    # Test HTTP
    run curl --location --silent --show-error --insecure --resolve "mail.${domain_idn}:80:${domain_ip}" "http://mail.${domain_idn}/${webpath}"
    assert_success
    assert_output --partial "$webproof"

    # Test HTTPS
    if [ "$SSL" = "yes" ]; then
        run v-list-mail-domain-ssl $user $domain
        assert_success

        run curl --location --silent --show-error --insecure --resolve "webmail.${domain_idn}:443:${domain_ip}" "https://webmail.${domain_idn}/${webpath}"
        assert_success
        assert_output --partial "$webproof"

        run curl --location --silent --show-error --insecure --resolve "mail.${domain_idn}:443:${domain_ip}" "https://mail.${domain_idn}/${webpath}"
        assert_success
        assert_output --partial "$webproof"
    fi
}

#----------------------------------------------------------#
#                         MAIN                             #
#----------------------------------------------------------#

# Add Test IP
@test "IP: Add Test IP" {
    interface=$(v-list-sys-interfaces plain | head -n 1)
    run v-add-sys-ip $ip 255.255.255.255 $interface
    assert_success
    refute_output
}

@test "IP: Add 2nd Test IP" {
    interface=$(v-list-sys-interfaces plain | head -n 1)
    run v-add-sys-ip $ip2 255.255.255.255 $interface
    assert_success
    refute_output
}

# create test user
@test "User: Add test user" {
    run v-add-user $user $user "test@idn.nu" default $ip
    assert_success
    refute_output
}

#----------------------------------------------------------#
#                   WEB (смместро.рф)                      #
#----------------------------------------------------------#

@test "WEB: Add WEB domain IDN" {
    run v-add-web-domain $user $domain $ip
    assert_success
    refute_output
}

@test "WEB: List WEB domain IDN" {
    run  v-list-web-domain $user $domain2
    assert_success
    assert_output --partial $domain2
}
@test "WEB: Delete WEB domain IDN" {
    run  v-delete-web-domain $user $domain
    assert_success
    refute_output
}
@test "WEB: List WEB domain IDN (Fail)" {
    run  v-list-web-domain $user $domain
    assert_failure $E_NOTEXIST   
}
@test "WEB: Add WEB domain + Alias IDN" {
    run v-add-web-domain $user $domain $ip yes  "test.${domain},test2.${domain},www.${domain}"
    assert_success
    refute_output
}
@test "WEB: List WEB domain (Alias) IDN" {
    run  v-list-web-domain $user $domain2
    assert_success
    assert_output --partial "test2.${domain2}"
}
@test "WEB: Delete Alias IDN" {
    run v-delete-web-domain-alias $user $domain "test.${domain}"
    assert_success
    refute_output
}
@test "WEB: Add Alias IDN" {
    run v-add-web-domain-alias $user $domain "test.${domain}"
    assert_success
    refute_output
}

@test "WEB: Add Alias (Wildcard) IDN" {
    run v-add-web-domain-alias $user $domain "*.${domain}"
    assert_success
    refute_output
}
@test "WEB: Delete Alias (Wildcard) IDN" {
    run v-delete-web-domain-alias $user $domain "*.${domain}"
    assert_success
    refute_output
}

@test "WEB: Add FTP user" {
    run v-add-web-domain-ftp $user $domain "test" "test_${domain}"
    assert_success
    refute_output
}

@test "WEB: Delete FTP user" {
    run v-delete-web-domain-ftp $user $domain "${user}_test"
    assert_success
    refute_output
}

@test "WEB: Add ssl" {
    cp -f $HESTIA/ssl/certificate.crt /tmp/$domain2.crt
    cp -f $HESTIA/ssl/certificate.key /tmp/$domain2.key

    run v-add-web-domain-ssl $user $domain /tmp
    assert_success
    refute_output
}

@test "WEB: Add force ssl" {
    run v-add-web-domain-ssl-force $user $domain
    assert_success
    refute_output
}

@test "WEB: Change Web IP" {
    run v-change-web-domain-ip $user $domain $ip2
    assert_success
    refute_output
}

@test "WEB: Change Web IP (back to first)" {
    run v-change-web-domain-ip $user $domain $ip
    assert_success
    refute_output
}


#----------------------------------------------------------#
#                   MAIL (смместро.рф)                     #
#----------------------------------------------------------#

@test "MAIL:  Add mail domain" {
    run v-add-mail-domain $user $domain
    assert_success
    refute_output
    # We convert IDN always to punicode to verify it
    validate_mail_domain $user "$domain"

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
#                         DNS                              #
#----------------------------------------------------------#

@test "DNS: Add domain" {
    run v-add-dns-domain $user $domain $ip
    assert_success
    refute_output
}

@test "DNS: Add domain (duplicate)" {
    run v-add-dns-domain $user $domain $ip
    assert_failure $E_EXISTS
}

@test "DNS: Add domain record" {
    run v-add-dns-record $user $domain test A $ip
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
    run v-change-dns-domain-exp $user $domain "2021-04-04"
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
#                  RESET USER                              #
#----------------------------------------------------------#

@test "User: Delete test user IDN" {
    run v-delete-user $user
    assert_success
    refute_output
}
# create test user
@test "User: Add test user IDN" {
    run v-add-user $user $user "test@idn.nu" default $ip
    assert_success
    refute_output
}

#----------------------------------------------------------#
#             WEB (xn--e1aaujjcdi.xn--p1ai)                #
#----------------------------------------------------------#

@test "2WEB: Add WEB domain IDN" {
    run v-add-web-domain $user $domain2 $ip
    assert_success
    refute_output
}

@test "2WEB: List WEB domain IDN" {
    run  v-list-web-domain $user $domain2
    assert_success
    assert_output --partial $domain2
}
@test "2WEB: Delete WEB domain IDN" {
    run  v-delete-web-domain $user $domain2
    assert_success
    refute_output
}
@test "2WEB: List WEB domain IDN (Fail)" {
    run  v-list-web-domain $user $domain2
    assert_failure $E_NOTEXIST   
}
@test "2WEB: Add WEB domain + Alias IDN" {
    run v-add-web-domain $user $domain2 $ip yes  "test.${domain},test2.${domain},www.${domain}"
    assert_success
    refute_output
}
@test "2WEB: List WEB domain (Alias) IDN" {
    run  v-list-web-domain $user $domain2
    assert_success
    assert_output --partial "test2.${domain2}"
}
@test "2WEB: Delete Alias IDN" {
    run v-delete-web-domain-alias $user $domain2 "test.${domain}"
    assert_success
    refute_output
}
@test "2WEB: Add Alias IDN" {
    run v-add-web-domain-alias $user $domain2 "test.${domain}"
    assert_success
    refute_output
}

@test "2WEB: Add Alias (Wildcard) IDN" {
    run v-add-web-domain-alias $user $domain2 "*.${domain}"
    assert_success
    refute_output
}
@test "2WEB: Delete Alias (Wildcard) IDN" {
    run v-delete-web-domain-alias $user $domain2 "*.${domain}"
    assert_success
    refute_output
}

@test "2WEB: Add FTP user" {
    run v-add-web-domain-ftp $user $domain2 "test" "test_${domain}"
    assert_success
    refute_output
}

@test "2WEB: Delete FTP user" {
    run v-delete-web-domain-ftp $user $domain2 "${user}_test"
    assert_success
    refute_output
}

@test "2WEB: Add ssl" {
    cp -f $HESTIA/ssl/certificate.crt /tmp/$domain2.crt
    cp -f $HESTIA/ssl/certificate.key /tmp/$domain2.key

    run v-add-web-domain-ssl $user $domain2 /tmp
    assert_success
    refute_output
}

@test "2WEB: Add force ssl" {
    run v-add-web-domain-ssl-force $user $domain2
    assert_success
    refute_output
}

@test "2WEB: Change Web IP" {
    run v-change-web-domain-ip $user $domain2 $ip2
    assert_success
    refute_output
}

@test "2WEB: Change Web IP (back to first)" {
    run v-change-web-domain-ip $user $domain2 $ip
    assert_success
    refute_output
}


#----------------------------------------------------------#
#                   MAIL (смместро.рф)                     #
#----------------------------------------------------------#

@test "2MAIL:  Add mail domain" {
    run v-add-mail-domain $user $domain2
    assert_success
    refute_output
    # We convert IDN always to punicode to verify it
    validate_mail_domain $user "$domain2"

    # echo -e "<?php\necho 'Server: ' . \$_SERVER['SERVER_SOFTWARE'];" > /var/lib/roundcube/check_server.php
    validate_webmail_domain $user $domain2 'Welcome to Roundcube Webmail'
    # rm /var/lib/roundcube/check_server.php
}

@test "2MAIL: Add domain (duplicate)" {
    run v-add-mail-domain $user $domain2
    assert_failure $E_EXISTS
}

@test "2MAIL: Add account" {
    run v-add-mail-account $user $domain2 test t3st-p4ssw0rd
    assert_success
    refute_output
}

@test "2MAIL: Add account (duplicate)" {
    run v-add-mail-account $user $domain2 test t3st-p4ssw0rd
    assert_failure $E_EXISTS
}

@test "2MAIL: Delete account" {
    run v-delete-mail-account $user $domain2 test
    assert_success
    refute_output
}

@test "2MAIL: Delete missing account" {
    run v-delete-mail-account $user $domain2 test
    assert_failure $E_NOTEXIST
}

#----------------------------------------------------------#
#                         DNS                              #
#----------------------------------------------------------#

@test "2DNS: Add domain" {
    run v-add-dns-domain $user $domain2 $ip
    assert_success
    refute_output
}

@test "2DNS: Add domain (duplicate)" {
    run v-add-dns-domain $user $domain2 $ip
    assert_failure $E_EXISTS
}

@test "2DNS: Add domain record" {
    run v-add-dns-record $user $domain2 test A $ip
    assert_success
    refute_output
}

@test "2DNS: Delete domain record" {
    run v-delete-dns-record $user $domain2 20
    assert_success
    refute_output
}

@test "2DNS: Delete missing domain record" {
    run v-delete-dns-record $user $domain2 20
    assert_failure $E_NOTEXIST
}

@test "2DNS: Change domain expire date" {
    run v-change-dns-domain-exp $user $domain2 "2021-04-04"
    assert_success
    refute_output
}

@test "2DNS: Change domain ip" {
    run v-change-dns-domain-ip $user $domain2 127.0.0.1
    assert_success
    refute_output
}

@test "2DNS: Suspend domain" {
    run v-suspend-dns-domain $user $domain2
    assert_success
    refute_output
}

@test "2DNS: Unsuspend domain" {
    run v-unsuspend-dns-domain $user $domain2
    assert_success
    refute_output
}

@test "2DNS: Rebuild" {
    run v-rebuild-dns-domains $user
    assert_success
    refute_output
}

#----------------------------------------------------------#
#                         CLEANUP                          #
#----------------------------------------------------------#

@test "User: Delete test user" {
    run v-delete-user $user
    assert_success
    refute_output
}


@test "Ip: Delete the test IP" {
    run v-delete-sys-ip $ip
    assert_success
    refute_output
}

@test "Ip: Delete 2nd test IP" { 
    run v-delete-sys-ip $ip2
    assert_success
    refute_output
}

@test 'assert()' {
  touch '/var/log/test.log'
  assert [ -e '/var/log/test.log' ]
}
