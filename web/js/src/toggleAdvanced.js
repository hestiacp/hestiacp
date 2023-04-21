import { updateTextareaWithInputValues } from './helpers.js';

// Add listeners to .js-toggle-options buttons
export default function initToggleAdvanced() {
	document.querySelectorAll('.js-toggle-options').forEach((toggleOptionsButton) => {
		toggleOptionsButton.addEventListener('click', toggleOptions);
	});
}

// Toggle between basic and advanced options.
// When switching from basic to advanced, the textarea is updated with the values from the inputs
function toggleOptions() {
	const advancedOptionsWrapper = document.querySelector('.js-advanced-options');
	const basicOptionsWrapper = document.querySelector('.js-basic-options');

	if (advancedOptionsWrapper.classList.contains('u-hidden')) {
		advancedOptionsWrapper.classList.remove('u-hidden');
		basicOptionsWrapper.classList.add('u-hidden');
		updateAdvancedTextarea();
	} else {
		advancedOptionsWrapper.classList.add('u-hidden');
		basicOptionsWrapper.classList.remove('u-hidden');
	}
}

// Update the advanced textarea with input values
function updateAdvancedTextarea() {
	const advancedTextarea = document.querySelector('.js-advanced-textarea');
	const textInputs = document.querySelectorAll('#vstobjects input[type=text]');
	updateTextareaWithInputValues(textInputs, advancedTextarea);
}
