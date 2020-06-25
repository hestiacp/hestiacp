#!/bin/bash

# set -e
# Autocompile Script for HestiaCP package Files.
# For building from local source folder use "~localsrc" keyword as hesia branch name,
#   and the script will not try to download the arhive from github, since '~' char is 
#   not accepted in branch name.
# -> ./hst_autocompile.sh --hestia '~localsrc' 'n'

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

get_branch_file() {
  local filename=$1
  local destination=$2
  if [ "$use_src_folder" = 'true' ]; then
      if [ -z "$destination" ]; then
        #if [ "$HESTIA_DEBUG" = 'true' ]; then echo cp "$SRC_DIR/$filename" ./ ; fi
        cp -f "$SRC_DIR/$filename" ./
      else
        #if [ "$HESTIA_DEBUG" = 'true' ]; then echo cp "$SRC_DIR/$filename" "$destination" ; fi
        cp -f "$SRC_DIR/$filename" "$destination"
      fi
  else
      download_file "https://raw.githubusercontent.com/$REPO/$branch/$filename" "$destination" $3
  fi
}

usage() {
  echo "Usage:"
  echo "    $0 (--all|--hestia|--nginx|--php) [--install] [--debug] [branch] [Y]"
  echo ""
  echo "    --all           Build all hestia packages."
  echo "    --hestia        Build only the Control Panel package."
  echo "    --nginx         Build only the backend nginx engine package."
  echo "    --php           Build only the backend php engine package"
  echo "    --install       Install generated packages"
  echo "    --keepbuild     Don't delete downloaded source and build folders"
  echo "    --debug         Debug mode"
  echo ""
  echo "For automated builds and installations, you may specify the branch"
  echo "after one of the above flags. To install the packages, specify 'Y'"
  echo "following the branch name."
  echo ""
  echo "Example: bash hst_autocompile.sh --hestia develop Y"
  echo "This would install a Hestia Control Panel package compiled with the"
  echo "develop branch code."
}

# Set compiling directory
REPO='EquisTango/hestiacp'
BUILD_DIR='/tmp/hestiacp-src/'
INSTALL_DIR='/usr/local/hestia'
SRC_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ARCHIVE_DIR="$SRC_DIR/src/archive/"
RPM_DIR="$BUILD_DIR/rpms/"
DEB_DIR="$BUILD_DIR/deb/"
if [ -f '/etc/redhat-release' ]; then
    BUILD_RPM=true
    BUILD_DEB=true
    OSTYPE='rhel'
else
    BUILD_RPM=false
    BUILD_DEB=true
    OSTYPE='debian'
fi

# Set packages to compile
for i in $*; do 
    case "$i" in
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
        --debug)
          HESTIA_DEBUG='true'
          ;;
        --install|Y)
          install='true'
          ;;
        --noinstall|N)
          install='false'
          ;;
        --keepbuild)
          KEEPBUILD='true'
          ;;
        --help|-h)
          usage
          exit 1
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
  echo -n "Please enter the name of the branch to build from (e.g. master): "
  read branch
fi

if [ $(echo "$branch" | grep '^~localsrc')  ]; then
  branch=$(echo "$branch" | sed 's/^~//'); 
  use_src_folder='true'
else
  use_src_folder='false'
fi

if [ -z $install ]; then
  echo -n 'Would you like to install the compiled packages? [y/N] '
  read install
fi

# Set Version for compiling
if [ -f "$SRC_DIR/src/deb/hestia/control" ] && [ "$use_src_folder" = 'true' ]; then
  BUILD_VER=$(cat $SRC_DIR/src/deb/hestia/control |grep "Version:" |cut -d' ' -f2)
  NGINX_V=$(cat $SRC_DIR/src/deb/nginx/control |grep "Version:" |cut -d' ' -f2)
  PHP_V=$(cat $SRC_DIR/src/deb/php/control |grep "Version:" |cut -d' ' -f2)
