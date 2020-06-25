Name:           hestia
Version:        1.2.0
Release:        0
Summary:        Hestia Control Panel
Group:          System Environment/Base
License:        GPLv3
URL:            https://www.hestiacp.com
Vendor:         hestiacp.com
Requires:       redhat-release >= 7
Requires:       bash
Requires:       awk
Requires:       sed
Requires:       acl
Requires:       sysstat
Requires:       setpriv
Provides:       hestia = %{version}
BuildRequires:  systemd-rpm-macros

%description
This package contains the Hestia Control Panel.

%prep

%build

%install
cp -rfa %{sourcedir}/usr %{buildroot}

%clean

%pre
# Run triggers only on updates
if [ -e "/usr/local/hestia/data/users/admin" ]; then
    # Validate version number and replace if different
    HESTIA_V=$(rpm --queryformat="%{VERSION}" -q hestia)
    if [ ! "$HESTIA_V" = "%{version}" ]; then
        sed -i "s/VERSION=.*/VERSION='$HESTIA_V'/g" /usr/local/hestia/conf/hestia.conf
    fi
fi

%post
%systemd_post hestia-nginx.service
if [ -e "/usr/local/hestia/data/users/admin" ]; then
    ###############################################################
    #                Initialize functions/variables               #
    ###############################################################

    # Load upgrade functions and refresh variables/configuration
    source /usr/local/hestia/func/upgrade.sh
    upgrade_refresh_config

    ###############################################################
    #             Set new version numbers for packages            #
    ###############################################################
    # Hestia Control Panel
    new_version=$(rpm --queryformat="%{VERSION}" -q hestia)

    # phpMyAdmin
    pma_v='5.0.2'

    ###############################################################
    #               Begin standard upgrade routines               #
    ###############################################################

    # Initialize backup directories
    upgrade_init_backup

    # Set up console display and welcome message
    upgrade_welcome_message

    # Execute version-specific upgrade scripts
    upgrade_start_routine

    # Upgrade phpMyAdmin if applicable
    upgrade_phpmyadmin

    # Set new version number in hestia.conf
    upgrade_set_version

    # Perform account and domain rebuild to ensure configuration files are correct
    upgrade_rebuild_users

    # Restart necessary services for changes to take full effect
    upgrade_restart_services

    # Add upgrade notification to admin user's panel and display completion message
    upgrade_complete_message
fi

%preun

%postun

%files
%defattr(-,root,root)
%attr(755,root,root) /usr/local/hestia

%changelog
* Thu Jun 25 2020 Ernesto Nicolás Carrea <equistango@gmail.com> - 1.2.0
- HestiaCP CentOS 8 support
