<?php

/**
 * DevIT Control Panel Password Driver
 *
 * @version 1.0
 * @author DevITCP <info@DevITcp.com>
 */
class rcube_DevIT_password {
	public function save($curpass, $passwd) {
		$rcmail = rcmail::get_instance();
		$DevIT_host = $rcmail->config->get("password_DevIT_host");

		if (empty($DevIT_host)) {
			$DevIT_host = "localhost";
		}

		$DevIT_port = $rcmail->config->get("password_DevIT_port");
		if (empty($DevIT_port)) {
			$DevIT_port = "8083";
		}

		$postvars = [
			"email" => $_SESSION["username"],
			"password" => $curpass,
			"new" => $passwd,
		];
		$url = "https://{$DevIT_host}:{$DevIT_port}/reset/mail/";
		$ch = curl_init();
		if (
			false ===
			curl_setopt_array($ch, [
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($postvars),
				CURLOPT_USERAGENT => "DevIT Control Panel Password Driver",
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
			])
		) {
			// should never happen
			throw new Exception("curl_setopt_array() failed: " . curl_error($ch));
		}
		$result = curl_exec($ch);
		if (curl_errno($ch) !== CURLE_OK) {
			throw new Exception("curl_exec() failed: " . curl_error($ch));
		}
		curl_close($ch);
		if (strpos($result, "ok") && !strpos($result, "error")) {
			return PASSWORD_SUCCESS;
		} else {
			return PASSWORD_ERROR;
		}
	}
}
