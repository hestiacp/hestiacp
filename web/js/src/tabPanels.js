// Tabs behavior (used on cron pages)
export default function handleTabPanels() {
	const tabs = document.querySelector('.js-tabs');

	if (!tabs) {
		return;
	}

	const tabItems = tabs.querySelectorAll('.tabs-item');
	const panels = tabs.querySelectorAll('.tabs-panel');
	tabItems.forEach((tab) => {
		tab.addEventListener('click', (event) => {
			// Reset state
			panels.forEach((panel) => {
				panel.hidden = true;
			});
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
