module.exports = {
	// Plugins
	plugins: [
		'@prettier/plugin-php',
		'prettier-plugin-nginx',
		'prettier-plugin-sh',
		'prettier-plugin-sql',
	],
	pluginSearchDirs: ['.'],
	// PHP Settings
	phpVersion: '8.1',
	braceStyle: '1tbs',
	// Overrides for some files
	overrides: [
		// JavaScript files
		{
			files: ['*.{js,cjs}'],
			options: {
				singleQuote: true,
			},
		},
		// Hestia CLI
		{
			files: ['bin/v-*', 'src/deb/*/{postinst,preinst,hestia,postrm}', 'install/common/api/*'],
			options: {
				parser: 'sh',
			},
		},
		// Nginx config
		{
			files: ['**/nginx/*.inc', '**/nginx/*.conf'],
			options: {
				parser: 'nginx',
				wrapParameters: false,
			},
		},
	],
};
