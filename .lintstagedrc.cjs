module.exports = {
	// Ignore certain directories to prevent crashes
	ignores: ['bin/**', 'install/**', 'src/**', 'web/**', 'func/**', 'test/**', 'docs/.vitepress/cache/**'],
	// Run markdownlint on MD files
	'*.md': 'markdownlint-cli2 --fix',
	// Run Biome on certain file types
	'*.{ts,js,css}?(x)': 'biome lint --write --no-errors-on-unmatched',
	// Run Prettier on supported file types
	'*.{js,ts,jsx,tsx,css,scss,html,json,yml,yaml,md,php,sh,sql,vue}': 'prettier --write',
};
