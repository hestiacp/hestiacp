// Page navigation methods called by shortcuts
const state = {
	active_menu: 1,
	menu_selector: '.main-menu-item',
	menu_active_selector: '.active',
};

export function moveFocusLeft() {
	moveFocusLeftRight('left');
}

export function moveFocusRight() {
	moveFocusLeftRight('right');
}

export function moveFocusDown() {
	moveFocusUpDown('down');
}

export function moveFocusUp() {
	moveFocusUpDown('up');
}

// Navigate to whatever item has been selected in the UI by other shortcuts
export function enterFocused() {
	const activeMainMenuItem = document.querySelector(state.menu_selector + '.focus a');
	if (activeMainMenuItem) {
		return (location.href = activeMainMenuItem.getAttribute('href'));
	}

	const activeUnit = document.querySelector(
		'.js-unit.focus .units-table-row-actions .shortcut-enter a'
	);
	if (activeUnit) {
		location.href = activeUnit.getAttribute('href');
	}
}

// Either click or follow a link based on the data-key-action attribute
export function executeShortcut(elm) {
	const action = elm.dataset.keyAction;
	if (action === 'js') {
		return elm.querySelector('.data-controls').click();
	}

	if (action === 'href') {
		location.href = elm.querySelector('a').getAttribute('href');
	}
}

function moveFocusLeftRight(direction) {
	const menuSelector = state.menu_selector;
	const activeSelector = state.menu_active_selector;
	const menuItems = Array.from(document.querySelectorAll(menuSelector));
	const currentFocused = document.querySelector(`${menuSelector}.focus`);
	const currentActive = document.querySelector(menuSelector + activeSelector);
	let index = menuItems.indexOf(currentFocused);

	if (index === -1) {
		index = menuItems.indexOf(currentActive);
	}

	menuItems.forEach((item) => item.classList.remove('focus'));

	if (direction === 'left') {
		if (index > 0) {
			menuItems[index - 1].classList.add('focus');
		} else {
			switchMenu('last');
		}
	} else if (direction === 'right') {
		if (index < menuItems.length - 1) {
			menuItems[index + 1].classList.add('focus');
		} else {
			switchMenu('first');
		}
	}
}

function moveFocusUpDown(direction) {
	const units = Array.from(document.querySelectorAll('.js-unit'));
	const currentFocused = document.querySelector('.js-unit.focus');
	let index = units.indexOf(currentFocused);

	if (index === -1) {
		index = 0;
	}

	if (direction === 'up' && index > 0) {
		index--;
	} else if (direction === 'down' && index < units.length - 1) {
		index++;
	} else {
		return;
	}

	if (currentFocused) {
		currentFocused.classList.remove('focus');
	}

	units[index].classList.add('focus');

	window.scrollTo({
		top: units[index].getBoundingClientRect().top - 200 + window.scrollY,
		behavior: 'smooth',
	});
}

function switchMenu(position = 'first') {
	if (state.active_menu === 0) {
		state.active_menu = 1;
		state.menu_selector = '.main-menu-item';
		state.menu_active_selector = '.active';

		const menuItems = document.querySelectorAll(state.menu_selector);
		const focusedIndex = position === 'first' ? 0 : menuItems.length - 1;
		menuItems[focusedIndex].classList.add('focus');
	}
}
