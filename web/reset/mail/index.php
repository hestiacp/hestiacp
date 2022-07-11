<?php
// Init
define('NO_AUTH_REQUIRED',true);
define('NO_AUTH_REQUIRED2',true);

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Checking IP of incoming connection, checking is it NAT address
$ok=0;
$ip=$_SERVER['REMOTE_ADDR'];
exec (HESTIA_CMD."v-list-sys-ips json", $output, $return_var);
$output=implode('', $output);
$arr=json_decode($output, true);
foreach ($arr as $arr_key => $arr_val) {
    // search for NAT IPs and allow them
	if ($ip==$arr_key || $ip==$arr_val['NAT']) {
		$ok=1;
		break;
	}
}
if ($ip == $_SERVER['SERVER_ADDR']) $ok=1;
if ($ip == '127.0.0.1') $ok=1;
if ($ok==0) exit;
if (isset($_SERVER['HTTP_X_REAL_IP']) || isset($_SERVER['HTTP_X_FORWARDED_FOR'])) exit;


/**
 * md5 crypt() password
 *
 * @param string $password
 * @throws InvalidArgumentException if salt is emptystring
 * @throws InvalidArgumentException if salt is longer than 8 characters
 * @return string
 */
function md5crypt(string $pw, string $salt): string
{
    if (strlen($salt) < 1) {
        // old implementation would crash with error "function generate_salt not defined", lets throw an exception instead
        throw new InvalidArgumentException('salt not given!');
    }
    if (strlen($salt) > 8) {
        throw new \InvalidArgumentException("maximum supported salt length for MD5 crypt is 8 characters!");
    }
    return crypt($pw, '$1$' . $salt);
}


// Check arguments
if ((!empty($_POST['email'])) && (!empty($_POST['password'])) && (!empty($_POST['new']))) {
    list($v_account, $v_domain) = explode('@', $_POST['email']);
    $v_domain = escapeshellarg($v_domain);
    $v_account = escapeshellarg($v_account);
    $v_password = $_POST['password'];

    // Get domain owner
    exec (HESTIA_CMD."v-search-domain-owner ".$v_domain." 'mail'", $output, $return_var);
    if ($return_var == 0) {
        $v_user = $output[0];
    }
    unset($output);

    // Get current md5 hash
    if (!empty($v_user)) {
        exec (HESTIA_CMD."v-get-mail-account-value ".escapeshellarg($v_user)." ".$v_domain." ".$v_account." 'md5'", $output, $return_var);
        if ($return_var == 0) {
            $v_hash = $output[0];
        }
    }
    unset($output);

    // Compare hashes
    if (!empty($v_hash)) {
        $salt = explode('$', $v_hash);
        if($salt[0] == "{MD5}"){
        $n_hash = md5crypt($v_password, $salt[2]);
        $n_hash = '{MD5}'.$n_hash;
        }else{
            $v_password = escapeshellarg($v_password);
            $s_hash = escapeshellarg($v_hash);
            exec(HESTIA_CMD."v-check-mail-account-hash ARGONID2 ". $v_password ." ". $s_hash, $output, $return_var);
            if($return_var != 0){
                $n_hash = '';
            }else{
                $n_hash = $v_hash;
            }
        }
        // Change password
        if ( $v_hash == $n_hash ) {
            $v_new_password = tempnam("/tmp","vst");
            $fp = fopen($v_new_password, "w");
            fwrite($fp, $_POST['new']."\n");
            fclose($fp);
            exec (HESTIA_CMD."v-change-mail-account-password ".escapeshellarg($v_user)." ".$v_domain." ".$v_account." ".$v_new_password, $output, $return_var);
            if ($return_var == 0) {
                echo "==ok==";
                exit;
            }
        }
    }
}

echo 'error';

exit;
