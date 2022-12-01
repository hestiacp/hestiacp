const App = {
	// Main namespases for page specific functions
	// Core namespaces
	Core: {},
	// CONSTANT VALUES
	Constants: {
		UNLIM_VALUE: 'unlimited', // overritten in i18n.js.php
		UNLIM_TRANSLATED_VALUE: 'unlimited', // overritten in i18n.js.php
	},
	// Actions. More widly used funcs
	Actions: {
		DB: {},
		WEB: {},
		PACKAGE: {},
		MAIL_ACC: {},
		MAIL: {},
	},
	// Utilities
	Helpers: {},
	i18n: {},
	Listeners: {
		DB: {},
		WEB: {},
		PACKAGE: {},
		MAIL_ACC: {},
	},
	Templates: {
		Templator: null,
		Tpl: {},
		_indexes: {},
	},
};

// Internals
Array.prototype.set = function (key, value) {
	const index = this[0][key];
	this[1][index] = value;
};
Array.prototype.get = function (key) {
	const index = this[0][key];
	return this[1][index];
};
Array.prototype.finalize = function () {
	this.shift();
	this[0] = this[0].join('');
	return this[0];
};
Array.prototype.done = function () {
	return this.join('');
};

App.Core.flatten_json = function (data, prefix) {
	const keys = Object.keys(data);
	let result = {};

	prefix || (prefix = '');

	if (!keys.length) {
		return false;
	}

	for (let i = 0, cnt = keys.length; i < cnt; i++) {
		const value = data[keys[i]];
		switch (typeof value) {
			case 'function':
				break;
			case 'object':
				result = { ...result, ...App.Core.flatten_json(value, prefix + '[' + keys[i] + ']') };
				break;
			default:
				result[prefix + '[' + keys[i] + ']'] = value;
		}
	}
	return result;
};

function set_sticky_class() {
	const toolbar = document.querySelector('.toolbar');
	const tableHeader = document.querySelector('.table-header');
	const toolbarOffset =
		toolbar.getBoundingClientRect().top + window.scrollY - document.documentElement.clientTop;
	const headerHeight = document.querySelector('.top-bar').offsetHeight;

	const isActive = window.scrollY > toolbarOffset - headerHeight;
	toolbar.classList.toggle('active', isActive);
	tableHeader.forEach((el) => el.classList.toggle('active', isActive));
}

function checkedAll() {
	/** @type boolean */
	const toggleAll = document.querySelector('input#toggle-all').checked;

	document.querySelectorAll('.ch-toggle').forEach((el) => (el.checked = toggleAll));
	document
		.querySelectorAll('.l-unit:not(.header)')
		.forEach((el) => el.classList.toggle('selected', toggleAll));
	document
		.querySelectorAll('.toggle-all')
		.forEach((el) => el.classList.toggle('clicked-on', toggleAll));
}

function doSearch(url) {
	const searchQuery = document.querySelector('.js-search-input').value;
	const searchToken = document.querySelector('input[name="token"]').value;
	location.href = `${url || '/search'}?q=${searchQuery}&token=${searchToken}`;
}

function elementHideShow(elementToHideOrShow, trigger) {
	const el = document.querySelector(`#${elementToHideOrShow}`);
	el.style.display = el.style.display === 'none' ? 'block' : 'none';

	if (typeof trigger !== 'undefined') {
		trigger.querySelector('.js-section-toggle-icon').classList.toggle('fa-square-minus');
		trigger.querySelector('.js-section-toggle-icon').classList.toggle('fa-square-plus');
	}
}
