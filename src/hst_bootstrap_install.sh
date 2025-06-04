#!/bin/bash

# Clean installation bootstrap for development purposes only
# Usage:    ./hst_bootstrap_install.sh [fork] [branch] [os]
# Example:  ./hst_bootstrap_install.sh DevITcp main ubuntu

# Define variables
fork=$1
branch=$2
os=$3

# Download specified installer and compiler
wget https://raw.githubusercontent.com/$fork/DevITcp/$branch/install/hst-install-$os.sh
wget https://raw.githubusercontent.com/$fork/DevITcp/$branch/src/hst_autocompile.sh

# Execute compiler and build DevIT core package
chmod +x hst_autocompile.sh
./hst_autocompile.sh --DevIT $branch no

# Execute DevIT Control Panel installer with default dummy options for testing
bash hst-install-$os.sh -f -y no -e admin@test.local -p P@ssw0rd -s DevIT-$branch-$os.test.local --with-debs /tmp/DevITcp-src/debs
