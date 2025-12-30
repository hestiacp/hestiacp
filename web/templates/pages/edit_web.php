<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/web/">
                <i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
            </a>
        </div>
        <div class="toolbar-buttons">
            <a href="/delete/web/cache/?domain=<?= htmlentities($v_domain); ?>&token=<?= $_SESSION['token']; ?>"
                class="button button-secondary js-clear-cache-button <?= $delete_cache_hidden ?>">
                <i class="fas fa-trash icon-red"></i><?= _("Purge NGINX Cache") ?>
            </a>
            <div class="form-check u-mb10">
                <?php $v_ssl_forcessl_checked = ($v_ssl_forcessl == 'yes') ? 'checked' : ''; ?>
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="v_ssl_forcessl"
                    id="v_ssl_forcessl"
                    <?= $v_ssl_forcessl_checked ?>>
                <label for="v_ssl_forcessl">
                    <?= _("Enable automatic HTTPS redirection") ?>
                </label>
            </div>
            <div class="form-check u-mb20">
                <?php $v_ssl_hsts_checked = ($v_ssl_hsts == 'yes') ? 'checked' : ''; ?>
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="v_ssl_hsts"
                    id="ssl_hsts"
                    <?= $v_ssl_hsts_checked ?>>
                <label for="ssl_hsts">
                    <?= _("Enable HTTP Strict Transport Security (HSTS)") ?>
                    <a href="https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security" target="_blank">
                        <i class="fas fa-question-circle"></i>
                    </a>
                </label>
            </div>
            <div x-cloak x-show="showCertificates" class="js-ssl-details">
                <div class="u-mb10">
                    <label for="ssl_crt" class="form-label">
                        <?= _("SSL Certificate") ?>
                        <span id="generate-csr"> /
                            <a class="form-link" target="_blank"
                                href="/generate/ssl/?domain=<?= htmlentities($v_domain) ?>">
                                <?= _("Generate Self-Signed SSL Certificate") ?>
                            </a>
                        </span>
                    </label>
                    <textarea class="form-control u-min-height100 u-console" name="v_ssl_crt" id="ssl_crt">
                                    <?= htmlentities(trim($v_ssl_crt, "'")) ?>
                                </textarea>
                </div>
                <div class="u-mb10">
                    <label for="v_ssl_key" class="form-label"><?= _("SSL Private Key") ?></label>
                    <textarea class="form-control u-min-height100 u-console" name="v_ssl_key" id="v_ssl_key">
                                    <?= htmlentities(trim($v_ssl_key, "'")) ?>
                                </textarea>
                </div>
                <div class="u-mb20">
                    <label for="v_ssl_ca" class="form-label">
                        <?= _("SSL Certificate Authority / Intermediate") ?>
                        <span class="optional">(<?= _("Optional") ?>)</span>
                    </label>
                    <textarea class="form-control u-min-height100 u-console" name="v_ssl_ca" id="v_ssl_ca">
                                    <?= htmlentities(trim($v_ssl_ca, "'")) ?>
                                </textarea>
                </div>
            </div>
            <div class="u-mb10">
                <label for="v_ftp" class="form-label"><?= _("Additional FTP account(s)") ?></label>
            </div>
            <div class="js-active-ftp-accounts">
                <?php foreach ($v_ftp_users as $i => $ftp_user) : ?>
                    <?php
                    $v_ftp_user     = $ftp_user['v_ftp_user'];
                    $v_ftp_password = $ftp_user['v_ftp_password'];
                    $v_ftp_path     = $ftp_user['v_ftp_path'];
                    $v_ftp_email    = $ftp_user['v_ftp_email'];
                    $v_ftp_pre_path = $ftp_user['v_ftp_pre_path'];
                    $v_ftp_path_prev = '';
                    if (!empty($v_ftp_path)) {
                        $v_ftp_path_prev = ($v_ftp_path[0] != '/' ? '/' : '') . htmlentities(trim($v_ftp_path, "'"));
                    }
                    ?>
                    <div class="js-ftp-account js-ftp-account-nrm"
                        name="v_add_domain_ftp"
                        style="<?= empty($v_ftp_user) ? 'display: none;' : '' ?>">
                        <div class="u-mb10">
                            <?= _("FTP") ?> #<span class="js-ftp-user-number"><?= $i + 1; ?></span>
                            <button
                                type="button"
                                class="form-link form-link-danger u-ml5 js-delete-ftp-account">
                                <?= _("Delete") ?>
                            </button>
                            <input
                                type="hidden"
                                class="js-ftp-user-deleted"
                                name="v_ftp_user[<?= $i ?>][delete]"
                                value="0">
                            <input type="hidden"
                                class="js-ftp-user-is-new"
                                name="v_ftp_user[<?= $i ?>][is_new]"
                                value="<?= htmlentities($ftp_user['is_new']) ?>">
                        </div>
                        <div class="u-pl30 u-mb10">
                            <label for="v_ftp_user[<?= $i ?>][v_ftp_user]" class="form-label">
                                <?= _("Username") ?><br>
                                <span style="color:#777;">
                                    <?php
                                    $prefix_msg = sprintf(
                                        _('Prefix %s will be added to username automatically'),
                                        $user_plain . "_"
                                    );
                                    ?>
                                    <?= $prefix_msg ?>
                                </span>
                            </label>
                            <input type="text"
                                class="form-control js-ftp-user"
                                <?= $ftp_user['is_new'] != 1 ? 'disabled="disabled"' : '' ?>
                                name="v_ftp_user[<?= $i ?>][v_ftp_user]"
                                id="v_ftp_user[<?= $i ?>][v_ftp_user]"
                                value="<?= htmlentities(trim($v_ftp_user, "'")) ?>">
                            <small class="hint js-ftp-user-hint"></small>
                        </div>
                        <div class="u-pl30 u-mb10">
                            <label for="v_ftp_user[<?= $i ?>][v_ftp_password]" class="form-label">
                                <?= _("Password") ?>
                                <button
                                    type="button"
                                    title="<?= _("Generate") ?>"
                                    class="u-unstyled-button u-ml5 js-ftp-password-generate">
                                    <i class="fas fa-arrows-rotate icon-green"></i>
                                </button>
                            </label>
                            <input type="text"
                                class="form-control js-ftp-user-psw"
                                name="v_ftp_user[<?= $i ?>][v_ftp_password]"
                                id="v_ftp_user[<?= $i ?>][v_ftp_password]"
                                value="<?= htmlentities(trim($v_ftp_password, "'")) ?>">
                        </div>
                        <div class="u-pl30 u-mb10">
                            <label for="v_ftp_user[<?= $i ?>][v_ftp_path]" class="form-label"><?= _("Path") ?></label>
                            <input type="hidden"
                                name="v_ftp_pre_path"
                                value="<?= !empty($v_ftp_pre_path) ? htmlentities(trim($v_ftp_pre_path, "'")) : '/' ?>">
                            <input type="hidden"
                                name="v_ftp_user[<?= $i ?>][v_ftp_path_prev]"
                                value="<?= $v_ftp_path_prev ?>">
                            <input type="text"
                                class="form-control js-ftp-path"
                                name="v_ftp_user[<?= $i ?>][v_ftp_path]"
                                id="v_ftp_user[<?= $i ?>][v_ftp_path]"
                                value="<?= $v_ftp_path_prev ?>">
                            <span class="hint-prefix"><?= htmlentities(trim($v_ftp_pre_path, "'")) ?></span>
                            <span class="hint js-ftp-path-hint"></span>
                        </div>
                        <?php if ($ftp_user['is_new'] == 1) : ?>
                            <div class="u-pl30 u-mb10">
                                <label for="v_ftp_user[<?= $i ?>][v_ftp_email]" class="form-label">
                                    <?= _("Send FTP credentials to email") ?>
                                </label>
                                <input type="email"
                                    class="form-control js-email-alert-on-psw"
                                    name="v_ftp_user[<?= $i ?>][v_ftp_email]"
                                    id="v_ftp_user[<?= $i ?>][v_ftp_email]"
                                    value="<?= htmlentities(trim($v_ftp_email, "'")) ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="form-link u-mt20 js-add-ftp-account" style="<?php if (empty($v_ftp_user)) {
                                                                                            echo 'display: none;';
                                                                                     } ?>">
                <?= _("Add FTP account") ?>
            </button>
            <!-- Removed stray closing PHP brace inserted by phpcbf -->
        </div>
    </div>

    </form>

