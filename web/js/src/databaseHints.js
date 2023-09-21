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
