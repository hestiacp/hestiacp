// Attaches listeners to nameserver add and remove links to clone or remove the input
export default function handleNameServerInput() {
	// Add new name server input
	const addNsButton = document.querySelector('.js-add-ns');
	if (addNsButton) {
		addNsButton.addEventListener('click', () => addNsInput(addNsButton));
	}

	// Remove name server input
	document.querySelectorAll('.js-remove-ns').forEach((removeNsElem) => {
		removeNsElem.addEventListener('click', () => removeNsInput(removeNsElem));
	});
}

function addNsInput(addNsButton) {
	const currentNsInputs = document.querySelectorAll('input[name^=v_ns]');
	const inputCount = currentNsInputs.length;

	if (inputCount < 8) {
		const template = currentNsInputs[0].parentElement.cloneNode(true);
		const templateNsInput = template.querySelector('input');

		templateNsInput.removeAttribute('value');
		templateNsInput.name = `v_ns${inputCount + 1}`;
		addNsButton.before(template);
	}

	if (inputCount === 7) {
		addNsButton.classList.add('u-hidden');
	}
}

function removeNsInput(removeNsElement) {
	removeNsElement.parentElement.remove();
	const currentNsInputs = document.querySelectorAll('input[name^=v_ns]');
	currentNsInputs.forEach((input, index) => (input.name = `v_ns${index + 1}`));
	document.querySelector('.js-add-ns').classList.remove('u-hidden');
}
