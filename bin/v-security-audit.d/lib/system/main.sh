#!/bin/bash

SYSTEM_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "${SYSTEM_DIR}/os.sh"
source "${SYSTEM_DIR}/ssh.sh"
source "${SYSTEM_DIR}/firewall.sh"
source "${SYSTEM_DIR}/hestia.sh"
source "${SYSTEM_DIR}/services.sh"
source "${SYSTEM_DIR}/database.sh"
source "${SYSTEM_DIR}/mail.sh"
source "${SYSTEM_DIR}/kernel.sh"
source "${SYSTEM_DIR}/users.sh"
source "${SYSTEM_DIR}/hardening.sh"

run_system_checks() {
	check_os
	check_kernel
	check_reboot
	check_auto_updates
	check_critical_updates
	check_ssh_hardening
	check_firewall
	check_hestia_panel
	check_services
	check_database
	check_mail
	check_kernel_hardening
	check_filesystem_security
	check_suid_capabilities
	check_user_accounts
	check_dns_mail_advanced
	check_php_hardening
	check_nginx_hardening
	check_hestia_update
	check_hestia_firewall_restrictions
}
