# Autocompile Script for HestiaCP deb Files.

# Set compiling directory
BUILD_DIR='/root'
INSTALL_DIR='/usr/local/hestia'

# Set git repository raw path
GIT_REP='https://raw.githubusercontent.com/hestiacp/hestiacp/master/src/deb'

# Set Version for compiling
HESTIA_V='0.9.8-28_amd64'
NGINX_V='1.15.9'
OPENSSL_V='1.1.1a'
PCRE_V='8.42'
ZLIB_V='1.2.11'
PHP_V='7.3.3'
SOGO_V='4.0.7'

# Generate Links for sourcecode
HESTIA='https://github.com/hestiacp/hestiacp/archive/master.zip'
NGINX='https://nginx.org/download/nginx-'$NGINX_V'.tar.gz'
OPENSSL='https://www.openssl.org/source/openssl-'$OPENSSL_V'.tar.gz'
PCRE='https://ftp.pcre.org/pub/pcre/pcre-'$PCRE_V'.tar.gz'
ZLIB='https://www.zlib.net/zlib-'$ZLIB_V'.tar.gz'
PHP='http://de2.php.net/distributions/php-'$PHP_V'.tar.gz'
SOGO='https://github.com/inverse-inc/sogo.git'
SOPE='https://github.com/inverse-inc/sope.git'

# Set package dependencies for compiling
SOFTWARE='build-essential libxml2-dev libz-dev libcurl4-gnutls-dev unzip openssl
          libssl-dev pkg-config git zip wget make debhelper gnustep-make
          libgnustep-base-dev libldap2-dev zlib1g-dev libpq-dev
          libmariadbclient-dev-compat libmemcached-dev liblasso3-dev
          libcurl4-gnutls-dev devscripts libexpat1-dev libpopt-dev libsbjson-dev
          libsbjson2.3 libcurl3'

# Define a timestamp function
timestamp() {
    date +%s
}

# Install needed software
echo "Update system repository..."
apt-get -qq update
echo "Installing dependencies for compilation..."
apt-get -qq install -y $SOFTWARE

# Fix for Debian PHP Envroiment
if [ ! -e /usr/local/include/curl ]; then
    ln -s /usr/include/x86_64-linux-gnu/curl /usr/local/include/curl
fi

# Set packages to compile
for arg; do
    case "$1" in
        --all)
          NGINX_B='true'
          PHP_B='true'
          HESTIA_B='true'
          SOGO_B='true'
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
        --sogo)
          SOGO_B='true'
          ;;
        *)
          NOARGUMENT='true'
          ;;
    esac
done

# Check if we should use beta branch
if [ "$2" == '--beta' ]; then
    GIT_REP='https://raw.githubusercontent.com/hestiacp/hestiacp/beta/src/deb'
fi

