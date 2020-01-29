#!/bin/bash

# Includes
source $HESTIA/func/main.sh
source $HESTIA/conf/hestia.conf

#
# Migrate legacy multiphp to full php-fpm backend
#
# nginx+fpm (default)
#   nothing to be done here,
#   (Adding new php backends will make them available on edit/web)
#
# nginx+multiphp,
# nginx+apache+multiphp,
# apache+multiphp:
#   Change Hestia WEB_BACKEND from null to php-fpm
#   Create backend templates ex: PHP-7_3, PHP-5_6 (in $HESTIA/data/templates/web/php-fpm/)
#   v-update-web-templates
#   Loop through all web domains
#   If official multiphp tpl is used ex: PHP-72, then change backend tpl and set app web template to default
#       ( old default.tpl backend maps to PHP-7_3 )
#   If not, parse php version from tpl file , fallback to latest version,
#   Copy all non-official tpls to php-fpm folder (as app web template includin bash script if present)
#
# a2 (non-fpm) or nginx+a2(non-fpm)
# - Skipped
#

DEFAULT_BTPL="PHP-7_3"
num_php_versions=$(ls -d /etc/php/*/fpm/pool.d 2>/dev/null |wc -l)
echo "Found $num_php_versions php versions"

if [ "$num_php_versions" -gt 1 ] && [ -z "$WEB_BACKEND" ]; then
    # Legacy multiphp

    sed -i "/^WEB_BACKEND=/d" $HESTIA/conf/hestia.conf
    echo "WEB_BACKEND='php-fpm'" >> $HESTIA/conf/hestia.conf

    for php_ver in $(ls /etc/php/); do
        [ ! -d "/etc/php/$php_ver/fpm/pool.d/" ] && continue
        cp -f "$HESTIA_INSTALL_DIR/php-fpm/multiphp.tpl"  ${WEBTPL}/php-fpm/PHP-${php_ver/\./_}.tpl
    done

    if [ ! -z "$WEB_SYSTEM" ]; then
        cp -rf "${HESTIA_INSTALL_DIR}/templates/web/$WEB_SYSTEM" "${WEBTPL}/"
    fi

    # Migrate domains
    for user in $($BIN/v-list-sys-users plain); do
        # Define user data and get suspended status
        USER_DATA=$HESTIA/data/users/$user
        SUSPENDED=$(get_user_value '$SUSPENDED')

        # Check if user is suspended
        if [ "$SUSPENDED" = "yes" ]; then
            suspended="yes"
            $BIN/v-unsuspend-user $user
        fi
        echo "Migrating legacy multiphp domains for user: $user"
        for domain in $($BIN/v-list-web-domains $user plain |cut -f1); do
            SUSPENDED_WEB=$(get_object_value 'web' 'DOMAIN' "$domain" '$SUSPENDED')
            # Check if web domain is suspended
            if [ "$SUSPENDED_WEB" = "yes" ]; then
                suspended_web="yes"
                $BIN/v-unsuspend-web-domain $user $domain
            fi
            echo "Processing domain: $domain"
            web_tpl="default"
            backend_tpl="$DEFAULT_BTPL"
            domain_tpl=$($BIN/v-list-web-domain $user $domain |grep "^TEMPLATE:" |awk '{print $2;}' );

            if [ "$domain_tpl" = "PHP-56" ]; then
                backend_tpl="PHP-5_6"
            elif [ "$domain_tpl" = "PHP-70" ]; then
                backend_tpl="PHP-7_0"
            elif [ "$domain_tpl" = "PHP-71" ]; then
                backend_tpl="PHP-7_1"
            elif [ "$domain_tpl" = "PHP-72" ]; then
                backend_tpl="PHP-7_2"
            elif [ "$domain_tpl" = "PHP-73" ] || [ "$domain_tpl" = "default" ] || [ -z "$domain_tpl" ]; then
                backend_tpl="PHP-7_3"
            elif [ "$domain_tpl" = "PHP-74" ]; then
                backend_tpl="PHP-7_4"
            else
                # Custom domain template used
                echo "Domain is using a custom multiphp template (or non-multiphp one)"

                web_tpl="$domain_tpl"
                if [ -f "${WEBTPL}/$WEB_SYSTEM/php-fpm/$web_tpl.tpl" ]; then
                    # php-fpm backend folder allready has a template with the same name
                    web_tpl="custom-$domain_tpl"
                fi

                # Copy custom template to php-fpm backend folder
                mkdir -p "$WEBTPL/$WEB_SYSTEM/php-fpm"
                if [ -f "$WEBTPL/$WEB_SYSTEM/$domain_tpl.sh" ]; then
                    cp "$WEBTPL/$WEB_SYSTEM/$domain_tpl.sh" "$WEBTPL/$WEB_SYSTEM/php-fpm/$web_tpl.sh"
                fi
                cp "$WEBTPL/$WEB_SYSTEM/$domain_tpl.tpl" "$WEBTPL/$WEB_SYSTEM/php-fpm/$web_tpl.tpl"
                cp "$WEBTPL/$WEB_SYSTEM/$domain_tpl.stpl" "$WEBTPL/$WEB_SYSTEM/php-fpm/$web_tpl.stpl"


                if [[ $(grep "unix:/" $WEBTPL/$WEB_SYSTEM/$domain_tpl.tpl |egrep -v "^\s*#" |tail -n1) \
                        =~ unix:\/run\/php\/php([0-9]+\.[0-9]+)-fpm.+\.sock ]]; then

                    # Found a custom template that is based on official multiphp one
                    backend_tpl="PHP-${BASH_REMATCH[1]/\./_}"
                    echo "Custom multiphp template ($domain_tpl) compatible with backend: $backend_tpl"

                    # Remove multiphp switching script
                    rm -f "$WEBTPL/$WEB_SYSTEM/php-fpm/$web_tpl.sh"

                    # Replace hardcoded php-fpm socket path with tpl variable, ignoring commented lines
                    sed '/^[[:space:]]*#/!s/unix:.*;/%backend_lsnr%;/g' "$WEBTPL/$WEB_SYSTEM/php-fpm/$web_tpl.tpl"
                    sed '/^[[:space:]]*#/!s/unix:.*;/%backend_lsnr%;/g' "$WEBTPL/$WEB_SYSTEM/php-fpm/$web_tpl.stpl"
                fi
            fi

            echo "Parsed config: oldTPL=$domain_tpl newTPL:$web_tpl newBackTPL:$backend_tpl"
            $BIN/v-change-web-domain-tpl "$user" "$domain" "$web_tpl" "no"
            $BIN/v-change-web-domain-backend-tpl "$user" "$domain" "$backend_tpl" "no"
            echo -e "--done--\n"

            # Suspend domain again, if it was suspended
            if [ "$suspended_web" = "yes" ]; then
                unset suspended_web
                $BIN/v-suspend-web-domain $user $domain
            fi
        done

        # Suspend user again, if he was suspended
        if [ "$suspended" = "yes" ]; then
            unset suspended
            $BIN/v-suspend-user $user
        fi
    done

    # cleanup legacy multiphp templates
    for php_ver in $(ls /etc/php/); do
        [ ! -d "/etc/php/$php_ver/fpm/pool.d/" ] && continue
        echo "Remove legacy multiphp templates for: $php_ver"
        [ -f "$WEBTPL/$WEB_SYSTEM/PHP-${php_ver//.}.sh" ]   && rm "$WEBTPL/$WEB_SYSTEM/PHP-${php_ver//.}.sh"
        [ -f "$WEBTPL/$WEB_SYSTEM/PHP-${php_ver//.}.tpl" ]  && rm "$WEBTPL/$WEB_SYSTEM/PHP-${php_ver//.}.tpl"
        [ -f "$WEBTPL/$WEB_SYSTEM/PHP-${php_ver//.}.stpl" ] && rm "$WEBTPL/$WEB_SYSTEM/PHP-${php_ver//.}.stpl"
    done

    # Remove default symlinks
    [ -f "$WEBTPL/$WEB_SYSTEM/default.sh" ]   && rm "$WEBTPL/$WEB_SYSTEM/default.sh"
    [ -f "$WEBTPL/$WEB_SYSTEM/default.tpl" ]  && rm "$WEBTPL/$WEB_SYSTEM/default.tpl"
    [ -f "$WEBTPL/$WEB_SYSTEM/default.stpl" ] && rm "$WEBTPL/$WEB_SYSTEM/default.stpl"


    $BIN/v-update-web-templates 'no'
fi
