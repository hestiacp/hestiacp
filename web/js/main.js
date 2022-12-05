document.addEventListener('alpine:init', () => {
	Alpine.data('notifications', () => ({
		open: false,
		items: [],
		toggle() {
			this.open = !this.open;
			if (this.open) {
				this.list();
			}
		},
		async list() {
			const token = document.querySelector('#token').getAttribute('token');
			const response = await fetch(`/list/notifications/?ajax=1&token=${token}`, {});
			if (!response.ok) {
				throw new Error('An error occured while listing notifications.');
			}
			const data = await response.clone().json();

			this.items = Object.entries(data).reduce(
				(acc, [_id, notification]) => acc.push(notification),
				[]
			);
		},
		async delete(id) {
			const token = document.querySelector('#token').getAttribute('token');
			await fetch(`/delete/notification/?delete=1&notification_id=${id}&token=${token}`);

			this.items = this.items.filter((notification) => notification.ID != id);
			if (this.items.length == 0) {
				this.open = false;
			}
		},
		async deleteAll() {
			const token = document.querySelector('#token').getAttribute('token');
			await fetch(`/delete/notification/?delete=1&token=${token}`);

			this.items = [];
			this.open = false;
		},
	}));
});
