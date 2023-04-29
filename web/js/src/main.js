import alpineInit from './alpineInit';
import focusFirstInput from './focusFirstInput';
import handleConfirmationDialogs from './confirmationDialog';
import handleCopyCreds from './copyCreds';
import handleCronGenerator from './cronGenerator';
import handleDiscardAllMail from './discardAllMail';
import handleDnsRecordHint from './dnsRecordHint';
import handleErrorMessage from './errorHandler';
import handleFormSubmit from './formSubmit';
import handleIpListDataSource from './ipListDataSource';
import handleListSelectAll from './listSelectAll';
import handleListSorting from './listSorting';
import handleNameServerInput from './nameServerInput';
import handlePasswordInput from './passwordInput';
import handleShortcuts from './shortcuts';
import handleStickyToolbar from './stickyToolbar';
import handleSyncEmailValues from './syncEmailValues';
import handleTabPanels from './tabPanels';
import handleToggleAdvanced from './toggleAdvanced';
import handleUnlimitedInput from './unlimitedInput';
import * as helpers from './helpers';

window.Hestia = { helpers };

initListeners();
focusFirstInput();

function initListeners() {
	handleConfirmationDialogs();
	handleCopyCreds();
	handleCronGenerator();
	handleDiscardAllMail();
	handleDnsRecordHint();
	handleFormSubmit();
	handleListSelectAll();
	handleListSorting();
	handleNameServerInput();
	handlePasswordInput();
	handleStickyToolbar();
	handleSyncEmailValues();
	handleTabPanels();
	handleToggleAdvanced();
}

document.addEventListener('alpine:init', () => {
	alpineInit();
	handleErrorMessage();
	handleIpListDataSource();
	handleShortcuts();
	handleUnlimitedInput();
});
