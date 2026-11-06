#!/usr/bin/env bash
#
# Generate and publish a HestiaCP apt repository for one channel.
#
# Build artifacts are expected in:
#   ${DEBS_DIR}/debs-<suite>-<arch>/*.deb
#   ${HESTIA_DIR}/hestia_*.deb
#
# Required environment:
#   CHANNEL, VERSION, SUITES
#   APT_GPG_PRIVATE_KEY, APT_GPG_PASSPHRASE (optional)
#   R2_ACCESS_KEY_ID, R2_SECRET_ACCESS_KEY, R2_BUCKET
#   R2_S3_ENDPOINT or R2_ACCOUNT_ID
#
# Optional:
#   DRY_RUN=true|false (default true)
#   PRUNE=true|false (default false)
set -euo pipefail

DEBUG="${DEBUG:-false}"
trap 'echo "::error::publish.sh failed at line $LINENO"' ERR

CHANNEL="${CHANNEL:?CHANNEL is required}"
VERSION="${VERSION:?VERSION is required}"
SUITES="${SUITES:?SUITES is required}"
DRY_RUN="${DRY_RUN:-true}"
PRUNE="${PRUNE:-false}"
FORCE="${FORCE:-false}"
DEBS_DIR="${DEBS_DIR:-debs}"
HESTIA_DIR="${HESTIA_DIR:-hestia}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

case "$CHANNEL" in
	beta)
		ORIGIN='Hesita Beta'
		LABEL='Hestia Beta'
		;;
	production)
		ORIGIN='apt.hestiacp.com'
		LABEL='apt repository'
		;;
	*)
		echo "::error::Unknown channel: $CHANNEL" >&2
		exit 1
		;;
esac

declare -A SUITE_SUFFIX=(
	[bullseye]=debian11
	[bookworm]=debian12
	[trixie]=debian13
	[jammy]=ubuntu22.04
	[noble]=ubuntu24.04
	[resolute]=ubuntu26.04
)

require() {
	local name="$1"
	if [[ -z "${!name:-}" ]]; then
		echo "::error::Missing required value: $name" >&2
		exit 1
	fi
}

require APT_GPG_PRIVATE_KEY
require R2_ACCESS_KEY_ID
require R2_SECRET_ACCESS_KEY
require R2_BUCKET

R2_BUCKET="${R2_BUCKET#s3://}"
R2_BUCKET="${R2_BUCKET%/}"

WORK="${RUNNER_TEMP:-/tmp}/hestia-apt-release"
REPO="$WORK/repo"
export GNUPGHOME="$WORK/gnupg"
rm -rf "$WORK"
mkdir -p "$REPO" "$GNUPGHOME"
chmod 700 "$GNUPGHOME"

# Import the signing key. Support both armored and base64-wrapped secrets.
echo '==> Importing signing key'
if [[ "$APT_GPG_PRIVATE_KEY" == *'BEGIN PGP PRIVATE KEY'* ]]; then
	printf '%s\n' "$APT_GPG_PRIVATE_KEY" | gpg --batch --import
else
	printf '%s' "$APT_GPG_PRIVATE_KEY" | base64 -d | gpg --batch --import
fi

KEY_ID="$(gpg --batch --list-secret-keys --with-colons | awk -F: '/^sec:/{print $5; exit}')"
if [[ -z "$KEY_ID" ]]; then
	echo '::error::No signing key found after import' >&2
	exit 1
fi
gpg --batch --export "$KEY_ID" > "$WORK/trusted.gpg"

GPG_ARGS=(--batch --yes --pinentry-mode loopback --local-user "$KEY_ID" --digest-algo SHA256)
if [[ -n "${APT_GPG_PASSPHRASE:-}" ]]; then
	printf '%s' "$APT_GPG_PASSPHRASE" > "$WORK/passphrase"
	chmod 600 "$WORK/passphrase"
	GPG_ARGS+=(--passphrase-file "$WORK/passphrase")
