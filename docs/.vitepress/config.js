import { defineConfig } from 'vitepress';
import { version } from '../../package.json';

export default defineConfig({
	lang: 'en-US',
	title: 'Hestia Control Panel',
	description: 'Open-source web server control panel.',

	lastUpdated: true,
	cleanUrls: false,

	head: [
		['link', { rel: 'icon', sizes: 'any', href: '/favicon.ico' }],
		['link', { rel: 'icon', type: 'image/svg+xml', sizes: '16x16', href: '/logo.svg' }],
		['link', { rel: 'apple-touch-icon', sizes: '180x180', href: '/apple-touch-icon.png' }],
		['link', { rel: 'manifest', href: '/site.webmanifest' }],
		['meta', { name: 'theme-color', content: '#b7236a' }],
	],

	themeConfig: {
		logo: '/logo.svg',

		nav: nav(),

		socialLinks: [
			{ icon: 'github', link: 'https://github.com/hestiacp/hestiacp' },
			{ icon: 'twitter', link: 'https://twitter.com/HestiaPanel' },
			{ icon: 'facebook', link: 'https://www.facebook.com/hestiacp' },
		],

		sidebar: { '/docs/': sidebarDocs() },

		outline: [2, 3],

		editLink: {
			pattern: 'https://github.com/hestiacp/hestiacp/edit/main/docs/:path',
			text: 'Edit this page on GitHub',
		},

		footer: {
			message: 'Released under the GPLv3 License.',
			copyright: 'Copyright © 2019-present Hestia Control Panel',
		},

		algolia: {
			appId: 'V04P0P5D2R',
			apiKey: '7a90a3ac7f9313f174c50b0f301f7ec6',
			indexName: 'hestia_cp',
		},
	},
});

/** @returns {import("vitepress").DefaultTheme.NavItem[]} */
function nav() {
	return [
		{ text: 'Features', link: '/features' },
		{ text: 'Install', link: '/install' },
		{ text: 'Documentation', link: '/docs/introduction/getting-started', activeMatch: '/docs/' },
		{ text: 'Team', link: '/team' },
		{ text: 'Demo', link: 'https://demo.hestiacp.com:8083/' },
		{ text: 'Forum', link: 'https://forum.hestiacp.com/' },
		{ text: 'Donate', link: '/donate' },
		{
			text: `v${version}`,
			items: [
				{
					text: 'Changelog',
					link: 'https://github.com/hestiacp/hestiacp/blob/main/CHANGELOG.md',
				},
				{
					text: 'Contributing',
					link: 'https://github.com/hestiacp/hestiacp/blob/main/CONTRIBUTING.md',
				},
				{
					text: 'Security policy',
					link: 'https://github.com/hestiacp/hestiacp/blob/main/SECURITY.md',
				},
			],
		},
	];
}
/** @returns {import("vitepress").DefaultTheme.SidebarItem[]} */
function sidebarDocs() {
	return [
		{
			text: 'Introduction',
			collapsed: false,
			items: [
				{ text: 'Getting started', link: '/docs/introduction/getting-started' },
				{ text: 'Best practices', link: '/docs/introduction/best-practices' },
			],
		},
		{
			text: 'User guide',
			collapsed: false,
			items: [
				{ text: 'Account', link: '/docs/user-guide/account' },
				{ text: 'Backups', link: '/docs/user-guide/backups' },
				{ text: 'Cron jobs', link: '/docs/user-guide/cron-jobs' },
				{ text: 'Databases', link: '/docs/user-guide/databases' },
				{ text: 'DNS', link: '/docs/user-guide/dns' },
				{ text: 'File manager', link: '/docs/user-guide/file-manager' },
				{ text: 'Mail domains', link: '/docs/user-guide/mail-domains' },
				{ text: 'Notifications', link: '/docs/user-guide/notifications' },
				{ text: 'Packages', link: '/docs/user-guide/packages' },
				{ text: 'Statistics', link: '/docs/user-guide/statistics' },
				{ text: 'Users', link: '/docs/user-guide/users' },
				{ text: 'Web domains', link: '/docs/user-guide/web-domains' },
			],
		},
		{
			text: 'Server administration',
			collapsed: false,
			items: [
				{ text: 'Backup & restore', link: '/docs/server-administration/backup-restore' },
				{ text: 'Configuration', link: '/docs/server-administration/configuration' },
				{ text: 'Customisation', link: '/docs/server-administration/customisation' },
				{ text: 'Databases & phpMyAdmin', link: '/docs/server-administration/databases' },
				{ text: 'DNS clusters & DNSSEC', link: '/docs/server-administration/dns' },
				{ text: 'Email', link: '/docs/server-administration/email' },
				{ text: 'File manager', link: '/docs/server-administration/file-manager' },
				{ text: 'Firewall', link: '/docs/server-administration/firewall' },
				{ text: 'OS upgrades', link: '/docs/server-administration/os-upgrades' },
				{ text: 'Rest API', link: '/docs/server-administration/rest-api' },
				{ text: 'SSL certificates', link: '/docs/server-administration/ssl-certificates' },
				{ text: 'Web templates & caching', link: '/docs/server-administration/web-templates' },
				{ text: 'Troubleshooting', link: '/docs/server-administration/troubleshooting' },
			],
		},
		{
			text: 'Contributing',
			collapsed: false,
			items: [
				{ text: 'Building Packages', link: '/docs/contributing/building' },
				{ text: 'Development', link: '/docs/contributing/development' },
				{ text: 'Documentation', link: '/docs/contributing/documentation' },
				{ text: 'Quick install app', link: '/docs/contributing/quick-install-app' },
				{ text: 'Testing', link: '/docs/contributing/testing' },
				{ text: 'Translations', link: '/docs/contributing/translations' },
			],
		},
		{
			text: 'Community',
			collapsed: false,
			items: [
				{ text: 'Hestia Nginx Cache', link: '/docs/community/hestia-nginx-cache' },
				{
					text: 'Ioncube installer for Hestia',
					link: '/docs/community/ioncube-hestia-installer',
				},
				{ text: 'Install script generator', link: '/docs/community/install-script-generator' },
			],
		},
		{
			text: 'Reference',
			collapsed: false,
			items: [
				{ text: 'API', link: '/docs/reference/api' },
				{ text: 'CLI', link: '/docs/reference/cli' },
			],
		},
	];
}
