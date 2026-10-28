#!/usr/bin/env bats

# Fixture tests run without installing Hestia or modifying any server data.
load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'
bats_require_minimum_version 1.5.0

setup() {
    HESTIA="$BATS_TEST_TMPDIR/hestia"
    HOMEDIR='/home'
    WEBMAIL_ALIAS=''
    mkdir -p "$HESTIA/data/users"
    source "$BATS_TEST_DIRNAME/../func/list.sh"
}

write_config() {
    local owner="$1"
    local type="$2"
    mkdir -p "$HESTIA/data/users/$owner"
    touch "$HESTIA/data/users/$owner/user.conf"
    printf '%s\n' "$3" > "$HESTIA/data/users/$owner/$type.conf"
}

@test "List all objects: Empty inventory succeeds in every format" {
    for type in web mail dns db; do
        run list_all_user_objects "$type" json
        assert_success
        assert_output '{}'

        run list_all_user_objects "$type" plain
        assert_success
        refute_output

        run list_all_user_objects "$type" csv
        assert_success
        assert_output --regexp '^USER,(DOMAIN|DATABASE),'
        [ "${#lines[@]}" -eq 1 ]

        run list_all_user_objects "$type" shell
        assert_success
        assert_output --regexp '^USER[[:space:]]+(DOMAIN|DATABASE)'
        [ "${#lines[@]}" -eq 2 ]
    done
}

@test "List all objects: Missing and empty per-user configs are empty inventories" {
    mkdir -p "$HESTIA/data/users/alice"
    touch "$HESTIA/data/users/alice/user.conf"
    for type in web mail dns db; do
        write_config bob "$type" ''
        run list_all_user_objects "$type" json
        assert_success
        assert_output '{}'
    done
}

@test "List all objects: Every type has string-valued JSON with real owners" {
    for type in web mail dns db; do
        for owner in alice bob; do
            write_config "$owner" "$type" "DOMAIN='$owner.example' DB='${owner}_db' DBUSER='${owner}_dbuser' USER='forged' PASSWORD='secret' U_DISK='003' SUSPENDED='no'"
        done
        run list_all_user_objects "$type" json
        assert_success
        run jq -e --arg type "$type" '
            length == 2 and
            ([.[].USER] | sort == ["alice", "bob"]) and
            all(.[]; .SUSPENDED == "no" and (has("PASSWORD") | not)) and
            ($type == "dns" or all(.[]; .U_DISK == "003")) and
            all(.[] | .[]; type == "string")
        ' <<< "$output"
        assert_success
    done
}

@test "List all objects: Unrelated files and directories without user.conf are ignored" {
    for type in web mail dns db; do
        write_config alice "$type" "DOMAIN='alice.example' DB='alice_db'"
    done
    touch "$HESTIA/data/users/.DS_Store" "$HESTIA/data/users/history.log" "$HESTIA/data/users/admin.conf.bak"
    mkdir -p "$HESTIA/data/users/orphan" "$HESTIA/data/users/invalid/user.conf"
    for type in web mail dns db; do
        # Invalid orphan data must never reach the parser.
        printf '%s\n' 'not a configuration' > "$HESTIA/data/users/orphan/$type.conf"
        printf '%s\n' 'not a configuration' > "$HESTIA/data/users/invalid/$type.conf"
        for format in plain csv shell json; do
            run list_all_user_objects "$type" "$format"
            assert_success
            refute_output --partial 'orphan'
            refute_output --partial 'invalid'
            if [ "$format" = json ]; then
                run jq -e 'length == 1 and all(.[]; .USER == "alice")' <<< "$output"
                assert_success
            else
                assert_output --partial 'alice'
            fi
        done
    done
}

