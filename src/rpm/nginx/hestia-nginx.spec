%global _hardened_build 1

%define WITH_CC_OPT $(echo %{optflags} $(pcre2-config --cflags)) -fPIC
%define WITH_LD_OPT -Wl,-z,relro -Wl,-z,now -pie

%define BASE_CONFIGURE_ARGS $(echo "--prefix=/usr/local/hestia/nginx --conf-path=/usr/local/hestia/nginx/conf/nginx.conf --error-log-path=%{_localstatedir}/log/hestia/nginx-error.log --http-log-path=%{_localstatedir}/log/hestia/access.log --pid-path=%{_rundir}/hestia-nginx.pid --lock-path=%{_rundir}/hestia-nginx.lock --http-client-body-temp-path=%{_localstatedir}/cache/hestia-nginx/client_temp --http-proxy-temp-path=%{_localstatedir}/cache/hestia-nginx/proxy_temp --http-fastcgi-temp-path=%{_localstatedir}/cache/hestia-nginx/fastcgi_temp --http-scgi-temp-path=%{_localstatedir}/cache/hestia-nginx/scgi_temp --user=admin --group=admin --with-compat --with-file-aio --with-threads --with-http_addition_module --with-http_auth_request_module --with-http_gunzip_module --with-http_gzip_static_module --with-http_random_index_module --with-http_realip_module --with-http_secure_link_module --with-http_slice_module --with-http_ssl_module --with-http_stub_status_module --with-http_sub_module --with-http_v2_module --with-stream --with-stream_realip_module --with-stream_ssl_module --with-stream_ssl_preread_module")


Name:           hestia-nginx
Version:        1.25.1-2
Release:        1%{dist}
Summary:        Hestia internal nginx web server
Group:          System Environment/Base
URL:            https://www.hestiacp.com
Source0:        https://nginx.org/download/nginx-%{version}.tar.gz
Source1:        hestia-nginx.service
Source2:        nginx.conf
License:        BSD
Vendor:         hestiacp.com
Requires:       redhat-release >= 8
Requires:       hestia-php
Provides:       hestia-nginx = %{version}
BuildRequires:  gcc, zlib-devel, pcre2-devel, openssl-devel, systemd

%description
This package contains internal nginx webserver for Hestia Control Panel web interface.

%prep
%autosetup -p1 -n nginx-%{version}

%build
./configure %{BASE_CONFIGURE_ARGS} \
    --with-cc-opt="%{WITH_CC_OPT}" \
    --with-ld-opt="%{WITH_LD_OPT}"
%make_build

%install
%{__rm} -rf $RPM_BUILD_ROOT
%{__make} DESTDIR=$RPM_BUILD_ROOT INSTALLDIRS=vendor install
mkdir -p %{buildroot}%{_unitdir}
%{__install} -m644 %{SOURCE1} %{buildroot}%{_unitdir}/hestia-nginx.service
rm -f %{buildroot}/usr/local/hestia/nginx/conf/nginx.conf
cp %{SOURCE2} %{buildroot}/usr/local/hestia/nginx/conf/nginx.conf
mv %{buildroot}/usr/local/hestia/nginx/sbin/nginx %{buildroot}/usr/local/hestia/nginx/sbin/hestia-nginx

%clean
%{__rm} -rf $RPM_BUILD_ROOT

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
* Fri Jun 16 2023 myrevery <github@myrevery.com> - 1.25.1-1
- Upgrade to NGINX 1.25.1 mainline version
- Implement TLS 1.3 0-RTT anti-replay

* Sun May 14 2023 Istiak Ferdous <hello@istiak.com> - 1.24.0-1
- 1.24.0-1

* Wed Jun 24 2020 Ernesto Nicolás Carrea <equistango@gmail.com> - 1.17.8
- HestiaCP CentOS 8 support

* Tue Jul 30 2013 Serghey Rodin <builder@vestacp.com> - 0.9.8-1
- upgraded to nginx-1.4.2

* Sat Apr 06 2013 Serghey Rodin <builder@vestacp.com> - 0.9.7-2
- new init script

* Wed Jun 27 2012 Serghey Rodin <builder@vestacp.com> - 0.9.7-1
- initial build
