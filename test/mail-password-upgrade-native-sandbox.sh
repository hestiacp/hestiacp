#!/bin/bash
# Run as root on a disposable Hestia/Dovecot 2.3/2.4 test machine:
#   bash test/mail-password-upgrade-native-sandbox.sh
# Optional: --dovecot-root /absolute/path/to/extracted/debian/packages
# Optional: --php-runtime /absolute/path/to/php (isolated fixture only)
# Requires existing packages; never installs anything or touches live configs.
# Host systemd supplies cgroup limits; the private namespace/chroot supplies
# read-only source/toolchain and scratch-only writes, with loopback-only traffic.
set -euo pipefail
if [ "${1-}" != '--inside-unit' ]; then
	if [ "$(id -u)" -ne 0 ]; then
		echo "Requires root for the isolated mount/PID/network namespaces" >&2
		exit 1
	fi
	project=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
	toolchain=""
	php_runtime='/usr/local/hestia/php/bin/php'
	while [ "$#" -gt 0 ]; do
		[ "$#" -ge 2 ]
		case "$1" in
			--dovecot-root)
				toolchain=$(realpath "$2")
				test -x "$toolchain/usr/sbin/dovecot"
				;;
			--php-runtime)
				php_runtime=$(realpath "$2")
				;;
			*)
				echo "Unknown option: $1" >&2
				exit 2
				;;
		esac
		shift 2
	done
	for program in systemd-run unshare prlimit setpriv python3 openssl ip dovecot doveconf exim4; do
		command -v "$program" > /dev/null
	done
	test -x "$php_runtime"
	mkdir -p "$project/tmp"
	base=$(mktemp -d "$project/tmp/password-native.XXXXXXXX")
	# Only this invocation's newly created tree is removed.
	trap 'rm -rf -- "$base"' EXIT
	mkdir -p "$base/source/tmp"
	cp -a "$project"/{bin,func,test,install,web} "$base/source/"
	systemd-run --quiet --wait --pipe --collect --unit="hestia-${base##*/}" \
		-p TasksMax=128 -p MemoryMax=768M -p CPUQuota=100% \
		-p RuntimeMaxSec=180 -p KillMode=control-group \
		/usr/bin/env -i PATH=/usr/bin:/usr/sbin:/bin \
		/bin/bash "$project/test/mail-password-upgrade-native-sandbox.sh" --inside-unit "$base" "$toolchain" "$php_runtime"
	exit 0
fi
base=$2
toolchain=${3-}
php_runtime=${4-/usr/local/hestia/php/bin/php}
root="$base/root"
source="$base/source"
mkdir -p "$root"/{usr,etc/ssl,etc/alternatives,etc/dovecot,etc/exim4/domains,etc/hestiacp,etc/systemd/system,dev,proc,home,run,tmp,var/lib/dovecot,scratch,work} \
	"$base/hestia"/{bin,func,conf,data,log,php/bin,php/lib,install/common/dovecot/2.4} "$base/runtime" "$source/bin"
for pair in bin:usr/bin sbin:usr/sbin lib:usr/lib lib64:usr/lib64; do
	ln -sfn "${pair#*:}" "$root/${pair%%:*}"
done
printf '%s\n' 'root:x:0:0:Synthetic root:/scratch/home:/bin/bash' \
	'nobody:x:65534:65534:Synthetic nobody:/nonexistent:/usr/sbin/nologin' \
	'dovecot:x:107:111:Synthetic auth:/usr/lib/dovecot:/usr/sbin/nologin' \
	'dovenull:x:108:112:Synthetic login:/nonexistent:/usr/sbin/nologin' \
	'alice:x:12000:8:Synthetic mailbox:/home/alice:/usr/sbin/nologin' \
	'Debian-exim:x:12001:8:Synthetic SMTP:/nonexistent:/usr/sbin/nologin' > "$root/etc/passwd"
printf '%s\n' 'root:x:0:' 'mail:x:8:dovecot,Debian-exim' 'dovecot:x:111:' \
	'dovenull:x:112:' 'Debian-exim:x:12001:' > "$root/etc/group"
