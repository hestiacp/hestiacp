#!/bin/bash

# set -e
# Autocompile Script for HestiaCP deb Files.
# For building from local source folder use "~localsrc" keyword as hesia branch name,
#   and the script will not try to download the arhive from github, since '~' char is 
#   not accepted in branch name.
# -> ./hst_autocompile.sh --hestia '~localsrc' 'n'

# Clear previous screen output
clear

# Define download function
download_file() {
  local url=$1
  local destination=$2
  local force=$3

  # Default destination is the curent working directory
  local dstopt=""

  if [ ! -z "$(echo "$url" | grep -E "\.(gz|gzip|bz2|zip|xz)$")" ]; then
    # When an archive file is downloaded it will be first saved localy
    dstopt="--directory-prefix=$ARCHIVE_DIR"
    local is_archive="true"
    local filename="${url##*/}"
    if [ -z "$filename" ]; then
      >&2 echo "[!] No filename was found in url, exiting ($url)"
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
      >&2 echo "[!] Archive $ARCHIVE_DIR/$filename is corrupted, redownloading"
      rm -f $ARCHIVE_DIR/$filename
    fi
  fi

  if [ ! -f "$ARCHIVE_DIR/$filename" ]; then
    wget $url -q $dstopt --show-progress --progress=bar:force --limit-rate=3m
  fi

  if [ ! -z "$destination" ] && [ "$is_archive" = "true" ]; then
    if [ "$destination" = "-" ]; then
      cat "$ARCHIVE_DIR/$filename"
    elif [ -d "$(dirname $destination)" ]; then
      cp "$ARCHIVE_DIR/$filename" "$destination"
    fi
  fi
}

# Set compiling directory
BUILD_DIR='/tmp/hestiacp-src'
DEB_DIR="$BUILD_DIR/debs"
INSTALL_DIR='/usr/local/hestia'
SRC_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ARCHIVE_DIR="$SRC_DIR/src/archive"

# Set command variables
if [ ! -z "$2" ]; then
  branch=$2
else
  echo -n "Please enter the name of the branch to build from (e.g. main): "
  read branch
fi

# Set Version for compiling
if [ -f "$SRC_DIR/src/deb/hestia/control" ] && [ "$branch" = '~localsrc' ]; then
  BUILD_VER=$(cat $SRC_DIR/src/deb/hestia/control |grep "Version:" |cut -d' ' -f2)
  NGINX_V=$(cat $SRC_DIR/src/deb/nginx/control |grep "Version:" |cut -d' ' -f2)
  PHP_V=$(cat $SRC_DIR/src/deb/php/control |grep "Version:" |cut -d' ' -f2)
else
  BUILD_VER=$(curl -s https://raw.githubusercontent.com/hestiacp/hestiacp/$branch/src/deb/hestia/control |grep "Version:" |cut -d' ' -f2)
  NGINX_V=$(curl -s https://raw.githubusercontent.com/hestiacp/hestiacp/$branch/src/deb/nginx/control |grep "Version:" |cut -d' ' -f2)
  PHP_V=$(curl -s https://raw.githubusercontent.com/hestiacp/hestiacp/$branch/src/deb/php/control |grep "Version:" |cut -d' ' -f2)
fi

if [ -z "$BUILD_VER" ]; then
  echo "Error: Branch invalid, could not detect version"
  exit 1
fi

BUILD_ARCH='amd64'
HESTIA_V="${BUILD_VER}_${BUILD_ARCH}"
OPENSSL_V='1.1.1g'
PCRE_V='8.44'
ZLIB_V='1.2.11'

# Create build directories
rm -rf $BUILD_DIR
mkdir -p $DEB_DIR
mkdir -p $ARCHIVE_DIR

# Set package dependencies for compiling
SOFTWARE='build-essential libxml2-dev libz-dev libzip-dev libgmp-dev libcurl4-gnutls-dev unzip openssl libssl-dev pkg-config libsqlite3-dev libonig-dev'

# Define a timestamp function
timestamp() {
    date +%s
}

if [ ! -z "$3" ]; then
  install=$3
else
  echo -n 'Would you like to install the compiled packages? [y/N] '
  read install
fi

# Install needed software
echo "Updating system APT repositories..."
apt-get -qq update > /dev/null 2>&1
echo "Installing dependencies for compilation..."
apt-get -qq install -y $SOFTWARE > /dev/null 2>&1

# Fix for Debian PHP Envroiment
if [ ! -e /usr/local/include/curl ]; then
    ln -s /usr/include/x86_64-linux-gnu/curl /usr/local/include/curl
fi

# Get system cpu cores
NUM_CPUS=$(grep "^cpu cores" /proc/cpuinfo | uniq |  awk '{print $4}')

# Set packages to compile
for arg; do
    case "$1" in
        --all)
          NGINX_B='true'
          PHP_B='true'
          HESTIA_B='true'
          ;;
        --nginx)
          NGINX_B='true'
          ;;
        --php)
          PHP_B='true'
          ;;
        --hestia)
          HESTIA_B='true'
          ;;
        *)
          NOARGUMENT='true'
          ;;
    esac
