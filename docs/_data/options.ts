export const options: OptionsListItem[] = [
	{
		name: " --port",
		id: "port",
		param: "--port",
		desc: "Change Hestia Port",
		selected: true,
		text: "8083",
		textField: true,
	},
	{
		name: " --lang",
		id: "language",
		param: "--lang",
		desc: "ISO 639-1 codes",
		selected: true,
		default: "en",
		selectField: true,
		text: "en",
	},
	{
		name: " --hostname",
		id: "hostname",
		param: "--hostname",
		desc: "Set hostname",
		selected: false,
		text: "",
		textField: true,
	},
	{
		name: " --email",
		id: "email",
		param: "--email",
		desc: "Set admin email",
		selected: false,
		text: "",
		textField: true,
	},
	{
		name: " --password",
		id: "password",
		param: "--password",
		desc: "Set admin password",
		selected: false,
		text: "",
		textField: true,
	},
	{
		name: " --apache",
		id: "apache",
		param: "--apache",
		desc: "Install Apache for htaccess support",
		selected: true,
	},
	{ name: " --phpfpm", id: "phpfpm", param: "--phpfpm", desc: "Install PHP-FPM.", selected: true },
	{
		name: " --multiphp",
		id: "multiphp",
		param: "--multiphp",
		desc: "Install Multiple PHP versions (5.6, 7.0, 7.1, 7.2, 7.3, 7.4, 8.0, 8.1, 8.2)",
		selected: true,
	},
	{
		name: " --vsftpd",
		id: "vsftpd",
		param: "--vsftpd",
		desc: "Use vsftpd for FTP: lightweight, minimalist, secure",
		selected: true,
		conflicts: "proftpd",
	},
	{
		name: " --proftpd",
		id: "proftpd",
		param: "--proftpd",
		desc: "Use ProFTPD for FTP: advanced, more modular, supports LDAP",
		selected: false,
		conflicts: "vsftpd",
	},
	{
		name: " --named",
		id: "named",
		param: "--named",
		desc: "Manage your DNS in Hestia with custom nameservers",
		selected: true,
	},
	{
		name: " --mysql",
		id: "mysql",
		param: "--mysql",
		desc: "Install MariaDB, an optimized fork of MySQL",
		selected: true,
		conflicts: "mysql8",
	},
	{
		name: " --mysql8",
		id: "mysql8",
		param: "--mysql8",
		desc: "Install MySQL 8",
		selected: false,
		conflicts: "mysql",
	},
	{
		name: " --postgresql",
		id: "postgresql",
		param: "--postgresql",
		desc: "Install PostgreSQL",
		selected: false,
	},
	{
		name: " --exim",
		id: "exim",
		param: "--exim",
		desc: "Send emails from webmail or via SMTP",
		selected: true,
	},
	{
		name: " --dovecot",
		id: "dovecot",
		param: "--dovecot",
		desc: "Receive emails and connect with email clients via IMAP/POP3",
		selected: true,
		depends: "exim",
	},
	{
		name: " --sieve",
		id: "sieve",
		param: "--sieve",
		desc: "Manage your own custom email filters",
		selected: false,
		depends: "dovecot",
	},
	{
		name: " --clamav",
		id: "clamav",
		param: "--clamav",
		desc: "Scans your email inbox for viruses",
		selected: true,
		depends: "exim",
	},
	{
		name: " --spamassassin",
		id: "spamassassin",
		param: "--spamassassin",
		desc: "Filter out spam emails from your inbox",
		selected: true,
		depends: "exim",
	},
	{
		name: " --iptables",
		id: "iptables",
		param: "--iptables",
		desc: "Manage your firewall within Hestia",
		selected: true,
	},
	{
		name: " --fail2ban",
		id: "fail2ban",
		param: "--fail2ban",
		desc: "Provides Bruteforce protection for SSH, Email, FTP, database",
		selected: true,
	},
	{ name: " --quota", id: "quota", param: "--quota", desc: "Filesystem Quota.", selected: false },
	{ name: " --api", id: "api", param: "--api", desc: "Activate API.", selected: true },
	{
		name: " --interactive",
		id: "interactive",
		param: "--interactive",
		desc: "Interactive install.",
		selected: true,
	},
	{ name: " --force", id: "force", param: "--force", desc: "Force installation.", selected: false },
];
