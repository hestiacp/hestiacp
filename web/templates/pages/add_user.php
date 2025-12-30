<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/user/">
                <i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
            </a>
        </div>
        <div class="toolbar-buttons">
            <button type="submit" class="button" form="main-form">
                <i class="fas fa-floppy-disk icon-purple"></i><?= _("Save") ?>
            </button>
        </div>
    </div>
</div>
<!-- End toolbar -->

<div class="container">

    <?php
    $x_login_disabled = ($v_login_disabled == "yes") ? 'true' : 'false';
    $pw_require_path = $_SERVER["HESTIA"] . "/web/templates/includes/password-requirements.php";
    $no_login_msg = _("Do not allow user to log in to Control Panel");
    ?>

    <form
        x-data="{ loginDisabled: <?= $x_login_disabled ?> }"
        id="main-form"
        name="v_add_user"
        method="post">
        <input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
        <input type="hidden" name="ok" value="Add">

        <div class="form-container">
            <h1 class="u-mb20"><?= _("Add User") ?></h1>
            <?php show_alert_message($_SESSION); ?>
            <div class="u-mb10">
                <label for="v_username" class="form-label"><?= _("Username") ?></label>
                <input
                    type="text"
                    class="form-control"
                    name="v_username"
                    id="v_username"
                    value="<?= htmlentities(trim($v_username, "'")) ?>"
                    tabindex="1"
                    required>
            </div>
            <div class="u-mb10">
                <label for="v_name" class="form-label"><?= _("Contact Name") ?></label>
                <input
                    type="text"
                    class="form-control"
                    name="v_name"
                    id="v_name"
                    value="<?= htmlentities(trim($v_name, "'")) ?>"
                    tabindex="2"
                    required>
            </div>
            <div class="u-mb10">
                <label for="v_email" class="form-label"><?= _("Email") ?></label>
                <input
                    type="email"
                    class="form-control js-sync-email-input"
                    name="v_email"
                    id="v_email"
                    value="<?= htmlentities(trim($v_email, "'")) ?>"
                    tabindex="3"
                    required>
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
                    <input
                        type="text"
                        class="form-control js-password-input"
                        name="v_password"
                        id="v_password"
                        value="<?= htmlentities(trim($v_password, "'")) ?>"
                        tabindex="4"
                        required>
                    <div class="password-meter">
                        <meter max="4" class="password-meter-input js-password-meter"></meter>
                    </div>
                </div>
            </div>
            <?php require $pw_require_path; ?>
            <div class="form-check">
                <input
                    x-model="loginDisabled"
                    class="form-check-input"
                    type="checkbox"
                    name="v_login_disabled"
                    id="v_login_disabled">
                <label for="v_login_disabled"><?= $no_login_msg ?></label>
            </div>
            <div x-cloak x-show="!loginDisabled" id="send-welcome">
                <?php $email_notice_attr = (!empty($v_email_notice) && $v_email_notice === 'yes') ? 'checked' : ''; ?>
                <div class="form-check u-mb10">
                    <input
                        class="form-check-input js-sync-email-checkbox"
                        type="checkbox"
                        name="v_email_notice"
                        id="v_email_notify"
                        tabindex="5"
                        <?= $email_notice_attr ?>>
                    <label for="v_email_notify">
                        <?= _("Send welcome email") ?>
                    </label>
                </div>
            </div>
            <div class="u-mb10">
                <label for="v_language" class="form-label"><?= _("Language") ?></label>
                <select class="form-select" name="v_language" id="v_language" tabindex="6" required>
                    <?php
                    foreach ($languages as $key => $value) {
                        $selected = '';
                        if (($key == $_SESSION['LANGUAGE']) && (empty($v_language))) {
                            $selected = ' selected';
                        }
                        if (isset($v_language) && (htmlentities($key) == trim($v_language, "'"))) {
                            $selected = ' selected';
                        }
                        printf(
                            "<option value=\"%s\"%s>%s</option>\n",
                            htmlentities($key),
                            $selected,
                            htmlentities($value)
                        );
                    }
                    ?>
                </select>
            </div>
            <div class="u-mb10">
                <label for="v_role" class="form-label"><?= _("Role") ?></label>
                <select class="form-select" name="v_role" id="v_role" required>
                    <?php
                    $sel_admin = $v_role == "admin" ? ' selected' : '';
                    $sel_dns = $v_role == "dns-cluster" ? ' selected' : '';
                    ?>
                    <option value="user"><?= _("User") ?></option>
                    <option value="admin" <?= $sel_admin ?>><?= _("Administrator") ?></option>
                    <option value="dns-cluster" <?= $sel_dns ?>><?= _("DNS Sync User") ?></option>
                </select>
            </div>
            <div class="u-mb10">
                <label for="v_package" class="form-label"><?= _("Package") ?></label>
                <select class="form-select" name="v_package" id="v_package" tabindex="8" required>
                    <?php
                    foreach ($data as $key => $value) {
                        $selected = '';
                        if ((!empty($v_package)) && ($key == $_POST['v_package'])) {
                            $selected = ' selected';
                        } else {
                            if ($key == 'default') {
                                $selected = ' selected';
                            }
                        }
                        printf(
                            "<option value=\"%s\"%s>%s</option>\n",
                            htmlentities($key),
                            $selected,
                            htmlentities($key)
                        );
                    }
                    ?>
                </select>
            </div>
            <div class="u-mb10">
                <label for="v_notify" class="form-label">
                    <?= _("Email login credentials to:") ?>
                </label>
                <input
                    type="email"
                    class="form-control js-sync-email-output"
                    name="v_notify"
                    id="v_notify"
                    value="<?= htmlentities(trim($v_notify, "'")) ?>"
                    tabindex="8">
            </div>
        </div>

    </form>

</div>