@test "List all objects: Table formats include each owner and only one header" {
    for type in web mail dns db; do
        for owner in alice bob; do
            write_config "$owner" "$type" "DOMAIN='$owner.example' DB='${owner}_db' DBUSER='dbuser' IP='192.0.2.1' TPL='default' TTL='3600' SSL='no' U_DISK='0' U_BANDWIDTH='0' ANTIVIRUS='no' ANTISPAM='no' DKIM='yes' ACCOUNTS='0' RECORDS='2' HOST='localhost' TYPE='mysql' SUSPENDED='no' DATE='2026-09-07'"
        done
        for format in plain csv shell; do
            run list_all_user_objects "$type" "$format"
            assert_success
            assert_output --regexp 'alice[[:space:],]+alice'
            assert_output --regexp 'bob[[:space:],]+bob'
            case "$format" in
                plain) [ "${#lines[@]}" -eq 2 ] ;;
                csv) [ "${#lines[@]}" -eq 3 ] ;;
                shell) [ "${#lines[@]}" -eq 4 ] ;;
            esac
        done
    done
}

@test "List all objects: Web document roots and optional fields do not leak between rows" {
    write_config alice web "DOMAIN='first.example' CUSTOM_DOCROOT='/srv/custom/' STATS_USER='first' IP6='2001:db8::1'"
    printf '%s\n' "DOMAIN='second.example'" >> "$HESTIA/data/users/alice/web.conf"
    write_config bob web "DOMAIN='third.example'"
    run list_all_user_objects web json
    assert_success
    run jq -e '
        .["first.example"].DOCUMENT_ROOT == "/srv/custom/" and
        .["second.example"].DOCUMENT_ROOT == "/home/alice/web/second.example/public_html/" and
        .["third.example"].DOCUMENT_ROOT == "/home/bob/web/third.example/public_html/" and
        .["second.example"].STATS_USER == "" and
        .["third.example"].IP6 == ""
    ' <<< "$output"
    assert_success
}

@test "List all objects: Web CSV quoting and shell columns match the existing command" {
    write_config alice web "DOMAIN='web.example' IP='192.0.2.1' IP6='2001:db8::1' U_DISK='1' U_BANDWIDTH='2' TPL='default' ALIAS='one.example two.example' STATS='awstats' STATS_USER='stats' SSL='yes' SSL_HOME='same' LETSENCRYPT='yes' FTP_USER='ftp' FTP_PATH='/srv/ftp path' AUTH_USER='auth' BACKEND='php' PROXY='nginx' PROXY_EXT='css js' SUSPENDED='no' TIME='12:00:00' DATE='2026-09-07'"
    run list_all_user_objects web csv
    assert_success
    assert_line --index 0 'USER,DOMAIN,IP,IP6,DOCROOT,U_DISK,U_BANDWIDTH,TPL,ALIAS,STATS,STATS_USER,SSL,SSL_HOME,LETSENCRYPT,FTP_USER,FTP_PATH,AUTH_USER,BACKEND,PROXY,PROXY_EXT,SUSPENDED,TIME,DATE'
    assert_line --index 1 'alice,web.example,192.0.2.1,2001:db8::1,/home/alice/web/web.example/public_html/,1,2,default,"one.example two.example",awstats,"stats",yes,same,yes,"ftp","/srv/ftp path","auth",php,nginx,"css js",no,12:00:00,2026-09-07'

    run list_all_user_objects web shell
    assert_success
    assert_line --index 0 --regexp '^USER[[:space:]]+DOMAIN[[:space:]]+IP[[:space:]]+TPL[[:space:]]+SSL[[:space:]]+DISK[[:space:]]+BW[[:space:]]+SPND[[:space:]]+DATE$'
    assert_line --index 2 --regexp '^alice[[:space:]]+web.example[[:space:]]+192.0.2.1[[:space:]]+default[[:space:]]+yes[[:space:]]+1[[:space:]]+2[[:space:]]+no[[:space:]]+2026-09-07$'
}

