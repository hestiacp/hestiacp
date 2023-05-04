import postcssImport from 'postcss-import';
import postcssPathReplace from 'postcss-path-replace';
import postcssSize from 'postcss-size';
import cssnano from 'cssnano';
import postcssPresetEnv from 'postcss-preset-env';

export default {
	plugins: [
		postcssImport,
		postcssPathReplace({
			publicPath: '/webfonts/',
			matched: '../webfonts/',
			mode: 'replace',
		}),
		postcssSize,
		cssnano,
		postcssPresetEnv({
			autoprefixer: {
				flexbox: 'no-2009',
			},
			stage: 1,
		}),
	],
};