if [[ $# -eq 0 ]] ; then
    echo "!!! Please run with argument --all, --hestia, --nginx, --php or --sogo !!!"
    exit 1
fi


#################################################################################
#
# Building hestia-nginx
#
#################################################################################

if [ "$NGINX_B" = true ] ; then
    # Change to build directory
    cd $BUILD_DIR

    # Check if target directory exist
    if [ -d $BUILD_DIR/hestia-nginx_$HESTIA_V ]; then
          #mv $BUILD_DIR/hestia-nginx_$HESTIA_V $BUILD_DIR/hestia-nginx_$HESTIA_V-$(timestamp)
          rm -r $BUILD_DIR/hestia-nginx_$HESTIA_V
    fi

    # Create directory
    mkdir $BUILD_DIR/hestia-nginx_$HESTIA_V

    # Download and unpack source files
    wget -qO- $NGINX | tar xz
    wget -qO- $OPENSSL | tar xz
    wget -qO- $PCRE | tar xz
    wget -qO- $ZLIB | tar xz

    # Change to nginx directory
    cd nginx-$NGINX_V

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

    # Check install directory and move if exists
    if [ -d $INSTALL_DIR ]; then
          #mv $INSTALL_DIR $INSTALL_DIR$(timestamp)
          rm -r $INSTALL_DIR
    fi

    # Create the files and install them
    make && make install

    # Cleare up unused files
    cd $BUILD_DIR
    rm -r nginx-$NGINX_V openssl-$OPENSSL_V pcre-$PCRE_V zlib-$ZLIB_V

    # Prepare Deb Package Folder Structure
    cd hestia-nginx_$HESTIA_V/
    mkdir -p usr/local/hestia etc/init.d DEBIAN

    # Download control, postinst and postrm files
    cd DEBIAN
    wget $GIT_REP/nginx/control
    wget $GIT_REP/nginx/copyright
    wget $GIT_REP/nginx/postinst
    wget $GIT_REP/nginx/postrm

    # Set permission
    chmod +x postinst postrm

    # Move nginx directory
    cd ..
    mv /usr/local/hestia/nginx usr/local/hestia/

    # Get Service File
    cd etc/init.d
    wget $GIT_REP/nginx/hestia
    chmod +x hestia

    # Get nginx.conf
    cd ../../
    rm usr/local/hestia/nginx/conf/nginx.conf
    wget $GIT_REP/nginx/nginx.conf -O usr/local/hestia/nginx/conf/nginx.conf

    # copy binary
    cp usr/local/hestia/nginx/sbin/nginx usr/local/hestia/nginx/sbin/hestia-nginx

    # change permission and build the package
    cd $BUILD_DIR
    chown -R  root:root hestia-nginx_$HESTIA_V
    dpkg-deb --build hestia-nginx_$HESTIA_V

    # clear up the source folder
    rm -r hestia-nginx_$HESTIA_V
fi


#################################################################################
#
# Building hestia-php
#
#################################################################################

if [ "$PHP_B" = true ] ; then
    # Change to build directory
    cd $BUILD_DIR

    # Check if target directory exist
    if [ -d $BUILD_DIR/hestia-php_$HESTIA_V ]; then
          #mv $BUILD_DIR/hestia-php_$HESTIA_V $BUILD_DIR/hestia-php_$HESTIA_V-$(timestamp)
          rm -r $BUILD_DIR/hestia-php_$HESTIA_V
    fi

    # Create directory
    mkdir $BUILD_DIR/hestia-php_$HESTIA_V

    # Download and unpack source files
    wget -qO- $PHP | tar xz

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
                --enable-mbstring

    # Create the files and install them
    make && make install

    # Cleare up unused files
    cd $BUILD_DIR
    rm -r php-$PHP_V

    # Prepare Deb Package Folder Structure
    cd hestia-php_$HESTIA_V/
    mkdir -p usr/local/hestia DEBIAN

    # Download control, postinst and postrm files
    cd DEBIAN
    wget $GIT_REP/php/control
    wget $GIT_REP/php/copyright

    # Move php directory
    cd ..
    mv /usr/local/hestia/php usr/local/hestia/

    # Get php-fpm.conf
    wget $GIT_REP/php/php-fpm.conf -O usr/local/hestia/php/etc/php-fpm.conf

    # Get php.ini
    wget $GIT_REP/php/php.ini -O usr/local/hestia/php/lib/php.ini

    # copy binary
    cp usr/local/hestia/php/sbin/php-fpm usr/local/hestia/php/sbin/hestia-php

    # change permission and build the package
    cd $BUILD_DIR
    chown -R  root:root hestia-php_$HESTIA_V
    dpkg-deb --build hestia-php_$HESTIA_V

    # clear up the source folder
    rm -r hestia-php_$HESTIA_V
fi


#################################################################################
#
# Building hestia
#
#################################################################################

if [ "$HESTIA_B" = true ] ; then
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
    wget $HESTIA
    unzip -q master.zip
    rm master.zip

    # Prepare Deb Package Folder Structure
    cd hestia_$HESTIA_V/
    mkdir -p usr/local/hestia DEBIAN

    # Download control, postinst and postrm files
    cd DEBIAN
    wget $GIT_REP/hestia/control
    wget $GIT_REP/hestia/copyright
    wget $GIT_REP/hestia/postinst

    # Set permission
    chmod +x postinst

    # Move needed directories
    cd ../../hestiacp-master
    mv bin func install upd web ../hestia_$HESTIA_V/usr/local/hestia/

    # Set permission
    cd ../hestia_$HESTIA_V/usr/local/hestia/bin
    chmod +x *

    # change permission and build the package
    cd $BUILD_DIR
    chown -R root:root hestia_$HESTIA_V
    dpkg-deb --build hestia_$HESTIA_V

    # clear up the source folder
    rm -r hestia_$HESTIA_V
    rm -r hestiacp-master
fi


#################################################################################
#
# Building sogo
# Source: https://github.com/lbausch/sogo4-debian-packaging
#
#################################################################################

if [ "$SOGO_B" = true ] ; then

    # Define git tags
    SOGO_GIT_TAG="SOGo-${SOGO_V}"
    SOPE_GIT_TAG="SOPE-${SOGO_V}"

    # Set sogo build dir
    PACKAGES_DIR="${BUILD_DIR}/sogo"

    # Create directory and cd
    mkdir -p "$PACKAGES_DIR"
    cd "$PACKAGES_DIR"

    # Download and install libwbxml2 and libwbxml2-dev
    wget -qc https://packages.inverse.ca/SOGo/nightly/4/debian/pool/stretch/w/wbxml2/libwbxml2-dev_0.11.6-1_amd64.deb
    wget -qc https://packages.inverse.ca/SOGo/nightly/4/debian/pool/stretch/w/wbxml2/libwbxml2-0_0.11.6-1_amd64.deb

    dpkg -i libwbxml2-0_0.11.6-1_amd64.deb libwbxml2-dev_0.11.6-1_amd64.deb
    rm libwbxml2-0_0.11.6-1_amd64.deb libwbxml2-dev_0.11.6-1_amd64.deb

    # Install any missing packages
    apt-get -f install -y

    # Checkout the SOPE repository with the given tag
    git clone --depth 1 --branch $SOPE_GIT_TAG $SOPE
    cd sope

    # Create deb packages
    cp -a packaging/debian debian
    ./debian/rules
    dpkg-checkbuilddeps && dpkg-buildpackage
    cd "$PACKAGES_DIR"

    # Install the built packages
    dpkg -i libsope*.deb

    # Checkout the SOGo repository with the given tag
    git clone --depth 1 --branch $SOGO_GIT_TAG $SOGO
    cd sogo

    # Create deb packages
    cp -a packaging/debian debian
    dch --newversion "$SOGO_V" "Automated build for version $SOGO_V"
    ./debian/rules
    dpkg-checkbuilddeps && dpkg-buildpackage -b

    # Copy deb package
    cp ${PACKAGES_DIR}/sogo_${SOGO_V}_amd64.deb $BUILD_DIR

    # Go back to build dir.
    cd $BUILD_DIR

    # Clear builddir
    rm -fr $PACKAGES_DIR

fi