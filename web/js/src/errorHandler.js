import { createConfirmationDialog } from './helpers';

// Displays page error message/notice in a confirmation dialog
export default function handleErrorMessage() {
	const errorMessage = Alpine.store('globals').ERROR_MESSAGE;

	if (errorMessage) {
		createConfirmationDialog({ message: errorMessage });
	}
}
