module.exports = {
	root: true,
	parser: '@typescript-eslint/parser',
	parserOptions: {
		sourceType: 'module',
		ecmaVersion: 'latest',
	},
	extends: [
		'eslint:recommended',
		'plugin:@typescript-eslint/recommended',
		'plugin:editorconfig/noconflict',
		'prettier',
	],
	plugins: ['editorconfig', '@typescript-eslint'],
	ignorePatterns: ['*.cjs'],
	env: {
		browser: true,
		es2021: true,
	},
	globals: {
		$: 'readonly',
		jQuery: 'readonly',
		App: 'readonly',
	},
	rules: {
		// Set those as warnings instead. They should be fixed at some point
		'@typescript-eslint/no-unused-vars': 'warn',
		'@typescript-eslint/no-empty-function': 'warn',
		'@typescript-eslint/no-this-alias': 'warn',
		'no-redeclare': 'off',
		'no-undef': 'off',
	},
};
