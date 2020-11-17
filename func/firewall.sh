
heal_iptables_links() {
    packages="iptables iptables-save iptables-restore"
    for package in $packages; do
        if [ ! -e "/sbin/${package}" ]; then
            if which ${package}; then
                ln -s "$(which ${package})" /sbin/${package}
            elif [ -e "/usr/sbin/${package}" ]; then
                ln -s /usr/sbin/${package} /sbin/${package}
            elif whereis -B /bin /sbin /usr/bin /usr/sbin -f -b ${package}; then
                autoiptables=$(whereis -B /bin /sbin /usr/bin /usr/sbin -f -b ${package} | cut -d '' -f 2)
                if [ -x "$autoiptables" ]; then
                    ln -s "$autoiptables" /sbin/${package}
                fi
            fi
        fi
    done
}
