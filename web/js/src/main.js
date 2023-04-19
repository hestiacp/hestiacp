import alpineInit from './alpineInit.js';
import focusFirstInput from './focusFirstInput.js';
import initListeners from './listeners.js';
import navigationMethods from './navigation.js';
import {
	randomPassword,
	createConfirmationDialog,
	generateMailCredentials,
	monitorAndUpdate,
} from './helpers.js';

function initializeApp() {
	window.VE = {
		// List view sorting state
		tmp: {
			sort_par: 'sort-name',
			sort_direction: -1,
			sort_as_int: false,
		},
		// Page navigation methods called by shortcuts
		navigation: navigationMethods(),
		// Helpers exposed for page-specific JS and inline <script> usage
		helpers: {
			createConfirmationDialog,
			randomPassword,
			generateMailCredentials,
			monitorAndUpdate,
		},
	};

	initListeners();
	focusFirstInput();
}

initializeApp();

document.addEventListener('alpine:init', () => alpineInit());
