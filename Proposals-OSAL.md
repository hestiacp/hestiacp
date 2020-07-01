# Proposals for HestiaCP

## OS abstraction layer

OSAL aims to provide a set of utilities to enable distro-independent (and potentially OS-independent) bash scripts.

### Examples

```bash
pacakge_install 'wget'
pacakge_install $PKG_APACHE

chown $USER_APACHE_DATA:$USER_APACHE_DATA /var/www/html

service_enable $SERVICE_NAME_APACHE

service_start $SERVICE_NAME_APACHE

php_prefix=$(multiphp_php_package_prefix '7.2')
pacakge_install ${php_prefix}-mysqlnd
```

(This are actual examples of reference implementation)

### Reference implementation

An actual, working implementation can be found on the `func` folder, consisting of the following files:

```bash
osal.sh                 # Main file
osal_debian_based.sh    # Template for Debian-based systems
osal_rhel_based.sh      # Template for RHEL-based systems
osal_centos_7.sh        # Template for CentOS 7
```

OSAL simplifies the task of supporting a specific distro or an entire family by implementing a sort of inheritance. For example, the OSAL utilities for Ubuntu 20.04 would be the sum of three templates:

1. One for all Debian-based systems
2. One with the specifics common to all Ubuntu systems
3. One for Ubuntu 20.04

Templates 2 and 3 are completely or partially optional. For example, there is actually no template for CentOS 8, because the generic RHEL template is enough to describe CentOS 8. The template for CentOS 7 is only one line, because there's only one change from RHEL-based to CentOS 7 (the preferred package manager).

#### Usage

Just `source osal.sh` and you're done.

### Open questions

* Should all variables and functions be prefixed with osal_ to avoid name clash and to help identify the source of such function/variable?
* Would it be better to use individual, executable shell scripts instead of functions?

#### Shell scripts

A combination of both shell scripts and functions is also possible, for exmaple:

```bash
# Command line usage
./func/osal/package_install 'wget'
```

```bash
#!/bin/sh
source func/osal/package_install
osal_package_install 'wget'
# Sourced inside a bash script
```

In this case, prefixing is a must.
