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
    # echo "# Setup_file" > &3
    if [ $BATS_TEST_NUMBER = 1 ]; then
        echo 'user=test-5285' > /tmp/hestia-test-env.sh
        echo 'user2=test-5286' >> /tmp/hestia-test-env.sh
        echo 'userbk=testbk-5285' >> /tmp/hestia-test-env.sh
        echo 'userpass1=test-5285' >> /tmp/hestia-test-env.sh
        echo 'userpass2=t3st-p4ssw0rd' >> /tmp/hestia-test-env.sh
        echo 'HESTIA=/usr/local/hestia' >> /tmp/hestia-test-env.sh
        echo 'domain=test-5285.hestiacp.com' >> /tmp/hestia-test-env.sh
        echo 'domainuk=test-5285.hestiacp.com.uk' >> /tmp/hestia-test-env.sh
        echo 'rootdomain=testhestiacp.com' >> /tmp/hestia-test-env.sh
        echo 'subdomain=cdn.testhestiacp.com' >> /tmp/hestia-test-env.sh
        echo 'database=test-5285_database' >> /tmp/hestia-test-env.sh
        echo 'dbuser=test-5285_dbuser' >> /tmp/hestia-test-env.sh
    fi
    
    source /tmp/hestia-test-env.sh
    source $HESTIA/func/main.sh
    source $HESTIA/conf/hestia.conf
    source $HESTIA/func/ip.sh
}

@test "is_access_key_id_format_valid valid" {
    run is_access_key_id_format_valid 'M0ocDoIKbsoXSqtk1mgc' "key"
    assert_success
}
@test "is_access_key_id_format_valid short" {
    run is_access_key_id_format_valid 'M0ocDoIKbsoXSqtk1mg' "key"
    assert_failure $E_INVALID
}
@test "is_access_key_id_format_valid long" {
    run is_access_key_id_format_valid 'M0ocDoIKbsoXSqtk1mgca' "key"
    assert_failure $E_INVALID
}
@test "is_access_key_id_format_valid non alpha" {
    run is_access_key_id_format_valid 'M0ocDoIKbsoX$qtk1mgc' "key"
    assert_failure $E_INVALID
}

@test "is_access_key_id_format_valid LHF" {
    run is_access_key_id_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_user_format_valid valid" {
    run is_user_format_valid 'hxh54SKbALne4s69VsqJRMbMd8Br' "key"
    assert_success
}
@test "is_user_format_valid short" {
    run is_user_format_valid 'hxh54SKbALne4s69VsqJR' "key"
    assert_success
}
@test "is_user_format_valid long" {
    run is_user_format_valid 'hxh54SKbALne4s69VsqJRMbMd8Braaa' "key"
    assert_failure $E_INVALID
}
@test "is_user_format_valid dash" {
    run is_user_format_valid 'hxh54SKbALne4-s6-VsqJRMbMd8Br' "key"
    assert_success
}

@test "is_user_format_valid dash repeat" {
    run is_user_format_valid 'hxh54SKbALne4s6--VsqJRMbMd8Br' "key"
    assert_success
}
@test "is_user_format_valid dash start" {
    run is_user_format_valid '-hxh54SKbALne4s6VsqJRMbMd8Br' "key"
    assert_failure $E_INVALID
}
@test "is_user_format_valid dash end" {
    run is_user_format_valid 'hxh54SKbALne4s6VsqJRMbMd8Br-' "key"
    assert_failure $E_INVALID
}
@test "is_user_format_valid LHF" {
    run is_user_format_valid 'M0ocDoIK
    soXSqtk1mgc' "key"
    assert_failure $E_INVALID
}

@test "is_fw_action_format_valid ACCEPT" {
    run is_fw_action_format_valid 'ACCEPT' "key"
    assert_success
}
@test "is_fw_action_format_valid DROP" {
    run is_fw_action_format_valid 'DROP' "key"
    assert_success
}
@test "is_fw_action_format_valid TEST" {
    run is_fw_action_format_valid 'TEST' "key"
    assert_failure $E_INVALID
}
@test "is_fw_action_format_valid LHF" {
    run is_fw_protocol_format_valid 'M0ocDoIK
    soXSqtk1mgc' "key"
    assert_failure $E_INVALID
}

@test "is_fw_protocol_format_valid ICMP" {
    run is_fw_protocol_format_valid 'ICMP' "key"
    assert_success
}

