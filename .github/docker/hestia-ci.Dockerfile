FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive
ENV container=docker

RUN apt-get update \
	&& apt-get install -y --no-install-recommends eatmydata \
	&& EATMYDATA_SO="$(ldconfig -p | awk '/libeatmydata.so/{print $NF; exit}')" \
	&& [ -n "${EATMYDATA_SO}" ] \
	&& echo "${EATMYDATA_SO}" > /etc/ld.so.preload \
	&& apt-get full-upgrade -y \
	&& apt-get install -y --no-install-recommends \
		apt-utils \
		apt-transport-https \
		ca-certificates \
		curl \
		dbus \
		fail2ban \
		git \
		gnupg \
		htop \
		iproute2 \
		iptables \
		jq \
		less \
		locales \
		lsb-release \
		nano \
		netcat-openbsd \
		netplan.io \
		net-tools \
		rsync \
		software-properties-common \
		sudo \
		systemd \
		systemd-sysv \
		systemd-timesyncd \
		tzdata \
		vim \
		wget \
	&& rm -rf /var/lib/apt/lists/*

RUN locale-gen en_US.UTF-8 \
	&& update-locale LANG=en_US.UTF-8

# Ensure sshd privilege separation dir exists at boot on /run tmpfs.
RUN printf 'd /run/sshd 0755 root root -\n' > /etc/tmpfiles.d/sshd.conf

# Avoid ssh.service entering start-limit-hit during rapid restart loops in tests.
RUN mkdir -p /etc/systemd/system/ssh.service.d \
	&& cat > /etc/systemd/system/ssh.service.d/10-docker.conf << 'EOF'
[Unit]
StartLimitIntervalSec=0
EOF

# Ensure netplan exists in Ubuntu containers so v-add-sys-ip uses netplan path in tests.
RUN cat > /etc/netplan/01-docker.yaml << 'EOF'
network:
  version: 2
  renderer: networkd
  ethernets:
    eth0:
      dhcp4: true
EOF

VOLUME ["/sys/fs/cgroup"]

STOPSIGNAL SIGRTMIN+3
CMD ["/sbin/init"]
