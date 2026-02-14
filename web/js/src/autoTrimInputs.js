//trim leading / trailing spaces form input fields
export default function handleAutoTrimInputs() {
	document.querySelectorAll('input[type="text"]').forEach((input) => {
		input.addEventListener('change', function () {
			this.value = this.value.trim();
		});
	});
}