</div>

<div class="u-hidden js-ftp-account-template">
    <div class="js-ftp-account js-ftp-account-nrm" name="v_add_domain_ftp">
        <div class="u-mb10">
            <?= _("FTP") ?> #<span class="js-ftp-user-number"></span>
            <button
                type="button"
                class="form-link form-link-danger u-ml5 js-delete-ftp-account">
                <?= _("Delete") ?>
            </button>
            <input type="hidden" class="js-ftp-user-deleted" name="v_ftp_user[%INDEX%][delete]" value="0">
            <input type="hidden" class="js-ftp-user-is-new" name="v_ftp_user[%INDEX%][is_new]" value="1">
        </div>
        <div class="u-pl30 u-mb10">
            <label for="v_ftp_user[%INDEX%][v_ftp_user]" class="form-label">
                <?= _("Username") ?><br>
                <span style="color:#777;">
                    <?= $prefix_msg ?>
                </span>
            </label>
            <?php $ftp_user_name = 'v_ftp_user[%INDEX%][v_ftp_user]';
            $ftp_user_id = 'v_ftp_user[%INDEX%][v_ftp_user]'; ?>
            <input
                type="text"
                class="form-control js-ftp-user"
                name="<?= $ftp_user_name ?>"
                id="<?= $ftp_user_id ?>"
                value="">
            <small class="hint js-ftp-user-hint"></small>
        </div>
        <div class="u-pl30 u-mb10">
            <label for="v_ftp_user[%INDEX%][v_ftp_password]" class="form-label">
                <?= _("Password") ?>
                <button
                    type="button"
                    title="<?= _("Generate") ?>"
                    class="u-unstyled-button u-ml5 js-ftp-password-generate">
                    <i class="fas fa-arrows-rotate icon-green"></i>
                </button>
            </label>
            <input type="text"
                class="form-control js-ftp-user-psw"
                name="v_ftp_user[%INDEX%][v_ftp_password]"
                id="v_ftp_user[%INDEX%][v_ftp_password]">
        </div>
        <div class="u-pl30 u-mb10">
            <label for="v_ftp_user[%INDEX%][v_ftp_path]" class="form-label"><?= _("Path") ?></label>
            <input type="hidden" name="v_ftp_pre_path" value="">
            <?php $ftp_path_name = 'v_ftp_user[%INDEX%][v_ftp_path]';
            $ftp_path_id = 'v_ftp_user[%INDEX%][v_ftp_path]'; ?>
            <input
                type="text"
                class="form-control js-ftp-path"
                name="<?= $ftp_path_name ?>"
                id="<?= $ftp_path_id ?>"
                value="">
            <span class="hint-prefix"><?= htmlentities(trim($v_ftp_pre_path_new_user, "'")) ?></span>
            <span class="hint js-ftp-path-hint"></span>
        </div>
        <div class="u-pl30 u-mb10">
            <label
                for="v_ftp_user[%INDEX%][v_ftp_email]"
                class="form-label">
                <?= _("Send FTP credentials to email") ?>
            </label>
            <input type="email"
                class="form-control js-email-alert-on-psw"
                name="v_ftp_user[%INDEX%][v_ftp_email]"
                id="v_ftp_user[%INDEX%][v_ftp_email]"
                value="">
        </div>
    </div>
</div>
