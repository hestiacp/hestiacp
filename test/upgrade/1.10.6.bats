#!/usr/bin/env bats
#
# One-off test for the 1.10.6 Exim HELO ACL migration.
# Delete this file (and its entry in .github/workflows/pr-docker-bats.yml)
# once upgrading directly from pre-1.10.6 releases is no longer supported.

load '../test_helper/bats-support/load'
load '../test_helper/bats-assert/load'

setup() {
    HESTIA="${HESTIA:-/usr/local/hestia}"
}

@test "Upgrade: Exim HELO ACL migration" {
    local workdir
    workdir=$(mktemp -d)
    local conf="${workdir}/exim4.conf.template"
    local bindir="${workdir}/bin"

    mkdir -p "${bindir}"
    for cmd in update-exim4.conf exim4 systemctl; do
        printf '#!/bin/sh\nexit 0\n' > "${bindir}/${cmd}"
        chmod +x "${bindir}/${cmd}"
    done

    # Recreate the pre-1.10.6 acl_check_mail block in a copy of the template
    cp "${HESTIA}/install/deb/exim/exim4.conf.template" "${conf}"
    cat > "${workdir}/legacy_acl" <<'EOF'
  deny  condition     = ${if eq{$sender_helo_name}{}}
        message       = HELO required before MAIL

  drop  !authenticated = *
		message       =  Helo name contains an IP address (HELO was $sender_helo_name) and not is valid
		condition     = ${if match{$sender_helo_name}{\N((\d{1,3}[.-]\d{1,3}[.-]\d{1,3}[.-]\d{1,3})|([0-9a-f]{8})|([0-9A-F]{8}))\N}{yes}{no}}
		condition     = ${if match {${lookup dnsdb{>: defer_never,ptr=$sender_host_address}}\}{$sender_helo_name}{no}{yes}}
		delay         = 45s

  drop  !authenticated = *
		condition     = ${if isip{$sender_helo_name}}
		message       = Access denied - Invalid HELO name (See RFC2821 4.1.3)

  drop  !authenticated = *
		condition     = ${if eq{[$interface_address]}{$sender_helo_name}}
		message       = $interface_address is _my_ address

  accept
EOF
    awk -v legacy="${workdir}/legacy_acl" '
        /^acl_check_mail:/ { print; while ((getline line < legacy) > 0) print line; skip = 1; next }
        skip && /^acl_check_rcpt:/ { skip = 0 }
        !skip { print }
    ' "${conf}" > "${workdir}/legacy_template"
    mv "${workdir}/legacy_template" "${conf}"

    # Run the migration function from the upgrade script against the copy
    sed -n '/^fix_exim_helo_checks() {/,/^}$/p' \
        "${HESTIA}/install/upgrade/versions/1.10.6.sh" > "${workdir}/func.sh"

    run env PATH="${bindir}:${PATH}" bash -c "source '${workdir}/func.sh'; fix_exim_helo_checks '${conf}'"
    assert_success
    run grep -q "NO_FCRDNS" "${conf}"
    assert_success
    run grep -q "Helo name contains an IP address" "${conf}"
    assert_failure

    # A second run must not change the file
    local sha_before
    sha_before=$(sha256sum "${conf}" | cut -d" " -f1)
    run env PATH="${bindir}:${PATH}" bash -c "source '${workdir}/func.sh'; fix_exim_helo_checks '${conf}'"
    assert_success
    assert_equal "$(sha256sum "${conf}" | cut -d" " -f1)" "${sha_before}"

    rm -rf "${workdir}"
}
