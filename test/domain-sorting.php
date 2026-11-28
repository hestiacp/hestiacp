<?php
require __DIR__ . "/../web/inc/domain_sorting.php";

$rules = [
	"// Test PSL",
	"com",
	"cz",
	"uk",
	"co.uk",
	"au",
	"com.au",
	"*.ck",
	"!www.ck",
	"github.io",
];
$cases = [
	"parent before alphabetic subdomains" => [
		[
			"2023.icys.cz",
			"z.example.com",
			"icys.cz",
			"a.example.com",
			"2020.icys.cz",
			"example.com",
		],
		[
			"example.com",
			"a.example.com",
			"z.example.com",
			"icys.cz",
			"2020.icys.cz",
			"2023.icys.cz",
		],
	],
	"parents need not be listed" => [
		["z.example.cz", "a.other.cz", "a.example.cz"],
		["a.example.cz", "z.example.cz", "a.other.cz"],
	],
	"multi-label suffixes do not mix unrelated domains" => [
		["a.zebra.co.uk", "b.alpha.co.uk", "alpha.co.uk", "a.alpha.co.uk"],
		["alpha.co.uk", "a.alpha.co.uk", "b.alpha.co.uk", "a.zebra.co.uk"],
	],
	"Australian suffix" => [
		["z.example.com.au", "a.example.com.au", "example.com.au"],
		["example.com.au", "a.example.com.au", "z.example.com.au"],
	],
	"nested subdomains sorted by their relative name" => [
		["z.example.cz", "b.example.cz", "a.b.example.cz", "example.cz"],
		["example.cz", "a.b.example.cz", "b.example.cz", "z.example.cz"],
	],
	"label boundaries prevent false parent matches" => [
		["example.cz", "notexample.cz", "a.notexample.cz", "a.example.cz"],
		["example.cz", "a.example.cz", "notexample.cz", "a.notexample.cz"],
	],
	"ASCII IDNs retain their original keys" => [
		["www.xn--bcher-kva.de", "xn--bcher-kva.de", "example.cz"],
		["example.cz", "xn--bcher-kva.de", "www.xn--bcher-kva.de"],
	],
	"PSL wildcard and exception" => [
		["z.www.ck", "www.ck", "a.foo.bar.ck", "foo.bar.ck"],
		["foo.bar.ck", "a.foo.bar.ck", "www.ck", "z.www.ck"],
	],
	"private suffixes" => [
		["z.beta.github.io", "a.alpha.github.io", "alpha.github.io"],
		["alpha.github.io", "a.alpha.github.io", "z.beta.github.io"],
	],
	"empty list" => [[], []],
	"single domain" => [["example.cz"], ["example.cz"]],
];

foreach ($cases as $label => [$input, $expected]) {
	$data = [];
	foreach ($input as $i => $domain) {
		$data[$domain] = ["marker" => $i, "SSL" => "yes"];
	}
	$keys = get_domain_sort_keys($input, $rules);
	foreach ($keys as $domain => $parts) {
		$reconstructed =
			$parts["subdomain"] === ""
				? $parts["domain"]
				: $parts["subdomain"] . "." . $parts["domain"];
		if ($reconstructed !== $domain) {
			throw new RuntimeException("Sort keys must reconstruct the original domain");
		}
	}
	$result = sort_domains_by_name($data, $keys);
	if (array_keys($result) !== $expected) {
		throw new RuntimeException($label . ": " . json_encode(array_keys($result)));
	}
	foreach ($result as $domain => $value) {
		if ($data[$domain] !== $value) {
			throw new RuntimeException("Domain metadata changed");
		}
	}
	echo "PASS: $label\n";
}

$input = ["a.example.co.uk", "example.co.uk", "example.cz", "a.example.cz"];
$keys = get_domain_sort_keys($input, []);
if ($keys["a.example.co.uk"]["domain"] !== "example.co.uk") {
	throw new RuntimeException("Missing PSL must still use a listed parent");
}
if (
	$keys["example.co.uk"]["domain"] !== "example.co.uk" ||
	$keys["example.co.uk"]["subdomain"] !== ""
) {
	throw new RuntimeException("A listed parent must share its children's group");
}
if ($keys["a.example.cz"]["domain"] !== "example.cz") {
	throw new RuntimeException("Missing PSL fallback for ordinary domains");
}
$keys = get_domain_sort_keys(["z.orphan.cz", "a.orphan.cz"], []);
if ($keys["z.orphan.cz"]["domain"] !== "orphan.cz") {
	throw new RuntimeException("Missing PSL and missing parent fallback");
}
echo "PASS: missing PSL fallback\n";

// Exercise the same backend path used by the unprivileged panel process.
$command = tempnam(sys_get_temp_dir(), "hestia-suffix-test-");
file_put_contents($command, '<?php echo "com\\ncz\\nco.uk\\n";');
define("HESTIA_CMD", escapeshellarg(PHP_BINARY) . " " . escapeshellarg($command) . " ");
try {
	$keys = get_domain_sort_keys(["a.alpha.co.uk", "a.zebra.co.uk"]);
	if (
		$keys["a.alpha.co.uk"]["domain"] !== "alpha.co.uk" ||
		$keys["a.zebra.co.uk"]["domain"] !== "zebra.co.uk"
	) {
		throw new RuntimeException("Panel must obtain suffix rules through the backend");
	}
	file_put_contents($command, "<?php exit(1);");
	$keys = get_domain_sort_keys(["a.example.co.uk", "example.co.uk"]);
	if ($keys["a.example.co.uk"]["domain"] !== "example.co.uk") {
		throw new RuntimeException("Backend failure must preserve the parent fallback");
	}
} finally {
	unlink($command);
}
echo "PASS: panel backend and backend failure\n";
