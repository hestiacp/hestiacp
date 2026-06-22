#!/bin/bash

#
# Orchestrates cross-architecture (--cross) and multi-OS (--all-os) builds of
# Hestia packages on a single machine. hst_autocompile.sh only ever builds for
# the environment it's actually running in; this script is what spins up the
# *other* environments needed for --cross/--all-os, using QEMU-emulated
# chroots (debootstrap + qemu-user-static), and runs the unmodified
# hst_autocompile.sh inside each one. The combination matching the host's own
# OS/release/architecture is built directly, no chroot needed.
#
# Usage:
#   ./chroot_build_all.sh (--all|--hestia|--nginx|--php|--web-terminal) \
#       (--cross|--all-os) [--install|--noinstall] [--keepbuild] [--debug] [branch]
#
# Examples:
#   ./chroot_build_all.sh --all --cross --noinstall --keepbuild '~localsrc'
#   ./chroot_build_all.sh --all --all-os --noinstall --keepbuild '~localsrc'
#

__DIR__="$(cd "$(dirname "${BASH_SOURCE[0]}")" > /dev/null 2>&1 && pwd)"
REPO_DIR="$(cd "$__DIR__/.." && pwd)"
BUILD_DIR="${BUILD_DIR:-/tmp/hestiacp-src}"
CHROOT_BASE_DIR='/var/lib/hestiacp-build-chroot'
ACTIVE_CHROOTS=()

# 	"debian bookworm"
#	"debian trixie"
# Supported OS releases for --all-os, as "distro codename" pairs.
ALLOS_DISTRO_RELEASES=(

	"ubuntu jammy"
	"ubuntu noble"
	"ubuntu resolute"
)

architecture="$(arch)"
if [ "$architecture" = 'aarch64' ]; then
	HOST_ARCH='arm64'
else
	HOST_ARCH='amd64'
fi

usage() {
	echo "Usage:"
	echo "    $0 (--all|--hestia|--nginx|--php|--web-terminal) (--cross|--all-os) [options] [branch]"
	echo ""
	echo "    --all           Build all hestia packages."
	echo "    --hestia        Build only the Control Panel package."
	echo "    --nginx         Build only the backend nginx engine package."
	echo "    --php           Build only the backend php engine package"
	echo "    --web-terminal  Build only the backend web terminal websocket package"
	echo "  Options:"
	echo "    --cross         Also build for the other architecture (AMD64<->ARM64),"
	echo "                    same OS as the host. nginx/php/web-terminal are built"
	echo "                    for the other arch inside a QEMU-emulated chroot."
	echo "    --all-os        Build for every supported OS release (Debian 12/13,"
	echo "                    Ubuntu 22.04/24.04/26.04) and both architectures, using"
	echo "                    a QEMU-emulated chroot for every combination other than"
	echo "                    the host's own. Implies --cross."
	echo "    --install       Install the packages built for the host's own OS/arch"
	echo "    --noinstall     Don't install (default)"
	echo "    --keepbuild     Don't delete downloaded source and build folders"
	echo "    --debug         Debug mode"
	echo "    --dontinstalldeps  Skip installing build dependencies"
	echo ""
	echo "Example: ./chroot_build_all.sh --all --all-os --noinstall --keepbuild '~localsrc'"
}

qemu_static_name() {
	case "$1" in
		arm64) echo "qemu-aarch64-static" ;;
		amd64) echo "qemu-x86_64-static" ;;
	esac
}

# Unmount everything prepare_chroot may have bind-mounted under a given
# chroot dir. Order matters: children (dev/pts, the bind mounts) before
# their parents (dev).
unmount_chroot_dir() {
	local chroot_dir=$1
	mountpoint -q "$chroot_dir$REPO_DIR" && umount "$chroot_dir$REPO_DIR"
	mountpoint -q "$chroot_dir$BUILD_DIR" && umount "$chroot_dir$BUILD_DIR"
	mountpoint -q "$chroot_dir/dev/pts" && umount "$chroot_dir/dev/pts"
	mountpoint -q "$chroot_dir/sys" && umount "$chroot_dir/sys"
	mountpoint -q "$chroot_dir/proc" && umount "$chroot_dir/proc"
	mountpoint -q "$chroot_dir/dev" && umount "$chroot_dir/dev"
}

