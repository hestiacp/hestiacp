#!/bin/bash

# Includes
# shellcheck source=/usr/local/hestia/conf/hestia.conf
source $HESTIA/conf/hestia.conf

# Variables and arguments
HESTIA="/usr/local/hestia"
script=$1
log=$2
scroll=$3

if [ "$DEBUG_MODE" = "no" ] || [ -z "$DEBUG_MODE" ]; then
	echo "ERROR: Developer mode is disabled."
	echo "Enable with v-change-sys-config-value DEBUG_MODE yes"
	exit 1
fi

if [ -z "$script" ]; then
	echo "ERROR: No script specified."
	echo "Usage:    ./lint_script ~/path/to/bin/v-script-name LOG_OUTPUT"
	exit 1
fi

# Install shellcheck
package_check=$(dpkg -l | grep shellcheck)
if [ -z "$package_check" ]; then
	echo "[ * ] Updating APT package cache..."
	apt-get -qq update > /dev/null 2>&1
	echo "[ * ] Installing shellcheck code linter..."
	apt-get -qq install -y shellcheck > /dev/null 2>&1
fi

# Set debug path and ensure it exists
DEBUG_PATH="$HOME/hst-debug/"
if [ ! -d "$DEBUG_PATH" ]; then
	mkdir "$DEBUG_PATH"
fi

# Generate timestamp
time_n_date=$(date +'%F %T')
time_n_date=$(echo $time_n_date | sed "s|:||g" | sed "s| |_|g")

# If logging specified, export shellcheck output to log
if [ "$log" = "yes" ]; then
	shellcheck -x "$script" > "$DEBUG_PATH/${script}_$date-$time.log"
else
	# Prompt user to scroll output from shellcheck
	if [ "$scroll" == "no" ] || [ -z "$scroll" ]; then
		clear
		shellcheck -x "$script"
	else
		clear
		shellcheck -x "$script" | less
	fi
fi
