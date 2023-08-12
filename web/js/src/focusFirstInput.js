// If no dialog is open, focus first input in main content form
// TODO: Replace this with autofocus attributes in the HTML
export default function focusFirstInput() {
	const openDialogs = document.querySelectorAll('dialog[open]');
	if (openDialogs.length === 0) {
		const input = document.querySelector(
			'#main-form .form-control:not([disabled]), #main-form .form-select:not([disabled])'
		);
		if (input) {
			input.focus();
		}
	}
}