else
  BUILD_VER=$(curl -s https://raw.githubusercontent.com/$REPO/$branch/src/deb/hestia/control |grep "Version:" |cut -d' ' -f2)
  NGINX_V=$(curl -s https://raw.githubusercontent.com/$REPO/$branch/src/deb/nginx/control |grep "Version:" |cut -d' ' -f2)
  PHP_V=$(curl -s https://raw.githubusercontent.com/$REPO/$branch/src/deb/php/control |grep "Version:" |cut -d' ' -f2)
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
if [ "$KEEPBUILD" != 'true' ]; then
  rm -rf $BUILD_DIR
fi
mkdir -p $BUILD_DIR
mkdir -p $DEB_DIR
mkdir -p $RPM_DIR
mkdir -p $BUILD_DIR/rpmbuild
mkdir -p $ARCHIVE_DIR

# Define a timestamp function
timestamp() {
    date +%s
}

# Install needed software
if [ "$OSTYPE" = 'rhel' ]; then
    # Set package dependencies for compiling
    SOFTWARE='gcc gcc-c++ make libxml2-devel zlib-devel libzip-devel gmp-devel libcurl-devel gnutls-devel unzip openssl openssl-devel pkg-config sqlite-devel oniguruma-devel dpkg rpm-build'

    echo "Updating system DNF repositories..."
    dnf upgrade -y > /dev/null 2>&1
    echo "Installing dependencies for compilation..."
    dnf install -y $SOFTWARE > /dev/null 2>&1
else
    # Set package dependencies for compiling
    SOFTWARE='build-essential libxml2-dev libz-dev libzip-dev libgmp-dev libcurl4-gnutls-dev unzip openssl libssl-dev pkg-config libsqlite3-dev libonig-dev rpm'

    echo "Updating system APT repositories..."
    apt-get -qq update > /dev/null 2>&1
    echo "Installing dependencies for compilation..."
    apt-get -qq install -y $SOFTWARE > /dev/null 2>&1

    # Fix for Debian PHP Envroiment
    if [ ! -e /usr/local/include/curl ]; then
        ln -s /usr/include/x86_64-linux-gnu/curl /usr/local/include/curl
    fi
fi

# Get system cpu cores
NUM_CPUS=$(grep "^cpu cores" /proc/cpuinfo | uniq |  awk '{print $4}')

if [ "$HESTIA_DEBUG" = 'true' ]; then
  if [ "$OSTYPE" = 'rhel' ]; then
    echo "OS type          : RHEL / CentOS / Fedora"
  else
    echo "OS type          : Debian / Ubuntu"
  fi
  echo "Branch           : $branch"
  echo "Install          : $install"
  echo "Build RPM        : $BUILD_RPM"
  echo "Build DEB        : $BUILD_DEB"
  echo "Hestia version   : $BUILD_VER"
  echo "Nginx version    : $NGINX_V"
  echo "PHP version      : $PHP_V"
  echo "Debug mode       : $HESTIA_DEBUG"
  echo "Source directory : $SRC_DIR"
fi

# Set git repository raw path
GIT_REP_DEB='https://raw.githubusercontent.com/'$REPO'/'$branch'/src/deb'
GIT_REP_RPM='https://raw.githubusercontent.com/'$REPO'/'$branch'/src/rpm'

# Generate Links for sourcecode
HESTIA_ARCHIVE_LINK='https://github.com/'$REPO'/archive/'$branch'.tar.gz'
NGINX='https://nginx.org/download/nginx-'$NGINX_V'.tar.gz'
OPENSSL='https://www.openssl.org/source/openssl-'$OPENSSL_V'.tar.gz'
PCRE='https://ftp.pcre.org/pub/pcre/pcre-'$PCRE_V'.tar.gz'
ZLIB='https://www.zlib.net/zlib-'$ZLIB_V'.tar.gz'
PHP='http://de2.php.net/distributions/php-'$PHP_V'.tar.gz'

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

    BUILD_DIR_HESTIANGINX=$BUILD_DIR/hestia-nginx_$NGINX_V

    if [ "$KEEPBUILD" != 'true' ] || [ ! -d "$BUILD_DIR_HESTIANGINX" ]; then
      # Check if target directory exist
      if [ -d "$BUILD_DIR_HESTIANGINX" ]; then
            #mv $BUILD_DIR/hestia-nginx_$NGINX_V $BUILD_DIR/hestia-nginx_$NGINX_V-$(timestamp)
            rm -r "$BUILD_DIR_HESTIANGINX"
      fi

      # Create directory
      mkdir -p $BUILD_DIR_HESTIANGINX

      # Download and unpack source files
      download_file $NGINX '-' | tar xz
      download_file $OPENSSL '-' | tar xz
      download_file $PCRE '-' | tar xz
      download_file $ZLIB '-' | tar xz

      # Change to nginx directory
      cd $BUILD_DIR/nginx-$NGINX_V

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
    fi

    # Change to nginx directory
    cd $BUILD_DIR/nginx-$NGINX_V

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
    if [ "$KEEPBUILD" != 'true' ]; then
      rm -r $BUILD_DIR/nginx-$NGINX_V $BUILD_DIR/openssl-$OPENSSL_V $BUILD_DIR/pcre-$PCRE_V $BUILD_DIR/zlib-$ZLIB_V
    fi
    cd $BUILD_DIR_HESTIANGINX

    # Prepare Package Folder Structure
    mkdir -p $BUILD_DIR_HESTIANGINX/usr/local/hestia

    if [ "$BUILD_DEB" = true ]; then
    # Download control, postinst and postrm files
      mkdir -p etc/init.d DEBIAN
      cd DEBIAN
      get_branch_file 'src/deb/nginx/control'
      get_branch_file 'src/deb/nginx/copyright'
      get_branch_file 'src/deb/nginx/postinst'
      get_branch_file 'src/deb/nginx/postrm'

      # Set permission
      chmod +x postinst postrm
      cd ..
    fi

    # Move nginx directory
    rm -rf $BUILD_DIR_HESTIANGINX/usr/local/hestia/nginx
    mv $BUILD_DIR/usr/local/hestia/nginx $BUILD_DIR_HESTIANGINX/usr/local/hestia/

    if [ "$BUILD_DEB" = true ]; then
      # Get Service File
      cd etc/init.d
      get_branch_file 'src/deb/nginx/hestia'
      chmod +x hestia
      cd ../../
    fi

    # Remove original nginx.conf (will use custom)
    rm -f $BUILD_DIR_HESTIANGINX/usr/local/hestia/nginx/conf/nginx.conf

    # copy binary
    cp $BUILD_DIR_HESTIANGINX/usr/local/hestia/nginx/sbin/nginx $BUILD_DIR_HESTIANGINX/usr/local/hestia/nginx/sbin/hestia-nginx

    # change permission and build the package
    cd $BUILD_DIR
    chown -R root:root $BUILD_DIR_HESTIANGINX

    if [ "$BUILD_DEB" = true ]; then
      get_branch_file 'src/deb/nginx/nginx.conf' "${BUILD_DIR_HESTIANGINX}/usr/local/hestia/nginx/conf/nginx.conf"
      dpkg-deb --build hestia-nginx_$NGINX_V
      mv *.deb $DEB_DIR
    fi
    if [ "$BUILD_RPM" = true ]; then
      mkdir -p $BUILD_DIR/rpmbuild
      get_branch_file 'src/rpm/nginx/hestia-nginx.spec' "${BUILD_DIR_HESTIANGINX}/hestia-nginx.spec"
      get_branch_file 'src/rpm/nginx/hestia-nginx.service' "${BUILD_DIR_HESTIANGINX}/hestia-nginx.service"
      get_branch_file 'src/rpm/nginx/nginx.conf' "${BUILD_DIR_HESTIANGINX}/usr/local/hestia/nginx/conf/nginx.conf"
      rpmbuild -bb --define "sourcedir $BUILD_DIR_HESTIANGINX" --buildroot=$BUILD_DIR/rpmbuild/ ${BUILD_DIR_HESTIANGINX}/hestia-nginx.spec
      mv ~/rpmbuild/RPMS/x86_64/hestia-nginx-*.rpm $RPM_DIR
    fi

    if [ "$KEEPBUILD" != 'true' ]; then
      # Clean up the source folder
      rm -r hestia-nginx_$NGINX_V
      rm -rf usr/
      if [ ! -z "$use_src_folder" ] && [ -d $$BUILD_DIR/hestiacp-$branch ]; then
        rm -r $BUILD_DIR/hestiacp-$branch
      fi
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
    cd php-$PHP_V

    # Configure PHP
    ./configure   --prefix=/usr/local/hestia/php \
                --enable-fpm \
                --with-fpm-user=admin \
                --with-fpm-group=admin \
                --with-libdir=lib/x86_64-linux-gnu \
                --with-mysqli \
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
    rm -r php-$PHP_V

    # Prepare Deb Package Folder Structure
    cd hestia-php_$PHP_V/
    mkdir -p usr/local/hestia DEBIAN

    # Download control, postinst and postrm files
    cd DEBIAN
    if [ -z "$use_src_folder" ]; then
      download_file $GIT_REP_DEB/php/control
      download_file $GIT_REP_DEB/php/copyright
    else
      cp $BUILD_DIR/hestiacp-$branch/src/deb/php/control ./
      cp $BUILD_DIR/hestiacp-$branch/src/deb/php/copyright ./
    fi

    # Move php directory
    cd ..
    mv ${BUILD_DIR}/usr/local/hestia/php usr/local/hestia/

    if [ -z "$use_src_folder" ]; then
      # Get php-fpm.conf
      download_file "$GIT_REP_DEB/php/php-fpm.conf" "usr/local/hestia/php/etc/php-fpm.conf"

      # Get php.ini
      download_file "$GIT_REP_DEB/php/php.ini" "usr/local/hestia/php/lib/php.ini"
    else
      cp $BUILD_DIR/hestiacp-$branch/src/deb/php/php-fpm.conf "usr/local/hestia/php/etc/php-fpm.conf"
      cp $BUILD_DIR/hestiacp-$branch/src/deb/php/php.ini "usr/local/hestia/php/lib/php.ini"
    fi



    # copy binary
    cp usr/local/hestia/php/sbin/php-fpm usr/local/hestia/php/sbin/hestia-php

    # change permission and build the package
    cd $BUILD_DIR
    chown -R  root:root hestia-php_$PHP_V
    if [ "$BUILD_DEB" = true ]; then
        dpkg-deb --build hestia-php_$PHP_V
        mv *.deb $DEB_DIR
    fi
    if [ "$BUILD_RPM" = true ]; then
        #TODO:
        mv *.rpm $RPM_DIR
    fi

    # clear up the source folder
    if [ ! "$HESTIA_DEBUG" = 'true' ]; then
      rm -r hestia-php_$PHP_V
      rm -rf usr/
      if [ ! -z "$use_src_folder" ] && [ -d $BUILD_DIR/hestiacp-$branch ]; then
        rm -r $BUILD_DIR/hestiacp-$branch
      fi
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
      download_file $GIT_REP_DEB/hestia/control
      download_file $GIT_REP_DEB/hestia/copyright
      download_file $GIT_REP_DEB/hestia/postinst
    else
      cp $BUILD_DIR/hestiacp-$branch/src/deb/hestia/control ./
      cp $BUILD_DIR/hestiacp-$branch/src/deb/hestia/copyright ./
      cp $BUILD_DIR/hestiacp-$branch/src/deb/hestia/postinst ./
    fi
    

    # Set permission
    chmod +x postinst

    # Move needed directories
    cd $BUILD_DIR/hestiacp-$branch
    mv bin func install web ../hestia_$HESTIA_V/usr/local/hestia/

    # Set permission
    cd ../hestia_$HESTIA_V/usr/local/hestia/bin
    chmod +x *

    # change permission and build the package
    cd $BUILD_DIR
    chown -R root:root hestia_$HESTIA_V
    if [ "$BUILD_DEB" = true ]; then
        dpkg-deb --build hestia_$HESTIA_V
        mv *.deb $DEB_DIR
    fi
    if [ "$BUILD_RPM" = true ]; then
        #TODO:
        mv *.rpm $RPM_DIR
    fi

    # clear up the source folder
    if [ ! "$HESTIA_DEBUG" = 'true' ]; then
      rm -r hestia_$HESTIA_V
      rm -rf hestiacp-$branch
    fi
fi


#################################################################################
#
# Install Packages
#
#################################################################################

if [ "$install" = 'yes' ] || [ "$install" = 'y' ] || [ "$install" = 'true' ]; then
    # Install all available packages
    echo "Installing packages..."
    if [ "$OSTYPE" = 'rhel' ]; then
        for i in $RPM_DIR/*.rpm; do
          dnf -y install $i
        done
    else
        for i in $DEB_DIR/*.deb; do
          dpkg -i $i
        done
    fi
    unset $answer
fi

