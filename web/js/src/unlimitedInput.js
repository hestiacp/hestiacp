// Adds listeners for "unlimited" input toggles
export default function handleUnlimitedInput() {
	document.querySelectorAll('.js-unlimited-toggle').forEach((toggleButton) => {
		const input = toggleButton.parentElement.querySelector('input');

		if (Alpine.store('globals').isUnlimitedValue(input.value)) {
			enableInput(input, toggleButton);
		} else {
			disableInput(input, toggleButton);
		}

		toggleButton.addEventListener('click', () => {
			toggleInput(input, toggleButton);
		});
	});
}

function enableInput(input, toggleButton) {
	toggleButton.classList.add('active');
	input.dataset.prevValue = input.value;
	input.value = Alpine.store('globals').UNLIM_TRANSLATED_VALUE;
	input.disabled = true;
}

function disableInput(input, toggleButton) {
	toggleButton.classList.remove('active');
	const previousValue = input.dataset.prevValue ? input.dataset.prevValue.trim() : null;
	if (previousValue) {
		input.value = previousValue;
	}
	if (Alpine.store('globals').isUnlimitedValue(input.value)) {
		input.value = '0';
	}
	input.disabled = false;
}

function toggleInput(input, toggleButton) {
	if (toggleButton.classList.contains('active')) {
		disableInput(input, toggleButton);
	} else {
		enableInput(input, toggleButton);
	}
}
