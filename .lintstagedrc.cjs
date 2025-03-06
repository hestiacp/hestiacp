module.exports = {
	// Run markdownlint on MD files
	'*.md': 'markdownlint-cli2 --fix',
	// Run Stylelint on CSS files
	'*.css': 'stylelint --fix --allow-empty-input',
	// Run Biome on TS, TSX, JS, JSX files
	'*.{ts,js}?(x)': 'biome lint --write --no-errors-on-unmatched',
	// Run Prettier where supported
	'*': 'prettier --write --ignore-unknown',
};
