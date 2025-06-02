#!/usr/bin/env bats

if [ "${PATH#*/usr/local/devcp/bin*}" = "$PATH" ]; then
    . /etc/profile.d/devcp.sh
fi

load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'
load 'test_helper/bats-file/load'

function random() {
head /dev/urandom | tr -dc 0-9 | head -c$1
}

function setup() {
    # echo "# Setup_file" > &3
    if [ $BATS_TEST_NUMBER = 1 ]; then
        echo 'user=test-5285' > /tmp/devcp-test-env.sh
        echo 'user2=test-5286' >> /tmp/devcp-test-env.sh
        echo 'userbk=testbk-5285' >> /tmp/devcp-test-env.sh
        echo 'userpass1=test-5285' >> /tmp/devcp-test-env.sh
        echo 'userpass2=t3st-p4ssw0rd' >> /tmp/devcp-test-env.sh
        echo 'HESTIA=/usr/local/devcp' >> /tmp/devcp-test-env.sh
        echo 'domain=test-5285.hestiacp.com' >> /tmp/devcp-test-env.sh
        echo 'domainuk=test-5285.hestiacp.com.uk' >> /tmp/devcp-test-env.sh
        echo 'rootdomain=testhestiacp.com' >> /tmp/devcp-test-env.sh
        echo 'subdomain=cdn.testhestiacp.com' >> /tmp/devcp-test-env.sh
        echo 'database=test-5285_database' >> /tmp/devcp-test-env.sh
        echo 'dbuser=test-5285_dbuser' >> /tmp/devcp-test-env.sh
    fi

    source /tmp/devcp-test-env.sh
    source $HESTIA/func/main.sh
    source $HESTIA/conf/devcp.conf
    source $HESTIA/func/ip.sh
}

@test "Setup Test domain" {
    run v-add-user $user $user $user@hestiacp.com default "Super Test"
    assert_success
    refute_output

    run v-add-web-domain $user 'testhestiacp.com'
    assert_success
    refute_output

    ssl=$(v-generate-ssl-cert "testhestiacp.com" "info@testhestiacp.com" US CA "Orange County" DevCP IT "mail.$domain" | tail -n1 | awk '{print $2}')
    mv $ssl/testhestiacp.com.crt /tmp/testhestiacp.com.crt
    mv $ssl/testhestiacp.com.key /tmp/testhestiacp.com.key

    # Use self signed certificates during last test
    run v-add-web-domain-ssl $user testhestiacp.com /tmp
    assert_success
    refute_output
}

@test "Web Config test" {
    for template in $(v-list-web-templates plain); do
        run v-change-web-domain-tpl $user testhestiacp.com $template
        assert_success
        refute_output
    done
}

@test "Proxy Config test" {
    if [ "$PROXY_SYSTEM" = "nginx" ]; then
        for template in $(v-list-proxy-templates plain); do
            run v-change-web-domain-proxy-tpl $user testhestiacp.com $template
            assert_success
            refute_output
        done
    else
        skip "Proxy not installed"
    fi
}

@test "Clean up" {
    run v-delete-user $user
    assert_success
    refute_output
}
