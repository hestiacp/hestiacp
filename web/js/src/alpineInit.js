import Alpine from 'alpinejs';
import notificationMethods from './notifications.js';
import initUnlimitedInput from './unlimitedInput.js';
import initShortcuts from './shortcuts.js';

// Set up various Alpine things, loads after Alpine is initialized
export default function alpineInit() {
	// Bulk edit forms
	Alpine.bind('BulkEdit', () => ({
		/** @param {SubmitEvent} evt */
		'@submit'(evt) {
			evt.preventDefault();
			document.querySelectorAll('.ch-toggle').forEach((el) => {
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
		.querySelectorAll('#vstobjects input, #vstobjects select, #vstobjects textarea')
		.forEach((el) => {
			el.addEventListener('change', () => {
				Alpine.store('form').makeDirty();
			});
		});

	// Register Alpine notifications methods
	Alpine.data('notifications', notificationMethods);
	initAlpineDependentModules();
}

function initAlpineDependentModules() {
	initUnlimitedInput();
	initShortcuts();
}
