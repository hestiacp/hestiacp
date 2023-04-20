const tabs = document.querySelector('.js-tabs');
if (tabs) {
	const tabItems = tabs.querySelectorAll('.tabs-item');
	const panels = tabs.querySelectorAll('.tabs-panel');
	tabItems.forEach((tab) => {
		tab.addEventListener('click', (event) => {
			// Reset state
			panels.forEach((panel) => (panel.hidden = true));
			tabItems.forEach((tab) => {
				tab.setAttribute('aria-selected', false);
				tab.setAttribute('tabindex', -1);
			});

			// Show the selected panel
			const tabId = event.target.getAttribute('id');
			const panel = document.querySelector(`[aria-labelledby="${tabId}"]`);
			panel.hidden = false;

			// Mark the selected tab as active
			event.target.setAttribute('aria-selected', true);
			event.target.setAttribute('tabindex', 0);
			event.target.focus();
		});
	});
}

document.querySelectorAll('.js-generate-cron').forEach((button) => {
	button.addEventListener('click', () => {
		const fieldset = button.closest('fieldset');
		const inputNames = ['min', 'hour', 'day', 'month', 'wday'];

		inputNames.forEach((inputName) => {
			const value = fieldset.querySelector(`[name=h_${inputName}]`).value;
			const formInput = document.querySelector(`#vstobjects input[name=v_${inputName}]`);

			formInput.value = value;
			formInput.classList.add('highlighted');

			formInput.addEventListener(
				'transitionend',
				() => {
					formInput.classList.remove('highlighted');
				},
				{ once: true }
			);
		});
	});
});
