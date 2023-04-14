applyRandomPassword = function (min_length = 16) {
	const passwordInput = document.querySelector('input[name=v_password]');
	if (passwordInput) {
		passwordInput.value = randomString(min_length);
		VE.helpers.recalculatePasswordStrength(passwordInput);
	}
};

App.Listeners.WEB.keypress_v_password = () => {
	const updateTimeout = (evt) => {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(() => {
			VE.helpers.recalculatePasswordStrength(evt.target);
		}, 100);
	};

	const passwordInput = document.querySelector('input[name="v_password"]');
	passwordInput.addEventListener('keypress', updateTimeout);
	passwordInput.addEventListener('input', updateTimeout);
};
App.Listeners.WEB.keypress_v_password();
