<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/mail/">
                <i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
            </a>
        </div>
        <div class="toolbar-buttons">
            <?= () ? 'checked' : '' ?>>
                        <label for="v_antispam">
                            <?= _("Spam Filter") ?>
                        </label>
                    </div>
                    <div class="form-check u-mb10">
                        <input class="form-check-input" type="checkbox" name="v_reject" id="v_reject" <?= () ? 'checked' : '' ?>>
                        <label for="v_reject">
                            <?= _("Reject Spam") ?>
                        </label>
                    </div>
                <?php } ?>
                <?= () ? 'checked' : '' ?>>
                        <label for="v_antivirus">
                            <?= _("Anti-Virus") ?>
                        </label>
                    </div>
                <?php } ?>
                <div class="form-check u-mb10">
                    <input class="form-check-input" type="checkbox" name="v_dkim" id="v_dkim" <?= () ? 'checked' : '' ?>>
                    <label for="v_dkim">
                        <?= _("DKIM Support") ?>
                    </label>
                </div>
                <div class="form-check u-mb10">
                    <input x-model="hasSmtpRelay" class="form-check-input" type="checkbox" name="v_smtp_relay" id="v_smtp_relay">
                    <label for="v_smtp_relay">
                        <?= _("SMTP Relay") ?>
                    </label>
                </div>
                <div x-cloak x-show="hasSmtpRelay" id="smtp_relay_table" class="u-pl30">
                    <div class="u-mb10">
                        <label for="v_smtp_relay_host" class="form-label"><?= _("Host") ?></label>
                        <input type="text" class="form-control" name="v_smtp_relay_host" id="v_smtp_relay_host" value="<?= htmlentities(trim($v_smtp_relay_host, "'")) ?>">
                    </div>
                    <div class="u-mb10">
                        <label for="v_smtp_relay_port" class="form-label"><?= _("Port") ?></label>
                        <input type="text" class="form-control" name="v_smtp_relay_port" id="v_smtp_relay_port" value="<?= htmlentities(trim($v_smtp_relay_port, "'")) ?>">
                    </div>
                    <div class="u-mb10">
                        <label for="v_smtp_relay_user" class="form-label"><?= _("Username") ?></label>
                        <input type="text" class="form-control" name="v_smtp_relay_user" id="v_smtp_relay_user" value="<?= htmlentities(trim($v_smtp_relay_user, "'")) ?>">
                    </div>
                    <div class="u-mb10">
                        <label for="v_smtp_relay_pass" class="form-label"><?= _("Password") ?></label>
                        <input type="text" class="form-control" name="v_smtp_relay_pass" id="v_smtp_relay_pass">
                    </div>
                </div>
            <?php } ?>
        </div>

    </form>

</div>
