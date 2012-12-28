<?php

/**
 * Vesta Control Panel Password Driver
 *
 * @version 1.0
 * @author Serghey Rodin <skid@vestacp.com>
 */

class rcube_vesta_password
{
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

        $request  = 'email='.$_SESSION['username'].'&';
        $request .= 'password='.$curpass.'&';
        $request .= 'new='.$passwd.'&';


        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                'content' => $request,
            ),
        ));

        $result = file_get_contents(
            $file = "https://".$vesta_host.":".$vesta_port."/reset/mail/?",
            $use_include_path = false,
            $context);

        if ($result == 'ok'){
            return PASSWORD_SUCCESS;
        }
        else {
            return PASSWORD_ERROR;
        }

    }
}
