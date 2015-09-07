<?php
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");
/*
// Check user session
if ((!isset($_SESSION['user'])) && (!defined('NO_AUTH_REQUIRED'))) {
    $_SESSION['request_uri'] = $_SERVER['REQUEST_URI'];
    header("Location: /login/");
    exit;
}
*/

?>

<title>Edit file <?= htmlspecialchars($_REQUEST['path']) ?></title>
<meta charset="utf-8" /> 

<link href="/css/file_manager_editor.css" type="text/css" rel="stylesheet">
<script src="/js/cheef-editor/jquery/jquery-1.8.3.min.js"></script>
<script src="/js/cheef-editor/ace/ace.js"></script>
<script src="/js/cheef-editor/ace/theme-twilight.js"></script>
<script src="/js/cheef-editor/ace/mode-ruby.js"></script>
<script src="/js/cheef-editor/jquery-ace.min.js"></script>

<?php 

    if (!empty($_REQUEST['path'])) {
        $content = '';
        $path = $_REQUEST['path'];
        if (is_readable($path)) {
            
            $image = getimagesize($path) ? true : false;
            
            if ($image) {
                header('Location: /view/file/?path='.$path);
                exit;
            }
            
            if (!empty($_POST['save'])) {
                $fn = tempnam ('/tmp', 'vst-save-file-');
                if ($fn) {
                    $f = fopen ($fn, 'w+');
                    fwrite($f, $_POST['contents']);
                    if ($f) {
                        copy($fn, $path);
                    }
                    unlink($fn);
                  }
            }
            
            $content = file_get_contents($path);
            $content = $content;
        }
    }
    else {
        $content = '';
    }

?>

<form method="post">
<input type="submit" name="save" value="Save" class="save" />


<textarea name="contents" class="editor" id="editor" rows="4" style="display:none;width: 100%; height: 100%;"><?php echo $content ?></textarea>

</form>
<script>
  $('.editor').ace({ theme: 'twilight', lang: 'ruby' });

  var dcrt = $('#editor').data('ace');
  var editor = dcrt.editor.ace;
  editor.gotoLine(0);
  editor.focus();
</script>
