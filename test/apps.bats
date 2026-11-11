#!/usr/bin/env bats

# Web domain applications (v-*-web-domain-app). Runs on nginx + Apache and
# on nginx-only installs. Uses the "custom" and "python" runtimes so that no
# Node.js download is needed.

if [ "${PATH#*/usr/local/hestia/bin*}" = "$PATH" ]; then
	. /etc/profile.d/hestia.sh
fi

load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'
load 'test_helper/bats-file/load'

function setup() {
	user=appbats-5285
	domain=app-5285.hestiacp.com
	domain2=app2-5285.hestiacp.com
	unit="hestia-app-${user}_${domain}.service"
	source $HESTIA/func/main.sh
	source $HESTIA/conf/hestia.conf
	source $HESTIA/func/ip.sh
	ip=$(v-list-sys-ips plain | head -n1 | cut -f1)
	appdir=$HOMEDIR/$user/web/$domain/app
}

# Request the domain through nginx
function request() {
	curl -s -m 10 --resolve "$1:80:$ip" "http://$1/"
}

function wait_for_app() {
	for _ in $(seq 1 50); do
		[ -n "$(request "$1")" ] && return 0
		sleep 0.2
	done
	return 1
}

@test "APP: Setup user and web domains" {
	run v-add-user $user "$(generate_password)" $user@hestiacp.com default
	assert_success
	run v-add-web-domain $user $domain
	assert_success
	run v-add-web-domain $user $domain2
	assert_success
}

@test "APP: Add custom app" {
	run v-add-web-domain-app $user $domain custom system 'exec python3 -m http.server "$PORT" --bind "$HOST"'
	assert_success
	refute_output
	assert_dir_exist "$appdir"
	assert_file_exist "/etc/systemd/system/$unit"
}

@test "APP: Add app (duplicate)" {
	run v-add-web-domain-app $user $domain custom system 'true'
	assert_failure $E_EXISTS
}

@test "APP: Reject invalid runtime, folder and command" {
	run v-add-web-domain-app $user $domain2 ruby system 'ruby app.rb'
	assert_failure $E_INVALID
	run v-add-web-domain-app $user $domain2 custom system 'true' '../../etc'
	assert_failure $E_INVALID
	run v-add-web-domain-app $user $domain2 custom system 'true' 'public_html'
	assert_failure $E_INVALID
	run v-add-web-domain-app $user $domain2 custom system $'true\nExecStartPre=/bin/false'
	assert_failure $E_INVALID
	run v-add-web-domain-app $user $domain2 python system ''
	assert_failure $E_ARGS
}

@test "APP: App template can't be selected without an app" {
	if [ -n "$PROXY_SYSTEM" ]; then
		run v-change-web-domain-proxy-tpl $user $domain2 app
	else
		run v-change-web-domain-tpl $user $domain2 app
	fi
	assert_failure $E_INVALID
}

@test "APP: Start app and serve the domain through nginx" {
	echo "hestia-app-proof" | runuser -u $user -- tee $appdir/index.html > /dev/null
	run v-start-web-domain-app $user $domain
	assert_success
	refute_output
	run systemctl is-active $unit
	assert_output 'active'
	wait_for_app $domain
	run request $domain
	assert_output 'hestia-app-proof'
}

@test "APP: App runs as the domain owner" {
	pid=$(systemctl show -p MainPID --value $unit)
	run stat -c %U /proc/$pid
	assert_output "$user"
}

@test "APP: List apps" {
	run v-list-web-domain-apps $user json
	assert_success
	assert_output --partial '"STATE": "active"'
	run v-list-web-domain-app $user $domain plain
	assert_success
	assert_output --partial "$domain"
	run v-list-web-domain-apps $user
	assert_success
}

@test "APP: Change environment" {
	printf '# comment\nexport GREETING="hello $HOME"\nEMPTY=\n' > /tmp/app-5285.env
	run v-change-web-domain-app-env $user $domain /tmp/app-5285.env
	assert_success
	refute_output
	run v-list-web-domain-app-env $user $domain plain
	assert_success
	assert_output "$(printf 'GREETING=hello $HOME\nEMPTY=')"
	rm -f /tmp/app-5285.env
}

@test "APP: Reject reserved and invalid environment" {
	echo 'PORT=1' > /tmp/app-5285.env
	run v-change-web-domain-app-env $user $domain /tmp/app-5285.env
	assert_failure $E_INVALID
	echo 'NOT VALID=1' > /tmp/app-5285.env
	run v-change-web-domain-app-env $user $domain /tmp/app-5285.env
	assert_failure $E_INVALID
	run v-change-web-domain-app-env $user $domain /etc/hostname
	assert_failure $E_INVALID
	rm -f /tmp/app-5285.env
}

