document.addEventListener('alpine:init', () => {
	const token = document.querySelector('#token').getAttribute('token');

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
