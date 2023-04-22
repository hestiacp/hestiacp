// Listen to form submit and update textarea if basic options are visible
document.querySelector('#vstobjects').addEventListener('submit', () => {
	const basicOptionsWrapper = document.querySelector('.js-basic-options');
	if (!basicOptionsWrapper.classList.contains('u-hidden')) {
		const advancedTextarea = document.querySelector('.js-advanced-textarea');
		const textInputs = document.querySelectorAll('#vstobjects input[type=text]');
		Hestia.helpers.updateTextareaWithInputValues(textInputs, advancedTextarea);
	}
});
