<?php
// Init
define('NO_AUTH_REQUIRED',true);
define('NO_AUTH_REQUIRED2',true);
error_reporting(NULL);

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Checking IP of incoming connection, checking is it NAT address
//echo '<pre>'; print_r($_SERVER); exit;
$ok=0;
$ip=$_SERVER['REMOTE_ADDR'];
exec (VESTA_CMD."v-list-sys-ips json", $output, $return_var);
$output=implode('', $output);
$arr=json_decode($output, true);
foreach ($arr as $arr_key => $arr_val) {
	if ($ip==$arr_key || $ip==$arr_val['NAT']) {
		$ok=1;
		break;
	}
}
if ($ip == $_SERVER['SERVER_ADDR']) $ok=1;
if ($ip == '127.0.0.1') $ok=1;
if ($ok==0) exit;

//
// sourceforge.net/projects/postfixadmin/
// md5crypt 
// Action: Creates MD5 encrypted password
// Call: md5crypt (string cleartextpassword)
//

function md5crypt ($pw, $salt="", $magic="")
{
    $MAGIC = "$1$";

    if ($magic == "") $magic = $MAGIC;
    if ($salt == "") $salt = create_salt ();
    $slist = explode ("$", $salt);
    if ($slist[0] == "1") $salt = $slist[1];

    $salt = substr ($salt, 0, 8);
    $ctx = $pw . $magic . $salt;
    $final = hex2bin (md5 ($pw . $salt . $pw));

    for ($i=strlen ($pw); $i>0; $i-=16)
    {
        if ($i > 16)
        {
            $ctx .= substr ($final,0,16);
        }
        else
        {
            $ctx .= substr ($final,0,$i);
        }
    }
    $i = strlen ($pw);

    while ($i > 0)
    {
        if ($i & 1) $ctx .= chr (0);
        else $ctx .= $pw[0];
        $i = $i >> 1;
    }
    $final = hex2bin (md5 ($ctx));

    for ($i=0;$i<1000;$i++)
    {
        $ctx1 = "";
        if ($i & 1)
        {
            $ctx1 .= $pw;
        }
        else
        {
            $ctx1 .= substr ($final,0,16);
        }
        if ($i % 3) $ctx1 .= $salt;
        if ($i % 7) $ctx1 .= $pw;
        if ($i & 1)
        {
            $ctx1 .= substr ($final,0,16);
        }
        else
        {
            $ctx1 .= $pw;
        }
        $final = hex2bin (md5 ($ctx1));
    }
    $passwd = "";
    $passwd .= to64 (((ord ($final[0]) << 16) | (ord ($final[6]) << 8) | (ord ($final[12]))), 4);
    $passwd .= to64 (((ord ($final[1]) << 16) | (ord ($final[7]) << 8) | (ord ($final[13]))), 4);
    $passwd .= to64 (((ord ($final[2]) << 16) | (ord ($final[8]) << 8) | (ord ($final[14]))), 4);
    $passwd .= to64 (((ord ($final[3]) << 16) | (ord ($final[9]) << 8) | (ord ($final[15]))), 4);
    $passwd .= to64 (((ord ($final[4]) << 16) | (ord ($final[10]) << 8) | (ord ($final[5]))), 4);
    $passwd .= to64 (ord ($final[11]), 2);
    return "$magic$salt\$$passwd";
}


//
// sourceforge.net/projects/postfixadmin/
// to64
//

function to64 ($v, $n)
{
    $ITOA64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    $ret = "";
    while (($n - 1) >= 0)
    {
        $n--;
        $ret .= $ITOA64[$v & 0x3f];
        $v = $v >> 6;
    }
    return $ret;
}


// Check arguments
if ((!empty($_POST['email'])) && (!empty($_POST['password'])) && (!empty($_POST['new']))) {
    list($v_account, $v_domain) = explode('@', $_POST['email']);
    $v_domain = escapeshellarg($v_domain);
    $v_account = escapeshellarg($v_account);
    $v_password = $_POST['password'];

    // Get domain owner
    exec (VESTA_CMD."v-search-domain-owner ".$v_domain." 'mail'", $output, $return_var);
    if ($return_var == 0) {
        $v_user = $output[0];
    }
    unset($output);

    // Get current md5 hash
    if (!empty($v_user)) {
        exec (VESTA_CMD."v-get-mail-account-value '".$v_user."' ".$v_domain." ".$v_account." 'md5'", $output, $return_var);
        if ($return_var == 0) {
            $v_hash = $output[0];
        }
    }
    unset($output);

    // Compare hashes
    if (!empty($v_hash)) {
        $salt = explode('$', $v_hash);
        $n_hash = md5crypt($v_password, $salt[2]);
        $n_hash = '{MD5}'.$n_hash;

        // Change password
        if ( $v_hash == $n_hash ) {
            $v_new_password = tempnam("/tmp","vst");
            $fp = fopen($v_new_password, "w");
            fwrite($fp, $_POST['new']."\n");
            fclose($fp);
            exec (VESTA_CMD."v-change-mail-account-password '".$v_user."' ".$v_domain." ".$v_account." ".$v_new_password, $output, $return_var);
            if ($return_var == 0) {
                echo "==ok==";
                exit;
            }
        }
    }
}

echo 'error';

exit;