cleanup_chroots() {
	local chroot_dir
	for chroot_dir in "${ACTIVE_CHROOTS[@]}"; do
		unmount_chroot_dir "$chroot_dir"
	done
}
trap cleanup_chroots EXIT

# One-time setup of the host tools needed for any chroot build.
ensure_cross_build_deps() {
	[ "$CROSS_DEPS_READY" = "true" ] && return
	echo >&2 "Installing debootstrap/qemu-user-static for cross/multi-OS builds..."
	apt-get -qq update > /dev/null 2>&1
	apt-get -qq install -y debootstrap qemu-user-static binfmt-support > /dev/null 2>&1
	CROSS_DEPS_READY='true'
}

# Make sure binaries for $target_arch actually get routed through QEMU.
# "systemctl restart systemd-binfmt" (the usual way qemu-user-static's
# registration gets activated) silently does nothing on hosts without
# systemd as PID 1, e.g. inside a container — so register directly via
# update-binfmt and verify it actually took, instead of finding out much
# later via a cryptic "Exec format error" deep inside debootstrap.
register_binfmt() {
	local target_arch=$1
	[ "$target_arch" = "$HOST_ARCH" ] && return

	local binfmt_name
	case "$target_arch" in
		arm64) binfmt_name="qemu-aarch64" ;;
		amd64) binfmt_name="qemu-x86_64" ;;
	esac

	if [ ! -d /proc/sys/fs/binfmt_misc ]; then
		mount binfmt_misc -t binfmt_misc /proc/sys/fs/binfmt_misc >&2
	fi

	if [ ! -e "/proc/sys/fs/binfmt_misc/$binfmt_name" ]; then
		update-binfmt --enable "$binfmt_name" >&2
	fi

	if [ ! -e "/proc/sys/fs/binfmt_misc/$binfmt_name" ]; then
		echo >&2 "[!] Could not register binfmt_misc handler '$binfmt_name' for $target_arch."
		echo >&2 "    This host/container can't run foreign-architecture binaries via"
		echo >&2 "    QEMU. If you're inside an LXC container (e.g. on Proxmox), this is"
		echo >&2 "    a host-level kernel facility that unprivileged containers normally"
		echo >&2 "    can't register themselves — either make the container privileged,"
		echo >&2 "    register '$binfmt_name' on the Proxmox host instead (it'll then be"
		echo >&2 "    inherited by containers automatically), or run this in a VM instead"
		echo >&2 "    of an LXC container."
		exit 1
	fi
}

