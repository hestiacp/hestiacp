#!/usr/bin/env bats

load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'

setup() {
	export HESTIA="$BATS_TEST_TMPDIR/hestia"
	export restart_calls="$BATS_TEST_TMPDIR/restart-calls"
	restart_queue="$HESTIA/data/queue/restart.pipe"
	restart_mocks="$BATS_TEST_TMPDIR/restart-mocks.sh"
	mkdir -p "$HESTIA"/{bin,conf,data/queue,log}
	ln -s "$BATS_TEST_DIRNAME/../func" "$HESTIA/func"

	local script
	for script in v-restart-cron v-restart-ftp v-restart-proxy v-restart-service v-restart-web v-restart-web-backend; do
		ln -s "$BATS_TEST_DIRNAME/../bin/$script" "$HESTIA/bin/$script"
	done
	printf '#!/bin/bash\nexit 0\n' > "$HESTIA/bin/v-log-action"
	chmod +x "$HESTIA/bin/v-log-action"

	cat > "$HESTIA/conf/hestia.conf" <<'EOF'
ROOT_USER='admin'
WEB_SYSTEM='nginx'
WEB_BACKEND='php-fpm'
PROXY_SYSTEM='nginx'
CRON_SYSTEM='cron'
FTP_SYSTEM='proftpd'
DEBUG_MODE='false'
SCHEDULED_RESTART='no'
EOF
	cat > "$restart_mocks" <<'EOF'
# Redirect the installed bootstrap path into the fixture.
source() {
	if [ "$1" = /etc/hestiacp/hestia.conf ]; then
		builtin source "$HESTIA/conf/hestia.conf"
	else
		builtin source "$@"
	fi
}

service() { printf 'service %s\n' "$*" >> "$restart_calls"; }
systemctl() { printf 'systemctl %s\n' "$*" >> "$restart_calls"; }
EOF
	: > "$restart_queue"
	: > "$restart_calls"
}

@test "Explicit service restart requests queue with automatic scheduling disabled" {
	local script
	local -a jobs=()
	for script in v-restart-cron v-restart-ftp v-restart-proxy v-restart-web v-restart-web-backend; do
		run env BASH_ENV="$restart_mocks" "$HESTIA/bin/$script" scheduled
		assert_success
		jobs+=("$HESTIA/bin/$script now")
	done
	local expected
	expected=$(printf '%s\n' "${jobs[@]}")
	run cat "$restart_queue"
	assert_output "$expected"
	[ ! -s "$restart_calls" ]
}

@test "An existing queued web restart runs and clears its entry" {
	printf '%s now\n' "$HESTIA/bin/v-restart-web" > "$restart_queue"

	run env BASH_ENV="$restart_mocks" bash "$restart_queue"
	assert_success
	run cat "$restart_calls"
	assert_output $'service nginx configtest\nsystemctl reset-failed nginx\nsystemctl restart nginx'
	[ ! -s "$restart_queue" ]
}

@test "An omitted restart mode queues with automatic scheduling enabled" {
	sed -i "s/^SCHEDULED_RESTART=.*/SCHEDULED_RESTART='yes'/" "$HESTIA/conf/hestia.conf"

	run env BASH_ENV="$restart_mocks" "$HESTIA/bin/v-restart-web"
	assert_success
	run cat "$restart_queue"
	assert_output "$HESTIA/bin/v-restart-web now"
	[ ! -s "$restart_calls" ]
}
