<!-- Begin toolbar -->
<div class="toolbar">
  <div class="toolbar-inner">
    <div class="toolbar-buttons">
      <?php if (($_SESSION['userContext'] === 'admin') && (isset($_GET['user'])) && ($_GET['user'] !== 'admin')) { ?>
        <a class="button button-secondary" id="btn-back" href="/list/key/?user=<?=htmlentities($_GET['user']);?>"><i class="fas fa-arrow-left status-icon blue"></i><?=_('Back');?></a>
      <?php } else { ?>
        <a class="button button-secondary" id="btn-back" href="/list/key/"><i class="fas fa-arrow-left status-icon blue"></i><?=_('Back');?></a>
      <?php } ?>
    </div>
    <div class="toolbar-buttons">
      <a href="#" class="button" data-action="submit" data-id="vstobjects"><i class="fas fa-floppy-disk status-icon purple"></i><?=_('Save');?></a>
    </div>
  </div>
</div>
<!-- End toolbar -->

<div class="l-center animate__animated animate__fadeIn">

  <form id="vstobjects" name="v_add_key" method="post">
    <input type="hidden" name="token" value="<?=$_SESSION['token']?>">
    <input type="hidden" name="ok" value="Add">

    <div class="form-container">
      <h1 class="form-title"><?=_('Add SSH Key');?></h1>
      <?php show_alert_message($_SESSION);?>
      <div>
        <label for="v_key" class="form-label"><?=_('SSH KEY') ?></label>
        <textarea class="form-control u-min-height300" name="v_key" id="v_key"><?=htmlentities(trim($v_key, "'"))?></textarea>
      </div>
    </div>

  </form>

</div>
