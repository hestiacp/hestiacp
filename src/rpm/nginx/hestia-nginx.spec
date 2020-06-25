Name:           hestia-nginx
Version:        1.17.8
Release:        0
Summary:        hestia Nginx
Group:          System Environment/Base
License:        BSD-like
URL:            https://www.hestiacp.com
Vendor:         hestiacp.com
Requires:       redhat-release >= 8
Provides:       hestia-nginx
BuildRequires:  systemd-rpm-macros
#BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)

%description
This package contains nginx webserver for Hestia Control Panel web interface.

%prep

%build

%install
cp -rfa %{sourcedir}/usr %{buildroot}
mkdir -p %{buildroot}%{_unitdir}
%{__install} -m644 %{sourcedir}/hestia-nginx.service %{buildroot}%{_unitdir}/hestia-nginx.service

%clean
#rm -rf %{buildroot}

%pre      

%post
%systemd_post hestia-nginx.service

%preun
%systemd_preun hestia-nginx.service

%postun
%systemd_postun_with_restart hestia-nginx.service

%files
%defattr(-,root,root)
%attr(755,root,root) /usr/local/hestia/nginx
%config(noreplace) /usr/local/hestia/nginx/conf/nginx.conf
%{_unitdir}/hestia-nginx.service


%changelog
* Wed Jun 24 2020 Ernesto Nicolás Carrea <equistango@gmail.com> - 1.17.8
- HestiaCP CentOS 8 support

* Tue Jul 30 2013 Serghey Rodin <builder@vestacp.com> - 0.9.8-1
- upgraded to nginx-1.4.2

* Sat Apr 06 2013 Serghey Rodin <builder@vestacp.com> - 0.9.7-2
- new init script

* Wed Jun 27 2012 Serghey Rodin <builder@vestacp.com> - 0.9.7-1
- initial build

