export default function handleClipboardCopy() {
	const copyInputs = document.querySelectorAll('.js-copy-input');
	const copyButtons = document.querySelectorAll('.js-copy-button');

	// Iterate over each input and button pair
	copyInputs.forEach((copyInput, index) => {
		let inputFocused = false;

		// Ensure corresponding button exists
		if (!copyButtons[index]) {
			return;
		}
		const copyButton = copyButtons[index];

		// Copy on focus and allow for partial selection
		copyInput.addEventListener('click', () => {
			if (!inputFocused) {
				copyInput.select();
				inputFocused = true;
				// Reset inputFocused when input loses focus
				copyInput.addEventListener(
					'blur',
					() => {
						inputFocused = false;
					},
					{ once: true },
				);
			}
		});

		// Copy to clipboard on button click
		copyButton.addEventListener('click', () => {
			navigator.clipboard.writeText(copyInput.value).then(() => {
				// Temporarily change button content
				const buttonIcon = copyButton.innerHTML;
				copyButton.innerHTML = 'Copied!';
				copyButton.disabled = true;

				// Revert button content after 2 seconds
				setTimeout(() => {
					copyButton.innerHTML = buttonIcon;
					copyButton.disabled = false;
				}, 2000);
			});
		});
	});
}
