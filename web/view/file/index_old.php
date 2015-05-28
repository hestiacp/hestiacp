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
        <title>iviewer</title>
        <script type="text/javascript" src="/js/jquery-1.7.2.min.js" ></script>
        <script type="text/javascript" src="/js/jquery-ui-1.8.20.custom.min.js" ></script>
        <script type="text/javascript" src="/js/jquery.mousewheel.min.js" ></script>
        <script type="text/javascript" src="/js/iviewer/jquery.iviewer.js" ></script>
        <script type="text/javascript">
            var $ = jQuery;
            $(document).ready(function(){
                  var iv1 = $("#viewer").iviewer({
                       src: "/view/file/?path=<?php echo $path ?>&raw=true", 
                       update_on_resize: false,
                       zoom_animation: true,
                       mousewheel: true,
                       onMouseMove: function(ev, coords) { },
                       onStartDrag: function(ev, coords) { return false; }, //this image will not be dragged
                       onDrag: function(ev, coords) { }
                  });

                   $("#in").click(function(){ iv1.iviewer('zoom_by', 1); }); 
                   $("#out").click(function(){ iv1.iviewer('zoom_by', -1); }); 
                   $("#fit").click(function(){ iv1.iviewer('fit'); }); 
                   $("#orig").click(function(){ iv1.iviewer('set_zoom', 100); }); 
                   $("#update").click(function(){ iv1.iviewer('update_container_info'); });
/*
                  var iv2 = $("#viewer2").iviewer(
                  {
                      src: "test_image2.jpg"
                  });

                  $("#chimg").click(function()
                  {
                    iv2.iviewer('loadImage', "test_image.jpg");
                    return false;
                  });*/
            });
        </script>
        <link rel="stylesheet" href="/js/iviewer/jquery.iviewer.css" />
        <style>
            .viewer
            {
                width: 50%;
                height: 500px;
                border: 1px solid black;
                position: relative;
            }
            
            .wrapper
            {
                overflow: hidden;
            }
        </style>
    </head>
    <body>
        <h1>iviewer</h1>
        <!-- wrapper div is needed for opera because it shows scroll bars for reason -->
        <div class="wrapper">
            <span>
                <a id="in" href="#">+</a>
                <a id="out" href="#">-</a>
                <a id="fit" href="#">fit</a>
                <a id="orig" href="#">orig</a>
                <a id="update" href="#">update</a>
            </span>
            <div id="viewer" class="viewer"></div>
        </div>
    </body>
</html>
