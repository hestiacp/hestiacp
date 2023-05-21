import { debounce } from './helpers';

// Handle "Custom document root -> Directory" hint on Edit Web Domain page
export default function handleDocRootHint() {
	const domainSelect = document.querySelector('.js-custom-docroot-domain');
	const dirInput = document.querySelector('.js-custom-docroot-dir');
	const prepathHiddenInput = document.querySelector('.js-custom-docroot-prepath');
	const docRootHint = document.querySelector('.js-custom-docroot-hint');

	if (!domainSelect || !dirInput || !prepathHiddenInput || !docRootHint) {
		return;
	}

	// Set initial hint on page load
	updateDocRootHint();

	// Add input listeners
	dirInput.addEventListener('input', debounce(updateDocRootHint));
	domainSelect.addEventListener('change', updateDocRootHint);

	// Update hint value
	function updateDocRootHint() {
		const prepath = prepathHiddenInput.value;
		const domain = domainSelect.value;
		const folder = dirInput.value;

		docRootHint.textContent = `${prepath}${domain}/public_html/${folder}`;
	}
}
