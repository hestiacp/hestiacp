import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import test from "node:test";
import { JSDOM } from "jsdom";

const source = readFileSync(new URL("../web/js/src/studio.js", import.meta.url), "utf8");
const labels = JSON.parse(
	readFileSync(
		new URL("../web/templates/includes/studio-locales/en.json", import.meta.url),
		"utf8",
	),
);

// Minimal native form/navigation structure. All values are fictional; no controller
// or administrative command is executed by these DOM tests.
const html = `<!doctype html><html lang="en"><body>
<div class="top-bar"><div class="top-bar-left">
<a class="top-bar-logo" href="/" title="Hestia Control Panel"><img src="/images/logo-header.svg" alt="Hestia Control Panel"></a>
</div><div class="top-bar-right"><div class="top-bar-menu">
<a href="/edit/user/" title="fixture-user (Example User)">Profile</a>
</div></div></div>
<nav class="main-menu"><div class="container"><ul>
<li class="main-menu-item"><a class="main-menu-item-link" href="/list/web/">Web</a></li>
</ul></div></nav>
<main class="app-content"><form name="v_configure_server" id="main-form" method="post" action="/edit/server/" x-data="{theme: 'studio'}">
<input type="hidden" name="token" value="synthetic-csrf-token">
<input type="hidden" name="save" value="save">
<div class="form-container"><h1>Configure Server</h1>
<details class="box-collapse"><summary><i class="fas fa-gear"></i>Basic Options</summary><div class="box-collapse-content">
<input name="v_hostname" value="panel.example.test" required>
<select name="v_theme" x-model="theme"><option value="studio" selected>studio</option></select>
</div></details>
<details class="box-collapse"><summary>Security</summary><div class="box-collapse-content" x-data="{enabled: true}">
<details class="collapse"><summary>Nested settings</summary><input name="v_required" required value=""></details>
<input type="checkbox" name="v_enabled" checked>
</div></details>
<button type="submit" name="save-button">Save</button>
</div></form></main><footer class="app-footer">Hestia v1.10.10</footer>
</body></html>`;

function fixture(withContext = true, language = "en", mobile = false) {
	const dom = new JSDOM(html, {
		url: "https://panel.example.test/edit/server/",
		runScripts: "outside-only",
	});
	const { window } = dom;
	window.getComputedStyle = () => ({ getPropertyValue: () => "4" });
	window.matchMedia = (query) => ({
		matches: mobile && query === "(max-width:767px)",
		addEventListener() {},
	});
	window.fetch = () => {
		throw new Error("Settings page must not issue dashboard requests");
	};
	if (withContext) {
		const context = window.document.createElement("script");
		context.type = "application/json";
		context.id = "studio-context";
		context.textContent = JSON.stringify({
			account: "fixture-user",
			admin: false,
			server: false,
			language,
			labels,
		});
		window.document.head.append(context);
	}
	const boot = () => {
		window.eval(source);
		window.document.dispatchEvent(new window.Event("DOMContentLoaded"));
	};
	return { dom, window, document: window.document, boot };
}

test("enhancements preserve native form ownership, values, CSRF, handlers and Alpine ancestors", () => {
	const view = fixture();
	const { window, document, boot } = view;
	try {
		const form = document.querySelector("form");
		const fields = [...form.elements];
		const before = [...new window.FormData(form)];
		const ancestors = fields.map((field) => field.closest("[x-data]"));
		const logo = document.querySelector(".top-bar-logo img");
		let received = 0;
		fields[2].addEventListener("change", () => received++);
		boot();
		assert.deepEqual([...new window.FormData(form)], before);
		assert.equal(form.getAttribute("action"), "/edit/server/");
		assert.equal(form.method, "post");
		assert.equal(form.name, "v_configure_server");
		for (const [i, field] of fields.entries()) {
			assert.ok(field.isConnected);
			assert.equal(field.form, form);
			assert.equal(field.closest("[x-data]"), ancestors[i]);
		}
		fields[2].dispatchEvent(new window.Event("change"));
		assert.equal(received, 1);
		assert.equal(document.querySelector(".top-bar-logo img"), logo);
		assert.equal(logo.alt, "Hestia Control Panel");
		const tabs = [...document.querySelectorAll(".studio-config-tab")];
		assert.equal(tabs.length, 2);
		assert.ok(tabs.every((tab) => tab.type === "button"));
		tabs[1].click();
		assert.equal(tabs[1].getAttribute("aria-selected"), "true");
		tabs[0].click();
		const invalid = form.elements.v_required;
		invalid.dispatchEvent(new window.Event("invalid"));
		assert.equal(invalid.closest(".box-collapse").hidden, false);
		assert.equal(invalid.closest(".collapse").open, true);
		assert.deepEqual([...new window.FormData(form)], before);
		boot();
		assert.equal(document.querySelectorAll(".studio-navigation-panel").length, 1);
	} finally {
		view.dom.window.close();
	}
});

test("all seven palettes, both appearances and the mobile menu remain usable", () => {
	const view = fixture(true, "ar", true);
	try {
		const { window, document, boot } = view;
		boot();
		assert.equal(document.documentElement.lang, "ar");
		assert.equal(document.documentElement.dir, "rtl");
		const swatches = [...document.querySelectorAll(".studio-swatch")];
		assert.deepEqual(
			swatches.map((button) => button.dataset.color),
			["green", "red", "blue", "yellow", "orange", "purple", "pink"],
		);
		for (const button of swatches) {
			button.click();
			assert.equal(document.documentElement.dataset.studioAccent, button.dataset.color);
			assert.equal(window.localStorage.getItem("hestia-studio-accent"), button.dataset.color);
		}
		for (const mode of ["dark", "light"]) {
			document.querySelector(`[data-studio-mode="${mode}"]`).click();
			assert.equal(document.documentElement.dataset.studioTheme, mode);
			assert.equal(window.localStorage.getItem("hestia-studio-color"), mode);
		}
		const menu = document.querySelector(".studio-mobile-button");
		menu.click();
		assert.equal(menu.getAttribute("aria-expanded"), "true");
		assert.equal(document.querySelector(".app-content").inert, true);
		document.dispatchEvent(new window.KeyboardEvent("keydown", { key: "Escape" }));
		assert.equal(menu.getAttribute("aria-expanded"), "false");
		assert.equal(document.querySelector(".app-content").inert, false);
	} finally {
		view.dom.window.close();
	}
});

test("without a Studio context the bundle leaves the native document untouched", () => {
	const view = fixture(false);
	try {
		const before = view.document.documentElement.outerHTML;
		view.boot();
		assert.equal(view.document.documentElement.outerHTML, before);
	} finally {
		view.dom.window.close();
	}
});
