module.exports = {
	// Plugins
	plugins: [
		'@prettier/plugin-php',
		'prettier-plugin-nginx',
		'prettier-plugin-sh',
		'prettier-plugin-sql',
	],
	// PHP Settings
	phpVersion: '8.2',
	braceStyle: '1tbs',
	endOfLine: 'lf',

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
