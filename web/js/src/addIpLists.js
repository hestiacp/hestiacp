import { parseAndSortIpLists } from './helpers';

// Populates the "IP address / IP list" select with created IP lists
// on the Add Firewall Rule page
export default function handleAddIpLists() {
	const ipListSelect = document.querySelector('.js-ip-list-select');

	if (!ipListSelect) {
		return;
	}

	const ipSetLists = parseAndSortIpLists(ipListSelect.dataset.ipsetLists);

	const headerOption = document.createElement('option');
	headerOption.textContent = 'IPset IP Lists';
	headerOption.disabled = true;
	ipListSelect.appendChild(headerOption);

	ipSetLists.forEach((ipSet) => {
		const ipOption = document.createElement('option');
		ipOption.textContent = ipSet.name;
		ipOption.value = `ipset:${ipSet.name}`;
		ipListSelect.appendChild(ipOption);
	});
}
