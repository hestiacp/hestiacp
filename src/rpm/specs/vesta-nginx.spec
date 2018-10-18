Name:           vesta-nginx
Version:        0.9.8
Release:        23
Summary:        Vesta Control Panel
Group:          System Environment/Base
License:        BSD-like
URL:            http://vestacp.com/
Vendor:         vestacp.com
Source0:        %{name}-%{version}.tar.gz
Source1:        nginx.conf
Source2:        vesta.init
Requires:       redhat-release >= 5
Provides:       vesta-nginx
BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)

%description
This package contains nginx webserver for Vesta Control Panel web interface.

%prep
%setup -q -n %{name}-%{version}

%build
./configure --prefix=/usr/local/vesta/nginx --with-http_ssl_module
make

%install
make install DESTDIR=%{buildroot} INSTALLDIRS=vendor
%{__install} -p -D -m 0755 %{SOURCE1} %{buildroot}/usr/local/vesta/nginx/conf/nginx.conf
%{__install} -p -D -m 0755 %{SOURCE2} %{buildroot}%{_initrddir}/vesta
%{__install} -p -D -m 0755  %{buildroot}/usr/local/vesta/nginx/sbin/nginx %{buildroot}/usr/local/vesta/nginx/sbin/vesta-nginx
%clean
rm -rf %{buildroot}

%post
/sbin/chkconfig --add vesta

%preun
if [ $1 = 0 ]; then
    /sbin/service vesta stop >/dev/null 2>&1
    /sbin/chkconfig --del vesta
fi

%postun
if [ $1 -ge 1 ]; then
    if [ -e "/var/run/vesta-nginx.pid" ]; then
        /sbin/service vesta restart > /dev/null 2>&1 || :
    fi
fi

%files
%defattr(-,root,root)
%attr(755,root,root) /usr/local/vesta/nginx
%{_initrddir}/vesta
%config(noreplace) /usr/local/vesta/nginx/conf/nginx.conf


%changelog
* Tue Jul 30 2013 Serghey Rodin <builder@vestacp.com> - 0.9.8-1
- upgraded to nginx-1.4.2

* Sat Apr 06 2013 Serghey Rodin <builder@vestacp.com> - 0.9.7-2
- new init script

* Wed Jun 27 2012 Serghey Rodin <builder@vestacp.com> - 0.9.7-1
- initial build