# Sets the global PREPARED_CHROOT_DIR instead of echoing the path back,
# since callers must NOT invoke this via "x=$(prepare_chroot ...)" — that
# forks a subshell, which would (a) swallow this function's "exit 1" on
# failure instead of stopping the script, and (b) silently drop the
# ACTIVE_CHROOTS append below, since it'd only exist in the subshell's copy.
prepare_chroot() {
	local distro=$1
	local release=$2
	local target_arch=$3
	local chroot_dir="$CHROOT_BASE_DIR/$distro-$release-$target_arch"
	PREPARED_CHROOT_DIR="$chroot_dir"

	ensure_cross_build_deps
	register_binfmt "$target_arch"

	# Use our own marker rather than e.g. "-d $chroot_dir/usr" to decide
	# whether bootstrapping is done: a chroot left behind by a failed stage-2
	# run already has /usr populated but is not actually usable.
	if [ ! -f "$chroot_dir/.hestiacp-chroot-ready" ]; then
		echo >&2 "Bootstrapping $distro/$release/$target_arch chroot at $chroot_dir (first run only, this can take a while)..."
		# A previous run may have left this mounted (e.g. an interrupted
		# build, or an older version of this script); rm -rf can't remove
		# anything under an active mountpoint.
		unmount_chroot_dir "$chroot_dir"
		rm -rf "$chroot_dir"
		mkdir -p "$chroot_dir"
		local mirror
		local components_opt=""
		case "$distro" in
			ubuntu)
				if [ "$target_arch" = "amd64" ]; then
					mirror="http://archive.ubuntu.com/ubuntu"
				else
					mirror="http://ports.ubuntu.com/ubuntu-ports"
				fi
				# Unlike Debian, Ubuntu splits a lot of common -dev packages
				# (e.g. libzip-dev) out of "main" into "universe". debootstrap
				# only enables "main" by default, both for what it can pull
				# during bootstrap and for the sources.list it writes out —
				# so without this, later apt-get installs for those packages
				# fail with "Unable to locate package".
				components_opt="--components=main,universe,restricted,multiverse"
				;;
			*)
				mirror="http://deb.debian.org/debian"
				;;
		esac

		if [ "$target_arch" != "$HOST_ARCH" ]; then
			# Foreign arch: debootstrap's second stage execs target-arch
			# binaries (dpkg, postinsts, ...) to finish unpacking, which only
			# works once the qemu interpreter is in place inside the chroot.
			# So do this as two stages: unpack packages (--foreign), drop in
			# qemu-*-static, then run the second stage via chroot.
			debootstrap --foreign $components_opt --arch="$target_arch" "$release" "$chroot_dir" "$mirror" >&2
			if [ $? -ne 0 ]; then
				echo >&2 "[!] Failed to bootstrap (stage 1) $distro/$release/$target_arch chroot"
				exit 1
			fi

			local qemu_bin
			qemu_bin=$(qemu_static_name "$target_arch")
			mkdir -p "$chroot_dir/usr/bin"
			cp -f "/usr/bin/$qemu_bin" "$chroot_dir/usr/bin/"

			chroot "$chroot_dir" /debootstrap/debootstrap --second-stage >&2
			if [ $? -ne 0 ]; then
				echo >&2 "[!] Failed to bootstrap (stage 2) $distro/$release/$target_arch chroot"
				exit 1
			fi
		else
			debootstrap $components_opt --arch="$target_arch" "$release" "$chroot_dir" "$mirror" >&2
			if [ $? -ne 0 ]; then
				echo >&2 "[!] Failed to bootstrap $distro/$release/$target_arch chroot"
				exit 1
			fi
		fi

		touch "$chroot_dir/.hestiacp-chroot-ready"
	fi

	if [ "$target_arch" != "$HOST_ARCH" ]; then
		local qemu_bin
		qemu_bin=$(qemu_static_name "$target_arch")
		mkdir -p "$chroot_dir/usr/bin"
		cp -f "/usr/bin/$qemu_bin" "$chroot_dir/usr/bin/"
	fi

	mkdir -p "$chroot_dir/dev" "$chroot_dir/proc" "$chroot_dir/sys" "$chroot_dir/dev/pts"
	mountpoint -q "$chroot_dir/dev" || mount --bind /dev "$chroot_dir/dev"
	mountpoint -q "$chroot_dir/dev/pts" || mount --bind /dev/pts "$chroot_dir/dev/pts"
	mountpoint -q "$chroot_dir/proc" || mount --bind /proc "$chroot_dir/proc"
	mountpoint -q "$chroot_dir/sys" || mount --bind /sys "$chroot_dir/sys"

	# Each combo gets its own host-side build directory bind-mounted onto the
	# same in-chroot path ($BUILD_DIR, e.g. /tmp/hestiacp-src). They must NOT
	# share one physical directory: with --keepbuild forced on every chroot
	# leg, a later combo would otherwise reuse an earlier combo's configured
	# source tree (e.g. PHP's) as-is, including its already-built, wrong-
	# arch/OS binaries that the build process then tries to execute itself.
	local host_build_dir="${BUILD_DIR}-${distro}-${release}-${target_arch}"
	mkdir -p "$host_build_dir" "$chroot_dir$BUILD_DIR" "$chroot_dir$REPO_DIR"
	mountpoint -q "$chroot_dir$BUILD_DIR" || mount --bind "$host_build_dir" "$chroot_dir$BUILD_DIR"
	mountpoint -q "$chroot_dir$REPO_DIR" || mount --bind "$REPO_DIR" "$chroot_dir$REPO_DIR"

	# debootstrap doesn't populate a resolver config, so without this DNS
	# resolution fails inside the chroot and apt-get update/install fail
	# silently (errors get redirected away in hst_autocompile.sh), which then
	# surfaces much later as confusing "command not found" errors. Refresh it
	# on every prepare, not just first bootstrap, in case the host's own
	# resolv.conf changed since (e.g. a new DHCP lease).
	mkdir -p "$chroot_dir/etc"
	cp -f /etc/resolv.conf "$chroot_dir/etc/resolv.conf"

	ACTIVE_CHROOTS+=("$chroot_dir")
}

