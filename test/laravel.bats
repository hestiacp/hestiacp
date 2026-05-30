#!/usr/bin/env bats

setup() {
    export HESTIA="$BATS_TEST_TMPDIR/hestia"
    export user="demo"
    export USER_DATA="$HESTIA/data/users/$user"
    mkdir -p "$USER_DATA" "$HESTIA/conf" "$HESTIA/bin" "$HESTIA/log"
    echo "ROOT_USER='admin'" > "$HESTIA/conf/hestia.conf"
    touch "$USER_DATA/laravel.conf"
    source ./func/main.sh
    source ./func/laravel.sh
}

@test "laravel_is_app_root accepts a Laravel application root" {
    mkdir -p "$BATS_TEST_TMPDIR/app/public"
    touch "$BATS_TEST_TMPDIR/app/artisan"
    touch "$BATS_TEST_TMPDIR/app/composer.json"
    touch "$BATS_TEST_TMPDIR/app/public/index.php"

    run laravel_is_app_root "$BATS_TEST_TMPDIR/app"

    [ "$status" -eq 0 ]
}

@test "laravel_is_app_root rejects a non-Laravel directory" {
    mkdir -p "$BATS_TEST_TMPDIR/not-laravel/public"
    touch "$BATS_TEST_TMPDIR/not-laravel/composer.json"

    run laravel_is_app_root "$BATS_TEST_TMPDIR/not-laravel"

    [ "$status" -ne 0 ]
}

@test "laravel_add_app_record stores one Hestia-style app record" {
    laravel_add_app_record "example.com" "$BATS_TEST_TMPDIR/app" "8.3" "git" "https://example.com/app.git" "main"

    run grep "DOMAIN='example.com'" "$USER_DATA/laravel.conf"

    [ "$status" -eq 0 ]
    [[ "$output" == *"APP_ROOT='$BATS_TEST_TMPDIR/app'"* ]]
    [[ "$output" == *"PHP_VERSION='8.3'"* ]]
    [[ "$output" == *"SOURCE_TYPE='git'"* ]]
    [[ "$output" == *"QUEUE_CONNECTION='database'"* ]]
}

@test "laravel_add_app_record updates an existing domain record" {
    laravel_add_app_record "example.com" "$BATS_TEST_TMPDIR/app-v1" "8.2" "local" "" ""
    laravel_add_app_record "example.com" "$BATS_TEST_TMPDIR/app-v2" "8.3" "git" "https://example.com/app.git" "main"

    run grep -c "DOMAIN='example.com'" "$USER_DATA/laravel.conf"

    [ "$status" -eq 0 ]
    [ "$output" = "1" ]

    run grep "DOMAIN='example.com'" "$USER_DATA/laravel.conf"

    [ "$status" -eq 0 ]
    [[ "$output" == *"APP_ROOT='$BATS_TEST_TMPDIR/app-v2'"* ]]
    [[ "$output" == *"SOURCE_TYPE='git'"* ]]
}
