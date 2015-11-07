<?php 
if (!empty($_REQUEST['path'])) {
    $path = $_REQUEST['path'];
    if (is_readable($path) && !empty($_REQUEST['raw'])) {
        header('content-type: image/jpeg'); 
        print file_get_contents($path);
        exit;
    }
}
else {
    die('File not found');
}


?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>fotorama</title>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<link  href="http://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.2/fotorama.css" rel="stylesheet"> <!-- 3 KB -->
	<script src="http://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.2/fotorama.js"></script> <!-- 16 KB -->
    </head>
    <body>
        <div class="fotorama">
	     <img src="/view/file/?path=<?=$path?>&raw=true" />
        </div>
    </body>
</html>
