import initConfirmationDialogs from './confirmationDialog.js';
import initListSelectAll from './selectAll.js';
import initListSorting from './listSorting.js';
import initLoadingSpinner from './loadingSpinner.js';
import initNameServerInput from './nameServerInput.js';
import initPasswordInput from './passwordInput.js';
import initStickyToolbar from './stickyToolbar.js';

// Attaches generic page listeners
export default function initPageListeners() {
	initConfirmationDialogs();
	initListSelectAll();
	initListSorting();
	initLoadingSpinner();
	initNameServerInput();
	initPasswordInput();
	initStickyToolbar();
}
