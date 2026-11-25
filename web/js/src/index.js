import handleAddIpLists from './addIpLists';
import alpineInit from './alpineInit';
import handleAutoTrimInputs from './autoTrimInputs';
import handleClipboardCopy from './clipboardCopy';
import handleConfirmAction from './confirmAction';
import handleCopyCreds from './copyCreds';
import handleCronGenerator from './cronGenerator';
import handleDatabaseHints from './databaseHints';
import handleDiscardAllMail from './discardAllMail';
import handleDnsRecordHint from './dnsRecordHint';
import handleDocRootHint from './docRootHint';
import handleEditWebListeners from './editWebListeners';
import handleErrorMessage from './errorHandler';
import focusFirstInput from './focusFirstInput';
import handleFormSubmit from './formSubmit';
import handleFtpAccountHints from './ftpAccountHints';
import handleFtpAccounts from './ftpAccounts';
import handleIpListDataSource from './ipListDataSource';
import handleListSorting from './listSorting';
import handleListUnitSelect from './listUnitSelect';
import handleNameServerInput from './nameServerInput';
import handlePasswordInput from './passwordInput';
import initRrdCharts from './rrdCharts';
import handleShortcuts from './shortcuts';
import handleStickyToolbar from './stickyToolbar';
import handleSyncEmailValues from './syncEmailValues';
import handleTabPanels from './tabPanels';
import handleToggleAdvanced from './toggleAdvanced';
import handleUnlimitedInput from './unlimitedInput';
import initWebTerminal from './webTerminal';

initListeners();
focusFirstInput();

function initListeners() {
	handleAddIpLists();
	handleAutoTrimInputs();
	handleConfirmAction();
	handleCopyCreds();
	handleClipboardCopy();
	handleCronGenerator();
	handleDiscardAllMail();
	handleDnsRecordHint();
	handleDocRootHint();
	handleEditWebListeners();
	handleFormSubmit();
	handleFtpAccounts();
	handleListSorting();
	handleListUnitSelect();
	handleNameServerInput();
	handlePasswordInput();
	handleStickyToolbar();
	handleSyncEmailValues();
	handleTabPanels();
	handleToggleAdvanced();
	initRrdCharts();
	initWebTerminal();
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
