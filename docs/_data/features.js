/** @typedef {{ text: string, items?: { text: string }[] }} FeatureListItem */

/** @type {FeatureListItem[]} */
export const users = [
	{ text: '支持SFTP chroot jails用于限制SFTP用户只能在指定的目录下活动。这样做可以增加服务器的安全性,减少因权限问题导致的安全漏洞' },
	{ text: '管理面板的双因素身份验证支持' },
	{ text: '用于通过 SFTP 和 SSH 登录的 SSH 密钥' },
];

/** @type {FeatureListItem[]} */
export const webDomains = [
	{ text: 'Nginx FastCGI 缓存支持 Nginx + PHP-FPM' },
	{ text: 'Nginx 代理缓存支持 Nginx + Apache2' },
	{ text: 'Web 网站的每个域名配置 TLS 证书' },
	{ text: '对 Web/Mail/DNS的多 IP 支持' },
	{ text: '支持 PHP 版本 5.6 到 8.2默认为 PHP8.2' },
	{
		text: '一键安装应用程序',
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
	{text: '用于入站和出站邮件服务(Exim 4, Dovecot, Webmail)的 TLS 证书',},
	{ text: 'Exim 的 SMTP 中继设置，以防端口 25 被提供商阻止' },
	{ text: '速率限制可按用户或电子邮件帐户调整' },
	{ text: 'Let’s 对邮件域的支持' },
	{ text: 'Roundcube 的最新版本' },
	{ text: '可选的 SnappyMail 安装' },
];

/** @type {FeatureListItem[]} */
export const dns = [
	{ text: '创建自己的名称服务器' },
	{ text: '轻松设置 DNS 集群' },
	{ text: '支持域上的 DNSSEC' },
];

/** @type {FeatureListItem[]} */
export const databases = [
	{ text: '支持 MariaDB 10.2 -> 10.11默认为 10.11' },
	{ text: '支持 MySQL 8' },
	{ text: '支持 PostgreSQL' },
	{ text: 'phpMyAdmin 和 phpPgAdmin 的最新版本' },
];

/** @type {FeatureListItem[]} */
export const serverAdmin = [
	{
		text: "自动备份到 SFTP、FTP 和通过 Rclone 与 50+ <a href='https://rclone.org/overview/'>云存储提供商</a>",
	},
];
