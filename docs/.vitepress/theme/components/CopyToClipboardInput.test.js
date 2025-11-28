import { cleanup, fireEvent, render, screen, waitFor } from '@testing-library/vue';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import CopyToClipboardInput from './CopyToClipboardInput.vue';

// Mock the clipboard API
Object.assign(navigator, {
	clipboard: {
		writeText: vi.fn(() => Promise.resolve()),
	},
});

describe('CopyToClipboardInput', () => {
	beforeEach(() => {
		render(CopyToClipboardInput, {
			props: { value: 'Test text' },
		});
	});

	afterEach(() => {
		cleanup();
	});

	it('renders correctly', () => {
		const input = screen.getByRole('textbox');
		const button = screen.getByRole('button', { name: 'Copy' });

		expect(input).toBeTruthy();
		expect(button).toBeTruthy();
	});

	it('selects text when input is focused', async () => {
		const input = screen.getByRole('textbox');
		await fireEvent.focus(input);

		expect(input.selectionStart).toBe(0);
		expect(input.selectionEnd).toBe('Test text'.length);
	});

	it('copies text to clipboard when button is clicked', async () => {
		const button = screen.getByRole('button', { name: 'Copy' });
		await fireEvent.click(button);

		expect(navigator.clipboard.writeText).toHaveBeenCalledWith('Test text');
		await waitFor(() => expect(button.textContent).toBe('Copied!'));
	});
});
