#!/usr/bin/env bats

if [ "${PATH#*/usr/local/hestia/bin*}" = "$PATH" ]; then
    . /etc/profile.d/hestia.sh
fi

load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'
load 'test_helper/bats-file/load'


function random() {
    head /dev/urandom | tr -dc 0-9 | head -c$1
}

function setup() {
    source /tmp/wildcard.sh
    source $HESTIA/func/main.sh
    source $HESTIA/conf/hestia.conf
    source $HESTIA/func/ip.sh
}

# User and domain needs to already exists as dns domain due to DNS 

@test "[ Web ] Create web domain" {
    run v-add-web-domain $user $domain $ip yes "*.$domain"
    assert_success
    refute_output
}

@test "[ Web ] Request new certificate for web domain" {
    run v-restart-web 
    run v-add-letsencrypt-domain $user $domain  "*.$domain"
    assert_success
    refute_output
}

@test "[ All ] Run renewal script for LE" {
    run v-update-letsencrypt-ssl
    assert_success
    refute_output
    
    run openssl x509 -text -in /usr/local/hestia/data/users/$user/ssl/$domain.crt
    assert_success
    assert_output --partial "*.$domain"
}

@test "[ Web ] Delete web  domain" {
    run v-delete-web-domain $user $domain "yes"
    assert_success
    refute_output
}
