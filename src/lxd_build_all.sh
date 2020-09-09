#!/bin/bash

#
# Script for preparing lxd enviorment and building Hestia packages for all supported distros
# - Run with sudo, not directly as root!
# 
# Arguments:
# ./lxd_build_all --cleanup
#     - Stop and delete all containers
#
# ./lxd_build_all --background
#     - Execute the build script on all containers simultaneously
#

# Configs:
oslist=('debian=9,10' 'ubuntu=16.04,18.04,20.04')
branch='main'


function setup_container() {
    if [ "$osname" = 'ubuntu' ]; then
        lxc init $osname:$osver "${containername}"
    else
        lxc init images:$osname/$osver "${containername}"
    fi

    mkdir -p "${__DIR__}/build/${containername}"
    chown $user: "${__DIR__}/build/${containername}"

    lxc config set ${containername} raw.idmap "both $user_id $user_gid"
    lxc config device add ${containername} debdir disk path=/opt/hestiacp source=${__DIR__}/build/${containername}
}

cmd=$1
__DIR__="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )" #"

# user=$(logname)
user=$SUDO_USER
user_id=$(id -u $user)
user_gid=$(id -g $user)

if [ -z "$user" ] || [ -z "$user_id" ] || [ -z "$user_gid" ] || [ "$user" = 'root' ]; then
    echo "Script must be run with sudo, not directly as root" && exit 1
fi


if ! dpkg-query -s lxd >/dev/null 2>&1; then
    apt -y install lxd
    lxd init --auto

    echo "root:$user_id:1"  | sudo tee -a /etc/subuid
    echo "root:$user_gid:1" | sudo tee -a /etc/subgid
fi

for osdef in "${oslist[@]}"; do
    osname=${osdef%%=*}
    osversions=$(echo ${osdef##*=} | tr "," "\n")

    for osver in $osversions; do

        containername="hst-${osname}-${osver/\./}"
        container_ip=""
        echo "Container $containername"

        if [ "$cmd" = '--cleanup' ]; then
            # Stop and delete container
            lxc stop $containername
            lxc rm $containername
            continue
        fi

        if ! lxc info $containername > /dev/null 2>&1; then
            setup_container
        fi

        lxc start $containername > /dev/null 2>&1

        # Wait for container to start
        while [ -z "$container_ip" ]; do
            sleep 1
            container_ip=$(lxc list --format csv -c 4,n |grep ",$containername$"| cut -d "," -f 1)
        done
        echo $container_ip

        cp -f "${__DIR__}/lxd_compile.sh" "${__DIR__}/build/${containername}/lxd_compile.sh"

        if [ "$cmd" = '--background' ]; then
            # Run build script in background
            lxc exec $containername -- /opt/hestiacp/lxd_compile.sh $branch >/dev/null 2>&1 &
        else
            lxc exec $containername -- /opt/hestiacp/lxd_compile.sh $branch
        fi

    done
done
