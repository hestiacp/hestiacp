module.exports = {
	// Run markdownlint on MD files
	'*.md': 'markdownlint-cli2 --fix',
	// Run Biome on certain file types
	'*.{ts,js,css}?(x)': 'biome lint --write --no-errors-on-unmatched',
	// Run Prettier where supported
	'*': 'prettier --write --ignore-unknown',
};