@test "APP: Proxy template can't be replaced while the app exists" {
	if [ -n "$PROXY_SYSTEM" ]; then
		run v-change-web-domain-proxy-tpl $user $domain default
	else
		run v-change-web-domain-tpl $user $domain default
	fi
	assert_failure $E_INUSE
}

@test "APP: Rename is refused while the app exists" {
	run v-change-web-domain-name $user $domain renamed-5285.hestiacp.com
	assert_failure $E_INUSE
}

@test "APP: Crashed app is restarted" {
	pid=$(systemctl show -p MainPID --value $unit)
	kill -9 $pid
	sleep 5
	run systemctl is-active $unit
	assert_output 'active'
	wait_for_app $domain
}

@test "APP: Suspend and unsuspend web domain" {
	run v-suspend-web-domain $user $domain
	assert_success
	run systemctl is-active $unit
	assert_output 'inactive'
	run v-unsuspend-web-domain $user $domain
	assert_success
	run systemctl is-active $unit
	assert_output 'active'
	wait_for_app $domain
	run request $domain
	assert_output 'hestia-app-proof'
}

@test "APP: Rebuild keeps the app running" {
	pid=$(systemctl show -p MainPID --value $unit)
	run v-rebuild-web-domains $user
	assert_success
	run systemctl show -p MainPID --value $unit
	assert_output "$pid"
}

@test "APP: Stop app" {
	run v-stop-web-domain-app $user $domain
	assert_success
	refute_output
	run systemctl is-active $unit
	assert_output 'inactive'
	run systemctl is-enabled $unit
	assert_output 'disabled'
}

@test "APP: JSON output escapes backslashes and quotes" {
	run v-change-web-domain-app $user $domain custom system 'exec python3 -m http.server "$PORT" --bind "$HOST" # a\b "q"'
	assert_success
	run bash -c "v-list-web-domain-apps $user json | python3 -c 'import json,sys; print(json.load(sys.stdin)[\"$domain\"][\"CMD\"])'"
	assert_success
	assert_output --partial 'a\b "q"'
}

@test "APP: Backup and restore keep the app" {
	printf 'GREETING=from-backup\n' > /tmp/app-5285.env
	run v-change-web-domain-app-env $user $domain /tmp/app-5285.env no
	assert_success
	rm -f /tmp/app-5285.env
	run v-start-web-domain-app $user $domain
	assert_success
	port=$(grep "^DOMAIN='$domain' " $HESTIA/data/users/$user/app.conf | grep -o "PORT='[0-9]*'")

	rm -f /backup/$user.*.tar
	run v-backup-user $user
	assert_success
	backup=$(ls -t /backup/$user.*.tar | head -n1)
	run bash -c "tar tf $backup | grep -c 'web/$domain/hestia/app'"
	assert_output '2'

	run v-delete-user $user
	assert_success
	assert_file_not_exist "/etc/systemd/system/$unit"

	run v-restore-user $user "$(basename $backup)"
	assert_success
	run grep -o "PORT='[0-9]*'" <(grep "^DOMAIN='$domain' " $HESTIA/data/users/$user/app.conf)
	assert_output "$port"
	run v-list-web-domain-app-env $user $domain plain
	assert_output 'GREETING=from-backup'
	run systemctl is-active $unit
	assert_output 'active'
	wait_for_app $domain
	run request $domain
	assert_output 'hestia-app-proof'
	rm -f "$backup"
}

@test "APP: Python app with virtualenv" {
	pydir=$HOMEDIR/$user/web/$domain2/app
	run v-add-web-domain-app $user $domain2 python system '.venv/bin/python -m http.server "$PORT" --bind "$HOST"'
	assert_success
	echo "python-proof" | runuser -u $user -- tee $pydir/index.html > /dev/null
	run v-update-web-domain-app-deps $user $domain2
	assert_success
	assert_file_exist "$pydir/.venv/bin/python"
	run stat -c %U "$pydir/.venv"
	assert_output "$user"
	run v-start-web-domain-app $user $domain2
	assert_success
	wait_for_app $domain2
	run request $domain2
	assert_output 'python-proof'
}

@test "APP: Delete app restores the previous template" {
	run v-delete-web-domain-app $user $domain2
	assert_success
	refute_output
	assert_file_not_exist "/etc/systemd/system/hestia-app-${user}_${domain2}.service"
	if [ -n "$PROXY_SYSTEM" ]; then
		run v-list-web-domain $user $domain2 json
		assert_output --partial '"PROXY": "default"'
	fi
	# App files are kept
	assert_file_exist "$HOMEDIR/$user/web/$domain2/app/index.html"
}

@test "APP: Delete web domain removes its app" {
	run v-delete-web-domain $user $domain
	assert_success
	assert_file_not_exist "/etc/systemd/system/$unit"
}

@test "APP: Cleanup" {
	run v-delete-user $user
	assert_success
	run bash -c "ls /etc/systemd/system/hestia-app-${user}_* 2> /dev/null"
	assert_output ''
}
