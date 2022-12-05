const Cookies = {
	/**
	 * Creates a cookie.
	 *
	 * @param {string} name The name of the cookie.
	 * @param {any} value The value to assign the cookie. It will be JSON encoded using JSON.stringify(...).
	 * @param {number} days The number of days in which the cookie will expire. If none is provided,
	 * it will create a session cookie.
	 */
	set(name, value, days = null) {
		let expires = '';
		if (days && !isNaN(days)) {
			const date = new Date();
			date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
			expires = `; expires=${date.toUTCString()}`;
		}

		document.cookie =
			`${name}=${JSON.stringify(value)}` + expires + '; path="/"; SameSite=None; Secure';
	},

	/**
	 * Reads a cookie.
	 *
	 * @param {string} name The name of the cookie.
	 * @returns {string} The value of the cookie, decoded with JSON.parse(...).
	 */
	read(name) {
		const value = document.cookie
			.split('; ')
			.find((row) => row.startsWith(`${name}=`))
			?.split('=')[1];

		return value ? JSON.parse(value) : undefined;
	},

	/**
	 * Removes a cookie.
	 *
	 * @param {string} name The name of the cookie.
	 */
	remove(name) {
		this.set(name, '', -1);
	},
};

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

	const rng = (min, max) => {
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
		let result;
		let attempts = 0;

		// eslint-disable-next-line no-constant-condition
		while (true) {
			crypto.getRandomValues(randArr);
			result = randArr[0];
			if (result >= min && result <= max) {
				return result;
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
		let result = '';
		for (let i = 0; i < length; ++i) {
			result += chars[rng(0, randmax)];
		}
		if (minimumStrengthRegex.test(result)) {
			return result;
		}
		++attempts;
		if (attempts > 1000000) {
			throw new Error('tried a million times, something is wrong');
		}
	}
}

document.addEventListener('alpine:init', () => {
	const token = document.querySelector('#token').getAttribute('token');

	// Sticky class helper
	window.addEventListener('scroll', () => {
		const toolbar = document.querySelector('.toolbar');
		const tableHeader = document.querySelector('.table-header');
		const toolbarOffset =
			toolbar.getBoundingClientRect().top + (window.scrollY - document.documentElement.clientTop);
		const headerHeight = document.querySelector('.top-bar').offsetHeight;
		const isActive = window.scrollY > toolbarOffset - headerHeight;

		toolbar.classList.toggle('active', isActive);
		if (tableHeader) {
			tableHeader.classList.toggle('active', isActive);
		}
	});

	// Select all helper
	const toggleAll = document.querySelector('#toggle-all');
	if (toggleAll) {
		toggleAll.addEventListener('change', (evt) => {
			document.querySelectorAll('.ch-toggle').forEach((el) => (el.checked = evt.target.checked));
			document
				.querySelectorAll('.l-unit')
				.forEach((el) => el.classList.toggle('selected', evt.target.checked));
		});
	}

	// Form state
	Alpine.store('form', {
		dirty: false,
		makeDirty() {
			this.dirty = true;
		},
	});
	document
		.querySelectorAll('#vstobjects input, #vstobjects select, #vstobjects textarea')
		.forEach((el) => {
			el.addEventListener('change', () => {
				Alpine.store('form').makeDirty();
			});
		});

	// Notifications data
	Alpine.data('notifications', () => ({
		initialized: false,
		open: false,
		notifications: [],
		toggle() {
			this.open = !this.open;
			if (!this.initialized) {
				this.initialized = true;
				this.list();
			}
		},
		async list() {
			const res = await fetch(`/list/notifications/?ajax=1&token=${token}`);
			if (!res.ok) {
				throw new Error('An error occured while listing notifications.');
			}

			this.notifications = Object.entries(res.json()).reduce(
				(acc, [_id, notification]) => [...acc, notification],
				[]
			);
		},
		async remove(id) {
			await fetch(`/delete/notification/?delete=1&notification_id=${id}&token=${token}`);

			this.notifications = this.notifications.filter((notification) => notification.ID != id);
			if (this.notifications.length == 0) {
				this.open = false;
			}
		},
		async removeAll() {
			await fetch(`/delete/notification/?delete=1&token=${token}`);

			this.notifications = [];
			this.open = false;
		},
	}));
});
