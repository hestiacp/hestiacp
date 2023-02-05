module.exports = {
	plugins: [
		require('postcss-import'),
		require('postcss-path-replace')({
			publicPath: '/webfonts/',
			matched: '../webfonts/',
			mode: 'replace',
		}),
		require('postcss-size'),
		require('cssnano'),
		require('postcss-preset-env')({
			autoprefixer: {
				flexbox: 'no-2009',
			},
			stage: 1,
		}),
	],
};
