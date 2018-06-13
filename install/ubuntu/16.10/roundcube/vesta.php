<?php

/**
 * Vesta Control Panel Password Driver
 *
 * @version 1.0
 * @author Serghey Rodin <skid@vestacp.com>
 */
class rcube_vesta_password {
    function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();
        $vesta_host = $rcmail->config->get('password_vesta_host');

        if (empty($vesta_host))
        {
            $vesta_host = 'localhost';
        }

        $vesta_port = $rcmail->config->get('password_vesta_port');
        if (empty($vesta_port))
        {
            $vesta_port = '8083';
        }

        $postvars = array(
          'email' => $_SESSION['username'],
          'password' => $curpass,
          'new' => $passwd
        );

        $postdata = http_build_query($postvars);

        $send  = 'POST /reset/mail/ HTTP/1.1' . PHP_EOL;
        $send .= 'Host: ' . $vesta_host . PHP_EOL;
        $send .= 'User-Agent: PHP Script' . PHP_EOL;
        $send .= 'Content-length: ' . strlen($postdata) . PHP_EOL;
        $send .= 'Content-type: application/x-www-form-urlencoded' . PHP_EOL;
        $send .= 'Connection: close' . PHP_EOL;
        $send .= PHP_EOL;
        $send .= $postdata . PHP_EOL . PHP_EOL;

        //$fp = fsockopen('ssl://' . $vesta_host, $vesta_port);
        $errno = "";
        $errstr = "";
        $context = stream_context_create();
        $result = stream_context_set_option($context, 'ssl', 'verify_peer', false);
        $result = stream_context_set_option($context, 'ssl', 'verify_peer_name', false);
        $result = stream_context_set_option($context, 'ssl', 'verify_host', false);
        $result = stream_context_set_option($context, 'ssl', 'allow_self_signed', true);

        $fp = stream_socket_client('ssl://' . $vesta_host . ':'.$vesta_port, $errno, $errstr, 60, STREAM_CLIENT_CONNECT, $context);
        fputs($fp, $send);
        $result = fread($fp, 2048);
        fclose($fp);

        $fp = fopen("/tmp/roundcube.log", 'w');
        fwrite($fp, "test ok");
        fwrite($fp, "\n");
        fclose($fp);


        if(strpos($result, 'ok') && !strpos($result, 'error'))
        {
            return PASSWORD_SUCCESS;
        }
        else {
            return PASSWORD_ERROR;
        }

    }
}