printf '%s\n' 'passwd: files' 'group: files' 'hosts: files' > "$root/etc/nsswitch.conf"
printf '%s\n' '127.0.0.1 localhost password-test.invalid' > "$root/etc/hosts"
printf '%s\n' 'password-native-fixture' > "$root/etc/hostname"
cp /etc/ld.so.cache "$root/etc/ld.so.cache"
cp /etc/ssl/openssl.cnf "$root/etc/ssl/openssl.cnf"
ln -s /proc/self/fd "$root/dev/fd"
ln -s /usr/bin/php8.5 "$root/etc/alternatives/php"
ln -s /usr/bin/mawk "$root/etc/alternatives/awk"
printf "%s\n" "HESTIA='/usr/local/hestia'" > "$root/etc/hestiacp/hestia.conf"
cp -a "$source"/{bin,func,install} "$base/hestia/"
chmod 755 "$base/hestia/bin/"*
cat > "$source/bin/systemctl" << 'STUB'
#!/bin/bash
# No service manager in this chroot. Native installed-service tests cover the
# actual mount-namespace restart; this daemon-only fixture reloads its config.
[ "$*" != daemon-reload ] || exit 0
[[ "$*" = 'restart dovecot' || "$*" = 'reload-or-restart dovecot' ]] || exit 2
exec /usr/sbin/dovecot reload
STUB
chmod 755 "$source/bin/systemctl"
cat > "$source/bin/flock" << 'STUB'
#!/bin/bash
# Test-only observation point: the writer has reached its shared-gate wait.
if [ -f /scratch/observe-admin-gate ]; then
	touch /scratch/admin-gate-attempt
fi
exec /usr/bin/flock "$@"
STUB
chmod 755 "$source/bin/flock"
touch "$base/hestia/php/bin/php" "$base/hestia/php/lib/php.ini"
exec env -i PATH=/usr/bin:/usr/sbin:/bin base="$base" root="$root" toolchain="$toolchain" php_runtime="$php_runtime" \
	timeout --signal=KILL 180 unshare --mount --net --pid --uts --fork --kill-child=KILL \
	/bin/bash -euo pipefail -c '
mount --make-rprivate /
hostname password-native-fixture
mount --bind "$root" "$root"
mount --bind /usr "$root/usr"
mount -o remount,bind,ro "$root/usr"
if [ -n "$toolchain" ]; then
	for target in usr/lib/dovecot usr/sbin/dovecot usr/bin/doveadm usr/bin/doveconf; do
		mount --bind "$toolchain/$target" "$root/$target"
		mount -o remount,bind,ro "$root/$target"
	done
fi
mount --bind "$base/hestia" "$root/usr/local/hestia"
mount --bind "$php_runtime" "$root/usr/local/hestia/php/bin/php"
mount -o remount,bind,ro "$root/usr/local/hestia/php/bin/php"
mount --bind /usr/local/hestia/php/lib/php.ini "$root/usr/local/hestia/php/lib/php.ini"
mount -o remount,bind,ro "$root/usr/local/hestia/php/lib/php.ini"
mount -o remount,bind,ro "$root/usr/local/hestia"
mount --bind "$base/source" "$root/work"
mount -o remount,bind,ro "$root/work"
mount -t tmpfs -o size=192m,nosuid,nodev tmpfs "$base/runtime"
mkdir -p "$base/runtime"/{hestia/conf,hestia/data,hestia/log,home,run,tmp,dovecot,domains,state,systemd}
mkdir -p "$base/runtime/tests"
chmod 777 "$base/runtime/tests"
printf "%s\n" isolated-synthetic-fixture > "$base/runtime/password-native-sandbox"
mount --bind "$base/runtime" "$root/scratch"
mount --bind "$base/runtime/tests" "$root/work/tmp"
for pair in hestia/conf:usr/local/hestia/conf hestia/data:usr/local/hestia/data hestia/log:usr/local/hestia/log home:home run:run tmp:tmp dovecot:etc/dovecot domains:etc/exim4/domains state:var/lib/dovecot systemd:etc/systemd/system; do
	mount --bind "$base/runtime/${pair%%:*}" "$root/${pair#*:}"
done
chmod 1777 "$base/runtime/tmp"
for device in null urandom random; do
	touch "$root/dev/$device"
	mount --bind "/dev/$device" "$root/dev/$device"
done
mount -t proc -o nosuid,nodev,noexec proc "$root/proc"
mount -o remount,bind,ro "$root"
ip link set lo up
exec prlimit --cpu=90 --as=805306368 --nofile=256 --fsize=8388608 \
	/usr/sbin/chroot "$root" /usr/bin/setpriv --no-new-privs \
	--bounding-set=-all,+setuid,+setgid,+chown,+dac_override,+fowner,+sys_chroot,+kill \
	--inh-caps=-all --ambient-caps=-all \
	/usr/bin/env -i PATH=/work/bin:/usr/bin:/usr/sbin:/bin HOME=/scratch/home TMPDIR=/tmp LC_ALL=C \
	PYTHONDONTWRITEBYTECODE=1 /bin/bash /work/test/password-upgrade-sandbox-suite.sh
'