else
	GPG_ARGS+=(--passphrase '')
fi

if [[ "$DEBUG" == 'true' ]]; then
	set -x
fi

BASE_VERSION="${VERSION%%~*}"
PRERELEASE_TAG=''
if [[ "$VERSION" == *'~'* ]]; then
	PRERELEASE_TAG="${VERSION#*~}"
fi

# Populate the pool with only the packages built for this release.
echo '==> Building package pool'
SUITE_LIST=()
for suite in ${SUITES//,/ }; do
	suite="${suite//[[:space:]]/}"
	[[ -n "$suite" ]] || continue
	if [[ -z "${SUITE_SUFFIX[$suite]:-}" ]]; then
		echo "::error::Unsupported suite: $suite" >&2
		exit 1
	fi
	SUITE_LIST+=("$suite")

	for arch in amd64 arm64; do
		echo "==> $suite/$arch: collecting packages"
		src="$DEBS_DIR/debs-$suite-$arch"
		if [[ ! -d "$src" ]]; then
			echo "::error::Missing artifact directory: $src" >&2
			exit 1
		fi

		shopt -s nullglob
		debs=("$src"/*.deb)
		shopt -u nullglob
		if ((${#debs[@]} == 0)); then
			echo "::error::No debs in $src" >&2
			exit 1
		fi

		declare -A seen_pkgs=()
		for deb in "${debs[@]}"; do
			pkg="$(dpkg-deb -f "$deb" Package)"
			arch_in_deb="$(dpkg-deb -f "$deb" Architecture)"
			ver="$(dpkg-deb -f "$deb" Version)"
			case "$pkg" in
				hestia-nginx | hestia-php | hestia-web-terminal) ;;
				hestia)
					echo "::error::Unexpected hestia package in native artifact $deb" >&2
					exit 1
					;;
				*)
					echo "::error::Unexpected package $pkg in $deb" >&2
					exit 1
					;;
			esac
			seen_pkgs["$pkg"]=$((${seen_pkgs[$pkg]:-0} + 1))
			if [[ "$arch_in_deb" != "$arch" ]]; then
				echo "::error::$deb has architecture $arch_in_deb, expected $arch" >&2
				exit 1
			fi
			if [[ -n "$PRERELEASE_TAG" && "$ver" != *"~$PRERELEASE_TAG"* ]]; then
				echo "::error::$deb has version $ver, expected a ~$PRERELEASE_TAG suffix" >&2
				exit 1
			fi
			if [[ "$pkg" == 'lib'* ]]; then
				letter="lib${pkg:3:1}"
			else
				letter="${pkg:0:1}"
			fi
			dest="$REPO/pool/$suite/main/$letter/$pkg"
			mkdir -p "$dest"
			cp "$deb" "$dest/"
		done

		for required in hestia-nginx hestia-php hestia-web-terminal; do
			if [[ "${seen_pkgs[$required]:-0}" -ne 1 ]]; then
				echo "::error::$suite/$arch: expected exactly one $required, found ${seen_pkgs[$required]:-0}" >&2
				exit 1
			fi
		done

		# The hestia package is built once with --cross for every distro suffix.
		shopt -s nullglob
		hestia_debs=("$HESTIA_DIR"/hestia_*"+${SUITE_SUFFIX[$suite]}"*"_${arch}.deb")
		shopt -u nullglob
		if ((${#hestia_debs[@]} != 1)); then
			echo "::error::Expected one hestia deb for $suite/$arch, found ${#hestia_debs[@]}" >&2
			exit 1
		fi
		deb="${hestia_debs[0]}"
		ver="$(dpkg-deb -f "$deb" Version)"
		arch_in_deb="$(dpkg-deb -f "$deb" Architecture)"
		if [[ "$ver" != "$BASE_VERSION"-* ]]; then
			echo "::error::$deb has version $ver, expected ${BASE_VERSION}-*" >&2
			exit 1
		fi
		if [[ -n "$PRERELEASE_TAG" && "$ver" != *"~$PRERELEASE_TAG"* ]]; then
			echo "::error::$deb has version $ver, expected a ~$PRERELEASE_TAG suffix" >&2
			exit 1
		fi
		if [[ "$arch_in_deb" != "$arch" ]]; then
			echo "::error::$deb has architecture $arch_in_deb, expected $arch" >&2
			exit 1
		fi
		dest="$REPO/pool/$suite/main/h/hestia"
		mkdir -p "$dest"
		cp "$deb" "$dest/"
	done
done

# Generate Packages, Packages.gz, Release, InRelease and Release.gpg per suite.
for suite in "${SUITE_LIST[@]}"; do
	echo "==> Generating metadata for $suite"
	(
		cd "$REPO"
		apt-ftparchive packages "pool/$suite"
	) > "$WORK/Packages.$suite"

	for arch in amd64 arm64; do
		mkdir -p "$REPO/dists/$suite/main/binary-$arch"
		python3 "$SCRIPT_DIR/split-packages.py" "$WORK/Packages.$suite" "$arch" \
			> "$REPO/dists/$suite/main/binary-$arch/Packages"
		gzip -9 -n -c "$REPO/dists/$suite/main/binary-$arch/Packages" \
			> "$REPO/dists/$suite/main/binary-$arch/Packages.gz"
	done

	cat > "$WORK/release.$suite.conf" << EOF
APT::FTPArchive::Release::Origin "$ORIGIN";
APT::FTPArchive::Release::Label "$LABEL";
APT::FTPArchive::Release::Suite "$suite";
APT::FTPArchive::Release::Codename "$suite";
APT::FTPArchive::Release::Architectures "amd64 arm64";
APT::FTPArchive::Release::Components "main";
EOF

	apt-ftparchive -c "$WORK/release.$suite.conf" release "$REPO/dists/$suite" \
		> "$WORK/Release.$suite"
	mv "$WORK/Release.$suite" "$REPO/dists/$suite/Release"
	gpg "${GPG_ARGS[@]}" --clearsign -o "$REPO/dists/$suite/InRelease" "$REPO/dists/$suite/Release"
	gpg "${GPG_ARGS[@]}" -abs -o "$REPO/dists/$suite/Release.gpg" "$REPO/dists/$suite/Release"
done

# Verify signatures and local metadata.
for suite in "${SUITE_LIST[@]}"; do
	echo "==> Verifying $suite"
	gpg --batch --verify "$REPO/dists/$suite/InRelease" > /dev/null 2>&1
	for arch in amd64 arm64; do
		grep -q '^Package: hestia$' "$REPO/dists/$suite/main/binary-$arch/Packages"
		grep -q "^Version: ${BASE_VERSION}-" "$REPO/dists/$suite/main/binary-$arch/Packages"
	done

	mkdir -p "$WORK/lists.$suite" "$WORK/cache.$suite"
	printf 'deb [signed-by=%s] file:%s %s main\n' "$WORK/trusted.gpg" "$REPO" "$suite" \
		> "$WORK/sources.$suite.list"
	APT_OPTS=(
		-o "Dir::Etc::sourcelist=$WORK/sources.$suite.list"
		-o 'Dir::Etc::sourceparts=/dev/null'
		-o "Dir::Etc::trusted=$WORK/trusted.gpg"
		-o 'Dir::Etc::trustedparts=/dev/null'
		-o "Dir::State::Lists=$WORK/lists.$suite"
		-o "Dir::Cache=$WORK/cache.$suite"
		-o 'APT::Get::List-Cleanup=0'
	)
	if [[ "$DEBUG" == 'true' ]]; then
		apt-get "${APT_OPTS[@]}" update
	else
		apt-get "${APT_OPTS[@]}" update > /dev/null
	fi
done

if [[ "$DRY_RUN" == 'true' ]]; then
	echo "==> Dry run complete: $REPO"
	find "$REPO" -type f | sort
	du -sh "$REPO"
	exit 0
fi

ENDPOINT="${R2_S3_ENDPOINT:-}"
if [[ -z "$ENDPOINT" ]]; then
	if [[ -z "${R2_ACCOUNT_ID:-}" ]]; then
		echo '::error::Set R2_S3_ENDPOINT or R2_ACCOUNT_ID' >&2
		exit 1
	fi
	ENDPOINT="https://${R2_ACCOUNT_ID}.r2.cloudflarestorage.com"
fi
ENDPOINT="${ENDPOINT%/}"

if [[ "$DEBUG" == 'true' ]]; then
	set +x
fi
export AWS_ACCESS_KEY_ID="$R2_ACCESS_KEY_ID"
export AWS_SECRET_ACCESS_KEY="$R2_SECRET_ACCESS_KEY"
export AWS_DEFAULT_REGION='auto'
export AWS_EC2_METADATA_DISABLED='true'
if [[ "$DEBUG" == 'true' ]]; then
	set -x
fi

AWS_FLAGS=(--only-show-errors)
if [[ "$DEBUG" == 'true' ]]; then
	AWS_FLAGS=()
fi

if [[ "$FORCE" != 'true' ]]; then
	echo '==> Checking for existing package objects'
	while IFS= read -r -d '' deb; do
		key="${deb#"$REPO/"}"
		if ! existing="$(aws s3api list-objects-v2 --bucket "$R2_BUCKET" --prefix "$key" --max-keys 1 --endpoint-url "$ENDPOINT" 2>&1)"; then
			echo "::error::Could not check s3://$R2_BUCKET/$key: $existing" >&2
			exit 1
		fi
		if [[ "$existing" == *'"Key"'* ]]; then
			echo "::error::Object already exists: s3://$R2_BUCKET/$key (bump pkgrev or set force=true)" >&2
			exit 1
		fi
	done < <(find "$REPO/pool" -type f -name '*.deb' -print0)
fi

for suite in "${SUITE_LIST[@]}"; do
	echo "==> Uploading $suite to s3://$R2_BUCKET"
	aws s3 sync "$REPO/pool/$suite/" "s3://$R2_BUCKET/pool/$suite/" \
		--endpoint-url "$ENDPOINT" "${AWS_FLAGS[@]}"
	aws s3 sync "$REPO/dists/$suite/main/" "s3://$R2_BUCKET/dists/$suite/main/" \
		--endpoint-url "$ENDPOINT" "${AWS_FLAGS[@]}"
	aws s3 cp "$REPO/dists/$suite/Release" "s3://$R2_BUCKET/dists/$suite/Release" \
		--endpoint-url "$ENDPOINT" "${AWS_FLAGS[@]}"
	aws s3 cp "$REPO/dists/$suite/Release.gpg" "s3://$R2_BUCKET/dists/$suite/Release.gpg" \
		--endpoint-url "$ENDPOINT" "${AWS_FLAGS[@]}"
	aws s3 cp "$REPO/dists/$suite/InRelease" "s3://$R2_BUCKET/dists/$suite/InRelease" \
		--endpoint-url "$ENDPOINT" "${AWS_FLAGS[@]}"

	if [[ "$PRUNE" == 'true' ]]; then
		echo "==> Pruning old objects for $suite"
		aws s3 sync "$REPO/pool/$suite/" "s3://$R2_BUCKET/pool/$suite/" \
			--endpoint-url "$ENDPOINT" "${AWS_FLAGS[@]}" --delete
		aws s3 sync "$REPO/dists/$suite/main/" "s3://$R2_BUCKET/dists/$suite/main/" \
			--endpoint-url "$ENDPOINT" "${AWS_FLAGS[@]}" --delete
	fi
done
