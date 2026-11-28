/* Hestia Studio 2.1.1 · GPL-3.0-or-later. Native forms and actions stay intact.
   Interface labels are supplied exclusively by studio.php and locale files. */
(() => {
	const icons = {
		grid: '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
		globe: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c5 5 5 13 0 18-5-5-5-13 0-18Z"/>',
		mail: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/>',
		database:
			'<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 4 16 4 16 0V5M4 12c0 4 16 4 16 0"/>',
		dns: '<rect x="8" y="3" width="8" height="5" rx="1"/><rect x="2" y="16" width="7" height="5" rx="1"/><rect x="15" y="16" width="7" height="5" rx="1"/><path d="M12 8v4M5.5 16v-4h13v4"/>',
		clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		backup: '<path d="M3 7h18v4H3zM5 11v10h14V11M8 3h8M10 15h4"/>',
		shield: '<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6zM8 12l3 3 5-6"/>',
		users:
			'<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 6"/>',
		folder: '<path d="M3 7a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v9H3z"/>',
		settings:
			'<path d="M4 6h16M4 12h16M4 18h16"/><circle cx="9" cy="6" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="8" cy="18" r="2"/>',
		chevron: '<path d="m9 5 7 7-7 7"/>',
		search: '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
		sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/>',
		moon: '<path d="M20.8 13.2A9 9 0 0 1 10.8 3.1 9 9 0 1 0 20.8 13.2Z"/>',
		server:
			'<rect x="3" y="3" width="18" height="7" rx="2"/><rect x="3" y="14" width="18" height="7" rx="2"/><path d="M7 6.5h.01M7 17.5h.01M11 6.5h6M11 17.5h6"/>',
		disk: '<path d="m6 4-3 9v6h18v-6l-3-9zM3 13h18M16 16h2"/>',
		activity: '<path d="M2 12h5l3-8 4 16 3-8h5"/>',
		key: '<circle cx="8" cy="8" r="5"/><path d="m12 12 9 9m-5-5 3-3m-6 0 3-3"/>',
		close: '<path d="m6 6 12 12M6 18 18 6"/>',
		menu: '<path d="M4 6h16M4 12h16M4 18h16"/>',
		refresh: '<path d="M20 7v5h-5M4 17v-5h5M6 6a8 8 0 0 1 14 6M4 12a8 8 0 0 0 14 6"/>',
		terminal: '<path d="m5 6 6 6-6 6m8 0h6"/>',
	};
	const el = (tag, cls, text) => {
		const n = document.createElement(tag);
		if (cls) n.className = cls;
		if (text !== undefined) n.textContent = text;
		return n;
	};
	const icon = (name) => {
		const n = el('span', 'studio-icon');
		n.innerHTML =
			'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
			(icons[name] || icons.grid) +
			'</svg>';
		return n;
	};
	const clean = (n) => (n?.textContent || '').replace(/\s+/g, ' ').trim();
	const pathOf = (href) => {
		try {
			return new URL(href, location.href).pathname;
		} catch {
			return '';
		}
	};
	Object.assign(icons, {
		cpu: '<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 3v3m6-3v3M9 18v3m6-3v3M3 9h3m-3 6h3m12-6h3m-3 6h3"/><rect x="9" y="9" width="6" height="6" rx="1"/>',
		memory:
			'<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M7 9v5m5-5v5m5-5v5M7 18v3m5-3v3m5-3v3"/>',
		edit: '<path d="m15 4 5 5M4 20l5-1L21 7a2 2 0 0 0-5-5L4 14Z"/>',
		trash: '<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/>',
		pause: '<path d="M8 5v14M16 5v14"/>',
		stop: '<rect x="5" y="5" width="14" height="14" rx="2"/>',
		play: '<path d="m7 4 14 8-14 8Z"/>',
		external:
			'<path d="M14 3h7v7m0-7L10 14M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5"/>',
		download: '<path d="M12 3v12m-5-5 5 5 5-5M4 15v6h16v-6"/>',
		document: '<path d="M14 3H5v18h14V8Zm0 0v5h5M8 12h8m-8 4h6"/>',
		check: '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
		off: '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6m-6 0 6-6"/>',
		login: '<path d="M14 3h7v18h-7M3 12h13m-5-5 5 5-5 5"/>',
		bell: '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
		help: '<circle cx="12" cy="12" r="9"/><path d="M9 9a3 3 0 0 1 6 0c0 2-3 2-3 4m0 4h.01"/>',
		back: '<path d="M20 12H4m7-7-7 7 7 7"/>',
		save: '<path d="M3 3h14l4 4v14H3Zm4 0v6h10V3M7 21v-8h10v8"/>',
		plus: '<circle cx="12" cy="12" r="9"/><path d="M12 7v10M7 12h10"/>',
		palette:
			'<path d="M12 3a9 9 0 1 0 0 18h1a2 2 0 0 0 0-4 2 2 0 0 1 0-4h5a3 3 0 0 0 3-3c0-4-4-7-9-7Z"/><path d="M7 9h.01M11 6h.01M16 7h.01M6 14h.01"/>',
	});
	const cellText = (n) => {
		const c = n?.cloneNode(true);
		c?.querySelectorAll('.u-hide-desktop,.studio-icon,.studio-native-icon').forEach((x) => {
			x.remove();
		});
		return clean(c);
	};
	function enhanceNativeIcons(scope) {
		const map = {
			lock: 'shield',
			'puzzle-piece': 'grid',
			'right-to-bracket': 'login',
			pencil: 'edit',
			pen: 'edit',
			trash: 'trash',
			pause: 'pause',
			stop: 'stop',
			play: 'play',
			'square-up-right': 'external',
			download: 'download',
			binoculars: 'document',
			'arrow-rotate-left': 'refresh',
			'arrow-rotate-right': 'refresh',
			'arrows-rotate': 'refresh',
			'chart-bar': 'activity',
			'chart-line': 'activity',
			'chart-area': 'activity',
			'circle-check': 'check',
			'circle-xmark': 'off',
			'circle-minus': 'off',
			'user-check': 'login',
			'user-tie': 'users',
			'circle-user': 'users',
			gear: 'settings',
			bell: 'bell',
			'circle-question': 'help',
			'right-from-bracket': 'login',
			'arrow-left': 'back',
			'arrow-right': 'chevron',
			'floppy-disk': 'save',
			'circle-plus': 'plus',
			'box-open': 'backup',
			'shield-halved': 'shield',
			server: 'server',
			'hard-drive': 'disk',
			database: 'database',
			clock: 'clock',
			key: 'key',
			'earth-americas': 'globe',
			'book-atlas': 'dns',
			'envelopes-bulk': 'mail',
			'code-branch': 'refresh',
			ethernet: 'dns',
			'paint-brush': 'palette',
			'magnifying-glass': 'search',
		};
		scope.querySelectorAll('.top-bar-notifications > .top-bar-menu-link > i').forEach((n) => {
			n.classList.add('studio-native-icon');
			n.dataset.studioIcon = 'bell';
			n.replaceChildren(icon('bell'));
			n.setAttribute('aria-hidden', 'true');
		});
		scope.querySelectorAll('.fas:not(.studio-native-icon)').forEach((n) => {
			const fa = [...n.classList].find((c) => map[c.slice(3)] && c.startsWith('fa-'));
			if (!fa) return;
			const name = map[fa.slice(3)];
			n.classList.remove('fas', fa);
			n.classList.add('studio-native-icon');
			n.dataset.studioIcon = name;
			n.replaceChildren(icon(name));
			n.setAttribute('aria-hidden', 'true');
		});
		scope
			.querySelectorAll(
				'.units-table-row-action-link,.top-bar-menu-link,.button-floating,.units-table-cell [title]',
			)
			.forEach((n) => {
				if (n.dataset.studioTooltip) return;
				let label =
					n.getAttribute('title') ||
					clean(n.querySelector('.u-hide-desktop,.top-bar-menu-link-label'));
				if (!label) return;
				if (n.matches('.studio-native-icon')) {
					const heading = n.closest('.units-table-cell')?.dataset.studioLabel;
					label = (heading ? heading + ': ' : '') + label;
					n.removeAttribute('aria-hidden');
					n.setAttribute('role', 'img');
					n.tabIndex = 0;
				}
				n.dataset.studioTooltip = label;
				n.removeAttribute('title');
				if (!n.classList.contains('studio-profile')) n.setAttribute('aria-label', label);
				if (n.dataset.studioIcon === 'off') n.classList.add('studio-status-off');
				if (n.matches('a') && /^\/(delete|stop|suspend)\//.test(pathOf(n.href)))
					n.classList.add('studio-action-danger');
			});
	}
	function installTooltips() {
		const tip = el('div', 'studio-tooltip');
		tip.id = 'studio-tooltip';
		tip.setAttribute('role', 'tooltip');
		tip.hidden = true;
		document.body.append(tip);
		let active = null,
			hideTimer;
		const hide = () => {
			clearTimeout(hideTimer);
			if (active) {
				const ids = (active.getAttribute('aria-describedby') || '')
					.split(' ')
					.filter((x) => x && x !== tip.id);
				ids.length
					? active.setAttribute('aria-describedby', ids.join(' '))
					: active.removeAttribute('aria-describedby');
			}
			active = null;
			tip.hidden = true;
		};
		const show = (n) => {
			if (!n) return;
			hide();
			active = n;
			tip.textContent = n.dataset.studioTooltip;
			tip.hidden = false;
			n.setAttribute(
				'aria-describedby',
				[n.getAttribute('aria-describedby'), tip.id].filter(Boolean).join(' '),
			);
			const r = n.getBoundingClientRect(),
				b = tip.getBoundingClientRect();
			tip.style.left =
				Math.max(8, Math.min(innerWidth - b.width - 8, r.left + r.width / 2 - b.width / 2)) + 'px';
			tip.style.top =
				(r.top > b.height + 12
					? r.top - b.height - 9
					: Math.min(innerHeight - b.height - 8, r.bottom + 9)) + 'px';
		};
		document.addEventListener('pointerover', (e) => {
			const n = e.target.closest('[data-studio-tooltip]');
			if (n && n !== active) show(n);
		});
		document.addEventListener('pointerout', (e) => {
			if (
				active?.contains(e.target) &&
				!active.contains(e.relatedTarget) &&
				!tip.contains(e.relatedTarget)
			)
				hideTimer = setTimeout(hide, 120);
		});
		tip.addEventListener('pointerenter', () => clearTimeout(hideTimer));
		tip.addEventListener('pointerleave', hide);
		document.addEventListener('focusin', (e) => show(e.target.closest('[data-studio-tooltip]')));
		document.addEventListener('focusout', hide);
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') hide();
		});
		document.addEventListener('click', hide);
		window.addEventListener('scroll', hide, true);
		window.addEventListener('resize', hide);
	}
	function configureSections(config, t) {
		const sections = [...config.querySelectorAll(':scope > details.box-collapse')];
		if (!sections.length) return;
		config.classList.add('studio-config-container');
		const index = el('nav', 'studio-section-index');
		index.setAttribute('aria-label', t('sectionNavigation'));
		index.setAttribute('role', 'tablist');
		index.setAttribute('aria-orientation', 'vertical');
		const buttons = [],
			select = (chosen, focus = false) => {
				sections.forEach((section, i) => {
					const selected = section === chosen;
					section.hidden = !selected;
					section.open = selected;
					buttons[i].setAttribute('aria-selected', String(selected));
					buttons[i].tabIndex = selected ? 0 : -1;
				});
				if (focus) buttons[sections.indexOf(chosen)].focus();
			};
		sections.forEach((section, i) => {
			const summary = section.querySelector('summary');
			section.id ||= 'studio-section-' + i;
			const b = el('button', 'studio-config-tab');
			b.type = 'button';
			b.id = 'studio-config-tab-' + i;
			b.setAttribute('role', 'tab');
			b.setAttribute('aria-controls', section.id);
			const nativeIcon = summary.querySelector('i');
			b.append(
				nativeIcon ? nativeIcon.cloneNode(true) : icon('settings'),
				el('span', '', clean(summary)),
			);
			section.setAttribute('role', 'tabpanel');
			section.setAttribute('aria-labelledby', b.id);
			summary.addEventListener('click', (e) => e.preventDefault());
			summary.tabIndex = -1;
			b.addEventListener('click', () => select(section));
			buttons.push(b);
			index.append(b);
		});
		index.addEventListener('keydown', (e) => {
			const i = buttons.indexOf(e.target);
			if (i < 0) return;
			let next;
			if (e.key === 'ArrowDown') next = (i + 1) % buttons.length;
			if (e.key === 'ArrowUp') next = (i + buttons.length - 1) % buttons.length;
			if (e.key === 'Home') next = 0;
			if (e.key === 'End') next = buttons.length - 1;
			if (next !== undefined) {
				e.preventDefault();
				select(sections[next], true);
			}
		});
		config.prepend(index);
		select(sections.find((x) => '#' + x.id === location.hash) || sections[0]);
		let validating = false;
		config.addEventListener(
			'invalid',
			(e) => {
				if (validating) return;
				validating = true;
				setTimeout(() => {
					validating = false;
				}, 0);
				const section = e.target.closest('details.box-collapse');
				if (section?.hidden) select(section);
				let parent = e.target.parentElement;
				while (parent && parent !== config) {
					if (parent.tagName === 'DETAILS') parent.open = true;
					parent = parent.parentElement;
				}
			},
			true,
		);
	}
	function installPalette(t, root) {
		const colors = ['green', 'red', 'blue', 'yellow', 'orange', 'purple', 'pink'];
		let saved;
		try {
			saved = localStorage.getItem('hestia-studio-accent');
		} catch {}
		const trigger = el('button', 'studio-palette-trigger');
		trigger.type = 'button';
		trigger.append(icon('palette'));
		trigger.setAttribute('aria-label', t('palette'));
		trigger.dataset.studioTooltip = t('palette');
		trigger.setAttribute('aria-expanded', 'false');
		trigger.setAttribute('aria-controls', 'studio-palette');
		const panel = el('div', 'studio-palette');
		panel.id = 'studio-palette';
		panel.hidden = true;
		panel.setAttribute('role', 'group');
		panel.setAttribute('aria-label', t('palette'));
		panel.append(el('strong', '', t('palette')));
		const swatches = el('div', 'studio-swatches');
		const select = (color) => {
			root.dataset.studioAccent = colors.includes(color) ? color : 'green';
			swatches.querySelectorAll('button').forEach((b) => {
				b.setAttribute('aria-pressed', String(b.dataset.color === root.dataset.studioAccent));
			});
			try {
				localStorage.setItem('hestia-studio-accent', root.dataset.studioAccent);
			} catch {}
		};
		colors.forEach((color) => {
			const b = el('button', 'studio-swatch');
			b.type = 'button';
			b.dataset.color = color;
			b.setAttribute('aria-label', t(color));
			b.dataset.studioTooltip = t(color);
			b.append(icon('check'));
			b.addEventListener('click', () => select(color));
			swatches.append(b);
		});
		panel.append(swatches);
		select(saved);
		const close = () => {
			panel.hidden = true;
			trigger.setAttribute('aria-expanded', 'false');
		};
		trigger.addEventListener('click', () => {
			panel.hidden = !panel.hidden;
			trigger.setAttribute('aria-expanded', String(!panel.hidden));
			if (!panel.hidden) {
				const r = trigger.getBoundingClientRect();
				panel.style.top = r.bottom + 10 + 'px';
				panel.style.left = Math.max(8, Math.min(innerWidth - 280, r.right - 270)) + 'px';
				swatches.querySelector('[aria-pressed=true]')?.focus();
			}
		});
		document.addEventListener('click', (e) => {
			if (!panel.contains(e.target) && !trigger.contains(e.target)) close();
		});
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && !panel.hidden) {
				close();
				trigger.focus();
			}
		});
		document.addEventListener('focusin', (e) => {
			if (!panel.contains(e.target) && !trigger.contains(e.target)) close();
		});
		window.addEventListener('resize', close);
		document.querySelector('.top-bar-right')?.prepend(trigger);
		document.body.append(panel);
	}
	function themeLocale(language) {
		if (typeof language === 'string' && /^[a-z]{2,3}(?:-[a-z]{2,3})?$/i.test(language)) {
			try {
				return Intl.getCanonicalLocales(language)[0] || 'en';
			} catch {}
		}
		return 'en';
	}
	function rrdPoints(payload, metric) {
		const start = Number(payload?.meta?.start),
			step = Number(payload?.meta?.step);
		if (
			!Number.isFinite(start) ||
			!Number.isFinite(step) ||
			step <= 0 ||
			!Array.isArray(payload.data)
		)
			throw Error('Invalid series');
		if (metric !== 'la' && metric !== 'mem') throw Error('Unsupported series');
		// HestiaCP 1.10.9 exports [LA * 100, processes]; RAM is already in MiB.
		const divisor = metric === 'la' ? 100 : 1;
		return payload.data.slice(0, 4096).map((row, i) => ({
			time: (start + i * step) * 1000,
			value:
				Array.isArray(row) && typeof row[0] === 'number' && Number.isFinite(row[0]) && row[0] >= 0
					? row[0] / divisor
					: null,
		}));
	}
	function renderDashboard({ main, heading, ctx, t, card, usageValues, entries }) {
		const hasWeb = entries.some((e) => e.key === 'web');
		const nativeRows = (scope) => [...scope.querySelectorAll('.units-table-row')];
		const originalSummary = main.querySelector('.server-summary');
		// Keep native nodes in their existing forms. This page is a read-only overview;
		// administration remains on the unchanged Services page.
		[...main.children]
			.filter((n) => n !== heading)
			.forEach((n) => {
				n.hidden = true;
				n.inert = true;
				n.classList.add('studio-overview-native');
			});
		main.classList.add('studio-overview');
		const dashboard = el('div', 'studio-dashboard');
		heading.after(dashboard);
		const metrics = el('div', 'studio-summary-grid studio-resource-cards');
		const cpu = card(t('cpu') + ' · ' + t('live'), '—', 'cpu'),
			memory = card(t('memory'), '—', 'memory'),
			disk = card(t('disk') + ' (/)', '—', 'disk'),
			traffic = card(t('bandwidth'), usageValues[1] || '—', 'activity', t('usageScope'));
		const meters = new Map();
		for (const c of [cpu, memory, disk]) {
			const meter = el('progress', 'studio-resource-meter');
			meter.max = 100;
			meter.value = 0;
			meter.setAttribute('aria-label', clean(c.querySelector('.studio-stat-top')));
			const detail = el('small', 'studio-stat-detail');
			c.append(meter, detail);
			meters.set(c, { meter, detail });
		}
		metrics.append(cpu, memory, disk, traffic);
		dashboard.append(metrics);
		const layout = el('div', 'studio-dashboard-grid'),
			chart = el('section', 'studio-dashboard-card studio-resource-chart'),
			chartHead = el('div', 'studio-card-head');
		chartHead.append(el('h2', '', t('rrd')));
		const periods = el('div', 'studio-chart-periods');
		chartHead.append(periods);
		chart.append(chartHead);
		const tabs = el('div', 'studio-chart-tabs');
		chart.append(tabs);
		const plot = el('div', 'studio-resource-plot');
		chart.append(plot);
		const chartFoot = el('div', 'studio-chart-foot');
		chart.append(chartFoot);
		const server = el('section', 'studio-dashboard-card studio-server-card'),
			serverHead = el('div', 'studio-card-head');
		serverHead.append(
			el('h2', '', clean(originalSummary?.querySelector('.server-summary-title'))),
			icon('server'),
		);
		server.append(serverHead);
		const serverBody = el('dl', 'studio-server-facts');
		server.append(serverBody);
		layout.append(chart, server);
		const websites = el('section', 'studio-dashboard-card studio-websites'),
			activity = el('section', 'studio-dashboard-card studio-activity');
		const makeHead = (section, label, href, ico) => {
			const h = el('div', 'studio-card-head');
			h.append(el('h2', '', label));
			const a = el('a', 'studio-card-link');
			a.href = href;
			a.setAttribute('aria-label', label);
			a.dataset.studioTooltip = label;
			a.append(icon(ico || 'chevron'));
			h.append(a);
			section.append(h);
		};
		makeHead(websites, t('web'), '/list/web/');
		const webBody = el('div', 'studio-website-list');
		webBody.append(el('p', 'studio-empty', t('loading')));
		websites.append(webBody);
		if (hasWeb) layout.append(websites);
		const logBody = el('ol', 'studio-activity-list');
		if (ctx.logs) {
			makeHead(activity, t('logs'), '/list/log/', 'clock');
			logBody.append(el('li', 'studio-empty', t('loading')));
			activity.append(logBody);
			layout.append(activity);
		} else websites.classList.add('studio-wide');
		dashboard.append(layout);
		const services = el('section', 'studio-dashboard-card studio-services'),
			serviceHead = el('div', 'studio-card-head'),
			serviceCount = el('a', 'studio-service-count');
		serviceCount.href = '/list/server/';
		serviceHead.append(el('h2', '', t('serviceStatus')), serviceCount);
		services.append(serviceHead);
		const serviceGrid = el('div', 'studio-service-grid');
		services.append(serviceGrid);
		dashboard.append(services);
		const state = el('div', 'studio-dashboard-state'),
			stamp = el('time'),
			refresh = el('button', 'button button-secondary');
		refresh.type = 'button';
		refresh.append(icon('refresh'), el('span', '', t('refresh')));
		state.append(stamp, refresh);
		dashboard.append(state);
		const locale = themeLocale(ctx.language),
			number = new Intl.NumberFormat(locale, { maximumFractionDigits: 1 }),
			loadNumber = new Intl.NumberFormat(locale, { maximumFractionDigits: 2 });
		const bytes = (value) => {
			if (!Number.isFinite(value)) return '—';
			const index =
				value >= 2 ** 40 ? 4 : value >= 2 ** 30 ? 3 : value >= 2 ** 20 ? 2 : value >= 1024 ? 1 : 0;
			return (
				number.format(value / 1024 ** index) + '\u00a0' + ['B', 'KiB', 'MiB', 'GiB', 'TiB'][index]
			);
		};
		let livePoints = [],
			chartMetric = 'la',
			period = 'daily',
			series = [],
			requestId = 0,
			disposed = false,
			polling = false;
		const updateMetrics = (value) => {
			for (const [node, metric] of [
				[cpu, value?.cpu],
				[memory, value?.memory],
				[disk, value?.disk],
			]) {
				const isCpu = node === cpu,
					valid = isCpu
						? typeof metric === 'number' && Number.isFinite(metric) && metric >= 0 && metric <= 100
						: metric &&
							Number.isFinite(metric.total) &&
							Number.isFinite(metric.used) &&
							metric.total > 0 &&
							metric.used >= 0 &&
							metric.used <= metric.total;
				node.querySelector('.studio-stat-value').textContent = valid
					? isCpu
						? number.format(metric) + '%'
						: bytes(metric.used) + ' / ' + bytes(metric.total)
					: '—';
				const { meter, detail } = meters.get(node);
				meter.hidden = !valid;
				meter.value = valid ? (isCpu ? metric : (metric.used / metric.total) * 100) : 0;
				detail.textContent = valid
					? isCpu
						? t('live') + (value.cores ? ' · ' + value.cores + ' vCPU' : '')
						: number.format(meter.value) + '%'
					: t('unavailable');
			}
			livePoints.push({
				time: Number(value?.sampledAt) * 1000 || Date.now(),
				value:
					typeof value?.cpu === 'number' &&
					Number.isFinite(value.cpu) &&
					value.cpu >= 0 &&
					value.cpu <= 100
						? value.cpu
						: null,
			});
			livePoints = livePoints.slice(-120);
			stamp.dateTime = new Date().toISOString();
			stamp.textContent = t('live') + ' · ' + new Date().toLocaleTimeString(locale);
			if (chartMetric === 'cpu') {
				series = livePoints;
				draw();
			}
		};
		const updateServer = (scope) => {
			serverBody.replaceChildren();
			scope.querySelectorAll('.server-summary-item').forEach((item) => {
				const row = el('div');
				row.append(
					el('dt', '', clean(item.querySelector('.server-summary-list-label'))),
					el('dd', '', clean(item.querySelector('.server-summary-list-value'))),
				);
				serverBody.append(row);
			});
		};
		const updateServices = (scope) => {
			const rows = nativeRows(scope);
			serviceGrid.replaceChildren();
			let running = 0;
			rows.forEach((row) => {
				const name =
					row.dataset.sortName || clean(row.querySelector('.units-table-heading-cell a'));
				if (!name) return;
				const up = !row.classList.contains('disabled');
				if (up) running++;
				const item = el('div', 'studio-service-item'),
					text = el('div', 'studio-service-text');
				const ico = /php/.test(name)
					? 'terminal'
					: /mysql|maria|postgres/.test(name)
						? 'database'
						: /exim|dovecot|spam/.test(name)
							? 'mail'
							: /bind|dns/.test(name)
								? 'dns'
								: /nginx|apache/.test(name)
									? 'globe'
									: 'server';
				item.append(icon(ico));
				text.append(el('strong', '', name), el('small', '', cellText(row.children[3])));
				const status = el(
					'span',
					'studio-service-status' + (up ? '' : ' is-stopped'),
					t(up ? 'running' : 'stopped'),
				);
				item.append(text, status);
				serviceGrid.append(item);
			});
			serviceCount.textContent =
				running + ' / ' + serviceGrid.children.length + ' · ' + t('running');
			if (!serviceGrid.children.length) serviceGrid.append(el('p', 'studio-empty', t('noResults')));
		};
		const svgEl = (name, attrs, text) => {
			const n = document.createElementNS('http://www.w3.org/2000/svg', name);
			for (const [k, v] of Object.entries(attrs || {})) n.setAttribute(k, String(v));
			if (text !== undefined) n.textContent = text;
			return n;
		};
		function draw() {
			plot.replaceChildren();
			chartFoot.textContent =
				t(chartMetric === 'cpu' ? 'live' : period) +
				' · ' +
				t(chartMetric === 'la' ? 'loadAverage' : chartMetric === 'mem' ? 'memory' : 'cpu');
			const values = series.filter((p) => p.value !== null);
			if (!values.length) {
				plot.append(el('p', 'studio-empty', t('unavailable')));
				return;
			}
			const max =
					chartMetric === 'cpu'
						? 100
						: Math.max(1, Math.ceil(Math.max(...values.map((p) => p.value)) * 1.12)),
				start = series[0].time,
				end = series.at(-1).time,
				width = Math.max(320, plot.clientWidth - 32),
				height = 235,
				left = 50,
				top = 15,
				bottom = 200;
			const svg = svgEl('svg', {
				viewBox: `0 0 ${width} ${height}`,
				role: 'img',
				'aria-label': chartFoot.textContent,
				preserveAspectRatio: 'xMidYMid meet',
			});
			svg.append(svgEl('title', {}, chartFoot.textContent));
			for (let i = 0; i <= 4; i++) {
				const y = top + ((bottom - top) * i) / 4;
				svg.append(
					svgEl('line', { x1: left, y1: y, x2: width - 15, y2: y, class: 'studio-chart-gridline' }),
					svgEl(
						'text',
						{ x: left - 10, y: y + 4, 'text-anchor': 'end', class: 'studio-chart-axis' },
						(chartMetric === 'la' ? loadNumber : number).format(max * (1 - i / 4)) +
							(chartMetric === 'cpu' ? '%' : ''),
					),
				);
			}
			const x = (time) => left + ((time - start) / Math.max(1, end - start)) * (width - left - 15),
				y = (value) => bottom - (value / max) * (bottom - top);
			let path = '',
				connected = false;
			series.forEach((p) => {
				if (p.value === null) {
					connected = false;
					return;
				}
				path += (connected ? 'L' : 'M') + x(p.time).toFixed(2) + ' ' + y(p.value).toFixed(2) + ' ';
				connected = true;
			});
			svg.append(svgEl('path', { d: path, class: 'studio-chart-line' }));
			if (values.length === 1)
				svg.append(
					svgEl('circle', {
						cx: x(values[0].time),
						cy: y(values[0].value),
						r: 3,
						class: 'studio-chart-dot',
					}),
				);
			for (let i = 0; i < 3; i++) {
				const time = start + ((end - start) * i) / 2,
					label = new Date(time).toLocaleString(
						locale,
						period === 'daily' || chartMetric === 'cpu'
							? { hour: '2-digit', minute: '2-digit' }
							: { month: 'short', day: 'numeric' },
					);
				svg.append(
					svgEl(
						'text',
						{
							x: left + ((width - left - 15) * i) / 2,
							y: 227,
							'text-anchor': i === 0 ? 'start' : i === 2 ? 'end' : 'middle',
							class: 'studio-chart-axis',
						},
						label,
					),
				);
			}
			plot.append(svg);
			if (chartMetric === 'mem') chartFoot.append(el('span', '', 'MiB'));
		}
		// A closed allowlist of authenticated read-only Hestia routes; never API keys.
		const allowed = [
			'/list/server/?studio=overview',
			'/list/web/',
			'/list/log/',
			'/list/rrd/ajax.php',
		];
		const controllers = new Set();
		async function read(path, body) {
			if (!allowed.includes(path)) throw Error('Unsupported route');
			const controller = new AbortController();
			controllers.add(controller);
			const timeout = setTimeout(() => controller.abort(), 12000);
			try {
				const response = await fetch(path, {
					method: body ? 'POST' : 'GET',
					credentials: 'same-origin',
					cache: 'no-store',
					redirect: 'error',
					signal: controller.signal,
					...(body
						? { headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) }
						: {}),
				});
				if (!response.ok) throw Error('Request failed');
				if (body) return await response.json();
				const doc = new DOMParser().parseFromString(await response.text(), 'text/html'),
					meta = JSON.parse(doc.getElementById('studio-context')?.textContent || 'null');
				if (!meta || meta.account !== ctx.account || !meta.server) throw Error('Session changed');
				return { doc, meta };
			} finally {
				clearTimeout(timeout);
				controllers.delete(controller);
			}
		}
		async function loadChart() {
			const id = ++requestId;
			tabs.querySelectorAll('button').forEach((b) => {
				b.setAttribute('aria-pressed', String(b.dataset.metric === chartMetric));
			});
			periods.hidden = chartMetric === 'cpu';
			periods.querySelectorAll('button').forEach((b) => {
				b.setAttribute('aria-pressed', String(b.dataset.period === period));
			});
			if (chartMetric === 'cpu') {
				series = livePoints;
				draw();
				return;
			}
			plot.replaceChildren(el('p', 'studio-empty', t('loading')));
			try {
				const payload = await read('/list/rrd/ajax.php', { service: chartMetric, period });
				if (id !== requestId || disposed) return;
				series = rrdPoints(payload, chartMetric);
				draw();
			} catch {
				if (id === requestId && !disposed) {
					series = [];
					draw();
				}
			}
		}
		for (const [metric, label] of [
			['la', 'loadAverage'],
			['mem', 'memory'],
			['cpu', 'cpu'],
		]) {
			const b = el('button', '', t(label) + (metric === 'cpu' ? ' · ' + t('live') : ''));
			b.type = 'button';
			b.dataset.metric = metric;
			b.addEventListener('click', () => {
				chartMetric = metric;
				loadChart();
			});
			tabs.append(b);
		}
		for (const p of ['daily', 'weekly', 'monthly']) {
			const b = el('button', '', t(p));
			b.type = 'button';
			b.dataset.period = p;
			b.addEventListener('click', () => {
				period = p;
				loadChart();
			});
			periods.append(b);
		}
		async function loadWeb() {
			if (!hasWeb) return;
			try {
				const { doc } = await read('/list/web/');
				if (disposed) return;
				const nativeAdd = doc.querySelector('a[href="/add/web/"]');
				if (nativeAdd && !heading.querySelector('.studio-primary-action')) {
					const add = el('a', 'button studio-primary-action');
					add.href = '/add/web/';
					add.append(icon('plus'), el('span', '', clean(nativeAdd)));
					heading.append(add);
				}
				webBody.replaceChildren();
				const headers = [...doc.querySelectorAll('.units-table-header>.units-table-cell')];
				const header = el('div', 'studio-website-row studio-website-header');
				for (const label of ['', clean(headers[1]), '', clean(headers[6]), clean(headers[4])])
					header.append(el('span', '', label));
				webBody.append(header);
				nativeRows(doc)
					.slice(0, 6)
					.forEach((row) => {
						const item = el('div', 'studio-website-row'),
							name = row.dataset.sortName || cellText(row.children[1]),
							link = el('a', 'studio-website-name', name),
							nativeLink = row.children[1]?.querySelector('a');
						const href = nativeLink?.getAttribute('href');
						link.href = href?.startsWith('/edit/web/?') ? href : '/list/web/';
						const avatar = el(
							'span',
							'studio-domain-avatar',
							Array.from(name)[0]?.toLocaleUpperCase() || '',
						);
						const ssl = row.children[6]?.querySelector('[title]'),
							status = el('span', 'studio-site-ssl');
						status.append(
							icon(ssl?.classList.contains('fa-circle-check') ? 'shield' : 'off'),
							el('span', '', ssl?.getAttribute('title') || '—'),
						);
						status.dataset.studioTooltip =
							'SSL: ' + (ssl?.getAttribute('title') || t('unavailable'));
						const stateIcon = icon(row.classList.contains('disabled') ? 'pause' : 'check');
						stateIcon.dataset.studioTooltip = t(
							row.classList.contains('disabled') ? 'stopped' : 'active',
						);
						stateIcon.setAttribute('role', 'img');
						stateIcon.setAttribute('aria-label', stateIcon.dataset.studioTooltip);
						item.append(
							avatar,
							link,
							stateIcon,
							status,
							el('span', 'studio-site-disk', cellText(row.children[4])),
						);
						webBody.append(item);
					});
				if (webBody.children.length === 1) webBody.append(el('p', 'studio-empty', t('noResults')));
			} catch {
				if (!disposed) webBody.replaceChildren(el('p', 'studio-empty', t('unavailable')));
			}
		}
		async function loadLogs() {
			if (!ctx.logs) return;
			try {
				const { doc } = await read('/list/log/');
				if (disposed) return;
				logBody.replaceChildren();
				nativeRows(doc)
					.slice(0, 5)
					.forEach((row) => {
						const item = el('li'),
							text = el('div');
						text.append(
							el('strong', '', cellText(row.children[4])),
							el('small', '', cellText(row.children[3])),
							el('time', '', cellText(row.children[1]) + ' · ' + cellText(row.children[2])),
						);
						item.append(icon('clock'), text);
						logBody.append(item);
					});
				if (!logBody.children.length) logBody.append(el('li', 'studio-empty', t('noResults')));
			} catch {
				if (!disposed) logBody.replaceChildren(el('li', 'studio-empty', t('unavailable')));
			}
		}
		async function refreshMetrics() {
			if (polling || disposed) return;
			polling = true;
			refresh.disabled = true;
			try {
				const { doc, meta } = await read('/list/server/?studio=overview');
				if (disposed) return;
				updateMetrics(meta.metrics);
				updateServer(doc);
				updateServices(doc);
			} catch {
				if (!disposed) {
					updateMetrics(null);
					stamp.textContent = t('unavailable');
				}
			} finally {
				polling = false;
				refresh.disabled = false;
			}
		}
		updateServer(main);
		updateServices(main);
		updateMetrics(ctx.metrics);
		loadChart();
		// Restore Hestia's normal back-page context after the initial auxiliary reads.
		Promise.allSettled([loadWeb(), loadLogs()]).then(() => refreshMetrics());
		refresh.addEventListener('click', () => {
			refreshMetrics();
			loadChart();
		});
		window.addEventListener('resize', () => {
			if (series.length && !disposed) draw();
		});
		const interval = setInterval(() => {
			if (!document.hidden) refreshMetrics();
		}, 30000);
		window.addEventListener(
			'pagehide',
			() => {
				disposed = true;
				clearInterval(interval);
				controllers.forEach((c) => {
					c.abort();
				});
			},
			{ once: true },
		);
	}
	function boot() {
		const root = document.documentElement;
		if (
			getComputedStyle(root).getPropertyValue('--studio-loaded').trim() !== '4' ||
			root.dataset.studioReady
		)
			return;
		let ctx;
		try {
			ctx = JSON.parse(document.getElementById('studio-context')?.textContent || 'null');
		} catch {}
		if (!ctx?.labels) return;
		const t = (key) => ctx.labels[key] || '';
		const locale = themeLocale(ctx.language);
		root.dataset.studioReady = '2.1.1';
		root.lang = locale;
		root.dir = /^(ar|fa|ku|ur)(-|$)/i.test(locale) ? 'rtl' : 'ltr';
		let stored;
		try {
			stored = localStorage.getItem('hestia-studio-color');
		} catch {}
		function setTheme(mode) {
			root.dataset.studioTheme = mode;
			try {
				localStorage.setItem('hestia-studio-color', mode);
			} catch {}
			document.querySelectorAll('[data-studio-mode]').forEach((b) => {
				b.setAttribute('aria-pressed', String(b.dataset.studioMode === mode));
			});
		}
		setTheme(
			['light', 'dark'].includes(stored)
				? stored
				: matchMedia('(prefers-color-scheme: dark)').matches
					? 'dark'
					: 'light',
		);
		const main = document.querySelector('.app-content'),
			nav = document.querySelector('.main-menu');
		if (!main || !nav) return;
		const nativeLinks = [
			...nav.querySelectorAll('.main-menu-item-link'),
			...document.querySelectorAll('.top-bar-menu a'),
		];
		const source = (path) => nativeLinks.find((a) => pathOf(a.href) === path),
			entries = [];
		const add = (key, href, ico, section, count) => {
			if (href && !entries.some((e) => e.key === key))
				entries.push({ key, href, ico, section, count });
		};
		const serverSource = source('/list/server/'),
			home =
				ctx.server && serverSource
					? '/list/server/?studio=overview'
					: '/list/stats/?studio=overview';
		if (ctx.landing && location.pathname === '/list/user/' && !location.search && !location.hash) {
			location.replace(home);
			return;
		}
		add('overview', home, 'grid', 'workspace');
		for (const [key, ico] of [
			['web', 'globe'],
			['mail', 'mail'],
			['dns', 'dns'],
			['db', 'database'],
			['cron', 'clock'],
			['backup', 'backup'],
		]) {
			const a = source('/list/' + key + '/');
			if (a)
				add(
					key,
					a.getAttribute('href'),
					ico,
					'workspace',
					clean(a.querySelector('.main-menu-stats li')).match(/:\s*(\d+)/)?.[1],
				);
		}
		for (const [key, path, ico] of [
			['files', '/fm/', 'folder'],
			['terminal', '/list/terminal/', 'terminal'],
			['stats', '/list/stats/', 'activity'],
		]) {
			const a = source(path);
			if (a) add(key, a.getAttribute('href'), ico, 'workspace');
		}
		if (ctx.admin && source('/list/user/')) {
			const a = source('/list/user/');
			add(
				'users',
				a.getAttribute('href'),
				'users',
				'serverManagement',
				clean(a.querySelector('.main-menu-stats li')).match(/:\s*(\d+)/)?.[1],
			);
			add('packages', '/list/package/', 'backup', 'serverManagement');
			add('ip', '/list/ip/', 'globe', 'serverManagement');
		}
		if (ctx.server && serverSource) {
			add('services', '/list/server/', 'server', 'serverManagement');
			if (ctx.firewall) add('firewall', '/list/firewall/', 'shield', 'serverManagement');
			add('rrd', '/list/rrd/', 'activity', 'serverManagement');
			add('settings', '/edit/server/', 'settings', 'serverManagement');
			add('updates', '/list/updates/', 'refresh', 'serverManagement');
		}
		const profileSource = source('/edit/user/');
		if (profileSource)
			add('profile', profileSource.getAttribute('href'), 'users', 'accountSection');
		if (ctx.keys) add('keys', '/list/key/', 'key', 'accountSection');
		if (ctx.accessKeys) add('accessKeys', '/list/access-key/', 'key', 'accountSection');
		if (ctx.logs) add('logs', '/list/log/', 'clock', 'accountSection');
		const viewPath = location.pathname,
			resource = viewPath.split('/').filter(Boolean)[1] || '';
		const overview =
			new URLSearchParams(location.search).get('studio') === 'overview' &&
			['/list/server/', '/list/stats/'].includes(viewPath);
		const groupMap = {
			user: 'users',
			package: 'packages',
			key: 'keys',
			'access-key': 'accessKeys',
			server: 'services',
			rrd: 'rrd',
			log: 'logs',
			firewall: 'firewall',
			ip: 'ip',
			stats: 'stats',
			updates: 'updates',
			web: 'web',
			mail: 'mail',
			dns: 'dns',
			db: 'db',
			cron: 'cron',
			backup: 'backup',
		};
		let pageKey = overview ? 'overview' : groupMap[resource] || 'workspace';
		if (viewPath === '/edit/server/') pageKey = 'settings';
		if (
			resource === 'user' &&
			viewPath.startsWith('/edit/') &&
			(!new URLSearchParams(location.search).get('user') ||
				new URLSearchParams(location.search).get('user') === ctx.account)
		)
			pageKey = 'profile';
		const nativeHeading = [...main.querySelectorAll('h1')].find(
			(h) => !h.closest('.server-summary'),
		);
		const title =
			pageKey === 'settings'
				? t('settings')
				: /\/(add|edit|setup)\//.test(viewPath) && nativeHeading
					? clean(nativeHeading)
					: t(pageKey);
		root.dataset.studioPage = pageKey;
		const makeLink = (e) => {
			const a = el('a', 'studio-nav-link' + (e.key === pageKey ? ' active' : ''));
			a.href = e.href;
			a.append(icon(e.ico), el('span', 'studio-nav-text', t(e.key)));
			if (e.count !== undefined) a.append(el('span', 'studio-nav-count', e.count));
			if (e.key === pageKey) a.setAttribute('aria-current', 'page');
			return a;
		};
		const old = nav.querySelector('.container');
		if (old) {
			old.classList.add('studio-native-navigation');
			old.inert = true;
			old.querySelectorAll('.main-menu-item').forEach((n) => {
				n.classList.remove('main-menu-item');
			});
		}
		const panel = el('div', 'studio-navigation-panel'),
			workspace = el('div', 'studio-workspace'),
			workspaceText = el('div');
		workspace.append(icon('server'));
		workspaceText.append(el('strong', '', location.hostname), el('small', '', ctx.account));
		workspace.append(workspaceText);
		panel.append(workspace);
		const scroll = el('div', 'studio-nav-scroll');
		for (const section of ['workspace', 'serverManagement', 'accountSection']) {
			const items = entries.filter((e) => e.section === section);
			if (!items.length) continue;
			scroll.append(el('p', 'studio-nav-label', t(section)));
			const list = el('ul', 'studio-nav-list');
			for (const e of items) {
				const li = el('li', 'main-menu-item' + (e.key === pageKey ? ' active' : ''));
				li.append(makeLink(e));
				list.append(li);
			}
			scroll.append(list);
		}
		panel.append(scroll);
		const foot = el('div', 'studio-sidebar-foot'),
			modes = el('div', 'studio-theme-bar');
		modes.setAttribute('aria-label', t('theme'));
		for (const mode of ['light', 'dark']) {
			const b = el('button');
			b.type = 'button';
			b.dataset.studioMode = mode;
			b.append(icon(mode === 'light' ? 'sun' : 'moon'), el('span', '', t(mode)));
			b.addEventListener('click', () => setTheme(mode));
			modes.append(b);
		}
		foot.append(modes);
		const version = el('div', 'studio-version');
		version.append(
			el('span', '', t('brand')),
			el(
				'span',
				'',
				clean(document.querySelector('.app-footer')).match(/v\d+\.\d+\.\d+/)?.[0] || '',
			),
		);
		foot.append(version);
		panel.append(foot);
		nav.append(panel);
		nav.id = 'studio-navigation';
		nav.setAttribute('aria-label', t('workspace'));
		setTheme(root.dataset.studioTheme);
		// Keep Hestia's logo and configured application name.
		const logo = document.querySelector('.top-bar-logo');
		if (logo) logo.href = home;
		const usage = document.querySelector('.top-bar-usage'),
			usageValues = usage
				? [...usage.querySelectorAll('.top-bar-usage-item')].slice(1).map(clean)
				: [];
		if (usage) {
			usage.classList.add('studio-native-usage');
			const crumb = el('div', 'studio-breadcrumb');
			crumb.append(el('span', '', t('workspace')), icon('chevron'), el('strong', '', title));
			usage.after(crumb);
		}
		if (profileSource) {
			// Hestia already supplies the current account's Contact Name in this title.
			// Do not read edit-form inputs: they may belong to a different account.
			const title = profileSource.getAttribute('title') || '',
				prefix = String(ctx.account) + ' (';
			const contact =
				title.startsWith(prefix) && title.endsWith(')')
					? title.slice(prefix.length, -1).trim()
					: '';
			const displayName = (contact || String(ctx.account)).replace(/\s+/g, ' ').trim();
			const segmenter =
				typeof Intl.Segmenter === 'function'
					? new Intl.Segmenter(locale, { granularity: 'grapheme' })
					: null;
			const letters = (value) =>
				segmenter ? [...segmenter.segment(value)].map((part) => part.segment) : Array.from(value);
			const words = displayName.split(/\s+/),
				initials = (
					words.length > 1
						? letters(words[0])[0] + letters(words[words.length - 1])[0]
						: letters(displayName).slice(0, 2).join('')
				).toLocaleUpperCase(locale);
			profileSource.classList.add('studio-profile');
			profileSource.replaceChildren();
			const text = el('span', 'studio-profile-text'),
				avatar = el('span', 'studio-avatar', initials);
			avatar.setAttribute('aria-hidden', 'true');
			text.append(
				el('strong', '', displayName),
				el('small', '', t(ctx.impersonating ? 'impersonating' : ctx.admin ? 'admin' : 'user')),
			);
			profileSource.append(avatar, text);
		}
		const heading = el('section', 'studio-page-heading'),
			intro = el('div');
		intro.append(
			el('div', 'studio-eyebrow', t('brand') + ' ' + t('brandSubtitle')),
			el('h1', '', title),
		);
		const description = t(pageKey + 'Description');
		if (description) intro.append(el('p', '', description));
		heading.append(intro);
		main.prepend(heading);
		if (nativeHeading) nativeHeading.classList.add('studio-native-heading');
		const primary = main.querySelector('.toolbar a[href^="/add/"]');
		if (primary) {
			heading.append(primary);
			primary.classList.add('studio-primary-action');
		}
		main.querySelectorAll('.toolbar form').forEach((f) => {
			if (f.querySelector('.toolbar-input-submit')) f.classList.add('studio-input-group');
		});
		// Form elements retain their original form and Alpine ancestors.
		const config = main.querySelector('form[name="v_configure_server"] .form-container');
		if (config) configureSections(config, t);
		const card = (label, value, ico, detail) => {
			const c = el('article', 'studio-stat'),
				top = el('div', 'studio-stat-top');
			top.append(el('span', '', label), icon(ico));
			c.append(top, el('strong', 'studio-stat-value', value));
			if (detail) c.append(el('small', 'studio-stat-detail', detail));
			return c;
		};
		const tables = [...main.querySelectorAll('.units-table')];
		tables.forEach((table) => {
			const headers = [...table.querySelectorAll('.units-table-header>.units-table-cell')];
			table.style.setProperty('--studio-columns', String(headers.length));
			table.querySelectorAll('.units-table-row').forEach((row) => {
				[...row.children].forEach((cell, i) => {
					if (!cell.classList.contains('units-table-cell')) return;
					const h = headers[i],
						label = clean(h) || h?.querySelector('[title]')?.getAttribute('title') || '';
					if (label) cell.dataset.studioLabel = label;
				});
			});
		});
		const table = tables[0],
			rows = table
				? [...table.querySelectorAll('.units-table-row')].filter(
						(r) => !r.classList.contains('u-hidden'),
					)
				: [];
		if (table && rows.length && !overview) {
			const summary = el('div', 'studio-summary-grid');
			summary.append(
				card(t('shown'), String(rows.length), 'grid'),
				card(
					t(pageKey === 'services' ? 'running' : 'active'),
					String(rows.filter((r) => !r.classList.contains('disabled')).length),
					'activity',
				),
			);
			if (usageValues[0]) summary.append(card(t('disk'), usageValues[0], 'disk', t('usageScope')));
			if (usageValues[1])
				summary.append(card(t('bandwidth'), usageValues[1], 'activity', t('usageScope')));
			heading.after(summary);
		}

		if (Array.isArray(ctx.statistics) && ctx.statistics.length > 1) {
			const nativeMonths = [...main.querySelectorAll('.stats-item')];
			const series = ctx.statistics
				.map((p, i) => ({
					...p,
					label: clean(nativeMonths[i]?.querySelector('.stats-item-header-title')),
					values: [...(nativeMonths[i]?.querySelectorAll('.stats-item-summary-title') || [])].map(
						(h) => clean(h.children[h.children.length - 1]),
					),
				}))
				.sort((a, b) => a.month.localeCompare(b.month))
				.slice(-12);
			const charts = el('div', 'studio-charts');
			for (const [metric, idx] of [
				['disk', 1],
				['bandwidth', 0],
			]) {
				const figure = el('figure', 'studio-history-card');
				figure.append(el('figcaption', '', t(metric)));
				const plot = el('div', 'studio-history-bars');
				plot.setAttribute('role', 'img');
				plot.setAttribute(
					'aria-label',
					t(metric) + ' · ' + series.map((p) => p.label + ': ' + p.values[idx]).join('; '),
				);
				const max = Math.max(...series.map((p) => p[metric]), 1);
				for (const point of series) {
					const col = el('div', 'studio-history-column');
					col.title = point.label + ' · ' + point.values[idx];
					const bar = el('span', 'studio-history-bar');
					bar.style.height = Math.max(2, (point[metric] / max) * 100) + '%';
					col.append(bar, el('small', '', point.label));
					plot.append(col);
				}
				figure.append(plot);
				charts.append(figure);
			}
			const stats = main.querySelector('.stats');
			stats?.before(charts);
		}

		const months = [...main.querySelectorAll('.stats-item')];
		if (months.length) {
			const summary = el('div', 'studio-summary-grid'),
				first = months[0],
				values = [...first.querySelectorAll('.stats-item-summary-title')].map((h) =>
					clean(h.children[h.children.length - 1]),
				);
			summary.append(
				card(t('latestPeriod'), clean(first.querySelector('.stats-item-header-title')), 'clock'),
			);
			if (values[0]) summary.append(card(t('bandwidth'), values[0], 'activity'));
			if (values[1]) summary.append(card(t('disk'), values[1], 'disk'));
			heading.after(summary);
		}
		if (overview && ctx.server && viewPath === '/list/server/') {
			renderDashboard({ main, heading, ctx, t, card, usageValues, entries });
		} else if (overview) {
			const summary = el('div', 'studio-summary-grid');
			for (const e of entries.filter((e) => e.count !== undefined).slice(0, 4))
				summary.append(card(t(e.key), e.count, e.ico));
			if (!summary.childElementCount)
				for (const [i, key] of ['disk', 'bandwidth'].entries())
					if (usageValues[i]) summary.append(card(t(key), usageValues[i], i ? 'activity' : 'disk'));
			heading.after(summary);
			const quick = el('section', 'studio-quick-card');
			quick.append(el('h2', '', t('quickLinks')));
			const links = el('div', 'studio-quick-links');
			for (const e of entries.filter((e) =>
				['web', 'mail', 'dns', 'users', 'settings', 'backup', 'stats', 'rrd'].includes(e.key),
			))
				links.append(makeLink({ ...e, count: undefined }));
			quick.append(links);
			summary.after(quick);
			main.classList.add('studio-overview');
		}
		installPalette(t, root);
		enhanceNativeIcons(document);
		installTooltips();
		const search = el('button', 'studio-search-trigger');
		search.type = 'button';
		search.setAttribute('aria-label', t('searchPages'));
		search.append(
			icon('search'),
			el('span', '', t('searchPages')),
			el('kbd', '', t('searchShortcut')),
		);
		document.querySelector('.top-bar-right')?.prepend(search);
		const dialog = el('dialog', 'studio-command');
		dialog.setAttribute('aria-label', t('searchPages'));
		const input = el('input');
		input.type = 'search';
		input.placeholder = t('searchPlaceholder');
		input.setAttribute('aria-label', t('searchPages'));
		const close = el('button', 'studio-command-close');
		close.type = 'button';
		close.setAttribute('aria-label', t('close'));
		close.append(icon('close'));
		const results = el('div', 'studio-command-results');
		dialog.append(input, close, results);
		document.body.append(dialog);
		const populate = () => {
			const q = input.value.toLocaleLowerCase(locale);
			results.replaceChildren();
			for (const e of entries.filter((e) => t(e.key).toLocaleLowerCase(locale).includes(q))) {
				const a = makeLink({ ...e, count: undefined });
				a.addEventListener('click', () => dialog.close());
				results.append(a);
			}
			if (!results.children.length) results.append(el('p', 'studio-empty', t('noResults')));
		};
		const openSearch = () => {
			input.value = '';
			populate();
			dialog.showModal();
			input.focus();
		};
		search.addEventListener('click', openSearch);
		close.addEventListener('click', () => dialog.close());
		input.addEventListener('input', populate);
		dialog.addEventListener('click', (e) => {
			if (e.target === dialog) dialog.close();
		});
		dialog.addEventListener('close', () => search.focus());
		input.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowDown') {
				e.preventDefault();
				results.querySelector('a')?.focus();
			}
			if (e.key === 'Enter') {
				const a = results.querySelector('a');
				if (a) a.click();
			}
		});
		document.addEventListener('keydown', (e) => {
			if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
				e.preventDefault();
				if (!dialog.open) openSearch();
			}
		});
		const mobile = el('button', 'studio-mobile-button');
		mobile.type = 'button';
		mobile.append(icon('menu'));
		mobile.setAttribute('aria-label', t('toggleMenu'));
		mobile.setAttribute('aria-controls', nav.id);
		mobile.setAttribute('aria-expanded', 'false');
		document.querySelector('.top-bar-left')?.prepend(mobile);
		const mq = matchMedia('(max-width:767px)');
		const sync = () => {
			const open = root.classList.contains('studio-mobile-open');
			nav.inert = mq.matches && !open;
			main.inert = mq.matches && open;
			mobile.setAttribute('aria-expanded', String(open));
		};
		const shut = () => {
			root.classList.remove('studio-mobile-open');
			sync();
		};
		mobile.addEventListener('click', () => {
			root.classList.toggle('studio-mobile-open');
			sync();
			if (!nav.inert) scroll.querySelector('a')?.focus();
		});
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && root.classList.contains('studio-mobile-open')) {
				shut();
				mobile.focus();
			}
		});
		document.addEventListener('click', (e) => {
			if (
				root.classList.contains('studio-mobile-open') &&
				!nav.contains(e.target) &&
				!mobile.contains(e.target)
			)
				shut();
		});
		nav.querySelectorAll('a').forEach((a) => {
			a.addEventListener('click', shut);
		});
		mq.addEventListener('change', sync);
		sync();
	}
	if (document.readyState === 'loading')
		document.addEventListener('DOMContentLoaded', boot, { once: true });
	else boot();
})();