@test "is_fw_protocol_format_valid UDP" {
    run is_fw_protocol_format_valid 'UDP' "key"
    assert_success
}
@test "is_fw_protocol_format_valid TCP" {
    run is_fw_protocol_format_valid 'TCP' "key"
    assert_success
}
@test "is_fw_protocol_format_valid TEST" {
    run is_fw_protocol_format_valid 'TEST' "key"
    assert_failure $E_INVALID
}


@test "is_domain_format_valid success" {
     run is_domain_format_valid 'hestiacp.com' "key"
    assert_success
}

@test "is_domain_format_valid www" {
     run is_domain_format_valid 'www' "key"
    assert_failure $E_INVALID
}
@test "is_domain_format_valid number" {
     run is_domain_format_valid '12345' "key"
    assert_failure $E_INVALID
}

@test "is_domain_format_valid .." {
     run is_domain_format_valid '..' "key"
    assert_failure $E_INVALID
}

@test "is_domain_format_valid hestiacp.com." {
     run is_domain_format_valid 'mx.hestiacp.com.' "key"
    assert_success
}

@test "is_domain_format_valid LF." {
     run is_domain_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_dns_record_format_valid" {
    rtype='MX'
    priority=1; 
    run is_dns_record_format_valid 'mx.hestiacp.com.'  
    assert_success
}

@test "is_dns_record_format_valid test" {
    rtype='MX'
priority=1; 
     run is_dns_record_format_valid 'c
1eshutdown
r' 
    assert_failure $E_INVALID
}

@test "is_alias_format_valid success" {
     run is_domain_format_valid 'hestiacp.com' "key"
    assert_success
}

@test "is_alias_format_valid www" {
     run is_domain_format_valid 'www' "key"
    assert_failure $E_INVALID
}
@test "is_alias_format_valid number" {
     run is_domain_format_valid '12345' "key"
    assert_failure $E_INVALID
}

@test "is_alias_format_valid .." {
     run is_domain_format_valid '..' "key"
    assert_failure $E_INVALID
}
@test "is_alias_format_valid LF." {
     run is_domain_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_extention_format_valid test" {
     run is_extention_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_string_format_valid test" {
     run is_string_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_database_format_valid test" {
     run is_database_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_date_format_valid test" {
     run is_date_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_dbuser_format_valid test" {
     run is_dbuser_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_dns_type_format_valid test" {
     run is_dns_type_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_email_format_valid test" {
     run is_email_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_fw_port_format_valid test" {
     run is_fw_port_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_int_format_valid test" {
     run is_int_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_interface_format_valid test" {
     run is_interface_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_ip_status_format_valid test" {
     run is_ip_status_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_cron_format_valid test" {
     run is_cron_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_name_format_valid test" {
     run is_name_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}


@test "is_role_valid test" {
     run is_role_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_object_format_valid test" {
     run is_object_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}


@test "is_common_format_valid test" {
     run is_common_format_valid 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "format_no_quotes .." {
     run format_no_quotes '..' "key"
    assert_success
}

@test "format_no_quotes text." {
     run format_no_quotes 'text.' "key"
    assert_success
}

@test "is_common_format_valid text" {
     run is_common_format_valid 'text' "key"
    assert_success
}


@test "format_no_quotes test" {
     run format_no_quotes 'c
1eshutdown
r' "key"
    assert_failure $E_INVALID
}

@test "is_type_valid" {
    run is_type_valid 'c
    1eshutdown
    r' "test,key"
    assert_failure $E_INVALID
}

@test "is_command_valid_format v-list-users" {
    run is_command_valid_format 'v-list-users'
    assert_success
}

@test "is_command_valid_format v-list--users (Fail)" {
    run is_command_valid_format 'v-list--users'
    assert_failure $E_INVALID
}

@test "is_command_valid_format h-list-users (Fail)" {
    run is_command_valid_format 'h-list-users'
    assert_failure $E_INVALID
}

@test "is_command_valid_format list-users (Fail)" {
    run is_command_valid_format 'list-users'
    assert_failure $E_INVALID
}

@test "is_command_valid_format vlist-users (Fail)" {
    run is_command_valid_format 'vlist-users'
    assert_failure $E_INVALID
}

@test "is_command_valid_format LF (Fail)" {
     run is_command_valid_format 'v-
1eshutdown
r' "key"
    assert_failure $E_INVALID
}