@test "List all objects: DNS plain and CSV retain their different field order" {
    write_config alice dns "DOMAIN='dns.example' IP='192.0.2.1' TPL='default' TTL='3600' EXP='2027-01-01' SOA='ns.example' SERIAL='2026090701' SRC='local' DNSSEC='yes' RECORDS='3' SUSPENDED='no' TIME='12:00:00' DATE='2026-09-07'"
    run list_all_user_objects dns plain
    assert_success
    assert_output $'alice\tdns.example\t192.0.2.1\tdefault\t3600\t2027-01-01\tns.example\tyes\t2026090701\tlocal\t3\tno\t12:00:00\t2026-09-07'

    run list_all_user_objects dns csv
    assert_success
    assert_line --index 0 'USER,DOMAIN,IP,TPL,TTL,EXP,SOA,SERIAL,SRC,DNSSEC,RECORDS,SUSPENDED,TIME,DATE'
    assert_line --index 1 'alice,dns.example,192.0.2.1,default,3600,2027-01-01,ns.example,2026090701,local,yes,3,no,12:00:00,2026-09-07'
}

@test "List all objects: Mail plain and CSV retain the existing output quirks" {
    write_config alice mail "DOMAIN='mail.example' ANTIVIRUS='no' ANTISPAM='no' DKIM='yes' SSL='yes' CATCHALL='catch@example.com' ACCOUNTS='2' U_DISK='4' SUSPENDED='no' TIME='12:00:00' DATE='2026-09-07' WEBMAIL_ALIAS='webmail' WEBMAIL='roundcube' REJECT='yes' RATE_LIMIT='200'"
    run list_all_user_objects mail plain
    assert_success
    assert_output $'alice\tmail.example\tno\tno\tyes\tyes$CATCHALL\t2\t4\tno\t12:00:00\t2026-09-07\twebmail\troundcube'

    run list_all_user_objects mail csv
    assert_success
    assert_line --index 0 'USER,DOMAIN,ANTIVIRUS,ANTISPAM,DKIM,SSL,CATCHALL,ACCOUNTS,U_DISK,SUSPENDED,TIME,DATE,WEBMAIL_ALIAS,WEBMAIL'
    assert_line --index 1 "alice,mail.example,no,no,yes,yes,catch@example.com,2,'4,no,12:00:00,2026-09-07,webmail,roundcube"
    [ "${#lines[@]}" -eq 2 ]
}

@test "List all objects: Mail aliases use the global default only for missing keys" {
    local format expected
    for WEBMAIL_ALIAS in webmail inbox ''; do
        write_config alice mail "DOMAIN='custom.example' WEBMAIL_ALIAS='custom' WEBMAIL='roundcube'"
        printf '%s\n' \
            "DOMAIN='missing.example' WEBMAIL='roundcube'" \
            "DOMAIN='empty.example' WEBMAIL_ALIAS='' WEBMAIL='roundcube'" \
            "DOMAIN='after-empty.example' WEBMAIL='roundcube'" >> "$HESTIA/data/users/alice/mail.conf"
        write_config bob mail "DOMAIN='bob.example' WEBMAIL='roundcube'"
        expected=$(printf '%s\t%s\t%s\n' \
            custom.example custom roundcube \
            missing.example "$WEBMAIL_ALIAS" roundcube \
            empty.example '' roundcube \
            after-empty.example "$WEBMAIL_ALIAS" roundcube \
            bob.example "$WEBMAIL_ALIAS" roundcube)

        for format in plain csv json; do
            run list_all_user_objects mail "$format"
            assert_success
            case "$format" in
                plain)
                    run awk -F '\t' '{print $2 "\t" $(NF-1) "\t" $NF}' <<< "$output"
                    ;;
                csv)
                    run awk -F ',' 'NR > 1 {print $2 "\t" $(NF-1) "\t" $NF}' <<< "$output"
                    ;;
                json)
                    run jq -r 'to_entries[] | [.key, .value.WEBMAIL_ALIAS, .value.WEBMAIL] | @tsv' <<< "$output"
                    ;;
            esac
            assert_success
            assert_output "$expected"
        done
    done
}

