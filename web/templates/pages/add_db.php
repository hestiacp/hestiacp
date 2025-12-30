<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/db/">
                <i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
            </a>
        </div>
        <div class="toolbar-buttons">
            <?php if (($_SESSION["role"] == "admin" && $accept === "true") || $_SESSION["role"] !== "admin") { ?>
                <button type="submit" class="button" form="main-form">
                    <i class="fas fa-floppy-disk icon-purple"></i><?= _("Save") ?>
                </button>
            <?php } ?>
        </div>
    </div>
</div>
<!-- End toolbar -->

<div class="container">

    <form
        x-data="{
            showAdvanced: <?= empty($v_adv) ? "false" : "true" ?>
        }"
        id="main-form"
        name="v_add_db"
        method="post">
        <input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
        <input type="hidden" name="ok" value="Add">

        <?php
        $db_database_val = htmlentities(trim($v_database, "'"));
        $db_dbuser_val = htmlentities(trim($v_dbuser, "'"));
        $db_email_val = htmlentities(trim($v_db_email, "'"));
        $create_account_warning = sprintf(
            _(
                "It is strongly advised to {create a standard user account} before "
                    . "adding %s to the server"
            ) . ' ' . _(
                "Due to the increased privileges the admin account possesses and "
                    . "potential security risks."
            ),
            _("a database")
        );

        // Prebuild type options to avoid long inline concatenations
        $type_options = '';
        foreach ($db_types as $dval) {
            $selected = (!empty($v_type) && $dval == $v_type) ? ' selected' : '';
            $type_options .= "<option value=\"" . htmlentities($dval) . "\""
                . $selected . ">" . htmlentities($dval) . "</option>\n";
        }

        // Prebuild host options
        $host_options = '';
        foreach ($db_hosts as $hval) {
            $selected = (!empty($v_host) && $hval == $v_host) ? ' selected' : '';
            $host_options .= "<option value=\"" . htmlentities($hval) . "\""
                . $selected . ">" . htmlentities($hval) . "</option>\n";
        }
        ?>

        <div class="form-container">
            <h1 class="u-mb20"><?= _("Add Database") ?></h1>
            <?php show_alert_message($_SESSION); ?>
            <?php if ($_SESSION["role"] == "admin" && $accept !== "true") { ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation"></i>
                    <p><?= htmlify_trans($create_account_warning, '</a>', '<a href="/add/user/">'); ?></p>
                </div>
            <?php } ?>
            <?php if ($_SESSION["role"] == "admin" && empty($accept)) { ?>
                <?php $add_user_href = '/add/user/';
                $add_db_accept_href = '/add/db/?accept=true'; ?>
                <div class="u-side-by-side u-mt20">
                    <a
                        class="button u-width-full u-mr10"
                        href="<?= $add_user_href ?>">
                        <?= _("Add User") ?>
                    </a>
                    <a
                        class="button button-danger u-width-full u-ml10"
                        href="<?= $add_db_accept_href ?>">
                        <?= _("Continue") ?>
                    </a>
                </div>
            <?php } ?>
            <?php if (($_SESSION["role"] == "admin" && $accept === "true") || $_SESSION["role"] !== "admin") { ?>
                <?php $prefix_msg = sprintf(
                    _("Prefix %s will be automatically added to database name and database user"),
                    "<span class=\"u-text-bold\">" . $user_plain . "_</span>"
                ); ?>
                <p class="hint u-mb20">
                    <?= $prefix_msg ?>
                </p>
                <div class="u-mb10">
                    <label for="v_database" class="form-label"><?= _("Database") ?></label>
                    <input
                        type="text"
                        class="form-control js-db-hint-database-name"
                        name="v_database"
                        id="v_database"
                        value="<?= $db_database_val ?>">
                    <small class="hint"></small>
                </div>
                <div class="u-mb10">
                    <label for="v_type" class="form-label"><?= _("Type") ?></label>
                    <select class="form-select" name="v_type" id="v_type">
                        <?= $type_options ?>
                    </select>
                </div>
                <div class="u-mb10">
                    <?php $max_username_msg = sprintf(_("Maximum %s characters length, including prefix"), 32); ?>
                    <label for="v_dbuser" class="form-label u-side-by-side">
                        <?= _("Username") ?>
                        <em><small>(<?= $max_username_msg ?>)</small></em>
                    </label>
                    <input
                        type="text"
                        class="form-control js-db-hint-username"
                        name="v_dbuser"
                        id="v_dbuser"
                        value="<?= $db_dbuser_val ?>">
                    <small class="hint"></small>
                </div>
                <div class="u-mb10">
                    <label for="v_password" class="form-label">
                        <?= _("Password") ?>
                        <button
                            type="button"
                            title="<?= _("Generate") ?>"
                            class="u-unstyled-button u-ml5 js-generate-password">
                            <i class="fas fa-arrows-rotate icon-green"></i>
                        </button>
                    </label>
                    <div class="u-pos-relative u-mb10">
                        <input type="text" class="form-control js-password-input" name="v_password" id="v_password">
                        <div class="password-meter">
                            <meter max="4" class="password-meter-input js-password-meter"></meter>
                        </div>
                    </div>
                </div>
                <?php require $_SERVER["HESTIA"] . "/web/templates/includes/password-requirements.php"; ?>
                <div class="u-mb20">
                    <label for="v_db_email" class="form-label">
                        <?= _("Email login credentials to:") ?>
                    </label>
                    <input
                        type="email"
                        class="form-control"
                        name="v_db_email"
                        id="v_db_email"
                        value="<?= $db_email_val ?>">
                </div>
                <div class="u-mb20">
                    <button x-on:click="showAdvanced = !showAdvanced" type="button" class="button button-secondary">
                        <?= _("Advanced Options") ?>
                    </button>
                </div>
                <div x-cloak x-show="showAdvanced">
                    <div class="u-mb10">
                        <label for="v_host" class="form-label"><?= _("Host") ?></label>
                        <select class="form-select" name="v_host" id="v_host">
                            <?= $host_options ?>
                        </select>
                    </div>
                    <div class="u-mb10">
                        <label for="v_charset" class="form-label"><?= _("Charset") ?></label>
                        <select class="form-select" name="v_charset" id="v_charset">
                            <?php
                            $charsets = [
                                'big5',
                                'dec8',
                                'cp850',
                                'hp8',
                                'koi8r',
                                'latin1',
                                'latin2',
                                'swe7',
                                'ascii',
                                'ujis',
                                'sjis',
                                'hebrew',
                                'tis620',
                                'euckr',
                                'koi8u',
                                'gb2312',
                                'greek',
                                'cp1250',
                                'gbk',
                                'latin5',
                                'armscii8',
                                'utf8',
                                'utf8mb4',
                                'ucs2',
                                'cp866',
                                'keybcs2',
                                'macce',
                                'macroman',
                                'cp852',
                                'latin7',
                                'cp1251',
                                'cp1256',
                                'cp1257',
                                'binary',
                                'geostd8',
                                'cp932',
                                'eucjpms'
                            ];

                            foreach ($charsets as $ch) {
                                $selected = '';
                                $is_selected = (!empty($v_charset) && $v_charset == $ch)
                                    || ($ch == 'utf8mb4' && empty($v_charset));
                                if ($is_selected) {
                                    $selected = ' selected';
                                }
                                ?>
                                <option value="<?= $ch ?>" <?= $selected ?>><?= $ch ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
        </div>
            <?php } ?>
</div>
</form>
</div>
