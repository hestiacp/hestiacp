<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/user/">
                <i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
            </a>
            <?= () ? 'checked' : '' ?>>
                        <label for="v_twofa">
                            <?= _("Enable two-factor authentication") ?>
                        </label>
                    </div>
                    <?php if (!empty($v_twofa)) { ?>
                        <p class="u-mb10"><?= _("Account Recovery Code") . ": " . $v_twofa ?></p>
                        <p class="u-mb10"><?= _("Please scan the code below in your 2FA application") ?>:</p>
                        <div class="u-mb10">
                            <img class="qr-code" src="<?= htmlentities($v_qrcode) ?>" alt="<?= _("2FA QR Code") ?>">
                        </div>
                    <?php } ?>
                </div>
                <div x-cloak x-show="!loginDisabled" id="password-options-ip">
                    <div class="form-check">
                        <input x-model="useIpAllowList" class="form-check-input" type="checkbox" name="v_login_use_iplist" id="v_login_use_iplist">
                        <label for="v_login_use_iplist">
                            <?= _("Use IP address allow list for login attempts") ?>
                        </label>
                    </div>
                </div>
                <div x-cloak x-show="useIpAllowList" id="ip-allowlist" class="u-mt10">
                    <input type="text" class="form-control" name="v_login_allowed_ips" value="<?= htmlentities(trim($v_login_allowed_ips, "'")) ?>" placeholder="<?= _("For example") ?>: 127.0.0.1,192.168.1.100">
                </div>
            </div>
            <div class="u-mb10">
                <label for="v_language" class="form-label"><?= _("Language") ?></label>
                <select class="form-select" name="v_language" id="v_language" required>
                    <?php
                    foreach ($languages as $key => $value) {
                        echo "\n\t\t\t\t\t\t\t\t\t<option value=\"" . $key . "\"";
                        $skey = "'" . $key . "'";
                        if (( $key == $v_language ) || ( $skey == $v_language)) {
                            echo 'selected' ;
                        }
                        if (( $key == detect_user_language() ) && (empty($v_language))) {
                            echo 'selected' ;
                        }
                        echo ">" . htmlentities($value) . "</option>\n";
                    }
                    ?>
                </select>
            </div>
            <?php if ($v_username != "admin" && $_SESSION["userContext"] === "admin" && $_SESSION["user"] != $v_username) : ?>
                <div class="u-mb10">
                    <label for="v_role" class="form-label"><?= _("Role") ?></label>
                    <select class="form-select" name="v_role" id="v_role" required>
                        <option value="user"><?= _("User") ?></option>
                        <option value="admin" <?= $v_role == "admin" ? "selected" : "" ?>><?= _("Administrator") ?></option>
                        <option value="dns-cluster" <?= $v_role == "dns-cluster" ? "selected" : "" ?>><?= _("DNS Sync User") ?></option>
                    </select>
                </div>
            <?php endif; ?>
            <?php if ($_SESSION["POLICY_USER_CHANGE_THEME"] !== "no") { ?>
            <div class="u-mb10">
                <label for="v_user_theme" class="form-label"><?= _("Theme") ?></label>
                <select class="form-select" name="v_user_theme" id="v_user_theme">
                    <?php
                    foreach ($themes as $key => $value) {
                        echo "\t\t\t\t<option value=\"" . $value . "\"";
                        if ((!empty($_SESSION['userTheme'])) && ( $value == $v_user_theme )) {
                            echo ' selected' ;
                        }
                        if ((empty($v_user_theme) && (!empty($_SESSION['THEME']))) && ( $value == $_SESSION['THEME'] )) {
                            echo ' selected' ;
                        }
                        echo ">" . $value . "</option>\n";
                    }
                    ?>
                </select>
            </div>
            <?php } ?>
                <div class="u-mb10">
                    <label for="v_sort_order" class="form-label"><?= _("Default List Sort Order") ?></label>
                    <select class="form-select" name="v_sort_order" id="v_sort_order">
                        <option value='date' <?php if ($v_sort_order === 'date') {
                            echo 'selected';
                                             } ?>><?= _("Date") ?></option>
                        <option value='name' <?php if ($v_sort_order === 'name') {
                            echo 'selected';
                                             } ?>><?= _("Name") ?></option>
                    </select>
                </div>
            <?php if ($_SESSION['userContext'] === 'admin') { ?>
                <div class="u-mb20">
                    <label for="v_package" class="form-label"><?= _("Package") ?></label>
                    <select class="form-select" name="v_package" id="v_package" required>
                        <?php
                        foreach ($packages as $key => $value) {
                            echo "\n\t\t\t\t\t\t\t\t\t<option value=\"" . htmlentities($key) . "\"";
                            $skey = "'" . $key . "'";
                            if (( $key == $v_package ) || ( $skey == $v_package)) {
                                echo 'selected' ;
                            }
                            echo ">" . htmlentities($key) . "</option>\n";
                        }
                        ?>
                    </select>
                </div>
                <div class="u-mb20">
                    <button x-on:click="showAdvanced = !showAdvanced" type="button" class="button button-secondary">
                        <?= _("Advanced Options") ?>
                    </button>
                </div>
                <div x-cloak x-show="showAdvanced">
                    <div class="u-mb10">
                        <label for="v_shell" class="form-label"><?= _("SSH Access") ?></label>
                        <select class="form-select" name="v_shell" id="v_shell">
                            <?php
                            foreach ($shells as $key => $value) {
                                echo "\t\t\t\t<option value=\"" . htmlentities($value) . "\"";
                                $svalue = "'" . $value . "'";
                                if (( $value == $v_shell ) || ($svalue == $v_shell )) {
                                    echo 'selected' ;
                                }
                                echo ">" . htmlentities($value) . "</option>\n";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="u-mb10">
                        <label for="v_phpcli" class="form-label"><?= _("PHP CLI Version") ?></label>
                        <select class="form-select" name="v_phpcli" id="v_phpcli">
                            <?php
                            foreach ($php_versions as $key => $value) {
                                $php = explode('-', $value);
                                echo "\t\t\t\t<option value=\"" . $value . "\"";
                                $svalue = "'" . $value . "'";
                                if ((!empty($v_phpcli)) && ( $value == $v_phpcli ) || ($svalue == $v_phpcli)) {
                                    echo ' selected' ;
                                }
                                if ((empty($v_phpcli)) && ($value == DEFAULT_PHP_VERSION)) {
                                    echo ' selected' ;
                                }
                                echo ">" . htmlentities($value) . "</option>\n";
                            }
                            ?>
                        </select>
                    </div>
                    <?php if ((isset($_SESSION['DNS_SYSTEM'])) && (!empty($_SESSION['DNS_SYSTEM']))) { ?>
                        <p class="form-label u-mb10"><?= _("Default Name Servers") ?></p>
                        <div class="u-mb5">
                            <input type="text" class="form-control" name="v_ns1" value="<?= htmlentities(trim($v_ns1, "'")) ?>">
                        </div>
                        <div class="u-mb5">
                            <input type="text" class="form-control" name="v_ns2" value="<?= htmlentities(trim($v_ns2, "'")) ?>">
                        </div>
                        <?php require $_SERVER["HESTIA"] . "/web/templates/includes/extra-ns-fields.php"; ?>
                        <button type="button" class="form-link u-mt20 js-add-ns" <?php if ($v_ns8) {
                            echo 'style="display:none;"';
                                                                                 } ?>>
                            <?= _("Add Name Server") ?>
                        </button>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

    </form>

</div>
