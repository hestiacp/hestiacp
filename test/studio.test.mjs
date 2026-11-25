import assert from "node:assert/strict";
import { readFile, readdir } from "node:fs/promises";
import { execFileSync } from "node:child_process";
import vm from "node:vm";
const languages =
	"ar az bg bn bs ca cs da de el en es et fa fi fr hr hu id it ja ka ko ku lo nl no pl pt pt-br ro ru sk sq sr sv th tr uk ur vi zh-cn zh-tw".split(
		" ",
	);
const base = JSON.parse(await readFile("web/templates/includes/studio-locales/en.json", "utf8"));
assert.deepEqual(
	(await readdir("web/templates/includes/studio-locales")).sort(),
	languages.map((x) => x + ".json").sort(),
);
for (const language of languages) {
	const labels = JSON.parse(
		await readFile(`web/templates/includes/studio-locales/${language}.json`, "utf8"),
	);
	assert.deepEqual(Object.keys(labels), Object.keys(base), language);
	assert.ok(
		Object.values(labels).every((s) => typeof s === "string" && s.trim()),
		language,
	);
}
const bridge = new URL("../web/templates/includes/studio.php", import.meta.url).pathname.replace(
	/^\/(?=[A-Za-z]:)/,
	"",
);
const php = `$_SESSION=json_decode(base64_decode($argv[1]),true);$languageBefore=array_intersect_key($_SESSION,array_flip(['language','LANGUAGE']));$page=$argv[3];$_SERVER['REQUEST_METHOD']=$argv[4];$_GET=json_decode(base64_decode($argv[5]),true);$data=['2026-11-01'=>['U_DISK'=>1024,'U_BANDWIDTH'=>2048,'PASSWORD'=>'DO-NOT-EXPOSE'],'invalid'=>['U_DISK'=>1,'U_BANDWIDTH'=>2]];include $argv[2];if($languageBefore!==array_intersect_key($_SESSION,array_flip(['language','LANGUAGE']))){throw new Exception('Theme changed a language preference');}`;
const session = {
	user: "fixture-admin",
	userContext: "admin",
	ROOT_USER: "fixture-admin",
	look: "",
	THEME: "studio",
	LANGUAGE: "en",
	API_SYSTEM: "2",
	FIREWALL_SYSTEM: "iptables",
	token: "DO-NOT-EXPOSE",
	password: "DO-NOT-EXPOSE",
};
function render(patch = {}, page = "list_stats", method = "GET", query = {}) {
	return execFileSync(
		"php",
		[
			"-r",
			php,
			Buffer.from(JSON.stringify({ ...session, ...patch })).toString("base64"),
			bridge,
			page,
			method,
			Buffer.from(JSON.stringify(query)).toString("base64"),
		],
		{ encoding: "utf8" },
	);
}
function context(...args) {
	const text = render(...args);
	assert.ok(!text.includes("DO-NOT-EXPOSE"));
	return JSON.parse(text.match(/id="studio-context">([\s\S]*?)<\/script>/)[1]);
}
assert.equal(render({ THEME: "default" }), "");
assert.equal(render({ user: "" }), "");
assert.equal(context({ userTheme: "", THEME: "studio" }).admin, true);
assert.equal(context({ userContext: "user", user: "fixture-user" }).server, false);
assert.equal(context({ userContext: "user", user: "fixture-user" }).admin, false);
assert.equal(
	context({ POLICY_SYSTEM_HIDE_SERVICES: "yes", ROOT_USER: "root-owner" }).server,
	false,
);
assert.equal(context({ POLICY_SYSTEM_HIDE_SERVICES: "yes" }).server, true);
assert.equal(context({ look: "fixture-user" }).admin, false);
assert.equal(context({}, "list_user").landing, true);
assert.equal(context({ hestia_studio_landing_seen: true }, "list_user").landing, false);
assert.equal(context({ look: "fixture-user" }, "list_user").landing, false);
assert.equal(context({ userContext: "user" }, "list_user").landing, false);
assert.equal(context({}, "list_user", "POST").landing, false);
assert.equal(context({}, "list_user", "GET", { sort: "name" }).landing, false);
assert.equal(context({}, "edit_user").landing, false);
assert.ok(context({}, "list_services", "GET", { studio: "overview" }).metrics);
assert.equal(context({}, "list_services").metrics, undefined);
assert.equal(
	context({ userContext: "user" }, "list_services", "GET", { studio: "overview" }).metrics,
	undefined,
);
assert.equal(
	context({ look: "fixture-user" }, "list_services", "GET", { studio: "overview" }).metrics,
	undefined,
);
assert.equal(
	context({ POLICY_SYSTEM_HIDE_SERVICES: "yes", ROOT_USER: "other-root" }, "list_services", "GET", {
		studio: "overview",
	}).metrics,
	undefined,
);
assert.equal(context({ user: "other-admin", look: "fixture-admin" }).keys, false);
assert.equal(context({ user: "other-admin", look: "fixture-admin" }).accessKeys, false);
assert.equal(
	context({ user: "fixture-user", userContext: "user", API_SYSTEM: "1" }).accessKeys,
	false,
);
assert.equal(context({ userContext: "user", POLICY_USER_VIEW_LOGS: "no" }).logs, false);
for (const language of languages) {
	const actual = context({ LANGUAGE: language });
	const expected = JSON.parse(
		await readFile(`web/templates/includes/studio-locales/${language}.json`, "utf8"),
	);
	assert.deepEqual(actual.labels, expected, language);
	assert.equal(actual.language, language);
}
assert.deepEqual(
	context({ language: "tr", LANGUAGE: "en" }).labels,
	JSON.parse(await readFile("web/templates/includes/studio-locales/tr.json", "utf8")),
	"Account language overrides server default",
);
assert.deepEqual(
	context({ language: "", LANGUAGE: "en" }).labels,
	base,
	"Empty account language uses server default",
);
assert.deepEqual(context({ LANGUAGE: "../../etc/passwd" }).labels, base);
for (const patch of [
	{ language: "../../etc/passwd", LANGUAGE: "ar" },
	{ language: "zz", LANGUAGE: "ar" },
	{ language: "ar-zz", LANGUAGE: "ar" },
	{ language: ["ar"], LANGUAGE: "ar" },
	{ language: "", LANGUAGE: "" },
	{ language: null, LANGUAGE: null },
	{ LANGUAGE: ["ar"] },
]) {
	const actual = context(patch);
	assert.equal(actual.language, "en");
	assert.deepEqual(
		actual.labels,
		base,
		"Invalid or missing dictionary preserves the English fallback in a process without gettext translations",
	);
}
assert.equal(
	context({ language: "", LANGUAGE: "fa" }).language,
	"fa",
	"Empty account language falls back to server language",
);
assert.equal(
	context({ language: "PT_BR", LANGUAGE: "ar" }).language,
	"pt-br",
	"Existing case/underscore normalization is preserved",
);
assert.equal(context({ language: "ZH_TW", LANGUAGE: "ar" }).language, "zh-tw");
assert.equal(context().statistics.length, 1);
assert.deepEqual(context().statistics[0], { month: "2026-11-01", disk: 1024, bandwidth: 2048 });
const hostile = "</script><script>alert(1)</script>";
const safe = render({ user: hostile });
assert.ok(!safe.includes(hostile));
assert.equal(context({ user: hostile }).account, hostile);
assert.ok(!render({ language: hostile }).includes(hostile));
assert.equal(context({ language: hostile }).language, "en");
const js = await readFile("web/js/src/studio.js", "utf8");
const css = await readFile("web/css/src/themes/studio.css", "utf8");
const parseSeries = vm.runInNewContext(js.replace(/\}\)\(\);\s*$/, "return rrdPoints;})();"), {
	document: { readyState: "loading", addEventListener() {} },
});
// HestiaCP 1.10.9 v-update-sys-rrd-la stores LA * 100. v-export-rrd
// emits rrdtool xport JSON with Load first and Processes second, unscaled.
// Fictional values model that wire format, not already-normalized chart values.
const loadPayload = JSON.parse(
	'{"meta":{"start":1000,"end":2800,"step":300,"rows":7,"columns":2,"legend":["Load","Processes"]},"data":[[1.05e+2,234],[null,235],[0.0e+0,236],[2.00e+2,237],[4.00e+1,238],[1.055e+2,239],[null,240]]}',
);
const points = parseSeries(loadPayload, "la");
const pointValues = (points) => Array.from(points, (p) => p.value);
assert.deepEqual(
	pointValues(points),
	[1.05, null, 0, 2, 0.4, 1.055, null],
	"Normalize Load / 100 exactly once; never substitute Processes or round RRD averages",
);
assert.deepEqual(
	pointValues(
		parseSeries(
			{
				meta: { start: 1000, step: 300 },
				data: [
					["NaN", 999],
					["105", 999],
					[-1, 999],
					[NaN, 999],
					[Infinity, 999],
					[false, 999],
					[],
					null,
					{ 0: 105 },
				],
			},
			"la",
		),
	),
	Array(9).fill(null),
	"Invalid samples remain gaps, not zero or process counts",
);
const memoryPayload = JSON.parse(
	'{"meta":{"start":1000,"end":1600,"step":300,"rows":3,"columns":3,"legend":["Used","Swap","Free"]},"data":[[2048,16,1024],[0,16,3072],[null,16,3072]]}',
);
assert.deepEqual(
	pointValues(parseSeries(memoryPayload, "mem")),
	[2048, 0, null],
	"Used memory stays in MiB; Swap and Free are not used",
);
assert.equal(points[1].time - points[0].time, 300000);
assert.equal(points[0].time, 1000000, "Match Hestia native RRD timestamps");
assert.throws(() => parseSeries({ meta: { start: 0, step: 0 }, data: [] }, "la"));
assert.throws(() => parseSeries({ meta: { start: 0, step: 300 }, data: "invalid" }, "la"));
assert.throws(() => parseSeries(loadPayload, "cpu"), "Live CPU is not an RRD series");
// Execute the actual boot and dashboard functions with a small DOM fixture.
// No browser, real server, credentials, or network access is needed.
function dashboardFixture(ctx, serverLanguage) {
	const nodes = [],
		numberLocales = [],
		dateLocales = [],
		requests = [];
	class Node {
		constructor(tag) {
			this.tag = tag;
			this.children = [];
			this.dataset = {};
			this.attributes = {};
			this.listeners = {};
			this.style = {};
			this.className = "";
			this.textContent = "";
			this.clientWidth = 640;
			nodes.push(this);
			this.classList = {
				add: (...names) => {
					this.className += " " + names.join(" ");
				},
				contains: (name) => this.className.split(/\s+/).includes(name),
			};
		}
		append(...children) {
			for (const child of children) {
				this.children.push(child);
				child.parent = this;
			}
		}
		replaceChildren(...children) {
			this.children = [];
			this.append(...children);
		}
		after(child) {
			this.parent?.append(child);
		}
		setAttribute(name, value) {
			this.attributes[name] = String(value);
		}
		getAttribute(name) {
			return this.attributes[name] ?? null;
		}
		addEventListener(name, handler) {
			this.listeners[name] = handler;
		}
		querySelectorAll(selector) {
			return this.children.flatMap((child) => [
				...((
					selector.startsWith(".")
						? child.classList.contains(selector.slice(1))
						: child.tag === selector
				)
					? [child]
					: []),
				...child.querySelectorAll(selector),
			]);
		}
		querySelector(selector) {
			return this.querySelectorAll(selector)[0] || null;
		}
	}
	const root = { lang: serverLanguage, dir: serverLanguage === "ar" ? "rtl" : "ltr", dataset: {} };
	const document = {
		documentElement: root,
		readyState: "loading",
		addEventListener() {},
		querySelectorAll: () => [],
		querySelector: () => null,
		getElementById: (id) => (id === "studio-context" ? { textContent: JSON.stringify(ctx) } : null),
		createElement: (tag) => new Node(tag),
		createElementNS: (_, tag) => new Node(tag),
	};
	class LocaleDate extends Date {
		toLocaleString(locale, options) {
			dateLocales.push(locale);
			return super.toLocaleString(locale, options);
		}
		toLocaleTimeString(locale, options) {
			dateLocales.push(locale);
			return super.toLocaleTimeString(locale, options);
		}
	}
	const runtime = {
		document,
		Date: LocaleDate,
		Intl: {
			getCanonicalLocales: Intl.getCanonicalLocales,
			NumberFormat: function (locale, options) {
				numberLocales.push(locale);
				return new Intl.NumberFormat(locale, options);
			},
		},
		getComputedStyle: () => ({ getPropertyValue: () => "4" }),
		localStorage: { getItem: () => null, setItem() {} },
		matchMedia: () => ({ matches: false }),
		window: { addEventListener() {} },
		setTimeout: () => 1,
		clearTimeout() {},
		setInterval: () => 1,
		clearInterval() {},
		AbortController,
		fetch: async (path, options) => {
			const body = options.body ? JSON.parse(options.body) : null;
			requests.push({ path, body });
			return {
				ok: true,
				json: async () => (body.service === "la" ? loadPayload : memoryPayload),
				text: async () => "",
			};
		},
		DOMParser: class {
			parseFromString() {
				return {
					getElementById: () => ({ textContent: JSON.stringify(ctx) }),
					querySelectorAll: () => [],
				};
			}
		},
	};
	// Expose lexical functions only in the VM copy; the shipped theme has no test hook.
	vm.runInNewContext(
		js.replace(/\}\)\(\);\s*$/, "globalThis.testTheme={boot,renderDashboard};})();"),
		runtime,
	);
	runtime.testTheme.boot();
	const main = new Node("main"),
		heading = new Node("section");
	main.append(heading);
	const card = (label, value) => {
		const n = new Node("article"),
			top = new Node("div"),
			text = new Node("strong");
		top.className = "studio-stat-top";
		top.textContent = label;
		text.className = "studio-stat-value";
		text.textContent = value;
		n.append(top, text);
		return n;
	};
	return {
		root,
		nodes,
		numberLocales,
		dateLocales,
		requests,
		start: () =>
			runtime.testTheme.renderDashboard({
				main,
				heading,
				ctx,
				t: (key) => ctx.labels[key] || key,
				card,
				usageValues: [],
				entries: [],
			}),
	};
}
const flush = () => new Promise((resolve) => setImmediate(resolve));
for (const [account, server] of [
	["ar", "en"],
	["fa", "en"],
	["ku", "en"],
	["ur", "en"],
	["en", "ar"],
	["tr", "ar"],
]) {
	const ctx = context({ language: account, LANGUAGE: server });
	ctx.logs = false;
	ctx.metrics = {
		cpu: 42.5,
		sampledAt: 1000,
		cores: 4,
		memory: { used: 2 ** 30, total: 4 * 2 ** 30 },
		disk: { used: 4 * 2 ** 30, total: 8 * 2 ** 30 },
	};
	const view = dashboardFixture(ctx, server),
		expectedDirection = ["ar", "fa", "ku", "ur"].includes(account) ? "rtl" : "ltr";
	assert.equal(
		view.root.lang,
		account,
		`Account ${account} replaces server HTML lang ${server} for presentation`,
	);
	assert.equal(
		view.root.dir,
		expectedDirection,
		`Account ${account}, server ${server}: direction follows translations`,
	);
	assert.deepEqual(
		ctx.labels,
		JSON.parse(await readFile(`web/templates/includes/studio-locales/${account}.json`, "utf8")),
	);
	view.start();
	await flush();
	const plot = view.nodes.find((n) => n.classList.contains("studio-resource-plot"));
	assert.ok(
		plot.querySelector("svg"),
		"Chart renders from actual loadChart call, with the metric passed through",
	);
	const axes = () => plot.querySelectorAll("text").map((n) => n.textContent);
	assert.equal(
		axes()[0],
		new Intl.NumberFormat(account, { maximumFractionDigits: 2 }).format(3),
		"Load chart axis uses the normalized range, not LA * 100 or Processes",
	);
	const path = plot.querySelector("path").getAttribute("d");
	assert.equal((path.match(/M/g) || []).length, 2, "A missing sample breaks the rendered line");
	const metricButton = (metric) =>
		view.nodes.find((n) => n.tag === "button" && n.dataset.metric === metric);
	metricButton("mem").listeners.click();
	await flush();
	assert.equal(
		axes()[0],
		new Intl.NumberFormat(account, { maximumFractionDigits: 1 }).format(2294),
		"Memory chart range remains in MiB",
	);
	const rrdRequests = view.requests.filter((r) => r.body).length;
	metricButton("cpu").listeners.click();
	await flush();
	assert.equal(
		axes()[0],
		new Intl.NumberFormat(account, { maximumFractionDigits: 1 }).format(100) + "%",
		"Live CPU keeps its 0–100 percent scale",
	);
	assert.equal(
		view.requests.filter((r) => r.body).length,
		rrdRequests,
		"Selecting live CPU must not fetch an RRD series",
	);
	assert.ok(
		view.nodes.some(
			(n) =>
				n.classList.contains("studio-stat-value") &&
				n.textContent ===
					new Intl.NumberFormat(account, { maximumFractionDigits: 1 }).format(42.5) + "%",
		),
		"Live CPU value is not divided by 100",
	);
	const weekly = view.nodes.find((n) => n.tag === "button" && n.dataset.period === "weekly");
	metricButton("la").listeners.click();
	await flush();
	weekly.listeners.click();
	await flush();
	assert.equal(
		axes().at(-3),
		new Date(1000000).toLocaleString(account, { month: "short", day: "numeric" }),
		"Calendar axis uses the account language",
	);
	assert.ok(
		view.numberLocales.length >= 2 && view.numberLocales.every((locale) => locale === account),
		"All theme number formatting uses account locale",
	);
	assert.ok(
		view.dateLocales.length >= 3 && view.dateLocales.every((locale) => locale === account),
		"Chart dates and refresh timestamps use account locale",
	);
}
for (const [patch, expected] of [
	[{ language: "", LANGUAGE: "ar" }, "ar"],
	[{ language: "en", LANGUAGE: "ar" }, "en"],
	[{ language: "../../etc/passwd", LANGUAGE: "ar" }, "en"],
	[{ language: "zz", LANGUAGE: "ar" }, "en"],
	[{ language: null, LANGUAGE: "" }, "en"],
	[{ language: "PT_BR", LANGUAGE: "ar" }, "pt-BR"],
]) {
	const view = dashboardFixture(context(patch), "ar");
	assert.equal(view.root.lang, expected);
	assert.equal(view.root.dir, expected === "ar" ? "rtl" : "ltr");
}
for (const invalid of [undefined, "", null, [], {}, "</script>", "en--US"]) {
	const view = dashboardFixture({ ...context(), language: invalid }, "ar");
	assert.equal(view.root.lang, "en");
	assert.equal(
		view.root.dir,
		"ltr",
		"Missing or malformed bridge locale safely falls back to English",
	);
}
console.log(
	"Studio contracts passed: 43 languages, role/policy boundaries, safe JSON, RRD scales and gaps, CPU/memory units, account-locale RTL/LTR and formatting.",
);
