import { parseAndSortIpLists } from './helpers';

// Populates the "Data Source" select with various IP lists on the New IP List page
export default function handleIpListDataSource() {
	const dataSourceSelect = document.querySelector('.js-datasource-select');

	if (!dataSourceSelect) {
		return;
	}

	// Parse IP lists from HTML and sort them alphabetically
	const countryIpLists = parseAndSortIpLists(dataSourceSelect.dataset.countryIplists);
	const blacklistIpLists = parseAndSortIpLists(dataSourceSelect.dataset.blacklistIplists);

	// Add IP lists to the "Data Source" select
	addIPListsToSelect(dataSourceSelect, Alpine.store('globals').BLACKLIST, blacklistIpLists);
	addIPListsToSelect(dataSourceSelect, Alpine.store('globals').IPVERSE, countryIpLists);
}

function addIPListsToSelect(dataSourceSelect, label, ipLists) {
	// Add a disabled option as a label
	addOption(dataSourceSelect, label, '', true);

	// Add IP lists to the select element
	ipLists.forEach((ipList) => {
		addOption(dataSourceSelect, ipList.name, ipList.source, false);
	});
}

function addOption(element, text, value, disabled) {
	const option = document.createElement('option');
	option.text = text;
	option.value = value;
	if (disabled) {
		option.disabled = true;
	}
	element.append(option);
}
