import { defineConfig, type DefaultTheme } from "vitepress";
import { version } from "../../package.json";

export default defineConfig({
	lang: "en-US",
	title: "Hestia Control Panel",
	description: "Open-source web server control panel.",

	lastUpdated: true,
	cleanUrls: "disabled",

	head: [
		["link", { rel: "icon", sizes: "any", href: "/favicon.ico" }],
		["link", { rel: "icon", type: "image/svg+xml", sizes: "16x16", href: "/logo.svg" }],
		["link", { rel: "apple-touch-icon", sizes: "180x180", href: "/apple-touch-icon.png" }],
		["link", { rel: "manifest", href: "/site.webmanifest" }],
		["meta", { name: "theme-color", content: "#b7236a" }],
	],

	themeConfig: {
		logo: "/logo.svg",

		nav: nav(),

		socialLinks: [
			{ icon: "github", link: "https://github.com/hestiacp/hestiacp" },
			{ icon: "discord", link: "https://discord.gg/nXRUZch" },
			{ icon: "twitter", link: "https://twitter.com/HestiaPanel" },
			{ icon: "facebook", link: "https://www.facebook.com/hestiacp" },
		],

		sidebar: { "/docs/": sidebarDocs() },

		outline: [2, 3],

		editLink: {
			pattern: "https://github.com/hestiacp/hestiacp/edit/main/docs/:path",
			text: "Edit this page on GitHub",
		},

		footer: {
			message: "Released under the GPLv3 License.",
			copyright: "Copyright © 2019-present Hestia Control Panel",
		},

		// algolia: {
		//   appId: "REPLACE_ME",
		//   apiKey: "REPLACE_ME",
		//   indexName: "REPLACE_ME",
		// },
	},
});

function nav(): DefaultTheme.NavItem[] {
	return [
		{ text: "Features", link: "/features.md" },
		{ text: "Documentation", link: "/docs/introduction/getting-started.md", activeMatch: "/docs/" },
		{ text: "Team", link: "/team.md" },
		{ text: "Demo", link: "https://demo.hestiacp.com:8083/" },
		{ text: "Forum", link: "https://forum.hestiacp.com/" },
		{ text: "Donate", link: "/donate.md" },
		{
			text: `v${version}`,
			items: [
				{
					text: "Changelog",
					link: "https://github.com/hestiacp/hestiacp/blob/main/CHANGELOG.md",
				},
				{
					text: "Contributing",
					link: "https://github.com/hestiacp/hestiacp/blob/main/CONTRIBUTING.md",
				},
				{
					text: "Security policy",
					link: "https://github.com/hestiacp/hestiacp/blob/main/SECURITY.md",
				},
			],
		},
	];
}

function sidebarDocs(): DefaultTheme.SidebarGroup[] {
	return [
		{
			text: "Introduction",
			collapsible: true,
			items: [
				{ text: "Getting started", link: "/docs/introduction/getting-started.md" },
				{ text: "Best practices", link: "/docs/introduction/best-practices.md" },
			],
		},
		{
			text: "User guide",
			collapsible: true,
			items: [
				{ text: "Account", link: "/docs/user-guide/account.md" },
				{ text: "Backups", link: "/docs/user-guide/backups.md" },
				{ text: "Cron jobs", link: "/docs/user-guide/cron-jobs.md" },
				{ text: "Databases", link: "/docs/user-guide/databases.md" },
				{ text: "DNS", link: "/docs/user-guide/dns.md" },
				{ text: "File manager", link: "/docs/user-guide/file-manager.md" },
				{ text: "Mail domains", link: "/docs/user-guide/mail-domains.md" },
				{ text: "Notifications", link: "/docs/user-guide/notifications.md" },
				{ text: "Packages", link: "/docs/user-guide/packages.md" },
				{ text: "Statistics", link: "/docs/user-guide/statistics.md" },
				{ text: "Users", link: "/docs/user-guide/users.md" },
				{ text: "Web domains", link: "/docs/user-guide/web-domains.md" },
			],
		},
		{
			text: "Server administration",
			collapsible: true,
			items: [
				{ text: "Backup & restore", link: "/docs/server-administration/backup-restore.md" },
				{ text: "Configuration", link: "/docs/server-administration/configuration.md" },
				{ text: "Customisation", link: "/docs/server-administration/customisation.md" },
				{ text: "Databases & phpMyAdmin", link: "/docs/server-administration/databases.md" },
				{ text: "DNS clusters & DNSSEC", link: "/docs/server-administration/dns.md" },
				{ text: "Email", link: "/docs/server-administration/email.md" },
				{ text: "File manager", link: "/docs/server-administration/file-manager.md" },
				{ text: "Firewall", link: "/docs/server-administration/firewall.md" },
				{ text: "OS upgrades", link: "/docs/server-administration/os-upgrades.md" },
				{ text: "Rest API", link: "/docs/server-administration/rest-api.md" },
				{ text: "SSL certificates", link: "/docs/server-administration/ssl-certificates.md" },
				{ text: "Web templates & caching", link: "/docs/server-administration/web-templates.md" },
				{ text: "Troubleshooting", link: "/docs/server-administration/troubleshooting.md" },
			],
		},
		{
			text: "Contributing",
			collapsible: true,
			items: [
				{ text: "Development", link: "/docs/contributing/development.md" },
				{ text: "Documentation", link: "/docs/contributing/documentation.md" },
				{ text: "Quick install app", link: "/docs/contributing/quick-install-app.md" },
				{ text: "Testing", link: "/docs/contributing/testing.md" },
				{ text: "Translations", link: "/docs/contributing/translations.md" },
			],
		},
		{
			text: "Community",
			collapsible: true,
			items: [
				{ text: "Hestia Nginx Cache", link: "/docs/community/hestia-nginx-cache.md" },
				{
					text: "Ioncube installer for Hestia",
					link: "/docs/community/ioncube-hestia-installer.md",
				},
				{ text: "Install script generator", link: "/docs/community/install-script-generator.md" },
			],
		},
		{
			text: "Reference",
			collapsible: true,
			items: [
				{ text: "API", link: "/docs/reference/api.md" },
				{ text: "CLI", link: "/docs/reference/cli.md" },
			],
		},
	];
}
