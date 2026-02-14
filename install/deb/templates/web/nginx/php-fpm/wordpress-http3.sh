#!/bin/bash
user="$1"
domain="$2"
ip="$3"
home="$4"

# Build the path to the domain's configuration directory.
domain_conf_path="${home}/${user}/conf/web/${domain}"

# Path to the Nginx SSL configuration file for this domain.
nginx_conf="${domain_conf_path}/nginx.ssl.conf"

# Check if any file under /etc/nginx/conf.d/domains/ contains a line
# with this IP followed by “quic reuseport;”. If not, proceed.
if ! grep -qR "${ip}.*quic reuseport" /etc/nginx/conf.d/domains/; then
	# Modify the domain's nginx config: replace "quic" with "quic reuseport"
	# to enable the reuseport option for QUIC.
	sed -i.bak 's/quic/quic reuseport/' "$nginx_conf"
	# Test the nginx configuration to ensure it is valid.
	if nginx_check="$(nginx -t)"; then
		rm -f "${nginx_conf}.bak"
		systemctl reload nginx
	else
		echo "Error: nginx conf is not valid" >&2
		echo "$nginx_check" >&2
		echo "Restoring nginc.ssl.conf backup file"
		mv "${nginx_conf}.bak" "${nginx_conf}"
	fi
fi
