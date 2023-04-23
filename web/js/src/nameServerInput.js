// Attaches listeners to nameserver add and remove links to clone or remove the input
export default function handleNameServerInput() {
	// Add new name server input
	const addNsElem = document.querySelector('.js-add-ns');
	if (addNsElem) {
		addNsElem.addEventListener('click', () => addNsInput(addNsElem));
	}

	// Remove name server input
	document.querySelectorAll('.js-remove-ns').forEach((removeNsElem) => {
		removeNsElem.addEventListener('click', () => removeNsInput(removeNsElem));
	});
}

function addNsInput(addNsElem) {
	const currentNsInputs = document.querySelectorAll('input[name^=v_ns]');
	const inputCount = currentNsInputs.length;

	if (inputCount < 8) {
		const template = currentNsInputs[0].parentElement.cloneNode(true);
		const templateNsInput = template.querySelector('input');

		templateNsInput.removeAttribute('value');
		templateNsInput.name = `v_ns${inputCount + 1}`;
		addNsElem.before(template);
	}

	if (inputCount === 7) {
		addNsElem.classList.add('u-hidden');
	}
}

function removeNsInput(removeNsElem) {
	removeNsElem.parentElement.remove();
	const currentNsInputs = document.querySelectorAll('input[name^=v_ns]');
	currentNsInputs.forEach((input, index) => (input.name = `v_ns${index + 1}`));
	document.querySelector('.js-add-ns').classList.remove('u-hidden');
}
