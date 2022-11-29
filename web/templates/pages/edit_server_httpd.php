<!-- Begin toolbar -->
<div class="toolbar">
  <div class="toolbar-inner">
    <div class="toolbar-buttons">
      <a class="button button-secondary" id="btn-back" href="/list/server/"><i class="fas fa-arrow-left status-icon blue"></i><?=_('Back');?></a>
      <a href="/edit/server/php/" class="button button-secondary"><i class="fas fa-pencil status-icon orange"></i><?=_('Configure');?> PHP</a>
    </div>
    <div class="toolbar-buttons">
      <a href="#" class="button" data-action="submit" data-id="vstobjects"><i class="fas fa-floppy-disk status-icon purple"></i><?=_('Save');?></a>
    </div>
  </div>
</div>
<!-- End toolbar -->

<div class="l-center animate__animated animate__fadeIn">

  <form id="vstobjects" name="v_configure_server" method="post">
    <input type="hidden" name="token" value="<?=$_SESSION['token']?>">
    <input type="hidden" name="save" value="save">

    <div class="form-container">
      <h1 class="form-title"><?=_('Configuring Server');?>: <?=$v_service_name;?></h1>
      <?php show_alert_message($_SESSION);?>
      <div class="u-mb20">
        <label for="v_config" class="form-label"><?=$v_config_path;?></label>
        <textarea class="form-control u-min-height300 u-allow-resize u-console" name="v_config" id="v_config"><?=$v_config;?></textarea>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="v_restart" id="v_restart" checked>
        <label for="v_restart">
          <?=_('restart');?>
        </label>
      </div>
    </div>

  </form>

</div>