run_cross_build() {
	local distro=$1
	local release=$2
	local target_arch=$3
	shift 3
	prepare_chroot "$distro" "$release" "$target_arch"
	local chroot_dir="$PREPARED_CHROOT_DIR"

	# This combo's build artifacts live under their own host_build_dir (see
	# prepare_chroot), not the shared $BUILD_DIR — so DEB_DIR must always be
	# pointed back at the shared location explicitly, or it'd default to
	# host_build_dir/deb instead. With --all-os, give each OS release its own
	# subdirectory there too, since packages can share the same
	# name/version/arch across releases (codenames are unique across distros,
	# so $release alone is enough, no need for a distro prefix).
	local target_deb_dir="$BUILD_DIR/deb"
	[ "$ALL_OS" = "true" ] && target_deb_dir="$target_deb_dir/$release"
	mkdir -p "$target_deb_dir"
	local deb_dir_export="export DEB_DIR='$target_deb_dir'; "

	# /proc is bind-mounted from the host (see prepare_chroot), so
	# hst_autocompile.sh's own cpu-core detection would see the host's full
	# core count. Under QEMU user-mode emulation each cc1 process uses far
	# more memory than native, so building with that many parallel jobs
	# reliably triggers the OOM killer ("Killed signal terminated program
	# cc1"). Force single-job compiles for foreign-arch (emulated) builds.
	if [ "$target_arch" != "$HOST_ARCH" ]; then
		deb_dir_export="${deb_dir_export}export NUM_CPUS=1; "
	fi

	echo "Cross-building $distro/$release/$target_arch inside $chroot_dir..."
	chroot "$chroot_dir" /bin/bash -c "${deb_dir_export}cd '$REPO_DIR/src' && DEBIAN_FRONTEND=noninteractive ./hst_autocompile.sh $* --noinstall --keepbuild"
	if [ $? -ne 0 ]; then
		echo >&2 "[!] Cross build for $distro/$release/$target_arch failed"
		exit 1
	fi
}

# Set packages to compile
for i in "$@"; do
	case "$i" in
		--all)
			NGINX_B='true'
			PHP_B='true'
			WEB_TERMINAL_B='true'
			HESTIA_B='true'
			;;
		--nginx)
			NGINX_B='true'
			;;
		--php)
			PHP_B='true'
			;;
		--web-terminal)
			WEB_TERMINAL_B='true'
			;;
		--hestia)
			HESTIA_B='true'
			;;
		--debug)
			HESTIA_DEBUG='true'
			;;
		--install | Y)
			install='true'
			;;
		--noinstall | N)
			install='false'
			;;
		--keepbuild)
			KEEPBUILD='true'
			;;
		--cross)
			CROSS='true'
			;;
		--all-os)
			ALL_OS='true'
			;;
		--dontinstalldeps)
			dontinstalldeps='true'
			;;
		--help | -h)
			usage
			exit 1
			;;
		*)
			branch="$i"
			;;
	esac
done

