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
    $type = $_GET['type'];
    print <<<JS
    <script type="text/javascript">parent.App.Pages.WEB_DOMAIN.setSSL('{$contents}', '{$type}');</script>
JS;
}

function handleUpload()
{
    if ($_FILES["upload-ssl"]["size"] < 20000) {
	if ($_FILES["upload-ssl"]["error"] > 0) {
	    return show_form() . "Error occured. Please try to upload again";
        }
	else {
	    /*echo "Upload: " . $_FILES["file"]["name"] . "<br />";
	    echo "Type: " . $_FILES["file"]["type"] . "<br />";
	    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
	    echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";*/

	    $contents = file_get_contents($_FILES["upload-ssl"]['tmp_name']);
	    return show_form().pass_contents($contents);

    	    /*if (file_exists("upload/" . $_FILES["file"]["name"])) {
        	echo $_FILES["file"]["name"] . " already exists. ";
            }
    	    else {
        	move_uploaded_file($_FILES["file"]["tmp_name"],
    		    	           "upload/" . $_FILES["file"]["name"]);
		echo "Stored in: " . "upload/" . $_FILES["file"]["name"];
    	    }*/
	}
    }
    else {
       return show_form() . "Filesize is too large. Please ensure you are uploading correct file";
    }
}

//
// functions
function show_form()
{
    $type = $_GET['type'];
    if (!in_array($type, array('key', 'cert'))) {
	exit;
    }
        
    print <<<HTML
	<form action="" method="post" enctype="multipart/form-data"><input type="hidden" value="true" name="process"><input type="file" name="upload-ssl" onChange="document.forms[0].submit()"/></form>
HTML;
}

?>