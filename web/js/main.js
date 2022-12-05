function getToken() {
	return document.querySelector('#token').getAttribute('token');
}

document.addEventListener('alpine:init', () => {
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
			const response = await fetch(`/list/notifications/?ajax=1&token=${getToken()}`);
			if (!response.ok) {
				throw new Error('An error occured while listing notifications.');
			}
			const data = await response.clone().json();

			this.notifications = Object.entries(data).reduce(
				(acc, [_id, notification]) => [...acc, notification],
				[]
			);
		},
		async delete(id) {
			await fetch(`/delete/notification/?delete=1&notification_id=${id}&token=${getToken()}`);

			this.notifications = this.notifications.filter((notification) => notification.ID != id);
			if (this.notifications.length == 0) {
				this.open = false;
			}
		},
		async deleteAll() {
			await fetch(`/delete/notification/?delete=1&token=${getToken()}`);

			this.notifications = [];
			this.open = false;
		},
	}));
});
