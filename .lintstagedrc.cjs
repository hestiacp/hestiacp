module.exports = {
	// Run markdownlint on MD files
	'*.md': 'markdownlint-cli2-fix',
	// Run Stylelint on CSS files
	'*.css': 'stylelint --fix',
	// Run ESLint on TS, TSX, JS, JSX files
	'*.{ts,js}?(x)': 'eslint --fix',
	// Run Prettier everywhere
	'*': 'prettier --write --ignore-unknown',
};
