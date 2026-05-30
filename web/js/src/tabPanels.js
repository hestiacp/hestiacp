// Tabs behavior (used on cron pages)
export default function handleTabPanels() {
	const tabGroups = document.querySelectorAll('.js-tabs');

	if (!tabGroups.length) {
		return;
	}

	tabGroups.forEach((tabs) => {
		const tabItems = tabs.querySelectorAll('.tabs-item');
		const panels = tabs.querySelectorAll('.tabs-panel');
		tabItems.forEach((tab) => {
			tab.addEventListener('click', (event) => {
				const selectedTab = event.target.closest('.tabs-item');
				if (!selectedTab) {
					return;
				}

				// Reset state
				panels.forEach((panel) => {
					panel.hidden = true;
				});
				tabItems.forEach((tab) => {
					tab.setAttribute('aria-selected', false);
					tab.setAttribute('tabindex', -1);
				});

				// Show the selected panel
				const tabId = selectedTab.getAttribute('id');
				const panel = tabs.querySelector(`[aria-labelledby="${tabId}"]`);
				if (!panel) {
					return;
				}
				panel.hidden = false;

				// Mark the selected tab as active
				selectedTab.setAttribute('aria-selected', true);
				selectedTab.setAttribute('tabindex', 0);
				selectedTab.focus();
			});
		});
	});
}
