module.exports = {
	root: true,
	parserOptions: {
		sourceType: 'module',
		ecmaVersion: 'latest',
	},
	extends: [
		'eslint:recommended',
		'plugin:editorconfig/noconflict',
		'plugin:import/recommended',
		'prettier',
	],
	plugins: ['editorconfig', 'import'],
	ignorePatterns: ['*.cjs'],
	env: {
		browser: true,
		es2021: true,
	},
	globals: {
		Alpine: 'readonly',
	},
	rules: {
		'no-unused-vars': [
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
