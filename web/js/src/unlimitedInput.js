export default function handleUnlimitedInput() {
	// Add listeners to "unlimited" input toggles
	document.querySelectorAll('.js-unlimited-toggle').forEach((toggleButton) => {
		const input = toggleButton.parentElement.querySelector('input');

		if (isUnlimitedValue(input.value)) {
			enableInput(input, toggleButton);
		} else {
			disableInput(input, toggleButton);
		}

		toggleButton.addEventListener('click', () => {
			toggleInput(input, toggleButton);
		});
	});
}

// Called on form submit to enable any disabled unlimited inputs
export function enableUnlimitedInputs() {
	document.querySelectorAll('input:disabled').forEach((input) => {
		if (isUnlimitedValue(input.value)) {
			input.disabled = false;
			input.value = 'unlimited';
		}
	});
}

function isUnlimitedValue(value) {
	const trimmedValue = value.trim();
	return trimmedValue === 'unlimited' || trimmedValue === Alpine.store('globals').UNLIMITED;
}

function enableInput(input, toggleButton) {
	toggleButton.classList.add('active');
	input.dataset.prevValue = input.value;
	input.value = Alpine.store('globals').UNLIMITED;
	input.disabled = true;
}

function disableInput(input, toggleButton) {
	toggleButton.classList.remove('active');
	const previousValue = input.dataset.prevValue ? input.dataset.prevValue.trim() : null;
	if (previousValue) {
		input.value = previousValue;
	}

	if (isUnlimitedValue(input.value)) {
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
