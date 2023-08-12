// Copies values from cron generator fields to main cron fields when "Generate" is clicked
export default function handleCronGenerator() {
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
					{ once: true }
				);
			});
		});
	});
}
