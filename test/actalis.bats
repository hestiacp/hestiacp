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
    source /tmp/hestia-test-env.sh
    source $HESTIA/func/main.sh
    source $HESTIA/conf/hestia.conf
    source $HESTIA/func/ip.sh
}

function require_actalis_eab() {
    if [ -z "$ACTALIS_EAB_KID" ] || [ -z "$ACTALIS_EAB_HMAC" ]; then
        skip "ACTALIS_EAB_KID and ACTALIS_EAB_HMAC are required for Actalis integration tests"
    fi
}

@test "[ User ] Create new user" {
    run v-add-user $user $user $user@hestiacp.com default "Super Test"
    assert_success
    refute_output
}

@test "[ User ] Configure Actalis EAB credentials" {
    require_actalis_eab
    run v-update-actalis-eab "$user" "$ACTALIS_EAB_KID" "$ACTALIS_EAB_HMAC"
    assert_success
    refute_output
}

@test "[ DNS ] Create DNS domain" {
    run v-add-dns-domain $user $domain $ip
    assert_success
    refute_output
}

@test "[ Web ] Create web domain with aliases" {
    run v-add-web-domain "$user" "$domain" "$ip" yes "www.$domain,renewal.$domain,foobar.$domain,bar.$domain"
    assert_success
    refute_output
}

@test "[ Web ] Create 2nd web domain" {
    run v-add-web-domain $user "hestia.$domain" $ip yes
    assert_success
    refute_output
}

@test "[ Web ] Reject unsupported Actalis aliases" {
    require_actalis_eab
    run v-add-actalis-domain "$user" "$domain" "www.$domain,foobar.$domain"
    assert_failure
}

@test "[ Web ] Request new certificate for web domain" {
    require_actalis_eab
    run v-add-actalis-domain $user $domain "www.$domain"
    assert_success
    refute_output
}

@test "[ Web ] Request 2nd new certificate for web domain" {
    require_actalis_eab
    run v-add-actalis-domain $user "hestia.$domain"
    assert_success
    refute_output
}

@test "[ Web ] Delete web ssl" {
    require_actalis_eab
    run v-delete-actalis-domain $user $domain "yes"
    assert_success
    refute_output
}

@test "[ Web ] Delete web domain" {
    run v-delete-web-domain $user $domain "yes"
    assert_success
    refute_output
}

@test "[ Redirect ] Create web domain" {
    run v-add-web-domain $user "redirect.$domain" $ip yes
    assert_success
    refute_output
}

@test "[ Redirect ] Add Domain redirect to other website" {
    run v-add-web-domain-redirect $user "redirect.$domain" "https://hestiacp.com" 301 "yes"
    assert_success
    refute_output
}

@test "[ Redirect ] Request new certificate for web" {
    require_actalis_eab
    run v-add-actalis-domain $user "redirect.$domain" ""
    assert_success
    refute_output
}

@test "[ Redirect ] Run renewal script for ACTALIS Redirected domain" {
    require_actalis_eab
    run v-update-actalis-ssl
    assert_success
    refute_output
}

@test "Delete user" {
    run v-delete-user $user
    assert_success
    refute_output
}
