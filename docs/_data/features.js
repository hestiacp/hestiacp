/** @typedef {{ text: string, items?: { text: string }[] }} FeatureListItem */

/** @type {FeatureListItem[]} */
export const users = [
	{ text: 'Support for SFTP chroot jails' },
	{ text: 'Two-Factor Authentication support for the Admin Panel' },
	{ text: 'SSH keys for login via SFTP and SSH' },
];

/** @type {FeatureListItem[]} */
export const webDomains = [
	{ text: 'Nginx FastCGI cache support for Nginx + PHP-FPM' },
	{ text: 'Nginx Proxy cache support for Nginx + Apache2' },
	{ text: 'Per-domain TLS certificates for web domains' },
	{ text: 'MultiIP support for Web/Mail/DNS' },
	{
		text: 'MultiPHP support for',
		items: [
			{ text: "PHP 5.6 (<a href='https://www.php.net/supported-versions.php'>EOL</a>)" },
			{ text: "PHP 7.0 (<a href='https://www.php.net/supported-versions.php'>EOL</a>)" },
			{ text: "PHP 7.1 (<a href='https://www.php.net/supported-versions.php'>EOL</a>)" },
			{ text: "PHP 7.2 (<a href='https://www.php.net/supported-versions.php'>EOL</a>)" },
			{ text: "PHP 7.3 (<a href='https://www.php.net/supported-versions.php'>EOL</a>)" },
			{ text: "PHP 7.4 (<a href='https://www.php.net/supported-versions.php'>EOL</a>)" },
			{ text: 'PHP 8.0' },
			{ text: 'PHP 8.1' },
			{ text: 'PHP 8.2' },
		],
	},
	{
		text: 'One-Click Install Apps',
		items: [
			{ text: 'WordPress' },
			{ text: 'Dokuwiki' },
			{ text: 'Drupal' },
			{ text: 'Grav' },
			{ text: 'Laravel' },
			{ text: 'MediaWiki' },
			{ text: 'NextCloud' },
			{ text: 'OpenCart' },
			{ text: 'Prestashop' },
			{ text: 'Symphony' },
		],
	},
];

/** @type {FeatureListItem[]} */
export const mail = [
	{
		text: 'Per-domain TLS certificates for inbound and outbound mail services (Exim 4, Dovecot, Webmail)',
	},
	{ text: 'SMTP relay setup for Exim in case port 25 is blocked by the provider' },
	{ text: 'Rate limit adjustable per user or email account' },
	{ text: 'Let’s Encrypt support for mail domains' },
	{ text: 'Latest version of Roundcube' },
	{ text: 'Optional SnappyMail installation' },
];

/** @type {FeatureListItem[]} */
export const dns = [
	{ text: 'Create your own nameservers' },
	{ text: 'Easy DNS cluster setup' },
	{ text: 'Support for DNSSEC on domains' },
];

/** @type {FeatureListItem[]} */
export const databases = [
	{ text: 'Support for MariaDB 10.2 -> 10.11 with 10.11 as default' },
	{ text: 'Support for MySQL 8' },
	{ text: 'Support for PostgreSQL' },
	{ text: 'Latest version of phpMyAdmin and phpPgAdmin' },
];

/** @type {FeatureListItem[]} */
export const serverAdmin = [
	{
		text: "Automated backups to SFTP, FTP and via Rclone with 50+ <a href='https://rclone.org/overview/'>Cloud storage providers</a>",
	},
];
