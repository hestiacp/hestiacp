// Populates the "Data Source" select with various IP lists on the New IP List page
export default function handleIpListDataSource() {
	const dataSourceSelect = document.querySelector('.js-datasource-select');

	if (!dataSourceSelect) {
		return;
	}

	// Parse IP lists from HTML and sort them alphabetically
	const countryIplists = parseAndSortIplists(dataSourceSelect.dataset.countryIplists);
	const blacklistIplists = parseAndSortIplists(dataSourceSelect.dataset.blacklistIplists);

	// Add IP lists to the "Data Source" select
	addIPListsToSelect(dataSourceSelect, Alpine.store('globals').BLACKLIST, blacklistIplists);
	addIPListsToSelect(dataSourceSelect, Alpine.store('globals').IPVERSE, countryIplists);
}

function parseAndSortIplists(iplistsData) {
	const iplists = JSON.parse(iplistsData || '[]');
	return iplists.sort((a, b) => a.name.localeCompare(b.name));
}

function addIPListsToSelect(dataSourceSelect, label, iplists) {
	// Add a disabled option as a label
	addOption(dataSourceSelect, label, '', true);

	// Add IP lists to the select element
	iplists.forEach((iplist) => {
		addOption(dataSourceSelect, iplist.name, iplist.source, false);
	});
}

function addOption(element, text, value, disabled) {
	const option = document.createElement('option');
	option.text = text;
	option.value = value;
	if (disabled) {
		option.disabled = true;
	}
	element.appendChild(option);
}
