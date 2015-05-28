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
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<link href="//cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.2/fotorama.css" rel="stylesheet">
	<script src="//cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.2/fotorama.js"></script>
    </head>
    <body>
        <div style="background-color: #eee; /*display: inline-block; vertical-align: middle;*/ height: 100%; text-align: center; /*position: absolute; /*top: 50%; left: 50%; margin-top: -50px; margin-left: -50px;  /*data-maxheight="100%" data-maxwidth="100%" */"
	class="fotoram" data-fit="scaledown" data-allowfullscreen="true" data-nav="false">
	     <img src="/view/file/?path=<?=$path?>&raw=true" style="background: #3A6F9A; vertical-align: middle;  /*max-height: 25px; max-width: 160px;*/" />
        </div>
    </body>
</html>
