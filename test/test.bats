#!/usr/bin/env bats

load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'
load 'test_helper/bats-file/load'


function random() {
    MATRIX='0123456789'
    LENGTH=$1
    while [ ${n:=1} -le $LENGTH ]; do
        rand="$rand${MATRIX:$(($RANDOM%${#MATRIX})):1}"
        let n+=1
    done
    echo "$rand"
}

function setup() {
    # echo "# Setup_file" > &3
    if [ $BATS_TEST_NUMBER = 1 ]; then
        echo 'user=test-5285' > /tmp/hestia-test-env.sh
        echo 'userpass1=test-5285' >> /tmp/hestia-test-env.sh
        echo 'userpass2=t3st-p4ssw0rd' >> /tmp/hestia-test-env.sh
        echo 'HESTIA=/usr/local/hestia' >> /tmp/hestia-test-env.sh
        echo 'domain=test-5285.hestiacp.com' >> /tmp/hestia-test-env.sh
    fi

    source /tmp/hestia-test-env.sh
    source $HESTIA/func/main.sh
}

#----------------------------------------------------------#
#                         MAIN                             #
#----------------------------------------------------------#

@test "Add new userXXX" {
    skip
    run v-add-user $user $user $user@hestiacp.com default Super Test
    assert_success
    refute_output
}


#----------------------------------------------------------#
#                         User                             #
#----------------------------------------------------------#

@test "Add new user" {
    run v-add-user $user $user $user@hestiacp.com default Super Test
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
    echo "interface=${interface}" >> /tmp/hestia-test-env.sh

    run v-add-sys-ip 198.18.0.123 255.255.255.255 $interface $user
    assert_success
    refute_output
}

@test "Ip: Add duplicate (duplicat)" {
    run v-add-sys-ip 198.18.0.123 255.255.255.255 $interface $user
    assert_failure $E_EXISTS
}

@test "Ip: Delete ip" {
    run v-delete-sys-ip 198.18.0.123
    assert_success
    refute_output
}

@test "Ip: Add IP for rest of the test" {
    run v-add-sys-ip 198.18.0.125 255.255.255.255 $interface $user
    assert_success
    refute_output
}


#----------------------------------------------------------#
#                         WEB                              #
#----------------------------------------------------------#

@test "WEB: Add web domain" {
    run v-add-web-domain $user $domain 198.18.0.125
    assert_success
    refute_output
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
}

@test "WEB: Unsuspend web domain" {
    run v-unsuspend-web-domain $user $domain
    assert_success
    refute_output
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
