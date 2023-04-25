// "Discard all mail" checkbox behavior
export default function handleDiscardAllMail() {
	const discardAllMailCheckbox = document.querySelector('.js-discard-all-mail');

	if (!discardAllMailCheckbox) return;

	discardAllMailCheckbox.addEventListener('click', () => {
		const forwardToTextarea = document.getElementById('v_fwd');
		const doNotStoreCheckbox = document.getElementById('v_fwd_for');

		if (discardAllMailCheckbox.checked) {
			// Disable "Forward to" textarea
			forwardToTextarea.disabled = true;

			// Check "Do not store forwarded mail" checkbox
			doNotStoreCheckbox.checked = true;

			// Hide "Do not store forwarded mail" checkbox container
			doNotStoreCheckbox.parentElement.classList.add('u-hidden');
		} else {
			// Enable "Forward to" textarea
			forwardToTextarea.disabled = false;

			// Show "Do not store forwarded mail" checkbox container
			doNotStoreCheckbox.parentElement.classList.remove('u-hidden');
		}
	});
}
