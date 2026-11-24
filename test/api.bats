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
    source /tmp/hestia-api-env.sh
    source $HESTIA/func/main.sh
    source $HESTIA/conf/hestia.conf
    source $HESTIA/func/ip.sh
    testkey="t$(random 15)"
}

function teardown() {
    if [ -n "${testkey:-}" ]; then
        rm -rf "${HESTIA:-/usr/local/hestia}/data/keys/${testkey}"*
    fi
}

@test "[Success][ Admin/password ] List users" {
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "user=admin&password=$password&returncode=no&cmd=v-list-users&arg1=plain" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "admin"
}

@test "[Success][ Hash ] List users" {
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash=$apikey&returncode=no&cmd=v-list-users&arg1=plain" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "admin"
}

@test "[Fail][ Hash ] Trailing newline in hash" {
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" --data-urlencode "hash=$apikey"$'\n' -d "cmd=v-list-users&arg1=plain" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "Error: authentication failed"
}

@test "[Fail][ Hash ] Unknown key" {
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash=${testkey}u&returncode=no&cmd=v-list-users&arg1=plain" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "Error: authentication failed"
}

@test "[Success][ Hash ] Shortest allowed key name" {
    touch "$HESTIA/data/keys/$testkey"
    assert_file_exist "$HESTIA/data/keys/$testkey"
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash=$testkey&returncode=no&cmd=v-list-users&arg1=plain" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "admin"
}

@test "[Fail][ Hash ] Key name longer than 128 characters" {
    longkey="${testkey}$(printf 'a%.0s' {1..113})"
    touch "$HESTIA/data/keys/$longkey"
    assert_file_exist "$HESTIA/data/keys/$longkey"
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash=$longkey&returncode=no&cmd=v-list-users&arg1=plain" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "Error: authentication failed"
}

@test "[Fail][ Hash ] Array hash" {
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash[]=aaaaaaaaaaaaaaaa&returncode=no&cmd=v-list-users&arg1=plain" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "hash must be a string"
}

@test "[Fail][ Hash ] Directory in key store" {
    mkdir "$HESTIA/data/keys/${testkey}d"
    assert_dir_exist "$HESTIA/data/keys/${testkey}d"
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash=${testkey}d&returncode=no&cmd=v-list-users&arg1=plain" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "Error: authentication failed"
}

@test "[Fail][ Hash ] Symlink in key store" {
    assert_file_exist "$HESTIA/data/keys/$apikey"
    ln -s "$HESTIA/data/keys/$apikey" "$HESTIA/data/keys/${testkey}l"
    assert [ -L "$HESTIA/data/keys/${testkey}l" ]
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash=${testkey}l&returncode=no&cmd=v-list-users&arg1=plain" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "Error: authentication failed"
}

@test "[Fail][ APIV2 ] Create new user" {
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash=$accesskey&returncode=yes&cmd=v-add-user&arg1=hestiatest&arg2=strongpassword&arg3=info@hestiacp.com" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "don't have permission to run the command v-add-user"
}

@test "[Success][ Hash ] Create tmp file" {
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash=$apikey&cmd=v-make-tmp-file&arg1=strongpassword&arg2=clusterpassword" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "OK"
}

@test "[Success][ Hash ] Create new user" {
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash=$apikey&cmd=v-add-user&arg1=hestiatest&arg2=/tmp/clusterpassword&arg3=info@hestiacp.com&arg4=default" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "OK"
}

@test "[Success][ Hash ] Check password" {
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash=$apikey&cmd=v-check-user-password&arg1=hestiatest&arg2=strongpassword" "https://$server:$port/api/index.php"
    assert_success
    assert_output --partial "OK"
}


@test "[Success][ Local ] Add user" {
    run v-add-user hestiatest 1234BCD info@hestiacp.com
    assert_success
}

@test "[Success][ Local ] Add DNS domain" {
    run v-add-dns-domain hestiatest ilovehestiacp.com 127.0.0.1
    assert_success
}

@test "[Success][ APIV2 ] Add remote DNS host" {
    run v-add-remote-dns-host $server $port "$accesskey" '' api 'hestiatest'
    assert_success
}

@test "[Success][ APIV2 ] Sync DNS cluster 1" {
    run v-sync-dns-cluster
    assert_success
}

@test "[Success][ Local ] nslookup ilovehestiacp.com" {
    run nslookup ilovehestiacp.com $server
    assert_success
    assert_output --partial "127.0.0.1"
}

@test "[Success][ Local ] Add DNS domain 2" {
    run v-add-dns-domain hestiatest ilovehestiacp.org 127.0.0.1
    assert_success
}

@test "[Success][ Local ] Add DNS record" {
    run v-add-dns-record hestiatest ilovehestiacp.org test A 127.0.0.1 yes 20
    assert_success
}

@test "[Success][ Local ] nslookup test.ilovehestiacp.org" {
    run nslookup test.ilovehestiacp.org $server
    assert_failure 1
    assert_output --partial "REFUSED"

    run nslookup test.ilovehestiacp.org localhost
    assert_success
    assert_output --partial "127.0.0.1"
}

@test "[Success][ APIV2 ] Sync DNS cluster 2" {
    run v-sync-dns-cluster
    assert_success

    run nslookup test.ilovehestiacp.org $server
    assert_success
    assert_output --partial "127.0.0.1"
}

@test "[Success][ Local ] Delete DNS record" {
    run v-delete-dns-record hestiatest ilovehestiacp.org 20
    assert_success
}

@test "[Success][ Local ] nslookup test.ilovehestiacp.org 2" {
    run nslookup test.ilovehestiacp.org $server
    assert_success
    assert_output --partial "127.0.0.1"

    run nslookup test.ilovehestiacp.org localhost
    assert_failure
}

@test "[Success][ APIV2 ] Sync DNS cluster 3" {
    run v-sync-dns-cluster
    assert_success

    run nslookup test.ilovehestiacp.org $server
    assert_failure
}


@test "[Success][ APIV2 ] Delete remote DNS host" {
    run v-delete-remote-dns-host $server
    assert_success
}


@test "[Success][ Local ] Delete user" {
    run v-delete-user hestiatest
    assert_success
}

@test "[Success][ Hash ] Delete user" {
    run curl -k -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "hash=$apikey&cmd=v-delete-user&arg1=hestiatest" "https://$server:$port/api/index.php"
}
