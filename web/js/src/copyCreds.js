import { debounce } from './helpers';

// Monitor "Account" and "Password" inputs on "Add/Edit Mail Account"
// page and update the sidebar "Account" and "Password" inputs
export default function handleCopyCreds() {
	monitorAndUpdate('.js-account-input', '.js-account-output');
	monitorAndUpdate('.js-password-input', '.js-password-output');
}

function monitorAndUpdate(inputSelector, outputSelector) {
	const inputElement = document.querySelector(inputSelector);
	const outputElement = document.querySelector(outputSelector);

	if (!inputElement || !outputElement) {
		return;
	}

	function updateOutput(value) {
		const postfix = outputElement.dataset.postfix;
		if (value && postfix) {
			value += postfix;
		}
		outputElement.value = value;
	}

	inputElement.addEventListener(
		'input',
		debounce((evt) => updateOutput(evt.target.value)),
	);
	updateOutput(inputElement.value);
}
