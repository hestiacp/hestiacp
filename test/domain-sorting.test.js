import assert from 'node:assert/strict';
import { afterEach, test } from 'node:test';
import { JSDOM } from 'jsdom';
import handleListSorting from '../web/js/src/listSorting.js';

afterEach(() => {
	delete globalThis.document;
});

function page(grouped = true) {
	const dom =
		new JSDOM(`<button class="js-toggle-sorting-menu"><span>Name</span><i class="fas fa-arrow-down-a-z"></i></button>
		<ul class="js-sorting-menu">
			<li data-entity="sort-name"><span class="name">Name</span><span class="up">Descending</span></li>
			<li data-entity="sort-date" data-sort-as-int="1"><span class="name">Date</span></li>
			<li data-entity="sort-disk" data-sort-as-int="1"><span class="name">Disk</span></li>
		</ul><div class="js-units-container"></div>`);
	globalThis.document = dom.window.document;
	const container = document.querySelector('.js-units-container');
	const fixtures = [
		['z.example.cz', 'example.cz', 'z', 1],
		['a.other.cz', 'other.cz', 'a', 4],
		['example.cz', 'example.cz', '', 3],
		['a.example.cz', 'example.cz', 'a', 2],
	];
	for (const [name, domain, subdomain, date] of fixtures) {
		const unit = document.createElement('div');
		unit.className = 'js-unit';
		unit.dataset.sortName = name;
		unit.dataset.sortDate = String(date);
		unit.dataset.sortDisk = String(date);
		if (grouped) {
			unit.dataset.sortDomain = domain;
			unit.dataset.sortSubdomain = subdomain;
		}
		container.appendChild(unit);
	}
	handleListSorting();
	return {
		container,
		click: (selector) => document.querySelector(selector).click(),
		order: () => [...container.children].map((unit) => unit.dataset.sortName),
	};
}

test('Name groups parents and subdomains in ascending order', () => {
	const view = page();
	view.click('[data-entity="sort-name"] .name');
	assert.deepEqual(view.order(), ['example.cz', 'a.example.cz', 'z.example.cz', 'a.other.cz']);
	assert.equal(view.container.classList.contains('is-domain-grouped'), true);
});

test('Descending reverses groups and children while keeping parents first', () => {
	const view = page();
	view.click('[data-entity="sort-name"] .up');
	assert.deepEqual(view.order(), ['a.other.cz', 'example.cz', 'z.example.cz', 'a.example.cz']);
});

test('Switching to Date and back restores the hierarchy', () => {
	const view = page();
	view.click('[data-entity="sort-date"] .name');
	assert.deepEqual(view.order(), ['a.other.cz', 'example.cz', 'a.example.cz', 'z.example.cz']);
	assert.equal(view.container.classList.contains('is-domain-grouped'), false);
	view.click('[data-entity="sort-name"] .name');
	assert.deepEqual(view.order(), ['example.cz', 'a.example.cz', 'z.example.cz', 'a.other.cz']);
});

test('Disk retains ordinary numeric sorting', () => {
	const view = page();
	view.click('[data-entity="sort-disk"] .name');
	assert.deepEqual(view.order(), ['a.other.cz', 'example.cz', 'a.example.cz', 'z.example.cz']);
});

test('Other list pages without domain metadata keep plain alphabetic sorting', () => {
	const view = page(false);
	view.click('[data-entity="sort-name"] .name');
	assert.deepEqual(view.order(), ['a.example.cz', 'a.other.cz', 'example.cz', 'z.example.cz']);
});