@test "List all objects: An unset global mail alias remains empty" {
    unset WEBMAIL_ALIAS
    write_config alice mail "DOMAIN='mail.example' WEBMAIL='roundcube'"
    run list_all_user_objects mail json
    assert_success
    run jq -e '.["mail.example"] | .WEBMAIL_ALIAS == "" and .WEBMAIL == "roundcube"' <<< "$output"
    assert_success
}

@test "List all objects: Database ownership is distinct from the database user" {
    write_config alice db "DB='alice_db' DBUSER='different_dbuser' HOST='localhost' TYPE='mysql' CHARSET='utf8mb4' U_DISK='1' SUSPENDED='no' TIME='12:00:00' DATE='2026-09-07' PASSWORD='hidden'"
    run list_all_user_objects db json
    assert_success
    run jq -e '.alice_db | .USER == "alice" and .DATABASE == "alice_db" and .DBUSER == "different_dbuser" and (has("PASSWORD") | not)' <<< "$output"
    assert_success

    run list_all_user_objects db plain
    assert_success
    assert_output $'alice\talice_db\tdifferent_dbuser\tlocalhost\tmysql\tutf8mb4\t1\tno\t12:00:00\t2026-09-07'

    run list_all_user_objects db shell
    assert_success
    assert_line --index 0 --regexp '^USER[[:space:]]+DATABASE[[:space:]]+USER[[:space:]]+HOST'
}

@test "List all objects: Quoted concatenation and escaped unquoted values are parsed as data" {
    write_config alice web "DOMAIN=alice.example ALIAS=one\\ two CUSTOM_DOCROOT=ab\\ c'de f'ghi STATS_USER='O'\\''Brien' TPL= SSL=''"
    run list_all_user_objects web json
    assert_success
    run jq -e --arg stats "O'Brien" '
        .["alice.example"] |
        .ALIAS == "one two" and .DOCUMENT_ROOT == "ab cde fghi" and
        .STATS_USER == $stats and .TPL == "" and .SSL == ""
    ' <<< "$output"
    assert_success
}

@test "List all objects: JSON safely escapes quotes backslashes and control characters" {
    local value=$'quotes " and backslash \\ and tab\t and carriage\r'
    write_config alice web "DOMAIN='alice.example' STATS_USER='$value'"
    run list_all_user_objects web json
    assert_success
    run jq -e --arg value "$value" '.["alice.example"].STATS_USER == $value' <<< "$output"
    assert_success
}

@test "List all objects: Shell payloads and forged configuration fields cannot execute" {
    local marker="$BATS_TEST_TMPDIR/injection-marker"
    local payload="\$(touch $marker)"
    write_config alice web "DOMAIN='alice.example' TPL='$payload' USER='mallory' ROOT_USER='mallory' PATH='/wrong' BIN='/wrong' HOMEDIR='/wrong' PASSWORD='hidden'"
    for format in plain csv shell json; do
        run list_all_user_objects web "$format"
        assert_success
        [ ! -e "$marker" ]
    done
    run jq -e --arg payload "$payload" '
        .["alice.example"] |
        .USER == "alice" and .TPL == $payload and
        .DOCUMENT_ROOT == "/home/alice/web/alice.example/public_html/" and
        (has("PASSWORD") | not) and (has("PATH") | not)
    ' <<< "$output"
    assert_success
}

@test "List all objects: Invalid configuration fails without partial stdout" {
    local malformed
    for malformed in "DOMAIN='broken" 'DOMAIN=broken\' "DOMAIN='broken.example' garbage" "INVALID-KEY='value'"; do
        for type in web mail dns db; do
            write_config alice "$type" "DOMAIN='valid.example' DB='alice_db'"
            write_config bob "$type" "$malformed"
            for format in plain csv shell json; do
                run --separate-stderr list_all_user_objects "$type" "$format"
                assert_failure
                refute_output
            done
        done
    done
}

