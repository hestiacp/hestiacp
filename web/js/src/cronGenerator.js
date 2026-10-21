// Copies values from cron generator fields to main cron fields when "Generate" is clicked
export default function handleCronGenerator() {
	setTimeout(initCronGenerator, 0);

	document.querySelectorAll('.js-generate-cron').forEach((button) => {
		button.addEventListener('click', () => {
			const fieldset = button.closest('fieldset');
			const inputNames = ['min', 'hour', 'day', 'month', 'wday'];

			inputNames.forEach((inputName) => {
				const value = fieldset.querySelector(`[name=h_${inputName}]`).value;
				const formInput = document.querySelector(`#main-form input[name=v_${inputName}]`);

				formInput.value = value;
				formInput.classList.add('highlighted');

				formInput.addEventListener(
					'transitionend',
					() => {
						formInput.classList.remove('highlighted');
					},
					{ once: true },
				);
			});
		});
	});
}

function initCronGenerator() {
	const mainForm = document.getElementById('main-form');
	if (!mainForm) return;

	const vMin = mainForm.querySelector('input[name="v_min"]')?.value;
	const vHour = mainForm.querySelector('input[name="v_hour"]')?.value;
	const vDay = mainForm.querySelector('input[name="v_day"]')?.value;
	const vMonth = mainForm.querySelector('input[name="v_month"]')?.value;
	const vWday = mainForm.querySelector('input[name="v_wday"]')?.value;

	if (!vMin) return;

	const tabs = [
		{
			id: 'tab-one',
			condition: vHour === '*' && vDay === '*' && vMonth === '*' && vWday === '*',
			fields: { h_min: vMin },
		},
		{
			id: 'tab-two',
			condition: vDay === '*' && vMonth === '*' && vWday === '*',
			fields: { h_hour: vHour, h_min: vMin },
		},
		{
			id: 'tab-three',
			condition: vMonth === '*' && vWday === '*',
			fields: { h_day: vDay, h_hour: vHour, h_min: vMin },
		},
		{
			id: 'tab-four',
			condition: vDay === '*' && vMonth === '*',
			fields: { h_wday: vWday, h_hour: vHour, h_min: vMin },
		},
		{
			id: 'tab-five',
			condition: vWday === '*',
			fields: { h_month: vMonth, h_day: vDay, h_hour: vHour, h_min: vMin },
		},
	];

	// Find the most appropriate tab
	let selectedTab = null;
	let selectedFields = null;

	for (const tab of tabs) {
		if (!tab.condition) continue;

		const panel = document.querySelector(`[aria-labelledby="${tab.id}"]`);
		if (!panel) continue;

		let allFieldsMatch = true;
		for (const [name, value] of Object.entries(tab.fields)) {
			const select = panel.querySelector(`select[name="${name}"]`);
			if (select) {
				const optionExists = Array.from(select.options).some((opt) => opt.value === value);
				if (!optionExists) {
					allFieldsMatch = false;
					break;
				}
			} else {
				allFieldsMatch = false;
				break;
			}
		}

		if (allFieldsMatch) {
			selectedTab = tab.id;
			selectedFields = tab.fields;
			break;
		}
	}

	if (selectedTab) {
		const tabElement = document.getElementById(selectedTab);
		if (tabElement) {
			tabElement.click(); // Activate the tab
			tabElement.blur(); // Remove the blue focus ring caused by programmatic clicking

			// Set the select values
			const panel = document.querySelector(`[aria-labelledby="${selectedTab}"]`);
			for (const [name, value] of Object.entries(selectedFields)) {
				const select = panel.querySelector(`select[name="${name}"]`);
				if (select) {
					select.value = value;
				}
			}
		}
	}
}
