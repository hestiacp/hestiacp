export default function handleLaravelToolkit() {
	const activateTab = (tabId) => {
		const tab = document.getElementById(tabId);
		if (tab?.classList.contains('tabs-item')) {
			tab.click();
		}
	};

	document.querySelectorAll('.js-laravel-secret-toggle').forEach((button) => {
		button.addEventListener('click', () => {
			const targetId = button.getAttribute('data-target');
			const input = document.getElementById(targetId);

			if (!input) {
				return;
			}

			const isHidden = input.getAttribute('type') === 'password';
			const icon = document.createElement('i');
			icon.className = isHidden ? 'fas fa-eye-slash icon-purple' : 'fas fa-eye icon-purple';

			input.setAttribute('type', isHidden ? 'text' : 'password');
			button.replaceChildren(
				icon,
				document.createTextNode(isHidden ? button.dataset.labelHide : button.dataset.labelShow),
			);
		});
	});

	document.querySelectorAll('a[href^="#tab-laravel-"]').forEach((link) => {
		link.addEventListener('click', () => {
			activateTab(link.getAttribute('href').slice(1));
		});
	});

	if (window.location.hash.startsWith('#tab-laravel-')) {
		activateTab(window.location.hash.slice(1));
	}
}
