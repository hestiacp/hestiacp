<?php

declare(strict_types=1);

if (!isset($argv[1], $argv[2], $argv[3])) {
	$error_json = [
		"valid" => false,
		"error_message" => "record, rtype, and priority arguments are required",
	];
	echo json_encode(
		$error_json,
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
	);
	exit(1);
}

$record = $argv[1];
$rtype = $argv[2];
$priority = $argv[3];
$known_types = [
	"A",
	"AAAA",
	"NS",
	"CNAME",
	"MX",
	"TXT",
	"SRV",
	"DNSKEY",
	"KEY",
	"IPSECKEY",
	"PTR",
	"SPF",
	"TLSA",
	"CAA",
	"DS",
];
$valid = true;
$error_message = null;
$cleaned_record = null;
$new_priority = null;

$validateInt = static function ($value, $min, $max) {
	return filter_var($value, FILTER_VALIDATE_INT, [
		"options" => ["min_range" => $min, "max_range" => $max],
	]);
};
$validateDomain = static function ($value) {
	return filter_var(rtrim($value, "."), FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
};
$validateHex = static function ($value) {
	return preg_match('/^[A-Fa-f0-9]+$/', $value) === 1;
};
$validateBase64 = static function ($value) {
	if ($value === "" || preg_match("/[^A-Za-z0-9+\/=]/", $value)) {
		return false;
	}
	return base64_decode($value, true) !== false;
};
$validatePrintableAscii = static function ($value) {
	return !preg_match('/[^\x20-\x7E]/', $value);
};
$validateSrvTarget = static function ($value) use ($validateDomain) {
	return $value === "." || $validateDomain($value);
};

if (!in_array($rtype, $known_types, true)) {
	$valid = false;
	$error_message = "unknown record type for validation: $rtype";
} elseif ($rtype === "A") {
	$valid = filter_var($record, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	if (!$valid) {
		$error_message = "invalid A record format";
	}
} elseif ($rtype === "AAAA") {
	$valid = filter_var($record, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	if (!$valid) {
		$error_message = "invalid AAAA record format";
	}
} elseif ($rtype === "NS" || $rtype === "CNAME" || $rtype === "PTR") {
	$valid = $validateDomain($record);
	if (!$valid) {
		$error_message = "invalid $rtype record format";
	}
} elseif ($rtype === "MX") {
	$valid = $validateDomain($record);
	if (!$valid) {
		$error_message = "invalid MX record format";
	} else {
		if ($priority === "") {
			$valid = false;
			$error_message = "MX record priority is required";
		} else {
			$valid = $validateInt($priority, 0, 65535);
			if ($valid === false) {
				$error_message = "invalid MX record priority format (must be between 0 and 65535)";
			}
		}
	}
} elseif ($rtype === "SRV") {
	$parts = preg_split("/\s+/", trim($record));
	$parts_count = count($parts);
	$target = null;
	$port = null;
	$weight = null;
	$priority_to_use = $priority;

	if ($parts_count === 4) {
		[$priority_from_value, $weight, $port, $target] = $parts;
		$priority_to_use = $priority_from_value;
	} elseif ($parts_count === 3) {
		if ($validateSrvTarget($parts[0])) {
			[$target, $port, $weight] = $parts;
		} elseif ($validateSrvTarget($parts[2])) {
			[$weight, $port, $target] = $parts;
		} else {
			$valid = false;
			$error_message = "invalid SRV record format (expected target with port and weight)";
		}
	} else {
		$valid = false;
		$error_message =
			"invalid SRV record format (must contain priority weight port target or target port weight)";
	}

	if ($valid !== false) {
		$priority_validated = $validateInt($priority_to_use, 0, 65535);
		$weight_validated = $validateInt($weight, 0, 65535);
		$port_validated = $validateInt($port, 0, 65535);
		$target_validated = $validateSrvTarget($target ?? "");

		if ($priority_validated === false) {
			$valid = false;
			$error_message = "invalid SRV record priority format (must be between 0 and 65535)";
		} elseif ($weight_validated === false) {
			$valid = false;
			$error_message = "invalid SRV record weight format (must be between 0 and 65535)";
		} elseif ($port_validated === false) {
			$valid = false;
			$error_message = "invalid SRV record port format (must be between 0 and 65535)";
		} elseif (!$target_validated) {
			$valid = false;
			$error_message = "invalid SRV record target format";
		} else {
			$new_priority = $priority_validated;
			$cleaned_record = $weight_validated . " " . $port_validated . " " . $target;
		}
	}
} elseif ($rtype === "TXT" || $rtype === "SPF") {
	if ($record === "") {
		$valid = false;
		$error_message = "$rtype record cannot be empty";
	} elseif (strlen($record) > 65535) {
		$valid = false;
		$error_message = "$rtype record exceeds maximum length";
	} elseif (!$validatePrintableAscii($record)) {
		$valid = false;
		$error_message = "$rtype record contains non-ASCII characters";
	}
} elseif ($rtype === "DNSKEY" || $rtype === "KEY") {
	$parts = preg_split("/\s+/", trim($record));
	if (count($parts) < 3) {
		$valid = false;
		$error_message = "invalid $rtype record format (expected flags protocol algorithm [public-key])";
	} else {
		[$flags, $protocol, $algorithm] = array_slice($parts, 0, 3);
		$public_key = implode(" ", array_slice($parts, 3));
		$flags_valid = $validateInt($flags, 0, 65535);
		$protocol_valid = $validateInt($protocol, 0, 255);
		$algorithm_valid = $validateInt($algorithm, 0, 255);
		if ($rtype === "DNSKEY" && $protocol !== "3") {
			$protocol_valid = false;
		}
		if ($flags_valid === false || $protocol_valid === false || $algorithm_valid === false) {
			$valid = false;
			$error_message = "invalid $rtype numeric fields";
		} elseif ($rtype === "KEY" && $algorithm === "0") {
			if ($public_key !== "") {
				$valid = false;
				$error_message = "invalid KEY public key for algorithm 0 (must be empty)";
			}
		} elseif ($public_key === "" || !$validateBase64($public_key)) {
			$valid = false;
			$error_message = "invalid $rtype public key (must be base64)";
		}
	}
} elseif ($rtype === "IPSECKEY") {
	$parts = preg_split("/\s+/", trim($record));
	if (count($parts) < 4) {
		$valid = false;
		$error_message =
			"invalid IPSECKEY record format (expected precedence gateway-type algorithm gateway [public-key])";
	} else {
		[$precedence, $gateway_type, $algorithm, $gateway] = array_slice($parts, 0, 4);
		$public_key = implode(" ", array_slice($parts, 4));
		$precedence_valid = $validateInt($precedence, 0, 255);
		$gateway_type_valid = $validateInt($gateway_type, 0, 3);
		$algorithm_valid = $validateInt($algorithm, 0, 255);
		if (
			$precedence_valid === false ||
			$gateway_type_valid === false ||
			$algorithm_valid === false
		) {
			$valid = false;
			$error_message = "invalid IPSECKEY numeric fields";
		} else {
			if ($gateway_type === "0") {
				if ($gateway !== "." && $gateway !== "") {
					$valid = false;
					$error_message = "invalid IPSECKEY gateway for type 0";
				}
			} elseif ($gateway_type === "1") {
				if (!filter_var($gateway, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
					$valid = false;
					$error_message = "invalid IPSECKEY IPv4 gateway";
				}
			} elseif ($gateway_type === "2") {
				if (!filter_var($gateway, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
					$valid = false;
					$error_message = "invalid IPSECKEY IPv6 gateway";
				}
			} else {
				if (!$validateDomain($gateway)) {
					$valid = false;
					$error_message = "invalid IPSECKEY domain gateway";
				}
			}
			if ($valid !== false) {
				if ($algorithm === "0") {
					if ($public_key !== "") {
						$valid = false;
						$error_message =
							"invalid IPSECKEY public key for algorithm 0 (must be empty)";
					}
				} elseif ($public_key === "" || !$validateBase64($public_key)) {
					$valid = false;
					$error_message = "invalid IPSECKEY public key (must be base64)";
				}
			}
		}
	}
} elseif ($rtype === "TLSA") {
	$parts = preg_split("/\s+/", trim($record));
	if (count($parts) < 4) {
		$valid = false;
		$error_message = "invalid TLSA record format (expected usage selector matching-type data)";
	} else {
		[$usage, $selector, $matching_type] = array_slice($parts, 0, 3);
		$data = implode(" ", array_slice($parts, 3));
		$usage_valid = $validateInt($usage, 0, 3);
		$selector_valid = $validateInt($selector, 0, 1);
		$matching_valid = $validateInt($matching_type, 0, 2);
		$data_length = strlen($data);
		if ($usage_valid === false || $selector_valid === false || $matching_valid === false) {
			$valid = false;
			$error_message = "invalid TLSA numeric fields";
		} elseif ($data === "" || !$validateHex($data) || $data_length % 2 !== 0) {
			$valid = false;
			$error_message = "invalid TLSA data";
		} elseif ($matching_valid === 1 && $data_length !== 64) {
			$valid = false;
			$error_message = "invalid TLSA data length for matching type 1";
		} elseif ($matching_valid === 2 && $data_length !== 128) {
			$valid = false;
			$error_message = "invalid TLSA data length for matching type 2";
		}
	}
} elseif ($rtype === "CAA") {
	$parts = preg_split("/\s+/", trim($record), 3);
	if (count($parts) < 3) {
		$valid = false;
		$error_message = "invalid CAA record format (expected flag tag value)";
	} else {
		[$flag, $tag, $value] = $parts;
		$flag_valid = $validateInt($flag, 0, 255);
		$tag_valid = preg_match('/^[A-Za-z0-9-]{1,63}$/', $tag);
		if ($flag_valid === false || $tag_valid === 0) {
			$valid = false;
			$error_message = "invalid CAA flag or tag";
		} elseif ($value === "") {
			$valid = false;
			$error_message = "invalid CAA value";
		}
	}
} elseif ($rtype === "DS") {
	$parts = preg_split("/\s+/", trim($record));
	if (count($parts) < 4) {
		$valid = false;
		$error_message = "invalid DS record format (expected keytag algorithm digest-type digest)";
	} else {
		[$key_tag, $algorithm, $digest_type] = array_slice($parts, 0, 3);
		$digest = implode(" ", array_slice($parts, 3));
		$key_tag_valid = $validateInt($key_tag, 0, 65535);
		$algorithm_valid = $validateInt($algorithm, 0, 255);
		$digest_type_valid = $validateInt($digest_type, 0, 255);
		$digest_lengths = [1 => 40, 2 => 64, 3 => 64, 4 => 96];
		$digest_length = strlen($digest);
		if (
			$key_tag_valid === false ||
			$algorithm_valid === false ||
			$digest_type_valid === false
		) {
			$valid = false;
			$error_message = "invalid DS numeric fields";
		} elseif ($digest === "" || !$validateHex($digest) || $digest_length % 2 !== 0) {
			$valid = false;
			$error_message = "invalid DS digest";
		} elseif (
			array_key_exists((int) $digest_type, $digest_lengths) &&
			$digest_length !== $digest_lengths[(int) $digest_type]
		) {
			$valid = false;
			$error_message = "invalid DS digest length for type $digest_type";
		}
	}
} else {
	$valid = false;
	$error_message = "validation not implemented for record type: $rtype";
}

$json = ["valid" => $valid !== false];
if ($error_message !== null) {
	$json["error_message"] = $error_message;
}
if ($cleaned_record !== null) {
	$json["cleaned_record"] = $cleaned_record;
}
if ($new_priority !== null) {
	$json["new_priority"] = $new_priority;
}
echo json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
