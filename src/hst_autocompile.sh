#!/bin/bash

# set -e
# Autocompile Script for DevITCP package Files.
# For building from local source folder use "~localsrc" keyword as hesia branch name,
#   and the script will not try to download the arhive from github, since '~' char is
#   not accepted in branch name.
# Compile but dont install -> ./hst_autocompile.sh --DevIT --noinstall --keepbuild '~localsrc'
# Compile and install -> ./hst_autocompile.sh --DevIT --install '~localsrc'

# Clear previous screen output
clear

# Define download function
download_file() {
	local url=$1
	local destination=$2
	local force=$3

	[ "$DevIT_DEBUG" ] && echo >&2 DEBUG: Downloading file "$url" to "$destination"

	# Default destination is the current working directory
	local dstopt=""

	if [ ! -z "$(echo "$url" | grep -E "\.(gz|gzip|bz2|zip|xz)$")" ]; then
		# When an archive file is downloaded it will be first saved localy
		dstopt="--directory-prefix=$ARCHIVE_DIR"
		local is_archive="true"
		local filename="${url##*/}"
		if [ -z "$filename" ]; then
			echo >&2 "[!] No filename was found in url, exiting ($url)"
			exit 1
		fi
		if [ ! -z "$force" ] && [ -f "$ARCHIVE_DIR/$filename" ]; then
			rm -f $ARCHIVE_DIR/$filename
		fi
	elif [ ! -z "$destination" ]; then
		# Plain files will be written to specified location
		dstopt="-O $destination"
	fi
	# check for corrupted archive
	if [ -f "$ARCHIVE_DIR/$filename" ] && [ "$is_archive" = "true" ]; then
		tar -tzf "$ARCHIVE_DIR/$filename" > /dev/null 2>&1
		if [ $? -ne 0 ]; then
			echo >&2 "[!] Archive $ARCHIVE_DIR/$filename is corrupted, redownloading"
			rm -f $ARCHIVE_DIR/$filename
		fi
	fi

	if [ ! -f "$ARCHIVE_DIR/$filename" ]; then
		[ "$DevIT_DEBUG" ] && echo >&2 DEBUG: wget $url -q $dstopt --show-progress --progress=bar:force --limit-rate=3m
		wget $url -q $dstopt --show-progress --progress=bar:force --limit-rate=3m
		if [ $? -ne 0 ]; then
			echo >&2 "[!] Archive $ARCHIVE_DIR/$filename is corrupted and exit script"
			rm -f $ARCHIVE_DIR/$filename
			exit 1
		fi
	fi

	if [ ! -z "$destination" ] && [ "$is_archive" = "true" ]; then
		if [ "$destination" = "-" ]; then
			cat "$ARCHIVE_DIR/$filename"
		elif [ -d "$(dirname $destination)" ]; then
			cp "$ARCHIVE_DIR/$filename" "$destination"
		fi
	fi
}

get_branch_file() {
	local filename=$1
	local destination=$2
	[ "$DevIT_DEBUG" ] && echo >&2 DEBUG: Get branch file "$filename" to "$destination"
	if [ "$use_src_folder" == 'true' ]; then
		if [ -z "$destination" ]; then
			[ "$DevIT_DEBUG" ] && echo >&2 DEBUG: cp -f "$SRC_DIR/$filename" ./
			cp -f "$SRC_DIR/$filename" ./
		else
			[ "$DevIT_DEBUG" ] && echo >&2 DEBUG: cp -f "$SRC_DIR/$filename" "$destination"
			cp -f "$SRC_DIR/$filename" "$destination"
		fi
	else
		download_file "https://raw.githubusercontent.com/$REPO/$branch/$filename" "$destination" $3
	fi
}

