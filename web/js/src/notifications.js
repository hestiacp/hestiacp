// Returns methods for handling notifications with Alpine.js
export default function notificationMethods() {
	return {
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

			this.notifications = Object.entries(await res.json()).reduce(
				(accumulator, [_id, notification]) => [...accumulator, notification],
				[]
			);
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
	};
}
