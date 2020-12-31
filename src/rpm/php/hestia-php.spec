Name:           hestia-php
Version:        %HESTIA-PHP-VERSION%
Release:        0
Summary:        Hestia internal PHP
Group:          System Environment/Base
URL:            https://www.hestiacp.com
License:        PHP and Zend and BSD and MIT and ASL 1.0 and NCSA
Vendor:         hestiacp.com
Requires:       redhat-release >= 7
Provides:       hestia-php = %{version}
BuildRequires:  systemd-rpm-macros

%description
This package contains internal PHP for Hestia Control Panel web interface.

%prep

%build

%install
cp -rfa %{sourcedir}/usr %{buildroot}
mkdir -p %{buildroot}%{_unitdir}
%{__install} -m644 %{sourcedir}/hestia-php.service %{buildroot}%{_unitdir}/hestia-php.service

%clean

%pre

%post
%systemd_post hestia-php.service

%preun
%systemd_preun hestia-php.service

%postun
%systemd_postun_with_restart hestia-php.service

%files
%defattr(-,root,root)
%attr(755,root,root) /usr/local/hestia/php
%attr(775,admin,admin) /usr/local/hestia/php/var/log
%attr(775,admin,admin) /usr/local/hestia/php/var/run
%config(noreplace) /usr/local/hestia/php/etc/php-fpm.conf
%config(noreplace) /usr/local/hestia/php/lib/php.ini
%{_unitdir}/hestia-php.service

%changelog
* Thu Jun 25 2020 Ernesto Nicolás Carrea <equistango@gmail.com> - 7.4.6
- HestiaCP CentOS 8 support
