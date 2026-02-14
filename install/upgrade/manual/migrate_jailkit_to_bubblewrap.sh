#!/bin/bash
# info: Removes Jailkit and migrates to Bubblewrap
#
# Jailkit was availble for a short period in 1.9.0 Beta releases
# How ever it has been replaced by Bubblewrap

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Includes
# shellcheck source=/usr/local/hestia/func/main.sh
source $HESTIA/func/main.sh
# shellcheck source=/usr/local/hestia/conf/hestia.conf
source $HESTIA/conf/hestia.conf

#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

# Checking if jailkit is installed
if [ ! -x /sbin/jk_init ]; then
	exit
fi

#----------------------------------------------------------#
#                       Action                             #
#----------------------------------------------------------#

# Enable the bubblewrap jail for the system
$BIN/v-add-sys-ssh-jail

## Migrate user jails to bubblewrap jails
for user in $("$BIN/v-list-users" list); do
	check_jail_enabled=$(grep "SHELL_JAIL_ENABLED='yes'" $HESTIA/data/users/$user/user.conf)

	# If jail enabled remove the jailkit jail first then bubblewrap the jail
	if [ -n "$check_jail_enabled" ]; then
		user_shell_rssh_nologin=$(grep "^$user:" /etc/passwd | egrep "rssh|nologin")

		# Only remove the jail when it's not needed for rssh or nologin
		if [ -z "$user_shell_rssh_nologin" ]; then
			# chown permissions back to user:user
			if [ -d "/home/$user" ]; then
				chown "$user":"$user" "/home/$user"
			fi

			# Deleting chroot jail for SSH
			delete_chroot_jail "$user"
		fi

		# Deleting user from groups
		gpasswd -d "$user" ssh-jailed > /dev/null 2>&1

		# Enable bubblewrap jail for user
		$BIN/v-change-user-shell $user jailbash

		# Remove config line from user.conf
		sed -i "/SHELL_JAIL_ENABLED='yes'/d" $HESTIA/data/users/$user/user.conf
	fi

	# Remove config line from user.conf
	sed -i "/SHELL_JAIL_ENABLED='no'/d" $HESTIA/data/users/$user/user.conf
done

packages=$(ls --sort=time $HESTIA/data/packages | grep .pkg)

for package in $packages; do
	# Remove config line from package.conf
	sed -i "/SHELL_JAIL_ENABLED='yes'/d" $HESTIA/data/packages/$package
	sed -i "/SHELL_JAIL_ENABLED='no'/d" $HESTIA/data/packages/$package
done

# Checking sshd directives
config='/etc/ssh/sshd_config'
ssh_i=$(grep -n "^# Hestia SSH Chroot" $config)

# Backing up config
cp $config $config.bak

# Disabling jailed ssh
if [ -n "$ssh_i" ]; then
	fline=$(echo "$ssh_i" | cut -f 1 -d :)
	lline=$((fline + 4))
	sed -i "${fline},${lline}d" $config

	/usr/sbin/sshd -t > /dev/null 2>&1
	if [ "$?" -ne 0 ]; then
		message="OpenSSH can not be restarted. Please check config:
            \n\n$(/usr/sbin/sshd -t)"
		echo -e "$message"
	else
		service ssh restart > /dev/null 2>&1
	fi
fi

# Remove group ssh-jailed
groupdel ssh-jailed 2> /dev/null

# Remove cronjob
rm -f /etc/cron.d/hestia-ssh-jail

# Remove jailkit
apt remove -qq jailkit -y > /dev/null 2>&1

#----------------------------------------------------------#
#                       Hestia                             #
#----------------------------------------------------------#

# Logging
log_history "Migrated jailkit to bubblewrap" '' 'admin'
log_event "$OK" "$ARGUMENTS"

exit
