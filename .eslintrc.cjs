module.exports = {
	root: true,
	parserOptions: {
		ecmaVersion: 'latest'
	},
	extends: ['eslint:recommended', 'plugin:editorconfig/noconflict', 'prettier'],
	plugins: ['editorconfig'],
	ignorePatterns: ['*.cjs'],
	env: {
		browser: true,
		es2021: true
	},
	globals: {
		$: 'readonly',
		jQuery: 'readonly',
		App: 'readonly'
	},
	rules: {
		'no-unused-vars': 'off',
		'no-undef': 'off',
		'no-redeclare': 'off'
	}
};
