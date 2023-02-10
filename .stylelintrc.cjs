module.exports = {
	extends: ['stylelint-config-standard'],
	rules: {
		'selector-class-pattern': null,
		'no-descending-specificity': null,
		'block-no-empty': null,
		'declaration-block-no-shorthand-property-overrides': null,
		// Seems to be broken a bit, but would be nice to have
		// 'declaration-property-value-no-unknown': true,
	},
};
