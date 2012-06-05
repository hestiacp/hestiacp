<br>
<form method="post">
<textarea size="20" class="add-input" name="v_ssl_cert"><?php if (!empty($v_ssl_cert)) echo $v_ssl_cert;  ?></textarea>
<br>
<input type="submit" name="ok" value="OK" class="add-button">

<br>
<?php
    if (!empty($_POST['v_ssl_cert'])) {
        $fp = fopen("/tmp/test.crt", 'w');
        fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_cert']));
        fclose($fp);
    }

?>