@test "List all objects: Invalid object types and formats are rejected without stdout" {
    for type in '../web' 'web;touch marker' unknown; do
        run --separate-stderr list_all_user_objects "$type" json
        assert_failure
        refute_output
    done
    for format in unknown 'json;touch marker'; do
        run --separate-stderr list_all_user_objects web "$format"
        assert_failure
        refute_output
    done
}

@test "List all objects: A final row without a newline is included" {
    write_config alice db ''
    printf '%s' "DB='alice_db' DBUSER='alice_user'" > "$HESTIA/data/users/alice/db.conf"
    run list_all_user_objects db json
    assert_success
    run jq -e '.alice_db.USER == "alice" and .alice_db.DBUSER == "alice_user"' <<< "$output"
    assert_success
}

@test "List all objects: Duplicate JSON identities fail without overwriting another owner" {
    for type in web mail dns db; do
        write_config alice "$type" "DOMAIN='duplicate.example' DB='duplicate_db'"
        write_config bob "$type" "DOMAIN='duplicate.example' DB='duplicate_db'"
        run --separate-stderr list_all_user_objects "$type" json
        assert_failure
        refute_output
    done
}

@test "List all objects: Symlinked user directories and configuration files are rejected" {
    mkdir -p "$BATS_TEST_TMPDIR/external-user"
    touch "$BATS_TEST_TMPDIR/external-user/user.conf"
    ln -s "$BATS_TEST_TMPDIR/external-user" "$HESTIA/data/users/linked"
    run --separate-stderr list_all_user_objects web json
    assert_failure
    refute_output

    HESTIA="$BATS_TEST_TMPDIR/config-symlink-hestia"
    mkdir -p "$HESTIA/data/users/alice"
    touch "$HESTIA/data/users/alice/user.conf"
    printf '%s\n' "DOMAIN='external.example'" > "$BATS_TEST_TMPDIR/external.conf"
    ln -s "$BATS_TEST_TMPDIR/external.conf" "$HESTIA/data/users/alice/web.conf"
    run --separate-stderr list_all_user_objects web json
    assert_failure
    refute_output
}

@test "List all objects: Nonregular configurations and missing user data are rejected" {
    mkdir -p "$HESTIA/data/users/alice/web.conf"
    touch "$HESTIA/data/users/alice/user.conf"
    run --separate-stderr list_all_user_objects web json
    assert_failure
    refute_output

    HESTIA="$BATS_TEST_TMPDIR/missing-hestia"
    run --separate-stderr list_all_user_objects web json
    assert_failure
    refute_output
}

@test "List all objects: An unreadable configuration cannot produce a partial inventory" {
    write_config alice web "DOMAIN='alice.example'"
    write_config bob web "DOMAIN='bob.example'"
    chmod 000 "$HESTIA/data/users/bob/web.conf"
    if [ -r "$HESTIA/data/users/bob/web.conf" ]; then
        skip 'The test user can read files regardless of their permission bits'
    fi
    for format in plain csv shell json; do
        run --separate-stderr list_all_user_objects web "$format"
        assert_failure
        refute_output
    done
}

@test "List all objects: One parser reads a hundred users without invoking existing commands" {
    local index owner
    local parser_calls="$BATS_TEST_TMPDIR/parser-calls"
    BIN="$BATS_TEST_TMPDIR/no-existing-commands"
    for ((index = 1; index <= 100; index++)); do
        printf -v owner 'user%03d' "$index"
        write_config "$owner" web "DOMAIN='$owner.example'"
    done
    awk() {
        printf '%s\n' 'called' >> "$parser_calls"
        command awk "$@"
    }
    run list_all_user_objects web json
    assert_success
    [ "$(wc -l < "$parser_calls")" -eq 1 ]
    run jq -e 'length == 100 and .["user100.example"].USER == "user100"' <<< "$output"
    assert_success
}
