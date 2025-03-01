import { debounce } from './helpers';

// Attach listener to DNS "Record" field to update its hint
export default function handleDnsRecordHint() {
	const recordInput = document.querySelector('.js-dns-record-input');

	if (!recordInput) {
		return;
	}

	if (recordInput.value.trim() !== '') {
		updateHint(recordInput);
	}

	recordInput.addEventListener(
		'input',
		debounce((evt) => updateHint(evt.target)),
	);
}

// Update DNS "Record" field hint
function updateHint(input) {
	const domainInput = document.querySelector('.js-dns-record-domain');
	const hintElement = input.parentElement.querySelector('.hint');
	let hint = input.value.trim();

	// Clear the hint if input is empty
	if (hint === '') {
		hintElement.textContent = '';
		return;
	}

	// Set domain name without rec in case of @ entries
	if (hint === '@') {
		hint = '';
	}

	// Don't show prefix if domain name equals rec value
	if (hint === domainInput.value) {
		hint = '';
	}

	// Add dot at the end if needed
	if (hint !== '' && hint.slice(-1) !== '.') {
		hint += '.';
	}

	hintElement.textContent = hint + domainInput.value;
}