usage() {
	echo "Usage:"
	echo "    $0 (--all|--DevIT|--nginx|--php|--web-terminal) [options] [branch] [Y]"
	echo ""
	echo "    --all           Build all DevIT packages."
	echo "    --DevIT        Build only the Control Panel package."
	echo "    --nginx         Build only the backend nginx engine package."
	echo "    --php           Build only the backend php engine package"
	echo "    --web-terminal  Build only the backend web terminal websocket package"
	echo "  Options:"
	echo "    --install       Install generated packages"
	echo "    --keepbuild     Don't delete downloaded source and build folders"
	echo "    --cross         Compile DevIT package for both AMD64 and ARM64"
	echo "    --debug         Debug mode"
	echo ""
	echo "For automated builds and installations, you may specify the branch"
	echo "after one of the above flags. To install the packages, specify 'Y'"
	echo "following the branch name."
	echo ""
	echo "Example: bash hst_autocompile.sh --DevIT develop Y"
	echo "This would install a DevIT Control Panel package compiled with the"
	echo "develop branch code."
}

# Set compiling directory
REPO='DevITcp/DevITcp'
BUILD_DIR='/tmp/DevITcp-src'
INSTALL_DIR='/usr/local/DevIT'
SRC_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ARCHIVE_DIR="$SRC_DIR/src/archive/"
architecture="$(arch)"
if [ $architecture == 'aarch64' ]; then
	BUILD_ARCH='arm64'
else
	BUILD_ARCH='amd64'
fi
DEB_DIR="$BUILD_DIR/deb"

# Set packages to compile
for i in $*; do
	case "$i" in
		--all)
			NGINX_B='true'
			PHP_B='true'
			WEB_TERMINAL_B='true'
			DevIT_B='true'
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
		--DevIT)
			DevIT_B='true'
			;;
		--debug)
			DevIT_DEBUG='true'
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
		--help | -h)
			usage
			exit 1
			;;
		--dontinstalldeps)
			dontinstalldeps='true'
			;;
		*)
			branch="$i"
			;;
	esac
done

