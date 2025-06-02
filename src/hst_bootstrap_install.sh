#!/bin/bash

# Clean installation bootstrap for development purposes only
# Usage:    ./dst_bootstrap_install.sh [fork] [branch] [os]
# Example:  ./dst_bootstrap_install.sh hestiacp main ubuntu

# Define variables
fork=$1
branch=$2
os=$3

# Download specified installer and compiler
wget https://raw.githubusercontent.com/$fork/hestiacp/$branch/install/dst-install-$os.sh
wget https://raw.githubusercontent.com/$fork/hestiacp/$branch/src/dst_autocompile.sh

# Execute compiler and build devcp core package
chmod +x dst_autocompile.sh
./dst_autocompile.sh --devcp $branch no

# Execute Hestia Control Panel installer with default dummy options for testing
bash dst-install-$os.sh -f -y no -e admin@test.local -p P@ssw0rd -s devcp-$branch-$os.test.local --with-debs /tmp/hestiacp-src/debs
