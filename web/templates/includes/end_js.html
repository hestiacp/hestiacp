<script src="/js/vendor/chart.min.js?<?=JS_LATEST_UPDATE?>"></script>
<script src="/js/vendor/jquery.cookie.js?<?=JS_LATEST_UPDATE?>"></script>
<script src="/js/vendor/jquery-ui.min.js?<?=JS_LATEST_UPDATE?>"></script>
<script src="/js/vendor/jquery.finder.js?<?=JS_LATEST_UPDATE?>"></script>
<script src="/js/hotkeys.js?<?=JS_LATEST_UPDATE?>"></script>
<script src="/js/events.js?<?=JS_LATEST_UPDATE?>"></script>
<script src="/js/app.js?<?=JS_LATEST_UPDATE?>"></script>
<script src="/js/init.js?<?=JS_LATEST_UPDATE?>"></script>
<script src="/js/i18n.js.php?<?=JS_LATEST_UPDATE?>"></script>
<script src="/js/templates.js?<?=JS_LATEST_UPDATE?>"></script>
<?php foreach(new DirectoryIterator($_SERVER['HESTIA'].'/web/js/custom_scripts') as $customScript){
  if($customScript->getExtension() === 'js'){
    echo '<script src="/js/custom_scripts/'.rawurlencode($customScript->getBasename()).'"></script>';
  } elseif($customScript->getExtension() === "php"){
    require_once($customScript->getPathname());
  }
 } ?>
<script>
  $(function() {
    set_sticky_class();
  });
</script>

<?php
  if (!empty($_SESSION['error_msg'])):
  ?>
  <div>
    <script>
      $(function() {
        $('#dialog:ui-dialog').dialog('destroy');
        $('#dialog-message').dialog({
          modal: true,
          resizable: false,
          buttons: {
            Ok: function() {
              $(this).dialog('close');
            }
          },
          create: function() {
            var buttonGroup = $(this).closest(".ui-dialog").find('.ui-dialog-buttonset');
            buttonGroup.find('button:first').addClass('button submit')
            buttonGroup.find('button:last').addClass('button button-secondary cancel');
          }
        });
      });
    </script>
    <div id="dialog-message" title="">
      <p><?=htmlentities($_SESSION['error_msg'])?></p>
    </div>
  </div>
<?php
  unset($_SESSION['error_msg']);
  endif;
