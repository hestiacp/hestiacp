import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { render, fireEvent, screen, cleanup, waitFor } from '@testing-library/vue';
import FloatingVue from 'floating-vue';
import InstallBuilder from './InstallBuilder.vue';

describe('InstallBuilder', () => {
	const options = [
		{ flag: 'option1', label: 'Option 1', description: 'Description for Option 1', default: 'no' },
		{
			flag: 'option2',
			label: 'Option 2',
			description: 'Description for Option 2',
			type: 'text',
			default: '',
		},
		{
			flag: 'option3',
			label: 'Option 3',
			description: 'Description for Option 3',
			type: 'select',
			options: [
				{ value: 'val1', label: 'Value 1' },
				{ value: 'val2', label: 'Value 2' },
			],
			default: 'val1',
		},
	];

	beforeEach(() => {
		render(InstallBuilder, {
			props: { options },
			global: {
				plugins: [FloatingVue],
			},
		});
	});

	afterEach(() => {
		cleanup();
	});

	it('renders all options correctly', () => {
		options.forEach((option) => {
			expect(screen.getByLabelText(option.label)).toBeTruthy();
		});
	});

	it('toggles an option when clicked', async () => {
		const option1 = screen.getByLabelText(options[0].label);
		await fireEvent.click(option1);
		expect(option1.checked).toBe(true);
	});

	it('updates the installation command when an option is toggled', async () => {
		const option1 = screen.getByLabelText(options[0].label);
		await fireEvent.click(option1);
		waitFor(() =>
			expect(screen.getByDisplayValue(/bash hst-install.sh --option1 yes/)).toBeTruthy(),
		);
	});

	it('updates the installation command when option text input changes', async () => {
		const option2 = screen.getByLabelText(options[1].label);
		await fireEvent.click(option2);

		const textInput = screen.getByLabelText(options[1].description);
		await fireEvent.update(textInput, 'custom-value');

		expect(screen.getByDisplayValue(/bash hst-install.sh --option2 custom-value/)).toBeTruthy();
	});

	it('updates the installation command when option select input changes', async () => {
		const option3 = screen.getByLabelText(options[2].label);
		await fireEvent.click(option3);

		const selectInput = screen.getByLabelText(options[2].description);
		await fireEvent.update(selectInput, { target: { value: 'val2' } });

		waitFor(() =>
			expect(screen.getByDisplayValue(/bash hst-install.sh --option3 val2/)).toBeTruthy(),
		);
	});
});
