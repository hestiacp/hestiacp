Name:           vesta-php
Version:        0.9.8
Release:        20
Summary:        Vesta Control Panel
Group:          System Environment/Base
License:        GPL
URL:            http://vestacp.com/
Vendor:         vestacp.com
Source0:        %{name}-%{version}.tar.gz
Source1:        php.ini
Source2:        php-fpm.conf
Requires:       redhat-release >= 5
Provides:       vesta-php
BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)

%description
This package contains php-cgi for Vesta Control Panel web interface.

%prep
%setup -q -n %{name}-%{version}

%build
./configure --prefix=/usr/local/vesta/php --with-zlib --enable-fpm --with-fpm-user=admin --with-fpm-group=admin --with-mysql --with-mysqli --with-curl --enable-mbstring

make

%install
make install INSTALL_ROOT=%{buildroot} INSTALLDIRS=vendor
install -p -D -m 0755 %{SOURCE1} %{buildroot}/usr/local/vesta/php/lib/
%{__install} -p -D -m 0755 %{SOURCE2} %{buildroot}/usr/local/vesta/php/etc/
%{__install} -p -D -m 0755  %{buildroot}/usr/local/vesta/php/sbin/php-fpm %{buildroot}/usr/local/vesta/php/sbin/vesta-php


rm -rf $RPM_BUILD_ROOT/.channels
rm -rf $RPM_BUILD_ROOT/.depdb
rm -rf $RPM_BUILD_ROOT/.depdblock
rm -rf $RPM_BUILD_ROOT/.filemap
rm -rf $RPM_BUILD_ROOT/.lock

%clean
rm -rf %{buildroot}

%post
if [ $1 -eq 1 ]; then
    if [ -e /usr/local/vesta/ioncube/ioncube.sh ]; then
        /usr/local/vesta/ioncube/ioncube.sh add
    fi
fi

%postun
if [ $1 -ge 1 ]; then
    if [ -e "/var/run/vesta-php.pid" ]; then
        /sbin/service vesta restart > /dev/null 2>&1 || :
    fi
fi

%files
%defattr(-,root,root)
%attr(755,root,root) /usr/local/vesta/php

%changelog
* Wed Jul 31 2013 Serghey Rodin <builder@vestacp.com> - 0.9.8-16
- New session save path

* Wed Jul 31 2013 Serghey Rodin <builder@vestacp.com> - 0.9.8-1
- Upgraded to 5.4.17

* Fri Mar 05 2013 Serghey Rodin <builder@vestacp.com> - 0.9.7-3
- Added LANG variable to evn in php-fpm.conf

* Wed Jun 27 2012 Serghey Rodin <builder@vestacp.com> - 0.9.7-2
- Added LANG variable to evn in php-fpm.conf

* Wed Jun 27 2012 Serghey Rodin <builder@vestacp.com> - 0.9.7-1
- initial php build
