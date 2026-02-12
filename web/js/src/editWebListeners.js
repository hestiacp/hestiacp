// Simple hide/show input listeners specific to Edit Web Domain form
// TODO: Replace these with Alpine.js usage consistently
// NOTE: Some functions use inline styles, as Alpine.js also uses them
export default function handleEditWebListeners() {
	// Listen to "Web Statistics" select menu to hide/show
	// "Statistics Authorization" checkbox and inner fields
	const statsSelect = document.querySelector('.js-stats-select');
	const statsAuthContainers = document.querySelectorAll('.js-stats-auth');
	if (statsSelect && statsAuthContainers.length) {
		statsSelect.addEventListener('change', () => {
			if (statsSelect.value === 'none') {
				statsAuthContainers.forEach((container) => {
					container.style.display = 'none';
				});
			} else {
				statsAuthContainers.forEach((container) => {
					container.style.display = 'block';
				});
			}
		});
	}

	// Listen to "Enable domain redirection" radio items to show
	// additional inputs if radio with value "custom" is selected
	document.querySelectorAll('.js-redirect-custom-value').forEach((element) => {
		element.addEventListener('change', () => {
			const customRedirectFields = document.querySelector('.js-custom-redirect-fields');
			if (customRedirectFields) {
				if (element.value === 'custom') {
					customRedirectFields.classList.remove('u-hidden');
				} else {
					customRedirectFields.classList.add('u-hidden');
				}
			}
		});
	});

        // Listen to automatic certificate checkboxes to hide/show SSL textareas
        const toggleLetsEncryptCheckbox = document.querySelector('.js-toggle-lets-encrypt');
        const toggleCloudflareCheckbox = document.querySelector('.js-toggle-cloudflare-origin');
        const sslDetails = document.querySelector('.js-ssl-details');
        const updateSslDetailsVisibility = () => {
                if (!sslDetails) {
                        return;
                }
                const letsEncryptChecked = toggleLetsEncryptCheckbox && toggleLetsEncryptCheckbox.checked;
                const cloudflareChecked = toggleCloudflareCheckbox && toggleCloudflareCheckbox.checked;
                sslDetails.style.display = letsEncryptChecked || cloudflareChecked ? 'none' : 'block';
        };
        if (toggleLetsEncryptCheckbox) {
                toggleLetsEncryptCheckbox.addEventListener('change', () => {
                        if (toggleLetsEncryptCheckbox.checked && toggleCloudflareCheckbox && toggleCloudflareCheckbox.checked) {
                                toggleCloudflareCheckbox.checked = false;
                                toggleCloudflareCheckbox.dispatchEvent(new Event('change'));
                        }
                        updateSslDetailsVisibility();
                });
        }
        if (toggleCloudflareCheckbox) {
                toggleCloudflareCheckbox.addEventListener('change', () => {
                        if (toggleCloudflareCheckbox.checked && toggleLetsEncryptCheckbox && toggleLetsEncryptCheckbox.checked) {
                                toggleLetsEncryptCheckbox.checked = false;
                                toggleLetsEncryptCheckbox.dispatchEvent(new Event('change'));
                        }
                        updateSslDetailsVisibility();
                });
        }
        updateSslDetailsVisibility();

	// Listen to "Advanced Options -> Proxy Template" select menu to
	// show "Purge Nginx Cache" button if "caching" selected
	const proxyTemplateSelect = document.querySelector('.js-proxy-template-select');
	const clearCacheButton = document.querySelector('.js-clear-cache-button');
	if (proxyTemplateSelect && clearCacheButton) {
		proxyTemplateSelect.addEventListener('change', () => {
			// NOTE: Match "caching" and "caching-*" values
			if (proxyTemplateSelect.value === 'caching' || proxyTemplateSelect.value.match(/^caching-/)) {
				clearCacheButton.classList.remove('u-hidden');
			} else {
				clearCacheButton.classList.add('u-hidden');
			}
		});
	}
}
