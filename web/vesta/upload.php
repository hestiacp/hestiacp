<?php

define('V_ROOT_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);                                                                                                                                                                        
                                                                                                                                                                                                                                      
require_once V_ROOT_DIR . 'config/Config.class.php';                                                                                                                                                                                  
require_once V_ROOT_DIR . 'core/utils/Utils.class.php';                                                                                                                                                                               
require_once V_ROOT_DIR . 'core/VestaSession.class.php';                                                                                                                                                                              
require_once V_ROOT_DIR . 'core/Vesta.class.php';                                                                                                                                                                                     
require_once V_ROOT_DIR . 'core/exceptions/SystemException.class.php';                                                                                                                                                                
require_once V_ROOT_DIR . 'core/exceptions/ProtectionException.class.php';                                                                                                                                                            
require_once V_ROOT_DIR . 'core/utils/Message.class.php';                                                                                                                                                                             
require_once V_ROOT_DIR . 'core/Request.class.php';                                                                                                                                                                                   
require_once V_ROOT_DIR . 'api/AjaxHandler.php';  

switch ($_GET['action']) {
    case 'show':
	if (!empty($_POST['process'])) {
	    handleUpload();
	}
	else {
        show_form();
	}
	break;
}

function pass_contents($content)
{
    if (trim($content) == '') {
	show_form(); print("<span id='msg' style='font-size:9px;color: red;'>Error occured. Please try to upload again</span>");
	return;
    }
    $type = $_GET['type'];
    print <<<JS
    <textarea id="result" style="display: none;">{$content}</textarea>
    <script type="text/javascript">parent.App.Pages.WEB_DOMAIN.setSSL('{$type}', this);</script>
JS;
}

function handleUpload()
{
    if ($_FILES["upload-ssl"]["size"] < 20000) {
	if ($_FILES["upload-ssl"]["error"] > 0) {
	    show_form(); print("<span id='msg' style='font-size:9px;color: red;'>Error occured. Please try to upload again</span>");
	    return;
        }
	else {
	    $contents = file_get_contents($_FILES["upload-ssl"]['tmp_name']);
	    return show_form().pass_contents($contents);
	}
    }
    else {
        show_form(); print("<span id='msg' style='font-size:9px;color: red;'>Filesize is too large. Please ensure you are uploading correct file</span>");
	return;
    }
}

//
// functions
function show_form()
{
    $type = $_GET['type'];
    if (!in_array($type, array('key', 'cert', 'ca'))) {
        exit;
    }
        
    print <<<HTML
	<script type="text/javascript">
	function upload()
	{
	    var l_dot = '';
	    document.getElementById('form-upl').style.display = 'none';
	    try {
		document.getElementById('msg').style.display = 'none';
	    } catch(e){};
	    document.getElementById('form-loading').style.display = 'block';
	    setInterval(function(){
		if (l_dot == '...') {
		    l_dot = '';
		}
		l_dot += '.';
		document.getElementById('form-loading').innerHTML = 'Processing'+l_dot;
	    }, 500);
	    setTimeout(function() {
    		document.forms[0].submit();
	    }, 1000);
	}
	</script>
	<p id="form-loading" style="font-size:11px;color:#333;"></p>
	<form id="form-upl" action="" method="post" style="padding:0;margin:0" enctype="multipart/form-data"><input type="hidden" value="true" name="process"><input type="file" name="upload-ssl" onChange="upload();"/></form>
HTML;
}

?>
