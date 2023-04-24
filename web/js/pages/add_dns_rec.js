// Updates database DNS record dynamically, showing its full domain path
App.Actions.DB.update_dns_record_hint = (input) => {
	const domainInput = document.querySelector('input[name="v_domain"]');
	const hintElement = input.parentElement.querySelector('.hint');

	// Clean hint
	let hint = input.value.trim();

	if (hint === '') {
		hintElement.textContent = '';
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
};

// Listener that triggers dns record name hint updating
App.Listeners.DB.keypress_dns_rec_entry = () => {
	const input = document.querySelector('input[name="v_rec"]');

	if (input.value.trim() != '') {
		App.Actions.DB.update_dns_record_hint(input);
	}

	const updateTimeout = (evt) => {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(() => {
			App.Actions.DB.update_dns_record_hint(evt.target);
		}, 100);
	};

	input.addEventListener('keypress', updateTimeout);
	input.addEventListener('input', updateTimeout);
};

//
// Page entry point
// Trigger listeners
App.Listeners.DB.keypress_dns_rec_entry();
