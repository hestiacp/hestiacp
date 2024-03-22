import { defineConfig } from 'vitepress';
import { version } from '../../package.json';
export default defineConfig({
	lang: 'en-US',
	base: '/hestiamb.github.io/',
	title: 'Hestia 服务器控制面板',
	description: '开源网络服务器控制面板.',

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

		editLink: {
			pattern: 'https://github.com/hestiacp/hestiacp/edit/main/docs/:path',
			text: '在 GitHub 上编辑此页面',
		},

		footer: {
			message: '根据 GPLv3 许可证发布',
			copyright: '版权所有 © 2019-永久 Hestia 控制面板',
		},

		docFooter: {
			prev: '上一页',
			next: '下一页'
		  },

		outline: {
			label: '页面导航'
		  },

		lastUpdated: {
			text: '最后更新于',
			formatOptions: {
			  dateStyle: 'short',
			  timeStyle: 'medium'
			}
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
		{ text: '面板特点介绍', link: '/features' },
		{ text: '安装', link: '/install' },
		{ text: '安装文档', link: '/docs/introduction/getting-started', activeMatch: '/docs/' },
		{ text: '团队', link: '/team' },
		{ text: '演示', link: 'https://demo.hestiacp.com:8083/' },
		{ text: '中文论坛', link: 'https://bbs.hestiamb.org/' },
		{ text: '赞助', link: '/donate' },
		{
			text: `v${version}`,
			items: [
				{
					text: '更新日志',
					link: 'https://github.com/hestiacp/hestiacp/blob/main/CHANGELOG.md',
				},
				{
					text: '为开发hestia的贡献名单',
					link: 'https://github.com/hestiacp/hestiacp/blob/main/CONTRIBUTING.md',
				},
				{
					text: '安全策略',
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
			text: '面板特点介绍',
			collapsed: false,
			items: [
				{ text: '开始', link: '/docs/introduction/getting-started' },
				{ text: '部署建议', link: '/docs/introduction/best-practices' },
			],
		},
		{
			text: '用户指南',
			collapsed: false,
			items: [
				{ text: '账户', link: '/docs/user-guide/account' },
				{ text: '备份', link: '/docs/user-guide/backups' },
				{ text: 'Cron定时任务', link: '/docs/user-guide/cron-jobs' },
				{ text: '数据库', link: '/docs/user-guide/databases' },
				{ text: 'DNS', link: '/docs/user-guide/dns' },
				{ text: '文件管理器', link: '/docs/user-guide/file-manager' },
				{ text: '邮件系统', link: '/docs/user-guide/mail-domains' },
				{ text: '通知', link: '/docs/user-guide/notifications' },
				{ text: '软件包', link: '/docs/user-guide/packages' },
				{ text: '统计查看', link: '/docs/user-guide/statistics' },
				{ text: '用户', link: '/docs/user-guide/users' },
				{ text: 'Web 网站添加', link: '/docs/user-guide/web-domains' },
			],
		},
		{
			text: '服务器管理',
			collapsed: false,
			items: [
				{ text: '备份和恢复', link: '/docs/server-administration/backup-restore' },
				{ text: '配置', link: '/docs/server-administration/configuration' },
				{ text: '自定义主题', link: '/docs/server-administration/customisation' },
				{ text: '数据库设置 & 数据库管理', link: '/docs/server-administration/databases' },
				{ text: 'DNS集群 & DNS安全扩展', link: '/docs/server-administration/dns' },
				{ text: '电子邮件', link: '/docs/server-administration/email' },
				{ text: '文件管理器', link: '/docs/server-administration/file-manager' },
				{ text: '防火墙', link: '/docs/server-administration/firewall' },
				{ text: '服务器操作系统升级', link: '/docs/server-administration/os-upgrades' },
				{ text: 'Hestia API 介绍', link: '/docs/server-administration/rest-api' },
				{ text: '服务器配置SSL证书', link: '/docs/server-administration/ssl-certificates' },
				{ text: 'Web模板和缓存及PHP模块安装', link: '/docs/server-administration/web-templates' },
				{ text: 'Thestia自带命令故障排除', link: '/docs/server-administration/troubleshooting' },
				{ text: '快速安装应用程序', link: '/docs/contributing/quick-install-app' },
			],
		},
		{
			text: '开发者文档',
			collapsed: false,
			items: [
				{ text: '构建包', link: '/docs/contributing/building' },
				{ text: '开发', link: '/docs/contributing/development' },
				{ text: '文档', link: '/docs/contributing/documentation' },
				{ text: 'beta版本测试', link: '/docs/contributing/testing' },
				{ text: '为官方做出贡献', link: '/docs/contributing/translations' },
			],
		},
		{
			text: '开源社区贡献介绍',
			collapsed: false,
			items: [
				{ text: 'Hestia Nginx 缓存', link: '/docs/community/hestia-nginx-cache' },
				{text: 'Hestia的php 扩展 Ioncube 安装程序', link: '/docs/community/ioncube-hestia-installer',},
				{ text: '自动脚本安装 Hestia面板 生成器', link: '/docs/community/install-script-generator' },
			],
		},
		{
			text: 'CLI命令系统和API接口介绍',
			collapsed: false,
			items: [
				{ text: 'API应用程序接口', link: '/docs/reference/api' },
				{ text: 'CLI命令行介绍', link: '/docs/reference/cli' },
			],
		},
	];
}