if [ $# -eq 0 ] || { [ "$CROSS" != "true" ] && [ "$ALL_OS" != "true" ]; }; then
	usage
	exit 1
fi

if [ -z "$branch" ]; then
	echo -n "Please enter the name of the branch to build from (e.g. main): "
	read -r branch
fi

HOST_DISTRO=$(lsb_release -is | tr '[:upper:]' '[:lower:]')
HOST_RELEASE=$(lsb_release -sc)

# With --all-os, give every OS release its own deb output subdirectory
# (release codenames don't collide across distros), including the host's
# own, since packages can share the same name/version/arch across releases.
NATIVE_DEB_DIR=""
if [ "$ALL_OS" = "true" ]; then
	NATIVE_DEB_DIR="$BUILD_DIR/deb/$HOST_RELEASE"
	mkdir -p "$NATIVE_DEB_DIR"
fi

# Build for the host's own OS/release/architecture directly, no chroot needed.
NATIVE_FLAGS=""
[ "$HESTIA_B" = "true" ] && NATIVE_FLAGS="$NATIVE_FLAGS --hestia"
[ "$NGINX_B" = "true" ] && NATIVE_FLAGS="$NATIVE_FLAGS --nginx"
[ "$PHP_B" = "true" ] && NATIVE_FLAGS="$NATIVE_FLAGS --php"
[ "$WEB_TERMINAL_B" = "true" ] && NATIVE_FLAGS="$NATIVE_FLAGS --web-terminal"
# hestia has no native code, so it's cheap to let hst_autocompile.sh's own
# --cross loop build it for both archs directly, without needing a chroot.
# Skip that on --all-os though: each OS in the matrix rebuilds hestia itself.
[ "$CROSS" = "true" ] && [ "$ALL_OS" != "true" ] && NATIVE_FLAGS="$NATIVE_FLAGS --cross"
[ "$HESTIA_DEBUG" = "true" ] && NATIVE_FLAGS="$NATIVE_FLAGS --debug"
[ "$KEEPBUILD" = "true" ] && NATIVE_FLAGS="$NATIVE_FLAGS --keepbuild"
[ "$dontinstalldeps" = "true" ] && NATIVE_FLAGS="$NATIVE_FLAGS --dontinstalldeps"
if [ "$install" = "true" ]; then
	NATIVE_FLAGS="$NATIVE_FLAGS --install"
else
	NATIVE_FLAGS="$NATIVE_FLAGS --noinstall"
fi

if [ -n "$NATIVE_DEB_DIR" ]; then
	DEB_DIR="$NATIVE_DEB_DIR" "$__DIR__/hst_autocompile.sh" $NATIVE_FLAGS "$branch"
else
	"$__DIR__/hst_autocompile.sh" $NATIVE_FLAGS "$branch"
fi
if [ $? -ne 0 ]; then
	echo >&2 "[!] Native build for the host's own OS/arch failed"
	exit 1
fi

# Build every other (distro, release, arch) combination via chroot.
PKG_FLAGS=""
NEEDS_CHROOT_BUILD='false'
if [ "$HESTIA_B" = "true" ] && [ "$ALL_OS" = "true" ]; then
	PKG_FLAGS="$PKG_FLAGS --hestia"
	NEEDS_CHROOT_BUILD='true'
fi
if [ "$NGINX_B" = "true" ]; then
	PKG_FLAGS="$PKG_FLAGS --nginx"
	NEEDS_CHROOT_BUILD='true'
fi
if [ "$PHP_B" = "true" ]; then
	PKG_FLAGS="$PKG_FLAGS --php"
	NEEDS_CHROOT_BUILD='true'
fi
if [ "$WEB_TERMINAL_B" = "true" ]; then
	PKG_FLAGS="$PKG_FLAGS --web-terminal"
	NEEDS_CHROOT_BUILD='true'
fi
[ "$HESTIA_DEBUG" = "true" ] && PKG_FLAGS="$PKG_FLAGS --debug"

if [ "$NEEDS_CHROOT_BUILD" = "true" ]; then
	TARGET_COMBOS=()
	if [ "$ALL_OS" = "true" ]; then
		for dr in "${ALLOS_DISTRO_RELEASES[@]}"; do
			distro="${dr%% *}"
			release="${dr#* }"
			for a in amd64 arm64; do
				if [ "$distro" = "$HOST_DISTRO" ] && [ "$release" = "$HOST_RELEASE" ] && [ "$a" = "$HOST_ARCH" ]; then
					continue
				fi
				TARGET_COMBOS+=("$distro:$release:$a")
			done
		done
	else
		if [ "$HOST_ARCH" = "amd64" ]; then
			TARGET_COMBOS=("$HOST_DISTRO:$HOST_RELEASE:arm64")
		else
			TARGET_COMBOS=("$HOST_DISTRO:$HOST_RELEASE:amd64")
		fi
	fi

	for combo in "${TARGET_COMBOS[@]}"; do
		distro="${combo%%:*}"
		rest="${combo#*:}"
		release="${rest%%:*}"
		target_arch="${rest#*:}"
		run_cross_build "$distro" "$release" "$target_arch" $PKG_FLAGS "$branch"
	done
fi
