<?php
/**
 * Hestia Studio 2.1.1 · GPL-3.0-or-later.
 * Presentation context for the optional built-in Studio theme.
 * Presentation metadata and a theme-owned session flag; no file writes or commands.
 */
if (empty($_SESSION['user']) || !isset($_SESSION['userContext'])) {
	return;
}
if ((!empty($_SESSION['userTheme']) ? $_SESSION['userTheme'] : ($_SESSION['THEME'] ?? '')) !== 'studio') {
	return;
}
(static function (array $session, string $page, array $data): void {
	$localeDir = __DIR__ . '/studio-locales';
	$english = json_decode((string) @file_get_contents($localeDir . '/en.json'), true);
	if (!is_array($english)) { return; }
	$labels = [];
	foreach ($english as $key => $message) {
		$labels[$key] = function_exists('_') ? _($message) : $message;
	}
	// Match Hestia detect_user_language(): account preference before server default.
	$requestedLanguage = !empty($session['language']) ? $session['language'] : ($session['LANGUAGE'] ?? 'en');
	$requestedLanguage = is_string($requestedLanguage) ? strtolower(str_replace('_', '-', $requestedLanguage)) : '';
	// Invalid or unavailable dictionaries retain the existing gettext/English labels.
	// Expose only the resolved dictionary language, never an unchecked session value.
	$language = 'en';
	if ($requestedLanguage !== 'en' && preg_match('/^[a-z]{2,3}(?:-[a-z]{2,3})?$/D', $requestedLanguage)) {
		$translations = json_decode((string) @file_get_contents($localeDir . '/' . $requestedLanguage . '.json'), true);
		if (is_array($translations)) {
			$labels = array_replace($labels, array_intersect_key($translations, $english));
			$language = $requestedLanguage;
		}
	}
	$account = !empty($session['look']) ? $session['look'] : $session['user'];
	$admin = $session['userContext'] === 'admin' && empty($session['look']);
	$root = $session['ROOT_USER'] ?? 'admin';
	$server = $admin && (($session['POLICY_SYSTEM_HIDE_SERVICES'] ?? '') !== 'yes' || $session['user'] === $root);
	$protected = $account === $root && $session['user'] !== $root;
	$api = (int) ($session['API_SYSTEM'] ?? 0);
	// Hestia sends administrators to list_user after login. Consume this flag on
	// the first themed page so later Users visits, forms and deep links stay put.
	$firstView = empty($_SESSION['hestia_studio_landing_seen']);
	$_SESSION['hestia_studio_landing_seen'] = true;
	$landing = $firstView && $admin && $page === 'list_user'
		&& ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && empty($_GET) && empty($_POST);
	$payload = [
		'version' => '2.1.1',
		'language' => $language,
		'account' => (string) $account,
		'landing' => $landing,
		'admin' => $admin,
		'impersonating' => !empty($session['look']),
		'server' => $server,
		'firewall' => $server && ($session['FIREWALL_SYSTEM'] ?? '') === 'iptables',
		'keys' => !$protected,
		'accessKeys' => !$protected && ($account === $root ? $api > 0 : $api > 1),
		'logs' => $session['userContext'] === 'admin' || ($session['POLICY_USER_VIEW_LOGS'] ?? '') !== 'no',
		'labels' => $labels,
	];
	// Read-only Linux metrics, only inside Hestia's authorized administrator
	// services page. No new endpoint, shell command or daemon.
	if ($server && $page === 'list_services' && ($_GET['studio'] ?? '') === 'overview') {
		$metrics = ['sampledAt' => time(), 'cpu' => null, 'cores' => null, 'memory' => null, 'disk' => null];
		if (PHP_OS_FAMILY === 'Linux') {
			$readCpu = static function (): ?array {
				$raw = @file_get_contents('/proc/stat', false, null, 0, 65536);
				if (!is_string($raw) || !preg_match('/^cpu[ \t]+([0-9 \t]+)$/m', $raw, $match)) { return null; }
				$ticks = array_map('floatval', preg_split('/\s+/', trim($match[1])));
				if (count($ticks) < 4) { return null; }
				return ['total' => array_sum(array_slice($ticks, 0, 8)), 'idle' => $ticks[3] + ($ticks[4] ?? 0), 'cores' => preg_match_all('/^cpu\d+\s/m', $raw)];
			};
			$before = $readCpu();
			if ($before !== null) {
				usleep(120000);
				$after = $readCpu();
				if ($after !== null && $after['total'] > $before['total']) {
					$metrics['cpu'] = round(max(0, min(100, 100 * (1 - ($after['idle'] - $before['idle']) / ($after['total'] - $before['total'])))), 1);
					$metrics['cores'] = $after['cores'];
				}
			}
			$memory = @file_get_contents('/proc/meminfo');
			if (is_string($memory) && preg_match('/^MemTotal:\s+(\d+)\s+kB/m', $memory, $total)
				&& preg_match('/^MemAvailable:\s+(\d+)\s+kB/m', $memory, $available)) {
				$total = (float) $total[1] * 1024;
				$available = min($total, (float) $available[1] * 1024);
				$metrics['memory'] = ['total' => $total, 'used' => $total - $available];
			}
			$total = @disk_total_space('/'); $free = @disk_free_space('/');
			if ($total !== false && $free !== false && $total > 0) {
				$metrics['disk'] = ['total' => $total, 'used' => max(0, $total - $free)];
			}
		}
		$payload['metrics'] = $metrics;
	}
	// Only expose the two usage series already authorized by the statistics controller.
	if ($page === 'list_stats') {
		$payload['statistics'] = [];
		foreach ($data as $month => $row) {
			if (!is_array($row) || !preg_match('/^\d{4}-\d{2}(?:-\d{2})?$/D', (string) $month)) { continue; }
			if (!is_numeric($row['U_DISK'] ?? null) || !is_numeric($row['U_BANDWIDTH'] ?? null)) { continue; }
			$payload['statistics'][] = ['month' => (string) $month, 'disk' => (float) $row['U_DISK'], 'bandwidth' => (float) $row['U_BANDWIDTH']];
		}
	}
	echo '<script type="application/json" id="studio-context">';
	echo json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
	echo '</script>';
})($_SESSION, isset($page) && is_string($page) ? $page : '', isset($data) && is_array($data) ? $data : []);
