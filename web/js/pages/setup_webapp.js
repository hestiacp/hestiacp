// eslint-disable-next-line @typescript-eslint/no-unused-vars
function applyRandomStringToTarget(target, min_length = 16) {
	document.querySelector(`#${target}`).value = randomString(min_length);
}
