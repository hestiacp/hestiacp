// Adds listeners for "unlimited" input toggles
export default function initUnlimitedInput() {
	document.querySelectorAll('.js-unlimited-toggle').forEach((toggleButton) => {
		const input = toggleButton.parentElement.querySelector('input');

		if (Alpine.store('globals').isUnlimitedValue(input.value)) {
			enableUnlimitedInput(input, toggleButton);
		} else {
			disableUnlimitedInput(input, toggleButton);
		}

		toggleButton.addEventListener('click', () => {
			toggleUnlimitedInput(input, toggleButton);
		});
	});
}

function enableUnlimitedInput(input, toggleButton) {
	toggleButton.classList.add('active');
	input.dataset.prevValue = input.value;
	input.value = Alpine.store('globals').UNLIM_TRANSLATED_VALUE;
	input.disabled = true;
}

function disableUnlimitedInput(input, toggleButton) {
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

function toggleUnlimitedInput(input, toggleButton) {
	if (toggleButton.classList.contains('active')) {
		disableUnlimitedInput(input, toggleButton);
	} else {
		enableUnlimitedInput(input, toggleButton);
	}
}
