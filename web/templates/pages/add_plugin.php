<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/plugin/">
                <i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
            </a>
        </div>
        <div class="toolbar-buttons">
            <button type="submit" class="button" form="vstobjects">
                <i class="fas fa-floppy-disk icon-purple"></i><?= _("Install") ?>
            </button>
        </div>
    </div>
</div>
<!-- End toolbar -->

<div class="container animate__animated animate__fadeIn">
    <form id="vstobjects" name="v_add_plugin" method="post" class="js-enable-inputs-on-submit">
        <input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
        <input type="hidden" name="ok" value="Add">

        <div class="form-container">
            <h1 class="form-title"><?= _("Install Plugin") ?></h1>
            <?php show_alert_message($_SESSION); ?>

            <div class="u-mb10">
                <label for="v_plugin_url" class="form-label"><?= _("Github repository") ?></label>
                <input type="text" class="form-control" name="v_plugin_url" id="v_plugin_url" value="<?= htmlentities(trim($v_plugin_url, "'")) ?>" required>
            </div>
        </div>
    </form>
</div>
