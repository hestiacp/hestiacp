class AppClass {
	// Actions. More widly used funcs
	Actions = {
		DB: {},
		WEB: {},
		PACKAGE: {},
		MAIL_ACC: {},
		MAIL: {},
	};

	// Utilities
	Helpers = {
		isUnlimitedValue: (value) =>
			value.trim() == GLOBAL.UNLIM_VALUE || value.trim() == GLOBAL.UNLIM_TRANSLATED_VALUE,
	};

	Listeners = {
		DB: {},
		WEB: {},
		PACKAGE: {},
		MAIL_ACC: {},
	};
}

const App = new AppClass();

function setStickyClass() {
	const toolbar = document.querySelector('.toolbar');
	const tableHeader = document.querySelector('.table-header');
	const toolbarOffset =
		toolbar.getBoundingClientRect().top + window.scrollY - document.documentElement.clientTop;
	const headerHeight = document.querySelector('.top-bar').offsetHeight;

	const isActive = window.scrollY > toolbarOffset - headerHeight;
	toolbar.classList.toggle('active', isActive);
	if (tableHeader) {
		tableHeader.classList.toggle('active', isActive);
	}
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

/**
 * generates a random string using a cryptographically secure rng,
 * and ensuring it contains at least 1 lowercase, 1 uppercase, and 1 number.
 *
 * @param {int} [length=16]
 * @throws {Error} if length is too small to create a "sufficiently secure" string
 * @returns {string}
 */
function randomString(length = 16) {
	const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	const secure_rng = (min, max) => {
		if (min < 0 || min > 0xffff) {
			throw new Error(
				'minimum supported number is 0, this generator can only make numbers between 0-65535 inclusive.'
			);
		}
		if (max > 0xffff || max < 0) {
			throw new Error(
				'max supported number is 65535, this generator can only make numbers between 0-65535 inclusive.'
			);
		}
		if (min > max) {
			throw new Error('dude min>max wtf');
		}
		// micro-optimization
		const randArr = max > 255 ? new Uint16Array(1) : new Uint8Array(1);
		let ret;
		let attempts = 0;
		// eslint-disable-next-line no-constant-condition
		while (true) {
			crypto.getRandomValues(randArr);
			ret = randArr[0];
			if (ret >= min && ret <= max) {
				return ret;
			}
			++attempts;
			if (attempts > 1000000) {
				// should basically never happen with max 0xFFFF/Uint16Array.
				throw new Error('tried a million times, something is wrong');
			}
		}
	};

	let attempts = 0;
	const minimumStrengthRegex = new RegExp(
		/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\d)[a-zA-Z\d]{8,}$/
	);
	const randmax = chars.length - 1;
	// eslint-disable-next-line no-constant-condition
	while (true) {
		let ret = '';
		for (let i = 0; i < length; ++i) {
			ret += chars[secure_rng(0, randmax)];
		}
		if (minimumStrengthRegex.test(ret)) {
			return ret;
		}
		++attempts;
		if (attempts > 1000000) {
			throw new Error('tried a million times, something is wrong');
		}
	}
}
