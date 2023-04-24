// Updates database username dynamically, showing its prefix
App.Actions.DB.update_db_username_hint = function (input) {
	const hintElement = input.parentElement.querySelector('.hint');

	if (input.value.trim() === '') {
		hintElement.textContent = '';
	}

	hintElement.textContent = Alpine.store('globals').DB_USER_PREFIX + input.value;
};

// Updates database name dynamically, showing its prefix
App.Actions.DB.update_db_databasename_hint = function (input) {
	const hintElement = input.parentElement.querySelector('.hint');

	if (input.value.trim() === '') {
		hintElement.textContent = '';
	}

	hintElement.textContent = Alpine.store('globals').DB_DBNAME_PREFIX + input.value;
};

// Listener that triggers database user hint updating
App.Listeners.DB.keypress_db_username = () => {
	const input = document.querySelector('input[name="v_dbuser"]');

	if (input.value.trim() != '') {
		App.Actions.DB.update_db_username_hint(input);
	}

	const updateTimeout = (evt) => {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(() => {
			App.Actions.DB.update_db_username_hint(evt.target);
		}, 100);
	};

	input.addEventListener('keypress', updateTimeout);
	input.addEventListener('input', updateTimeout);
};

// Listener that triggers database user hint updating
App.Listeners.DB.keypress_db_databasename = () => {
	const input = document.querySelector('input[name="v_database"]');
	const prefixIndex = input.value.indexOf(Alpine.store('globals').DB_DBNAME_PREFIX);

	if (prefixIndex === 0) {
		input.value = input.value.slice(Alpine.store('globals').DB_DBNAME_PREFIX.length);
	}

	if (input.value.trim() != '') {
		App.Actions.DB.update_db_databasename_hint(input);
	}

	const updateTimeout = (evt) => {
		clearTimeout(window.frp_dbn_tmt);
		window.frp_dbn_tmt = setTimeout(() => {
			App.Actions.DB.update_db_databasename_hint(evt.target);
		}, 100);
	};

	input.addEventListener('keypress', updateTimeout);
	input.addEventListener('input', updateTimeout);
};

//
// Page entry point
// Trigger listeners
App.Listeners.DB.keypress_db_username();
App.Listeners.DB.keypress_db_databasename();
