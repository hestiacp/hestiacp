Name:           vesta-ioncube
Version:        0.9.8
Release:        20
Summary:        ionCube Loader
Group:          System Environment/Base
License:        "Freely redistributable without restriction"
URL:            https://www.ioncube.com
Vendor:         ioncube.com
Source0:        %{name}-%{version}.tar.gz
BuildRoot:      %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)
Requires:       vesta-php
Provides:       vesta-ioncube

%define         _vestadir  /usr/local/vesta/ioncube

%description
This package contains ionCube loader for Vesta

%prep
%setup -q -n %{name}-%{version}

%build

%install
install -d  %{buildroot}%{_vestadir}
%{__cp} -ad ./* %{buildroot}%{_vestadir}

%clean
rm -rf %{buildroot}

%post
if [ $1 -eq 1 ]; then
    if [ -e /usr/local/vesta/ioncube/ioncube.sh ]; then
        /usr/local/vesta/ioncube/ioncube.sh add
    fi
fi

%preun
if [ $1 -eq 0 ]; then
    if [ -e /usr/local/vesta/ioncube/ioncube.sh ]; then
        /usr/local/vesta/ioncube/ioncube.sh delete
    fi
fi

%files
%{_vestadir}

%changelog
* Fri Jun 16 2017 Serghey Rodin <builder@vestacp.com> - 0.9.8-18
- Initial package for ionCube 6.1.0

