export interface FeatureListItem {
	text: string;
	items?: { text: string }[];
}

export const users: FeatureListItem[] = [
	{ text: "Support for SFTP chroot jails" },
	{ text: "Two-Factor Authentication support for the Admin Panel" },
	{ text: "SSH keys for login via SFTP and SSH" },
];

export const webDomains: FeatureListItem[] = [
	{ text: "Nginx FastCGI cache support for Nginx + PHP-FPM" },
	{ text: "Nginx Proxy cache support for Nginx + Apache2" },
	{ text: "Per-domain TLS certificates for web domains" },
	{ text: "MultiIP support for Web/Mail/DNS" },
	{
		text: "MultiPHP support for",
		items: [
			{ text: "PHP 5.6 (<a href='https://www.php.net/supported-versions.php'>EOL</a>)" },
			{ text: "PHP 7.0 (<a href='https://www.php.net/supported-versions.php'>EOL</a>)" },
			{ text: "PHP 7.1 (<a href='https://www.php.net/supported-versions.php'>EOL</a>)" },
			{ text: "PHP 7.2 (<a href='https://www.php.net/supported-versions.php'>EOL</a>)" },
			{ text: "PHP 7.3 (<a href='https://www.php.net/supported-versions.php'>EOL</a>)" },
			{ text: "PHP 7.4" },
			{ text: "PHP 8.0" },
			{ text: "PHP 8.1" },
		],
	},
	{
		text: "One-Click Install Apps",
		items: [
			{ text: "WordPress" },
			{ text: "Dokuwiki" },
			{ text: "Drupal" },
			{ text: "Grav" },
			{ text: "Laravel" },
			{ text: "MediaWiki" },
			{ text: "NextCloud" },
			{ text: "OpenCart" },
			{ text: "Prestashop" },
			{ text: "Symphony" },
		],
	},
];

export const mail: FeatureListItem[] = [
	{
		text: "Per-domain TLS certificates for inbound and outbound mail services (Exim 4, Dovecot, Webmail)",
	},
	{ text: "SMTP relay setup for Exim in case port 25 is blocked by the provider" },
	{ text: "Rate limit adjustable per user or email account" },
	{ text: "Let’s Encrypt support for mail domains" },
	{ text: "Latest version of Roundcube" },
	{ text: "Optional Rainloop installation" },
];

export const databases: FeatureListItem[] = [
	{ text: "Support for MariaDB 10.2 -> 10.8 with 10.6 as default" },
	{ text: "Support for PostgreSQL" },
	{ text: "Latest version of phpMyAdmin" },
];

export const serverAdmin: FeatureListItem[] = [
	{ text: "Automated backups to SFTP, FTP or Backblaze B2" },
];
