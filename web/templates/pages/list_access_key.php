<?php
// Prevent resubmit form on page refresh
if (!empty($_POST["ok"])) { ?>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
<?php } ?>

<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/access-key/">
                <i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
            </a>
        </div>
    </div>
</div>
<!-- End toolbar -->

<div class="container">
    <form id="main-form">

        <div class="form-container">
            <h1 class="u-mb20"><?= _("Access Key") ?></h1>
            <?php show_alert_message($_SESSION); ?>
            <?php if (!empty($key_data["ACCESS_KEY_ID"])) { ?>
                <div class="u-mt15 u-mb10">
                    <?php $access_key_id_val = htmlentities(trim($key_data['ACCESS_KEY_ID'], "'")); ?>
                    <label for="access_key_id" class="form-label"><?= _("Access Key ID") ?></label>
                    <input
                        type="text"
                        class="form-control"
                        id="access_key_id"
                        maxlength="255"
                        readonly
                        value="<?= $access_key_id_val ?>">
                </div>
            <?php } ?>
            <?php if (!empty($_SESSION["ok_msg"])) { ?>
                <?php if (!empty($key_data["ACCESS_KEY_ID"]) && !empty($key_data["SECRET_ACCESS_KEY"])) { ?>
                    <div class="u-mb20">
                        <label for="secret_key" class="form-label">
                            <?= _("Secret Key") ?>
                            <br>
                            <?php $secret_warning = _("Warning! Last chance to save secret key!"); ?>
                            <span class="inline-alert inline-alert-warning u-mb20"><?= $secret_warning ?></span>
                        </label>
                        <?php $secret_key_val = htmlentities(trim($key_data['SECRET_ACCESS_KEY'], "'")); ?>
                        <input
                            type="text"
                            class="form-control"
                            id="secret_key"
                            maxlength="255"
                            readonly
                            value="<?= $secret_key_val ?>">
                    </div>
                <?php } ?>
            <?php } ?>
            <p class="u-mb10"><?= _("Permissions") ?></p>
            <ul class="u-list-bulleted u-mb10">
                <?php foreach ($key_data["PERMISSIONS"] as $api_name) { ?>
                    <li><?= _($api_name) ?></li>
                <?php } ?>
            </ul>
            <div class="u-mb10">
                <?php $comment_val = htmlentities(trim($key_data['COMMENT'], "'")); ?>
                <label for="service" class="form-label"><?= _("Comment") ?></label>
                <input
                    type="text"
                    class="form-control"
                    id="service"
                    maxlength="255"
                    readonly
                    value="<?= $comment_val ?>">
            </div>
        </div>

    </form>

</div>
