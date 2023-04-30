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
		'plugin:import/recommended',
		'prettier',
	],
	plugins: ['editorconfig', '@typescript-eslint', 'import'],
	ignorePatterns: ['*.cjs'],
	env: {
		browser: true,
		es2021: true,
	},
	globals: {
		Hestia: 'readonly',
		Alpine: 'readonly',
	},
	rules: {
		'@typescript-eslint/no-unused-vars': [
			'error',
			{
				argsIgnorePattern: '^_',
				varsIgnorePattern: '^_',
				caughtErrorsIgnorePattern: '^_',
			},
		],
		'import/order': [
			'error',
			{
				groups: ['builtin', 'external', 'internal', 'parent', 'sibling', 'index', 'object', 'type'],
			},
		],
		'no-console': 'error',
		'prefer-const': 'error',
		'import/no-unresolved': 'off',
	},
};
