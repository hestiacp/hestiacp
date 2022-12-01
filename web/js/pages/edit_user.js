function applyRandomString(min_length = 16) {
	document.querySelector('input[name=v_password]').value = randomString2(min_length);
	App.Actions.WEB.update_password_meter();
}

App.Actions.WEB.update_password_meter = () => {
	/**
	 * @type string
	 */
	const password = document.querySelector('input[name=v_password]').value;

	const validations = [
		password.length >= 8, // Min length of 8
		password.search(/[a-z]/) > -1, // Contains 1 lowercase letter
		password.search(/[A-Z]/) > -1, // Contains 1 uppercase letter
		password.search(/[0-9]/) > -1, // Contains 1 number
	];
	const strength = validations.reduce((acc, cur) => acc + cur, 0);

	document.querySelector('.password-meter').value = strength;
};

App.Listeners.WEB.keypress_v_password = () => {
	const updateTimeout = (evt) => {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(() => {
			App.Actions.WEB.update_password_meter(evt.target, evt.target.value);
		}, 100);
	};

	const passwordInput = document.querySelector('input[name="v_password"]');
	passwordInput.addEventListener('keypress', updateTimeout);
	passwordInput.addEventListener('input', updateTimeout);
};
App.Listeners.WEB.keypress_v_password();

(function () {
	$('.js-add-ns-button').click(function () {
		var n = $('input[name^=v_ns]').length;
		if (n < 8) {
			var t = $($('input[name=v_ns1]').parents('div')[0]).clone(true, true);
			t.find('input').attr({ value: '', name: 'v_ns' + (n + 1) });
			t.find('span').show();
			$('.js-add-ns').before(t);
		}
		if (n == 7) {
			$('.js-add-ns').addClass('u-hidden');
		}
	});

	$('.js-remove-ns').click(function () {
		$(this).parents('div')[0].remove();
		$('input[name^=v_ns]').each(function (i, ns) {
			$(ns).attr({ name: 'v_ns' + (i + 1) });
			i < 2 ? $(ns).parent().find('span').hide() : $(ns).parent().find('span').show();
		});
		$('.js-add-ns').removeClass('u-hidden');
	});

	$('input[name^=v_ns]').each(function (i, ns) {
		i < 2 ? $(ns).parent().find('span').hide() : $(ns).parent().find('span').show();
	});
})();
