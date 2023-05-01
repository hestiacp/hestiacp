import alpineInit from './alpineInit';
import focusFirstInput from './focusFirstInput';
import handleConfirmAction from './confirmAction';
import handleCopyCreds from './copyCreds';
import handleCronGenerator from './cronGenerator';
import handleDatabaseHints from './databaseHints';
import handleDiscardAllMail from './discardAllMail';
import handleDnsRecordHint from './dnsRecordHint';
import handleDocRootHint from './docRootHint';
import handleEditWebListeners from './editWebListeners';
import handleErrorMessage from './errorHandler';
import handleFormSubmit from './formSubmit';
import handleFtpAccountHints from './ftpAccountHints';
import handleFtpAccounts from './ftpAccounts';
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
	handleConfirmAction();
	handleCopyCreds();
	handleCronGenerator();
	handleDiscardAllMail();
	handleDnsRecordHint();
	handleDocRootHint();
	handleEditWebListeners();
	handleFormSubmit();
	handleFtpAccounts();
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
	handleDatabaseHints();
	handleErrorMessage();
	handleFtpAccountHints();
	handleIpListDataSource();
	handleShortcuts();
	handleUnlimitedInput();
});
