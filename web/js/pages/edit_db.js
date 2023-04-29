// Updates database username dynamically, showing its prefix
App.Actions.DB.update_db_username_hint = function (input) {
	const hintElement = input.parentElement.querySelector('.hint');

	if (input.value.trim() === '') {
		hintElement.textContent = '';
	}

	hintElement.textContent = Alpine.store('globals').USER_PREFIX + input.value;
};

// Updates database name dynamically, showing its prefix
App.Actions.DB.update_db_databasename_hint = function (input) {
	const hintElement = input.parentElement.querySelector('.hint');

	if (input.value.trim() === '') {
		hintElement.textContent = '';
	}

	hintElement.textContent = Alpine.store('globals').USER_PREFIX + input.value;
};

// Listener that triggers database user hint updating
App.Listeners.DB.keypress_db_username = () => {
	const input = document.querySelector('input[name="v_dbuser"]');

	if (input.value.trim() != '') {
		App.Actions.DB.update_db_username_hint(input);
	}

	input.addEventListener(
		'input',
		Hestia.helpers.debounce((evt) => {
			App.Actions.DB.update_db_username_hint(evt.target);
		}, 100)
	);
};

// Listener that triggers database user hint updating
App.Listeners.DB.keypress_db_databasename = () => {
	const input = document.querySelector('input[name="v_database"]');

	// Remove prefix from the input if it exists (for editing)
	const prefixIndex = input.value.indexOf(Alpine.store('globals').USER_PREFIX);
	if (prefixIndex === 0) {
		input.value = input.value.slice(Alpine.store('globals').USER_PREFIX.length);
	}

	if (input.value.trim() != '') {
		App.Actions.DB.update_db_databasename_hint(input);
	}

	input.addEventListener(
		'input',
		Hestia.helpers.debounce((evt) => {
			App.Actions.DB.update_db_databasename_hint(evt.target);
		}, 100)
	);
};

//
// Page entry point
// Trigger listeners
App.Listeners.DB.keypress_db_username();
App.Listeners.DB.keypress_db_databasename();
