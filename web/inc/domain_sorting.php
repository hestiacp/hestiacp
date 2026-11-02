<?php

/**
 * Build shared server/browser sort keys without changing domain names or their data.
 * Use Hestia's cached Public Suffix List; never fetch it during a page request.
 */
function get_domain_sort_keys(array $domains, ?array $suffix_rules = null): array {
	if ($suffix_rules === null) {
		// The panel user cannot read the root-owned extensions directory directly.
		$suffix_rules = [];
		exec(HESTIA_CMD . "v-list-sys-public-suffixes", $suffix_rules, $status);
		if ($status !== 0) {
			$suffix_rules = [];
		}
	}
	$rules = [];
	foreach ($suffix_rules as $rule) {
		$rule = trim(explode("//", $rule, 2)[0]);
		if ($rule === "") {
			continue;
		}
		$prefix = "";
		if ($rule[0] === "!") {
			$prefix = "!";
			$rule = substr($rule, 1);
		} elseif (str_starts_with($rule, "*.")) {
			$prefix = "*.";
			$rule = substr($rule, 2);
		}
		if (function_exists("idn_to_ascii")) {
			$rule = idn_to_ascii($rule, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) ?: $rule;
		}
		$rules[$prefix . strtolower($rule)] = true;
	}

	$names = [];
	foreach ($domains as $domain) {
		$names[$domain] = strtolower(rtrim($domain, "."));
	}
	$known_domains = array_fill_keys(array_values($names), true);
	$listed_parents = [];
	if (!$rules) {
		foreach ($names as $name) {
			$labels = explode(".", $name);
			while (count($labels) > 2) {
				array_shift($labels);
				$parent = implode(".", $labels);
				if (isset($known_domains[$parent])) {
					$listed_parents[$parent] = true;
				}
			}
		}
	}
	$keys = [];
	foreach ($names as $domain => $name) {
		$labels = explode(".", $name);
		$count = count($labels);
		$suffix_length = 1; // PSL's implicit wildcard rule.
		for ($i = 0; $i < $count; ++$i) {
			$suffix = implode(".", array_slice($labels, $i));
			if (isset($rules["!" . $suffix])) {
				$suffix_length = $count - $i - 1;
				break;
			}
			if (isset($rules[$suffix])) {
				$suffix_length = max($suffix_length, $count - $i);
			}
			if ($i > 0 && isset($rules["*." . $suffix])) {
				$suffix_length = max($suffix_length, $count - $i + 1);
			}
		}
		$base = implode(".", array_slice($labels, -($suffix_length + 1)));
		if (!$rules) {
			// A missing/unreadable cache must not break the page. Prefer a listed parent.
			for ($length = 2; $length <= $count; ++$length) {
				$parent = implode(".", array_slice($labels, -$length));
				if (
					isset($known_domains[$parent]) &&
					($parent !== $name || isset($listed_parents[$name]))
				) {
					$base = $parent;
					break;
				}
			}
		}
		$keys[$domain] = [
			"domain" => $base,
			"subdomain" => $name === $base ? "" : substr($name, 0, -(strlen($base) + 1)),
		];
	}
	return $keys;
}

function sort_domains_by_name(array $domains, array $keys): array {
	uksort($domains, function ($a, $b) use ($keys) {
		return strcmp($keys[$a]["domain"], $keys[$b]["domain"]) ?:
			strcmp($keys[$a]["subdomain"], $keys[$b]["subdomain"]);
	});
	return $domains;
}
