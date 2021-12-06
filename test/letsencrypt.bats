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
    source /tmp/hestia-le-env.sh
    source $HESTIA/func/main.sh
    source $HESTIA/conf/hestia.conf
    source $HESTIA/func/ip.sh
}

@test "Create new user" {
    run v-add-user $user $user $user@hestiacp.com default "Super Test"
    assert_success
    refute_output
}

@test "Create DNS domain" {
    run v-add-dns-domain $user $domain $ip
    assert_success
    refute_output
}

@test "Create web domain" {
    run v-add-web-domain $user $domain $ip yes "www.$domain,renewal.$domain"
    assert_success
    refute_output
}

@test "Request new certificate for web domain" {
    run v-add-letsencrypt-domain $user $domain "www.$domain,renewal.$domain"
    assert_success
    refute_output
}

@test "Create mail domain" {
    run v-add-mail-domain $user $domain
    assert_success
    refute_output
}

@test "Request new Certificate for Mail Domain" {
    run v-add-letsencrypt-domain $user $domain "" "yes"
    assert_success
    refute_output
}

@test "Run renewal script for LE" {
    run v-update-letsencrypt-ssl
    assert_success
    refute_output
}

@test Delete mail ssl" {
    v-delete-letsencrypt-domain $user $domain "yes" "yes"
    assert_success
    refute_output
}

@test Delete web ssl" {
    v-delete-letsencrypt-domain $user $domain "yes" "no"
    assert_success
    refute_output
}

@test "Delete user" {
    run v-delete-user $user
    assert_success
    refute_output
}