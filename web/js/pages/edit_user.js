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

(function () {
	$('.js-add-ns').click(function () {
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
