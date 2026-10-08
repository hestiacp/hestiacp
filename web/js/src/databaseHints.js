import { debounce } from './helpers';

// Attach listener to database "Name" and "Username" fields to update their hints
export default function handleDatabaseHints() {
	const usernameInput = document.querySelector('.js-db-hint-username');
	const databaseNameInput = document.querySelector('.js-db-hint-database-name');

	if (!usernameInput || !databaseNameInput) {
		return;
	}

	removeUserPrefix(databaseNameInput);
	attachUpdateHintListener(usernameInput);
	attachUpdateHintListener(databaseNameInput);

	const typeSelect = document.getElementById('v_type');
	const hostSelect = document.getElementById('v_host');

	if (typeSelect && hostSelect) {
		filterHostOptions(typeSelect, hostSelect);
		typeSelect.addEventListener('change', () => filterHostOptions(typeSelect, hostSelect));
	}
}

// Remove prefix from "Database" input if it exists during initial load (for editing)
function removeUserPrefix(input) {
	const prefixIndex = input.value.indexOf(Alpine.store('globals').USER_PREFIX);
	if (prefixIndex === 0) {
		input.value = input.value.slice(Alpine.store('globals').USER_PREFIX.length);
	}
}

function attachUpdateHintListener(input) {
	if (input.value.trim() !== '') {
		updateHint(input);
	}

	input.addEventListener(
		'input',
		debounce((evt) => updateHint(evt.target)),
	);
}

function updateHint(input) {
	const hintElement = input.parentElement.querySelector('.hint');

	if (input.value.trim() === '') {
		hintElement.textContent = '';
	}

	hintElement.textContent = Alpine.store('globals').USER_PREFIX + input.value;
}

function filterHostOptions(typeSelect, hostSelect) {
	const selectedType = typeSelect.value;
	let hasVisibleSelectedOption = false;

	for (const option of hostSelect.options) {
		const types = option.getAttribute('data-types');
		if (types && types.split(',').includes(selectedType)) {
			option.style.display = '';
			option.disabled = false;
			if (option.selected) {
				hasVisibleSelectedOption = true;
			}
		} else {
			option.style.display = 'none';
			option.disabled = true;
			option.selected = false;
		}
	}

	if (!hasVisibleSelectedOption) {
		for (const option of hostSelect.options) {
			if (!option.disabled) {
				option.selected = true;
				break;
			}
		}
	}
}
