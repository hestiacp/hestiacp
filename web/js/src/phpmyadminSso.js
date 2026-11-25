import { createConfirmationDialog, showSpinner } from './helpers';

export default function handlePhpMyAdminSso() {
	const settings = document.querySelector('[data-pma-sso-token]');
	if (!settings) {
		return;
	}

	const messages = JSON.parse(settings.dataset.messages);
	let pending = false;
	for (const button of document.querySelectorAll('[data-pma-sso-database]')) {
		button.addEventListener('click', async () => {
			if (pending) {
				return;
			}
			pending = true;
			showSpinner();

			let url;
			let error = 'unknown';
			try {
				const database = button.dataset.pmaSsoDatabase;
				const launch = await requestSso('/list/db/', {
					pma_sso: 1,
					token: settings.dataset.pmaSsoToken,
					database,
				});
				if (launch.error) {
					error = launch.error;
				} else {
					const endpoint = new URL(launch.endpoint);
					if (endpoint.protocol !== 'https:' || endpoint.hostname !== window.location.hostname) {
						throw new Error('Unexpected phpMyAdmin endpoint');
					}
					const result = await requestSso(endpoint, launch.parameters);
					if (result.error) {
						error = result.error;
					} else if (Number.isInteger(result.server) && result.server > 0) {
						url = new URL('index.php', endpoint);
						url.searchParams.set('server', result.server);
						url.searchParams.set('db', database);
					}
				}
			} catch {
				error = 'unknown';
			} finally {
				pending = false;
				document.querySelector('.js-spinner').classList.remove('active');
			}

			// The dialog helper accepts HTML. Encode the translated text before passing it in.
			const message = document.createElement('span');
			if (!url) {
				message.textContent = Object.hasOwn(messages, error) ? messages[error] : messages.unknown;
				createConfirmationDialog({ message: message.innerHTML });
				return;
			}

			// Open only after the session is ready. Some browsers require a fresh click.
			const tab = window.open('about:blank', '_blank');
			if (tab) {
				tab.opener = null;
				tab.location.replace(url.href);
				return;
			}
			message.textContent = messages.ready;
			createConfirmationDialog({
				message: message.innerHTML,
				targetUrl: url.href,
				newTab: true,
				confirmText: messages.open,
			});
		});
	}
}

async function requestSso(url, parameters) {
	const response = await fetch(url, {
		method: 'POST',
		body: new URLSearchParams(parameters),
		credentials: 'include',
		redirect: 'error',
		signal: AbortSignal.timeout(25000),
	});
	if (!response.ok) {
		throw new Error('phpMyAdmin sign-on request failed');
	}
	return response.json();
}
