// "Discard all mail" checkbox behavior on Add/Edit Mail Account pages
export default function handleDiscardAllMail() {
	const discardAllMailCheckbox = document.querySelector('.js-discard-all-mail');

	if (!discardAllMailCheckbox) {
		return;
	}

	discardAllMailCheckbox.addEventListener('click', () => {
		const forwardToTextarea = document.querySelector('.js-forward-to-textarea');
		const doNotStoreCheckbox = document.querySelector('.js-do-not-store-checkbox');

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