if [[ $# -eq 0 ]]; then
	usage
	exit 1
fi

# Clear previous screen output
clear

# Set command variables
if [ -z $branch ]; then
	echo -n "Please enter the name of the branch to build from (e.g. main): "
	read branch
fi

if [ $(echo "$branch" | grep '^~localsrc') ]; then
	branch=$(echo "$branch" | sed 's/^~//')
	use_src_folder='true'
else
	use_src_folder='false'
fi

if [ -z $install ]; then
	echo -n 'Would you like to install the compiled packages? [y/N] '
	read install
fi

# Set Version for compiling
if [ -f "$SRC_DIR/src/deb/DevIT/control" ] && [ "$use_src_folder" == 'true' ]; then
	BUILD_VER=$(cat $SRC_DIR/src/deb/DevIT/control | grep "Version:" | cut -d' ' -f2)
	NGINX_V=$(cat $SRC_DIR/src/deb/nginx/control | grep "Version:" | cut -d' ' -f2)
	PHP_V=$(cat $SRC_DIR/src/deb/php/control | grep "Version:" | cut -d' ' -f2)
	WEB_TERMINAL_V=$(cat $SRC_DIR/src/deb/web-terminal/control | grep "Version:" | cut -d' ' -f2)
else
	BUILD_VER=$(curl -s https://raw.githubusercontent.com/$REPO/$branch/src/deb/DevIT/control | grep "Version:" | cut -d' ' -f2)
	NGINX_V=$(curl -s https://raw.githubusercontent.com/$REPO/$branch/src/deb/nginx/control | grep "Version:" | cut -d' ' -f2)
	PHP_V=$(curl -s https://raw.githubusercontent.com/$REPO/$branch/src/deb/php/control | grep "Version:" | cut -d' ' -f2)
	WEB_TERMINAL_V=$(curl -s https://raw.githubusercontent.com/$REPO/$branch/src/deb/web-terminal/control | grep "Version:" | cut -d' ' -f2)
fi

if [ -z "$BUILD_VER" ]; then
	echo "Error: Branch invalid, could not detect version"
	exit 1
fi

echo "Build version $BUILD_VER, with Nginx version $NGINX_V, PHP version $PHP_V and Web Terminal version $WEB_TERMINAL_V"

DevIT_V="${BUILD_VER}_${BUILD_ARCH}"
OPENSSL_V='3.4.0'
PCRE_V='10.44'
ZLIB_V='1.3.1'

# Create build directories
if [ "$KEEPBUILD" != 'true' ]; then
	rm -rf $BUILD_DIR
fi
mkdir -p $BUILD_DIR
mkdir -p $DEB_DIR
mkdir -p $ARCHIVE_DIR

# Define a timestamp function
timestamp() {
	date +%s
}

if [ "$dontinstalldeps" != 'true' ]; then
	# Install needed software
	# Set package dependencies for compiling
	SOFTWARE='wget tar git curl build-essential libxml2-dev libz-dev libzip-dev libgmp-dev libcurl4-gnutls-dev unzip openssl libssl-dev pkg-config libsqlite3-dev libonig-dev rpm lsb-release'

	echo "Updating system APT repositories..."
	apt-get -qq update > /dev/null 2>&1
	echo "Installing dependencies for compilation..."
	apt-get -qq install -y $SOFTWARE > /dev/null 2>&1

	# Installing Node.js 20.x repo
	apt="/etc/apt/sources.list.d"
	codename="$(lsb_release -s -c)"

	if [ -z $(which "node") ]; then
		curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
	fi

	echo "Installing Node.js..."
	apt-get -qq update > /dev/null 2>&1
	apt -qq install -y nodejs > /dev/null 2>&1

	nodejs_version=$(/usr/bin/node -v | cut -f1 -d'.' | sed 's/v//g')

	if [ "$nodejs_version" -lt 18 ]; then
		echo "Requires Node.js 18.x or higher"
		exit 1
	fi

	# Fix for Debian PHP environment
	if [ $BUILD_ARCH == "amd64" ]; then
		if [ ! -L /usr/local/include/curl ]; then
			ln -s /usr/include/x86_64-linux-gnu/curl /usr/local/include/curl
		fi
	fi
fi

# Get system cpu cores
NUM_CPUS=$(grep "^cpu cores" /proc/cpuinfo | uniq | awk '{print $4}')

if [ "$DevIT_DEBUG" ]; then
	echo "OS type          : Debian / Ubuntu"
	echo "Branch           : $branch"
	echo "Install          : $install"
	echo "DevIT version   : $BUILD_VER"
	echo "Nginx version    : $NGINX_V"
	echo "PHP version      : $PHP_V"
	echo "Web Term version : $WEB_TERMINAL_V"
	echo "Architecture     : $BUILD_ARCH"
	echo "Debug mode       : $DevIT_DEBUG"
	echo "Source directory : $SRC_DIR"
fi

# Generate Links for sourcecode
DevIT_ARCHIVE_LINK='https://github.com/DevITcp/DevITcp/archive/'$branch'.tar.gz'
if [[ $NGINX_V =~ - ]]; then
	NGINX='https://nginx.org/download/nginx-'$(echo $NGINX_V | cut -d"-" -f1)'.tar.gz'
else
	NGINX='https://nginx.org/download/nginx-'$(echo $NGINX_V | cut -d"~" -f1)'.tar.gz'
fi

OPENSSL='https://www.openssl.org/source/openssl-'$OPENSSL_V'.tar.gz'
PCRE='https://github.com/PCRE2Project/pcre2/releases/download/pcre2-'$PCRE_V'/pcre2-'$PCRE_V'.tar.gz'
ZLIB='https://github.com/madler/zlib/archive/refs/tags/v'$ZLIB_V'.tar.gz'

if [[ $PHP_V =~ - ]]; then
	PHP='http://de2.php.net/distributions/php-'$(echo $PHP_V | cut -d"-" -f1)'.tar.gz'
else
	PHP='http://de2.php.net/distributions/php-'$(echo $PHP_V | cut -d"~" -f1)'.tar.gz'
fi

# Forward slashes in branchname are replaced with dashes to match foldername in github archive.
branch_dash=$(echo "$branch" | sed 's/\//-/g')

#################################################################################
#
# Building DevIT-nginx
#
#################################################################################

if [ "$NGINX_B" = true ]; then
	echo "Building DevIT-nginx package..."
	if [ "$CROSS" = "true" ]; then
		echo "Cross compile not supported for DevIT-nginx, DevIT-php or DevIT-web-terminal"
		exit 1
	fi

	# Change to build directory
	cd $BUILD_DIR

	BUILD_DIR_DevITNGINX=$BUILD_DIR/DevIT-nginx_$NGINX_V
	if [[ $NGINX_V =~ - ]]; then
		BUILD_DIR_NGINX=$BUILD_DIR/nginx-$(echo $NGINX_V | cut -d"-" -f1)
	else
		BUILD_DIR_NGINX=$BUILD_DIR/nginx-$(echo $NGINX_V | cut -d"~" -f1)
	fi

	if [ "$KEEPBUILD" != 'true' ] || [ ! -d "$BUILD_DIR_DevITNGINX" ]; then
		# Check if target directory exist
		if [ -d "$BUILD_DIR_DevITNGINX" ]; then
			#mv $BUILD_DIR/DevIT-nginx_$NGINX_V $BUILD_DIR/DevIT-nginx_$NGINX_V-$(timestamp)
			rm -r "$BUILD_DIR_DevITNGINX"
		fi

		# Create directory
		mkdir -p $BUILD_DIR_DevITNGINX

		# Download and unpack source files
		download_file $NGINX '-' | tar xz
		download_file $OPENSSL '-' | tar xz
		download_file $PCRE '-' | tar xz
		download_file $ZLIB '-' | tar xz

		# Change to nginx directory
		cd $BUILD_DIR_NGINX

		# configure nginx
		./configure --prefix=/usr/local/DevIT/nginx \
			--with-http_v2_module \
			--with-http_ssl_module \
			--with-openssl=../openssl-$OPENSSL_V \
			--with-openssl-opt=enable-ec_nistp_64_gcc_128 \
			--with-openssl-opt=no-nextprotoneg \
			--with-openssl-opt=no-weak-ssl-ciphers \
			--with-openssl-opt=no-ssl3 \
			--with-pcre=../pcre2-$PCRE_V \
			--with-pcre-jit \
			--with-zlib=../zlib-$ZLIB_V
	fi

	# Change to nginx directory
	cd $BUILD_DIR_NGINX

	# Check install directory and remove if exists
	if [ -d "$BUILD_DIR$INSTALL_DIR" ]; then
		rm -r "$BUILD_DIR$INSTALL_DIR"
	fi

	# Copy local DevIT source files
	if [ "$use_src_folder" == 'true' ] && [ -d $SRC_DIR ]; then
		cp -rf "$SRC_DIR/" $BUILD_DIR/DevITcp-$branch_dash
	fi

	# Create the files and install them
	make -j $NUM_CPUS && make DESTDIR=$BUILD_DIR install

	# Clear up unused files
	if [ "$KEEPBUILD" != 'true' ]; then
		rm -r $BUILD_DIR_NGINX $BUILD_DIR/openssl-$OPENSSL_V $BUILD_DIR/pcre2-$PCRE_V $BUILD_DIR/zlib-$ZLIB_V
	fi
	cd $BUILD_DIR_DevITNGINX

	# Move nginx directory
	mkdir -p $BUILD_DIR_DevITNGINX/usr/local/DevIT
	rm -rf $BUILD_DIR_DevITNGINX/usr/local/DevIT/nginx
	mv $BUILD_DIR/usr/local/DevIT/nginx $BUILD_DIR_DevITNGINX/usr/local/DevIT/

	# Remove original nginx.conf (will use custom)
	rm -f $BUILD_DIR_DevITNGINX/usr/local/DevIT/nginx/conf/nginx.conf

	# copy binary
	mv $BUILD_DIR_DevITNGINX/usr/local/DevIT/nginx/sbin/nginx $BUILD_DIR_DevITNGINX/usr/local/DevIT/nginx/sbin/DevIT-nginx

	# change permission and build the package
	cd $BUILD_DIR
	chown -R root:root $BUILD_DIR_DevITNGINX
	# Get Debian package files
	mkdir -p $BUILD_DIR_DevITNGINX/DEBIAN
	get_branch_file 'src/deb/nginx/control' "$BUILD_DIR_DevITNGINX/DEBIAN/control"
	if [ "$BUILD_ARCH" != "amd64" ]; then
		sed -i "s/amd64/${BUILD_ARCH}/g" "$BUILD_DIR_DevITNGINX/DEBIAN/control"
	fi
	get_branch_file 'src/deb/nginx/copyright' "$BUILD_DIR_DevITNGINX/DEBIAN/copyright"
	get_branch_file 'src/deb/nginx/postinst' "$BUILD_DIR_DevITNGINX/DEBIAN/postinst"
	get_branch_file 'src/deb/nginx/postrm' "$BUILD_DIR_DevITNGINX/DEBIAN/portrm"
	chmod +x "$BUILD_DIR_DevITNGINX/DEBIAN/postinst"
	chmod +x "$BUILD_DIR_DevITNGINX/DEBIAN/portrm"

	# Init file
	mkdir -p $BUILD_DIR_DevITNGINX/etc/init.d
	get_branch_file 'src/deb/nginx/DevIT' "$BUILD_DIR_DevITNGINX/etc/init.d/DevIT"
	chmod +x "$BUILD_DIR_DevITNGINX/etc/init.d/DevIT"

	# Custom config
	get_branch_file 'src/deb/nginx/nginx.conf' "${BUILD_DIR_DevITNGINX}/usr/local/DevIT/nginx/conf/nginx.conf"

	# Build the package
	echo Building Nginx DEB
	dpkg-deb -Zxz --build $BUILD_DIR_DevITNGINX $DEB_DIR

	rm -r $BUILD_DIR/usr

	if [ "$KEEPBUILD" != 'true' ]; then
		# Clean up the source folder
		rm -r DevIT- nginx_$NGINX_V
		rm -rf $BUILD_DIR/rpmbuild
		if [ "$use_src_folder" == 'true' ] && [ -d $BUILD_DIR/DevITcp-$branch_dash ]; then
			rm -r $BUILD_DIR/DevITcp-$branch_dash
		fi
	fi
fi

#################################################################################
#
# Building DevIT-php
#
#################################################################################

if [ "$PHP_B" = true ]; then
	if [ "$CROSS" = "true" ]; then
		echo "Cross compile not supported for DevIT-nginx, DevIT-php or DevIT-web-terminal"
		exit 1
	fi

	echo "Building DevIT-php package..."

	BUILD_DIR_DevITPHP=$BUILD_DIR/DevIT-php_$PHP_V

	BUILD_DIR_PHP=$BUILD_DIR/php-$(echo $PHP_V | cut -d"~" -f1)

	if [[ $PHP_V =~ - ]]; then
		BUILD_DIR_PHP=$BUILD_DIR/php-$(echo $PHP_V | cut -d"-" -f1)
	else
		BUILD_DIR_PHP=$BUILD_DIR/php-$(echo $PHP_V | cut -d"~" -f1)
	fi

	if [ "$KEEPBUILD" != 'true' ] || [ ! -d "$BUILD_DIR_DevITPHP" ]; then
		# Check if target directory exist
		if [ -d $BUILD_DIR_DevITPHP ]; then
			rm -r $BUILD_DIR_DevITPHP
		fi

		# Create directory
		mkdir -p $BUILD_DIR_DevITPHP

		# Download and unpack source files
		cd $BUILD_DIR
		download_file $PHP '-' | tar xz

		# Change to untarred php directory
		cd $BUILD_DIR_PHP

		# Configure PHP
		./configure --prefix=/usr/local/DevIT/php \
			--with-libdir=lib/$(arch)-linux-gnu \
			--enable-fpm --with-fpm-user=admin --with-fpm-group=admin \
			--with-openssl \
			--with-mysqli \
			--with-gettext \
			--with-curl \
			--with-zip \
			--with-gmp \
			--enable-mbstring
	fi

	cd $BUILD_DIR_PHP

	# Create the files and install them
	make -j $NUM_CPUS && make INSTALL_ROOT=$BUILD_DIR install

	# Copy local DevIT source files
	if [ "$use_src_folder" == 'true' ] && [ -d $SRC_DIR ]; then
		[ "$DevIT_DEBUG" ] && echo DEBUG: cp -rf "$SRC_DIR/" $BUILD_DIR/DevITcp-$branch_dash
		cp -rf "$SRC_DIR/" $BUILD_DIR/DevITcp-$branch_dash
	fi
	# Move php directory
	[ "$DevIT_DEBUG" ] && echo DEBUG: mkdir -p $BUILD_DIR_DevITPHP/usr/local/DevIT
	mkdir -p $BUILD_DIR_DevITPHP/usr/local/DevIT

	[ "$DevIT_DEBUG" ] && echo DEBUG: rm -r $BUILD_DIR_DevITPHP/usr/local/DevIT/php
	if [ -d $BUILD_DIR_DevITPHP/usr/local/DevIT/php ]; then
		rm -r $BUILD_DIR_DevITPHP/usr/local/DevIT/php
	fi

	[ "$DevIT_DEBUG" ] && echo DEBUG: mv ${BUILD_DIR}/usr/local/DevIT/php ${BUILD_DIR_DevITPHP}/usr/local/DevIT/
	mv ${BUILD_DIR}/usr/local/DevIT/php ${BUILD_DIR_DevITPHP}/usr/local/DevIT/

	# copy binary
	[ "$DevIT_DEBUG" ] && echo DEBUG: cp $BUILD_DIR_DevITPHP/usr/local/DevIT/php/sbin/php-fpm $BUILD_DIR_DevITPHP/usr/local/DevIT/php/sbin/DevIT-php
	cp $BUILD_DIR_DevITPHP/usr/local/DevIT/php/sbin/php-fpm $BUILD_DIR_DevITPHP/usr/local/DevIT/php/sbin/DevIT-php

	# Change permissions and build the package
	chown -R root:root $BUILD_DIR_DevITPHP
	# Get Debian package files
	[ "$DevIT_DEBUG" ] && echo DEBUG: mkdir -p $BUILD_DIR_DevITPHP/DEBIAN
	mkdir -p $BUILD_DIR_DevITPHP/DEBIAN
	get_branch_file 'src/deb/php/control' "$BUILD_DIR_DevITPHP/DEBIAN/control"
	if [ "$BUILD_ARCH" != "amd64" ]; then
		sed -i "s/amd64/${BUILD_ARCH}/g" "$BUILD_DIR_DevITPHP/DEBIAN/control"
	fi

	os=$(lsb_release -is)
	release=$(lsb_release -rs)
	if [[ "$os" = "Ubuntu" ]] && [[ "$release" = "20.04" ]]; then
		sed -i "/Conflicts: libzip5/d" "$BUILD_DIR_DevITPHP/DEBIAN/control"
		sed -i "s/libzip4/libzip5/g" "$BUILD_DIR_DevITPHP/DEBIAN/control"
	fi
	if [[ "$os" = "Ubuntu" ]] && [[ "$release" = "24.04" ]]; then
		sed -i "/Conflicts: libzip5/d" "$BUILD_DIR_DevITPHP/DEBIAN/control"
		sed -i "s/libzip4/libzip4t64/g" "$BUILD_DIR_DevITPHP/DEBIAN/control"
	fi

	get_branch_file 'src/deb/php/copyright' "$BUILD_DIR_DevITPHP/DEBIAN/copyright"
	get_branch_file 'src/deb/php/postinst' "$BUILD_DIR_DevITPHP/DEBIAN/postinst"
	chmod +x $BUILD_DIR_DevITPHP/DEBIAN/postinst
	# Get custom config
	get_branch_file 'src/deb/php/php-fpm.conf' "${BUILD_DIR_DevITPHP}/usr/local/DevIT/php/etc/php-fpm.conf"
	get_branch_file 'src/deb/php/php.ini' "${BUILD_DIR_DevITPHP}/usr/local/DevIT/php/lib/php.ini"

	# Build the package
	echo Building PHP DEB
	[ "$DevIT_DEBUG" ] && echo DEBUG: dpkg-deb -Zxz --build $BUILD_DIR_DevITPHP $DEB_DIR
	dpkg-deb -Zxz --build $BUILD_DIR_DevITPHP $DEB_DIR

	rm -r $BUILD_DIR/usr

	# clear up the source folder
	if [ "$KEEPBUILD" != 'true' ]; then
		rm -r $BUILD_DIR/php-$(echo $PHP_V | cut -d"~" -f1)
		rm -r $BUILD_DIR_DevITPHP
		if [ "$use_src_folder" == 'true' ] && [ -d $BUILD_DIR/DevITcp-$branch_dash ]; then
			rm -r $BUILD_DIR/DevITcp-$branch_dash
		fi
	fi
fi

#################################################################################
#
# Building DevIT-web-terminal
#
#################################################################################

if [ "$WEB_TERMINAL_B" = true ]; then
	if [ "$CROSS" = "true" ]; then
		echo "Cross compile not supported for DevIT-nginx, DevIT-php or DevIT-web-terminal"
		exit 1
	fi

	echo "Building DevIT-web-terminal package..."

	BUILD_DIR_DevIT_TERMINAL=$BUILD_DIR/DevIT-web-terminal_$WEB_TERMINAL_V

	# Check if target directory exist
	if [ -d $BUILD_DIR_DevIT_TERMINAL ]; then
		rm -r $BUILD_DIR_DevIT_TERMINAL
	fi

	# Create directory
	mkdir -p $BUILD_DIR_DevIT_TERMINAL
	chown -R root:root $BUILD_DIR_DevIT_TERMINAL

	# Get Debian package files
	[ "$DevIT_DEBUG" ] && echo DEBUG: mkdir -p $BUILD_DIR_DevIT_TERMINAL/DEBIAN
	mkdir -p $BUILD_DIR_DevIT_TERMINAL/DEBIAN
	get_branch_file 'src/deb/web-terminal/control' "$BUILD_DIR_DevIT_TERMINAL/DEBIAN/control"
	if [ "$BUILD_ARCH" != "amd64" ]; then
		sed -i "s/amd64/${BUILD_ARCH}/g" "$BUILD_DIR_DevIT_TERMINAL/DEBIAN/control"
	fi

	get_branch_file 'src/deb/web-terminal/copyright' "$BUILD_DIR_DevIT_TERMINAL/DEBIAN/copyright"
	get_branch_file 'src/deb/web-terminal/postinst' "$BUILD_DIR_DevIT_TERMINAL/DEBIAN/postinst"
	chmod +x $BUILD_DIR_DevIT_TERMINAL/DEBIAN/postinst

	# Get server files
	[ "$DevIT_DEBUG" ] && echo DEBUG: mkdir -p "${BUILD_DIR_DevIT_TERMINAL}/usr/local/DevIT/web-terminal"
	mkdir -p "${BUILD_DIR_DevIT_TERMINAL}/usr/local/DevIT/web-terminal"
	get_branch_file 'src/deb/web-terminal/package.json' "${BUILD_DIR_DevIT_TERMINAL}/usr/local/DevIT/web-terminal/package.json"
	get_branch_file 'src/deb/web-terminal/package-lock.json' "${BUILD_DIR_DevIT_TERMINAL}/usr/local/DevIT/web-terminal/package-lock.json"
	get_branch_file 'src/deb/web-terminal/server.js' "${BUILD_DIR_DevIT_TERMINAL}/usr/local/DevIT/web-terminal/server.js"
	chmod +x "${BUILD_DIR_DevIT_TERMINAL}/usr/local/DevIT/web-terminal/server.js"

	cd $BUILD_DIR_DevIT_TERMINAL/usr/local/DevIT/web-terminal
	npm ci --omit=dev

	# Systemd service
	[ "$DevIT_DEBUG" ] && echo DEBUG: mkdir -p $BUILD_DIR_DevIT_TERMINAL/etc/systemd/system
	mkdir -p $BUILD_DIR_DevIT_TERMINAL/etc/systemd/system
	get_branch_file 'src/deb/web-terminal/DevIT-web-terminal.service' "$BUILD_DIR_DevIT_TERMINAL/etc/systemd/system/DevIT-web-terminal.service"

	# Build the package
	echo Building Web Terminal DEB
	[ "$DevIT_DEBUG" ] && echo DEBUG: dpkg-deb -Zxz --build $BUILD_DIR_DevIT_TERMINAL $DEB_DIR
	dpkg-deb -Zxz --build $BUILD_DIR_DevIT_TERMINAL $DEB_DIR

	# clear up the source folder
	if [ "$KEEPBUILD" != 'true' ]; then
		rm -r $BUILD_DIR_DevIT_TERMINAL
		if [ "$use_src_folder" == 'true' ] && [ -d $BUILD_DIR/DevITcp-$branch_dash ]; then
			rm -r $BUILD_DIR/DevITcp-$branch_dash
		fi
	fi
fi

#################################################################################
#
# Building DevIT
#
#################################################################################

arch="$BUILD_ARCH"

if [ "$DevIT_B" = true ]; then
	if [ "$CROSS" = "true" ]; then
		arch="amd64 arm64"
	fi
	for BUILD_ARCH in $arch; do
		echo "Building DevIT Control Panel package..."

		BUILD_DIR_DevIT=$BUILD_DIR/DevIT_$DevIT_V

		# Change to build directory
		cd $BUILD_DIR

		if [ "$KEEPBUILD" != 'true' ] || [ ! -d "$BUILD_DIR_DevIT" ]; then
			# Check if target directory exist
			if [ -d $BUILD_DIR_DevIT ]; then
				rm -r $BUILD_DIR_DevIT
			fi

			# Create directory
			mkdir -p $BUILD_DIR_DevIT
		fi

		cd $BUILD_DIR
		rm -rf $BUILD_DIR/DevITcp-$branch_dash
		# Download and unpack source files
		if [ "$use_src_folder" == 'true' ]; then
			[ "$DevIT_DEBUG" ] && echo DEBUG: cp -rf "$SRC_DIR/" $BUILD_DIR/DevITcp-$branch_dash
			cp -rf "$SRC_DIR/" $BUILD_DIR/DevITcp-$branch_dash
		elif [ -d $SRC_DIR ]; then
			download_file $DevIT_ARCHIVE_LINK '-' 'fresh' | tar xz
		fi

		mkdir -p $BUILD_DIR_DevIT/usr/local/DevIT

		# Build web and move needed directories
		cd $BUILD_DIR/DevITcp-$branch_dash
		npm ci --ignore-scripts
		npm run build
		cp -rf bin func install web $BUILD_DIR_DevIT/usr/local/DevIT/

		# Set permissions
		find $BUILD_DIR_DevIT/usr/local/DevIT/ -type f -exec chmod -x {} \;

		# Allow send email via /usr/local/DevIT/web/inc/mail-wrapper.php via cli
		chmod +x $BUILD_DIR_DevIT/usr/local/DevIT/web/inc/mail-wrapper.php
		# Allow the executable to be executed
		chmod +x $BUILD_DIR_DevIT/usr/local/DevIT/bin/*
		find $BUILD_DIR_DevIT/usr/local/DevIT/install/ \( -name '*.sh' \) -exec chmod +x {} \;
		chmod -x $BUILD_DIR_DevIT/usr/local/DevIT/install/*.sh
		chown -R root:root $BUILD_DIR_DevIT
		# Get Debian package files
		mkdir -p $BUILD_DIR_DevIT/DEBIAN
		get_branch_file 'src/deb/DevIT/control' "$BUILD_DIR_DevIT/DEBIAN/control"
		if [ "$BUILD_ARCH" != "amd64" ]; then
			sed -i "s/amd64/${BUILD_ARCH}/g" "$BUILD_DIR_DevIT/DEBIAN/control"
		fi
		get_branch_file 'src/deb/DevIT/copyright' "$BUILD_DIR_DevIT/DEBIAN/copyright"
		get_branch_file 'src/deb/DevIT/preinst' "$BUILD_DIR_DevIT/DEBIAN/preinst"
		get_branch_file 'src/deb/DevIT/postinst' "$BUILD_DIR_DevIT/DEBIAN/postinst"
		chmod +x $BUILD_DIR_DevIT/DEBIAN/postinst
		chmod +x $BUILD_DIR_DevIT/DEBIAN/preinst

		echo Building DevIT DEB
		dpkg-deb -Zxz --build $BUILD_DIR_DevIT $DEB_DIR

		# clear up the source folder
		if [ "$KEEPBUILD" != 'true' ]; then
			rm -r $BUILD_DIR_DevIT
			rm -rf DevITcp-$branch_dash
		fi
		cd $BUILD_DIR/DevITcp-$branch_dash
	done
fi

#################################################################################
#
# Install Packages
#
#################################################################################

if [ "$install" = 'yes' ] || [ "$install" = 'y' ] || [ "$install" = 'true' ]; then
	# Install all available packages
	echo "Installing packages..."
	for i in $DEB_DIR/*.deb; do
		dpkg -i $i
		if [ $? -ne 0 ]; then
			exit 1
		fi
	done
	unset $answer
fi
