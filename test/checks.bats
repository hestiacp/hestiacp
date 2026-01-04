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

@test "is_hash_format_valid accesskey:secret valid" {
    run is_hash_format_valid 'bxDaKPyAfLPRgSkoqlkI:Pc8czGPRECp3GxTNMr3LF6zWc8cjfPrNHy_-=A' "hash"
    assert_success
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

@test "is_dns_record_format_valid MX missing priority" {
	rtype='MX'
	priority=''
	run is_dns_record_format_valid 'mx.hestiacp.com.'
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid SRV 4-field" {
	rtype='SRV'
	priority=''
	run is_dns_record_format_valid '10 20 5060 srv.hestiacp.com.'
	assert_success
}

@test "is_dns_record_format_valid SRV invalid priority" {
	rtype='SRV'
	priority=''
	run is_dns_record_format_valid 'abc 20 5060 srv.hestiacp.com.'
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid SRV null target" {
	rtype='SRV'
	priority=''
	run is_dns_record_format_valid '0 5 0 .'
	assert_success
}

@test "is_dns_record_format_valid TXT newline" {
	rtype='TXT'
	priority=''
	run is_dns_record_format_valid 'foo
bar'
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid TXT single quote" {
	rtype='TXT'
	priority=''
	run is_dns_record_format_valid "foo'bar"
	assert_success
}

@test "is_dns_record_format_valid TXT empty" {
	rtype='TXT'
	priority=''
	run is_dns_record_format_valid ''
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid TXT non-ascii" {
	rtype='TXT'
	priority=''
	run is_dns_record_format_valid 'café'
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid DNSKEY valid" {
	rtype='DNSKEY'
	priority=''
	run is_dns_record_format_valid '257 3 13 AwEAAc1='
	assert_success
}

@test "is_dns_record_format_valid DNSKEY invalid protocol" {
	rtype='DNSKEY'
	priority=''
	run is_dns_record_format_valid '257 1 13 AwEAAc1'
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid DS invalid hex" {
	rtype='DS'
	priority=''
	run is_dns_record_format_valid '12345 8 1 ZZZZ'
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid TLSA valid" {
	rtype='TLSA'
	priority=''
	run is_dns_record_format_valid '3 1 1 0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef'
	assert_success
}

@test "is_dns_record_format_valid CAA missing value" {
	rtype='CAA'
	priority=''
	run is_dns_record_format_valid '0 issue'
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid IPSECKEY valid" {
	rtype='IPSECKEY'
	priority=''
	run is_dns_record_format_valid '10 1 2 192.0.2.1 AQIDBA=='
	assert_success
}

@test "is_dns_record_format_valid IPSECKEY invalid gateway" {
	rtype='IPSECKEY'
	priority=''
	run is_dns_record_format_valid '10 1 2 not-an-ip AQIDBA=='
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid IPSECKEY algorithm0 with key fails" {
	rtype='IPSECKEY'
	priority=''
	run is_dns_record_format_valid '10 1 0 192.0.2.1 AQIDBA=='
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid TLSA type1 wrong length" {
	rtype='TLSA'
	priority=''
	run is_dns_record_format_valid '3 1 1 0123'
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid TLSA matching_type plus sign wrong length" {
	rtype='TLSA'
	priority=''
	run is_dns_record_format_valid '3 1 +1 0123'
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid DS type1 wrong length" {
	rtype='DS'
	priority=''
	run is_dns_record_format_valid '12345 8 1 012345'
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid DS type2 correct length" {
	rtype='DS'
	priority=''
	run is_dns_record_format_valid '12345 8 2 0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef'
	assert_success
}

@test "is_dns_record_format_valid KEY algorithm0 empty key" {
	rtype='KEY'
	priority=''
	run is_dns_record_format_valid '256 3 0'
	assert_success
}

@test "is_dns_record_format_valid KEY algorithm0 with key fails" {
	rtype='KEY'
	priority=''
	run is_dns_record_format_valid '256 3 0 AQID'
	assert_failure $E_INVALID
}

@test "is_dns_record_format_valid KEY algorithm1 missing key fails" {
	rtype='KEY'
	priority=''
	run is_dns_record_format_valid '256 3 1'
	assert_failure $E_INVALID
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
     run is_alias_format_valid 'hestiacp.com' "key"
    assert_success
}

@test "is_alias_format_valid success www.domain.com" {
     run is_alias_format_valid 'www.hestiacp.com' "key"
    assert_success
}
@test "is_alias_format_valid success hestiacp.com,www.hestiacp.com" {
     run is_alias_format_valid 'hestiacp.com,www.hestiacp.com' "key"
    assert_success
}

@test "is_alias_format_valid success *.hestiacp.com" {
     run is_alias_format_valid '*.hestiacp.com' "key"
    assert_success
}

@test "is_alias_format_valid success www.hestiacp.com,*.hestiacp.com" {
     run is_alias_format_valid 'www.hestiacp.com,*.hestiacp.com' "key"
    assert_success
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

@test "format_no_quotes test2" {
     run format_no_quotes 'test bericht' "key"
    assert_success
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
