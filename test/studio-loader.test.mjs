import assert from "node:assert/strict";
import { execFileSync } from "node:child_process";
import { mkdtempSync, mkdirSync, writeFileSync, rmSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";
import { fileURLToPath } from "node:url";
import test from "node:test";

test("native loader selects Studio only for an authenticated Studio account", () => {
	const root = mkdtempSync(join(tmpdir(), "hestia-studio-"));
	const custom = join(root, "web/js/custom_scripts");
	mkdirSync(custom, { recursive: true });
	mkdirSync(join(root, "web/css/themes"), { recursive: true });
	writeFileSync(join(root, "web/css/themes/studio.min.css"), "/* built theme */");
	writeFileSync(join(custom, "example.js"), "/* unrelated customization */");
	writeFileSync(join(custom, "example.php"), '<?php echo "custom-hook-preserved";');
	const loader = fileURLToPath(new URL("../web/templates/includes/js.php", import.meta.url));
	const css = fileURLToPath(new URL("../web/templates/includes/css.php", import.meta.url));
	const php = `
		if (!function_exists('_')) { function _($text) { return $text; } }
		$_SESSION=json_decode($argv[1],true);
		$before=$_SESSION;
		$_SERVER['HESTIA']=$argv[2];
		$_SERVER['REQUEST_METHOD']='GET';
		$_GET=[]; $_POST=[];
		$user_plain='fixture-user'; $page='edit_user'; $data=[];
		define('JS_LATEST_UPDATE','v=fixture');
		include $argv[3]; include $argv[4];
		unset($_SESSION['unset_alerts'],$_SESSION['hestia_studio_landing_seen']);
		if ($_SESSION !== $before) throw new Exception('Changed native session data');
	`;
	const session = { user: "fixture-user", userContext: "user", THEME: "studio", LANGUAGE: "en" };
	try {
		for (const [patch, enabled] of [
			[{}, true],
			[{ THEME: "default", userTheme: "studio" }, true],
			[{ userTheme: "" }, true],
			[{ userTheme: "default" }, false],
			[{ userTheme: "dark" }, false],
			[{ userTheme: "flat" }, false],
			[{ userTheme: "vestia" }, false],
			[{ THEME: "default" }, false],
			[{ user: "" }, false],
			[{ userContext: null }, false],
		]) {
			const html = execFileSync(
				"php",
				["-r", php, JSON.stringify({ ...session, ...patch }), root, css, loader],
				{ encoding: "utf8" },
			);
			assert.equal(html.includes('id="studio-context"'), enabled, JSON.stringify(patch));
			assert.equal(html.includes("/js/dist/studio.min.js?v=fixture"), enabled);
			assert.equal((html.match(/id="studio-context"/g) || []).length, enabled ? 1 : 0);
			assert.ok(html.includes("/js/dist/main.min.js?v=fixture"));
			assert.ok(html.includes("/css/themes/default.min.css?v=fixture"));
			assert.ok(html.includes("/js/custom_scripts/example.js"));
			assert.ok(html.includes("custom-hook-preserved"));
			if (enabled) {
				assert.ok(html.includes("/css/themes/studio.min.css?v=fixture"));
				assert.ok(!html.includes("/css/themes/custom/studio"));
				assert.ok(!html.includes("/js/custom_scripts/studio"));
			}
		}
	} finally {
		rmSync(root, { recursive: true, force: true });
	}
});
