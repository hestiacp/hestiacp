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

@test "[ User ] Create new user" {
    run v-add-user $user $user $user@hestiacp.com default "Super Test"
    assert_success
    refute_output
}

@test "[ DNS ]Create DNS domain" {
    run v-add-dns-domain $user $domain $ip
    assert_success
    refute_output
}

@test "[ Web ] Create web domain" {
    run v-add-web-domain $user $domain $ip yes "www.$domain,renewal.$domain,foobar.$domain,bar.$domain"
    assert_success
    refute_output
}

@test "[ Web ] Request new certificate for web domain" {
    run v-add-letsencrypt-domain $user $domain "www.$domain,renewal.$domain,foobar.$domain,bar.$domain"
    assert_success
    refute_output
}

@test "[ Mail ] Create mail domain" {
    run v-add-mail-domain $user $domain
    assert_success
    refute_output
}

@test "[ Mail ] Request new Certificate for Mail Domain" {
    run v-add-letsencrypt-domain $user $domain "" "yes"
    assert_success
    refute_output
}

@test "[ All ] Run renewal script for LE" {
    run v-update-letsencrypt-ssl
    assert_success
    refute_output
}

@test "[ All ] Remove alias and update ssl" {
    run v-delete-web-domain-alias $user $domain bar.$domain
    assert_success
    refute_output

    run v-update-letsencrypt-ssl
    assert_success
    refute_output
    
}


@test [ Web ] Delete web ssl" {
    run v-delete-letsencrypt-domain $user $domain "yes"
    assert_success
    refute_output
}

@test [ Mail ] Delete mail ssl" {
    run v-delete-letsencrypt-domain $user $domain "yes" "yes"
    assert_success
    refute_output
}

@test "[ Web ] Delete web  domain" {
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

@test "[ Redirect ] Request new certificate for web {
    run v-add-letsencrypt-domain $user "redirect.$domain" ""
    assert_success
    refute_output
}

@test "[ Redirect ] Run renewal script for LE Redirected domain" {
    run v-update-letsencrypt-ssl
    assert_success
    refute_output
}



@test "Delete user" {
    run v-delete-user $user
    assert_success
    refute_output
}
