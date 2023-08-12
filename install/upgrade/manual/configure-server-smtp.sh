#!/bin/bash
# info: setup SMTP Account for server logging
# options: NONE
# labels:
#
# example: configure-server-smtp.sh
#
# This function provides an user-interactive configuration of a SMTP account
# for the server to use for logging, notification and warn emails etc.

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Includes
# shellcheck source=/etc/hestiacp/hestia.conf
source /etc/hestiacp/hestia.conf
# shellcheck source=/usr/local/hestia/func/main.sh
source $HESTIA/func/main.sh
# shellcheck source=/usr/local/hestia/conf/hestia.conf
source $HESTIA/conf/hestia.conf

function setupFiles {
	echo "Use SMTP account for server communication (Y/n): "
	read use_smtp_prompt

	use_smtp="${use_smtp_prompt:-y}"
	use_smtp="${use_smtp,,}"
	if [ "${use_smtp}" == "y" ]; then
		use_smtp=true

		echo "Enter SMTP Host:"
		read -i $SERVER_SMTP_HOST -e smtp_server_host
		echo "Enter SMTP Port:"
		read -i $SERVER_SMTP_PORT -e smtp_server_port
		echo "Enter SMTP Security:"
		read -i $SERVER_SMTP_SECURITY -e smtp_server_security
		echo "Enter SMTP Username:"
		read -i $SERVER_SMTP_USER -e smtp_server_user_name
		echo "Enter SMTP Password (stored as plaintext):"
		read -i $SERVER_SMTP_PASSWD -e smtp_server_password
		echo "Enter Email Address:"
		read -i $SERVER_SMTP_ADDR -e smtp_server_addr
	else
		use_smtp=false
	fi

	echo "Summary:
	Use SMTP: $use_smtp
	SMTP Host: $smtp_server_host
	SMTP Port: $smtp_server_port
	SMTP Security: $smtp_server_security
	SMTP Username: $smtp_server_user_name
	SMTP Password: $smtp_server_password
	Email Address: $smtp_server_addr
	Are these values correct? (y/N)"
	read correct_validation
	correct="${correct_validation:-n}"
	correct="${correct,,}"
	if [ "${correct}" != "y" ]; then
		echo "Not Proceeding. Restart or Quit (r/Q)?"
		read restart_quit_prompt
		restart_quit="${restart_quit_prompt:-q}"
		restart_quit="${restart_quit,,}"
		if [ "${restart_quit}" == "r" ]; then
			clear
			setupFiles
		else
			exit 3
		fi
	else
		$BIN/v-change-sys-config-value "USE_SERVER_SMTP" "${use_smtp:-}"
		$BIN/v-change-sys-config-value "SERVER_SMTP_HOST" "${smtp_server_host:-}"
		$BIN/v-change-sys-config-value "SERVER_SMTP_PORT" "${smtp_server_port:-}"
		$BIN/v-change-sys-config-value "SERVER_SMTP_SECURITY" "${smtp_server_security:-}"
		$BIN/v-change-sys-config-value "SERVER_SMTP_USER" "${smtp_server_user_name:-}"
		$BIN/v-change-sys-config-value "SERVER_SMTP_PASSWD" "${smtp_server_password:-}"
		$BIN/v-change-sys-config-value "SERVER_SMTP_ADDR" "${smtp_server_addr:-}"
	fi
}

setupFiles
