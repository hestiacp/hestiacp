applyRandomPassword = function (min_length = 16) {
	const passwordInput = document.querySelector('.js-password-input');
	if (passwordInput) {
		passwordInput.value = randomString(min_length);
		VE.helpers.recalculatePasswordStrength(passwordInput);
	}
};
