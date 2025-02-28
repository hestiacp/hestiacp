// Set up various Alpine things after it's initialized
export default function alpineInit() {
	// Bulk edit forms
	Alpine.bind('BulkEdit', () => ({
		/** @param {SubmitEvent} evt */
		'@submit'(evt) {
			evt.preventDefault();
			document.querySelectorAll('.js-unit-checkbox').forEach((el) => {
				if (el.checked) {
					const input = document.createElement('input');
					input.type = 'hidden';
					input.name = el.name;
					input.value = el.value;
					evt.target.appendChild(input);
				}
			});

			evt.target.submit();
		},
	}));

	// Form state
	Alpine.store('form', {
		dirty: false,
		makeDirty() {
			this.dirty = true;
		},
	});
	document
		.querySelectorAll('#main-form input, #main-form select, #main-form textarea')
		.forEach((el) => {
			el.addEventListener('change', () => {
				Alpine.store('form').makeDirty();
			});
		});

	// Notifications methods called by the view code
	Alpine.data('notifications', () => ({
		initialized: false,
		open: false,
		notifications: [],
		toggle() {
			this.open = !this.open;
			if (!this.initialized) {
				this.list();
			}
		},
		async list() {
			const token = document.querySelector('#token').getAttribute('token');
			const res = await fetch(`/list/notifications/?ajax=1&token=${token}`);
			this.initialized = true;
			if (!res.ok) {
				throw new Error('An error occurred while listing notifications.');
			}

			this.notifications = Object.values(await res.json());
		},
		async remove(id) {
			const token = document.querySelector('#token').getAttribute('token');
			await fetch(`/delete/notification/?delete=1&notification_id=${id}&token=${token}`);

			this.notifications = this.notifications.filter((notification) => notification.ID != id);
			if (this.notifications.length === 0) {
				this.open = false;
			}
		},
		async removeAll() {
			const token = document.querySelector('#token').getAttribute('token');
			await fetch(`/delete/notification/?delete=1&token=${token}`);

			this.notifications = [];
			this.open = false;
		},
	}));
}
