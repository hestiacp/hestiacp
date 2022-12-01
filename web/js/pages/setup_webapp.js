function applyRandomStringToTarget(target, min_length = 16) {
	document.querySelector(`#${target}`).value = randomString2(min_length);
}
