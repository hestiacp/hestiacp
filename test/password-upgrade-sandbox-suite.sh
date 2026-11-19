#!/bin/bash
# Invoked only by the namespace/chroot launcher.
set -euo pipefail
test "$(cat /scratch/password-native-sandbox)" = isolated-synthetic-fixture
cd /scratch
for suite in password-upgrade.py mail-password-upgrade.py; do
	setpriv --reuid=65534 --regid=65534 --clear-groups --no-new-privs \
		env PATH=/usr/bin:/usr/sbin:/bin python3 "/work/test/$suite" -v
done
for file in /work/bin/v-upgrade-user-password-hash /work/bin/v-change-sys-mail-password-upgrade \
	/work/bin/v-list-sys-config /work/func/{main,syshealth,mail-password-lock}.sh /work/install/hst-install-{debian,ubuntu}.sh \
	/work/test/*sandbox*.sh; do
	bash -n "$file"
done
for file in /work/func/dovecot-password-upgrade.php /work/web/login/index.php \
	/work/web/edit/server/index.php /work/web/templates/pages/edit_server.php \
	/work/test/*password*.php; do
	php -n -l "$file"
done
exec python3 /work/test/mail-password-upgrade-native.py
