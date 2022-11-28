$(function () {
	$("#v_email").change(function () {
		if ($("#v_email_notify").prop("checked")) {
			document.getElementById("v_notify").value = document.getElementById("v_email").value;
		}
	});
	$("#v_email_notify").change(function () {
		if ($("#v_email_notify").prop("checked")) {
			document.getElementById("v_notify").value = document.getElementById("v_email").value;
		} else {
			document.getElementById("v_notify").value = "";
		}
	});
});

applyRandomString = function (min_length = 16) {
	$("input[name=v_password]").val(randomString2(min_length));
	App.Actions.WEB.update_password_meter();
};

App.Actions.WEB.update_password_meter = function () {
	var password = $('input[name="v_password"]').val();
	var min_small = new RegExp(/^(?=.*[a-z]).+$/);
	var min_cap = new RegExp(/^(?=.*[A-Z]).+$/);
	var min_num = new RegExp(/^(?=.*\d).+$/);
	var min_length = 8;
	var score = 0;

	if (password.length >= min_length) {
		score = score + 1;
	}
	if (min_small.test(password)) {
		score = score + 1;
	}
	if (min_cap.test(password)) {
		score = score + 1;
	}
	if (min_num.test(password)) {
		score = score + 1;
	}
	$(".password-meter").val(score);
};

App.Listeners.WEB.keypress_v_password = function () {
	var ref = $('input[name="v_password"]');
	ref.bind("keypress input", function (evt) {
		clearTimeout(window.frp_usr_tmt);
		window.frp_usr_tmt = setTimeout(function () {
			var elm = $(evt.target);
			App.Actions.WEB.update_password_meter(elm, $(elm).val());
		}, 100);
	});
};

App.Listeners.WEB.keypress_v_password();