done

if [ $(echo "$branch" | grep '^~localsrc')  ]; then
  branch=$(echo "$branch" | sed 's/^~//'); 
  use_src_folder='true'
fi

if [[ $# -eq 0 ]] ; then
  echo "ERROR: Invalid compilation flag specified. Valid flags include:"
  echo "--all:      Build all hestia packages."
  echo "--hestia:   Build only the Control Panel package."
  echo "--nginx:    Build only the backend nginx engine package."
  echo "--php:      Build only the backend php engine package"
  echo ""
  echo "For automated builds and installatioms, you may specify the branch"
  echo "after one of the above flags. To install the packages, specify 'Y'"
  echo "following the branch name."
  echo ""
  echo "Example: bash hst_autocompile.sh --hestia develop Y"
  echo "This would install a Hestia Control Panel package compiled with the"
  echo "develop branch code."
  exit 1
fi

# Set git repository raw path
GIT_REP='https://raw.githubusercontent.com/hestiacp/hestiacp/'$branch'/src/deb'

# Generate Links for sourcecode
HESTIA_ARCHIVE_LINK='https://github.com/hestiacp/hestiacp/archive/'$branch'.tar.gz'
NGINX='https://nginx.org/download/nginx-'$(echo $NGINX_V |cut -d"~" -f1)'.tar.gz'
OPENSSL='https://www.openssl.org/source/openssl-'$OPENSSL_V'.tar.gz'
PCRE='https://ftp.pcre.org/pub/pcre/pcre-'$PCRE_V'.tar.gz'
ZLIB='https://www.zlib.net/zlib-'$ZLIB_V'.tar.gz'
PHP='http://de2.php.net/distributions/php-'$(echo $PHP_V |cut -d"~" -f1)'.tar.gz'

# Forward slashes in branchname are replaced with dashes to match foldername in github archive.
branch=$(echo "$branch" |sed 's/\//-/g');

#################################################################################
#
# Building hestia-nginx
#
#################################################################################

if [ "$NGINX_B" = true ] ; then

    echo "Building hestia-nginx package..."
    # Change to build directory
    cd $BUILD_DIR

    # Check if target directory exist
    if [ -d $BUILD_DIR/hestia-nginx_$NGINX_V ]; then
          #mv $BUILD_DIR/hestia-nginx_$NGINX_V $BUILD_DIR/hestia-nginx_$NGINX_V-$(timestamp)
          rm -r $BUILD_DIR/hestia-nginx_$NGINX_V
    fi

    # Create directory
    mkdir $BUILD_DIR/hestia-nginx_$NGINX_V

    # Download and unpack source files
    download_file $NGINX '-' | tar xz
    download_file $OPENSSL '-' | tar xz
    download_file $PCRE '-' | tar xz
    download_file $ZLIB '-' | tar xz

    # Change to nginx directory
    cd nginx-$(echo $NGINX_V |cut -d"~" -f1)

    # configure nginx
    ./configure   --prefix=/usr/local/hestia/nginx \
                  --with-http_ssl_module \
                  --with-openssl=../openssl-$OPENSSL_V \
                  --with-openssl-opt=enable-ec_nistp_64_gcc_128 \
                  --with-openssl-opt=no-nextprotoneg \
                  --with-openssl-opt=no-weak-ssl-ciphers \
                  --with-openssl-opt=no-ssl3 \
                  --with-pcre=../pcre-$PCRE_V \
                  --with-pcre-jit \
                  --with-zlib=../zlib-$ZLIB_V

    # Check install directory and remove if exists
    if [ -d "$BUILD_DIR$INSTALL_DIR" ]; then
          rm -r "$BUILD_DIR$INSTALL_DIR"
    fi

        # Copy local hestia source files
    if [ ! -z "$use_src_folder" ] && [ -d $SRC_DIR ]; then
      cp -rf "$SRC_DIR/" $BUILD_DIR/hestiacp-$branch
    fi

    # Create the files and install them
    make -j $NUM_CPUS && make DESTDIR=$BUILD_DIR install

    # Cleare up unused files
    cd $BUILD_DIR
    rm -r nginx-$(echo $NGINX_V |cut -d"~" -f1) openssl-$OPENSSL_V pcre-$PCRE_V zlib-$ZLIB_V

    # Prepare Deb Package Folder Structure
    cd hestia-nginx_$NGINX_V/
    mkdir -p usr/local/hestia etc/init.d DEBIAN

    # Download control, postinst and postrm files
    cd DEBIAN
    if [ -z "$use_src_folder" ]; then
      download_file $GIT_REP/nginx/control
      download_file $GIT_REP/nginx/copyright
      download_file $GIT_REP/nginx/postinst
      download_file $GIT_REP/nginx/postrm
    else
      cp $BUILD_DIR/hestiacp-$branch/src/deb/nginx/control ./
      cp $BUILD_DIR/hestiacp-$branch/src/deb/nginx/copyright ./
      cp $BUILD_DIR/hestiacp-$branch/src/deb/nginx/postinst ./
      cp $BUILD_DIR/hestiacp-$branch/src/deb/nginx/postrm ./
    fi

    # Set permission
    chmod +x postinst postrm

    # Move nginx directory
    cd ..
    mv $BUILD_DIR/usr/local/hestia/nginx usr/local/hestia/

    # Get Service File
    cd etc/init.d
    if [ -z "$use_src_folder" ]; then
      download_file $GIT_REP/nginx/hestia
    else
      cp $BUILD_DIR/hestiacp-$branch/src/deb/nginx/hestia ./
    fi
    chmod +x hestia

    # Get nginx.conf
    cd ../../
    rm usr/local/hestia/nginx/conf/nginx.conf

    if [ -z "$use_src_folder" ]; then
      download_file $GIT_REP/nginx/nginx.conf "usr/local/hestia/nginx/conf/nginx.conf"
    else
      cp $BUILD_DIR/hestiacp-$branch/src/deb/nginx/nginx.conf "usr/local/hestia/nginx/conf/nginx.conf"
    fi
        
    # copy binary
    cp usr/local/hestia/nginx/sbin/nginx usr/local/hestia/nginx/sbin/hestia-nginx

    # change permission and build the package
    cd $BUILD_DIR
    chown -R  root:root hestia-nginx_$NGINX_V
    dpkg-deb --build hestia-nginx_$NGINX_V
    mv *.deb $DEB_DIR

    # clear up the source folder
    rm -r hestia-nginx_$NGINX_V
    rm -rf usr/
    if [ ! -z "$use_src_folder" ] && [ -d $$BUILD_DIR/hestiacp-$branch ]; then
      rm -r $BUILD_DIR/hestiacp-$branch
    fi
fi


#################################################################################
#
# Building hestia-php
#
#################################################################################

if [ "$PHP_B" = true ] ; then
    echo "Building hestia-php package..."
    # Change to build directory
    cd $BUILD_DIR

    # Check if target directory exist
    if [ -d $BUILD_DIR/hestia-php_$PHP_V ]; then
          #mv $BUILD_DIR/hestia-php_$PHP_V $BUILD_DIR/hestia-php_$PHP_V-$(timestamp)
          rm -r $BUILD_DIR/hestia-php_$PHP_V
    fi

    # Create directory
    mkdir ${BUILD_DIR}/hestia-php_$PHP_V

    # Download and unpack source files
    download_file $PHP '-' | tar xz

    # Change to php directory
    cd php-$(echo $PHP_V |cut -d"~" -f1)

    # Configure PHP
    ./configure   --prefix=/usr/local/hestia/php \
                --enable-fpm \
                --with-fpm-user=admin \
                --with-fpm-group=admin \
                --with-libdir=lib/x86_64-linux-gnu \
                --with-mysqli \
                --with-gettext \
                --with-curl \
                --with-zip \
                --with-gmp \
                --enable-mbstring

    # Create the files and install them
    make -j $NUM_CPUS && make INSTALL_ROOT=$BUILD_DIR install

    # Copy local hestia source files
    if [ ! -z "$use_src_folder" ] && [ -d $SRC_DIR ]; then
      cp -rf "$SRC_DIR/" $BUILD_DIR/hestiacp-$branch
    fi

    # Cleare up unused files
    cd $BUILD_DIR
    rm -r php-$(echo $PHP_V |cut -d"~" -f1)

    # Prepare Deb Package Folder Structure
    cd hestia-php_$PHP_V/
    mkdir -p usr/local/hestia DEBIAN

    # Download control, postinst and postrm files
    cd DEBIAN
    if [ -z "$use_src_folder" ]; then
      download_file $GIT_REP/php/control
      download_file $GIT_REP/php/copyright
      download_file $GIT_REP/php/postinst
    else
      cp $BUILD_DIR/hestiacp-$branch/src/deb/php/control ./
      cp $BUILD_DIR/hestiacp-$branch/src/deb/php/copyright ./
      cp $BUILD_DIR/hestiacp-$branch/src/deb/php/postinst ./
    fi

    # Set permission
    chmod +x postinst

    # Move php directory
    cd ..
    mv ${BUILD_DIR}/usr/local/hestia/php usr/local/hestia/

    if [ -z "$use_src_folder" ]; then
      # Get php-fpm.conf
      download_file "$GIT_REP/php/php-fpm.conf" "usr/local/hestia/php/etc/php-fpm.conf"

      # Get php.ini
      download_file "$GIT_REP/php/php.ini" "usr/local/hestia/php/lib/php.ini"
    else
      cp $BUILD_DIR/hestiacp-$branch/src/deb/php/php-fpm.conf "usr/local/hestia/php/etc/php-fpm.conf"
      cp $BUILD_DIR/hestiacp-$branch/src/deb/php/php.ini "usr/local/hestia/php/lib/php.ini"
    fi



    # copy binary
    cp usr/local/hestia/php/sbin/php-fpm usr/local/hestia/php/sbin/hestia-php

    # change permission and build the package
    cd $BUILD_DIR
    chown -R  root:root hestia-php_$PHP_V
    dpkg-deb --build hestia-php_$PHP_V
    mv *.deb $DEB_DIR

    # clear up the source folder
    rm -r hestia-php_$PHP_V
    rm -rf usr/
    if [ ! -z "$use_src_folder" ] && [ -d $BUILD_DIR/hestiacp-$branch ]; then
      rm -r $BUILD_DIR/hestiacp-$branch
    fi
fi


#################################################################################
#
# Building hestia
#
#################################################################################

if [ "$HESTIA_B" = true ]; then
    echo "Building Hestia Control Panel package..."
    # Change to build directory
    cd $BUILD_DIR

    # Check if target directory exist
    if [ -d $BUILD_DIR/hestia_$HESTIA_V ]; then
          #mv $BUILD_DIR/hestia_$HESTIA_V $BUILD_DIR/hestia_$HESTIA_V-$(timestamp)
          rm -r $BUILD_DIR/hestia_$HESTIA_V
    fi

    # Create directory
    mkdir $BUILD_DIR/hestia_$HESTIA_V

    # Download and unpack source files
    if [ -z "$use_src_folder" ]; then
      download_file $HESTIA_ARCHIVE_LINK '-' 'fresh' | tar xz
    elif [ -d $SRC_DIR ]; then
      cp -rf "$SRC_DIR/" $BUILD_DIR/hestiacp-$branch
    fi

    # Prepare Deb Package Folder Structure
    cd hestia_$HESTIA_V/
    mkdir -p usr/local/hestia DEBIAN

    # Download control, postinst and postrm files
    cd DEBIAN
    if [ -z "$use_src_folder" ]; then
      download_file $GIT_REP/hestia/control
      download_file $GIT_REP/hestia/copyright
      download_file $GIT_REP/hestia/postinst
    else
      cp $BUILD_DIR/hestiacp-$branch/src/deb/hestia/control ./
      cp $BUILD_DIR/hestiacp-$branch/src/deb/hestia/copyright ./
      cp $BUILD_DIR/hestiacp-$branch/src/deb/hestia/postinst ./
    fi
    

    # Set permission
    chmod 755 postinst

    # Move needed directories
    cd $BUILD_DIR/hestiacp-$branch
    mv bin func install web ../hestia_$HESTIA_V/usr/local/hestia/

    # Set permission
    cd ../hestia_$HESTIA_V/usr/local/hestia/bin
    chmod +x *

    # change permission and build the package
    cd $BUILD_DIR
    chown -R root:root hestia_$HESTIA_V
    dpkg-deb --build hestia_$HESTIA_V
    mv *.deb $DEB_DIR

    # clear up the source folder
    rm -r hestia_$HESTIA_V
    rm -rf hestiacp-$branch
fi


#################################################################################
#
# Install Packages
#
#################################################################################

if [ "$install" = 'yes' ] || [ "$install" = 'y' ]; then
    echo "Installing packages..."
    for i in $DEB_DIR/*.deb; do
      # Install all available packages
      dpkg -i $i
    done
    unset $answer
fi
