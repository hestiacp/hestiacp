applyRandomStringToTarget = function (target, min_length = 16) {
	var elm = document.getElementById(target);
	$(elm).val(randomString2(min_length));
};
