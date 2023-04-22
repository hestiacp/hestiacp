import alpineInit from './alpineInit.js';
import focusFirstInput from './focusFirstInput.js';
import initConfirmationDialogs from './confirmationDialog.js';
import initListSelectAll from './selectAll.js';
import initListSorting from './listSorting.js';
import initLoadingSpinner from './loadingSpinner.js';
import initNameServerInput from './nameServerInput.js';
import initPasswordInput from './passwordInput.js';
import initShortcuts from './shortcuts.js';
import initStickyToolbar from './stickyToolbar.js';
import initToggleAdvanced from './toggleAdvanced.js';
import initUnlimitedInput from './unlimitedInput.js';
import * as helpers from './helpers.js';

window.Hestia = { helpers };

initListeners();
focusFirstInput();

function initListeners() {
	initConfirmationDialogs();
	initListSelectAll();
	initListSorting();
	initLoadingSpinner();
	initNameServerInput();
	initPasswordInput();
	initStickyToolbar();
	initToggleAdvanced();
}

document.addEventListener('alpine:init', () => {
	alpineInit();
	initUnlimitedInput();
	initShortcuts();
});
