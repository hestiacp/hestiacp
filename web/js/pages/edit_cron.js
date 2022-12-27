document.addEventListener('DOMContentLoaded', () => {
	// TODO: Replace jQuery UI tabs with something else
	$('.js-cron-tabs').tabs();

	const generateCronButtons = document.querySelectorAll('.js-generate-cron');
	generateCronButtons.forEach((button) => {
		button.addEventListener('click', () => {
			const fieldset = button.closest('fieldset');
			const inputNames = ['min', 'hour', 'day', 'month', 'wday'];

			inputNames.forEach((inputName) => {
				const value = fieldset.querySelector(`[name=h_${inputName}]`).value;
				const formInput = document.querySelector(`#vstobjects input[name=v_${inputName}]`);

				formInput.value = value;
				formInput.classList.add('highlighted');

				setTimeout(() => {
					formInput.classList.remove('highlighted');
				}, 250);
			});
		});
	});
});
