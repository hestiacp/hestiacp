<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/dns/">
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
        name="v_add_dns"
        method="post">
        <input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
        <input type="hidden" name="ok" value="Add">

        <div class="form-container">
            <h1 class="u-mb20"><?= _("Add DNS Zone") ?></h1>
            <?php show_alert_message($_SESSION); ?>
            <?php if ($_SESSION["role"] == "admin" && $accept !== "true") { ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation"></i>
                    <?php
                    $user_link_open = '<a href="/add/user/">';
                    $user_link_close = '</a>';
                    $advise_template = _(
                        "It is strongly advised to {create a standard user account} before adding %s to the server "
                    ) . "due to the increased privileges the admin account possesses "
                        . "and potential security risks.";
                    $advise_msg = sprintf($advise_template, _('a dns domain'));
                    ?>
                    <p><?= htmlify_trans($advise_msg, $user_link_close, $user_link_open) ?></p>
                </div>
            <?php } ?>
            <?php if ($_SESSION["role"] == "admin" && empty($accept)) { ?>
                <div class="u-side-by-side u-mt20">
                    <a href="/add/user/" class="button u-width-full u-mr10"><?= _("Add User") ?></a>
                    <a
                        href="/add/dns/?accept=true"
                        class="button button-danger u-width-full u-ml10">
                        <?= _("Continue") ?>
                    </a>
                </div>
            <?php } ?>
            <?php if (($_SESSION["role"] == "admin" && $accept === "true") || $_SESSION["role"] !== "admin") { ?>
                <div class="u-mb10">
                    <label for="v_domain" class="form-label"><?= _("Domain") ?></label>
                    <?php $v_domain_value = htmlentities(trim($v_domain, "'")); ?>
                    <input
                        type="text"
                        class="form-control"
                        name="v_domain"
                        id="v_domain"
                        value="<?= $v_domain_value ?>"
                        required>
                </div>
                <div class="u-mb10">
                    <label for="v_ip" class="form-label"><?= _("IP Address") ?></label>
                    <div class="u-pos-relative">
                        <select class="form-select" tabindex="-1" onchange="this.nextElementSibling.value=this.value">
                            <option value="">clear</option>
                            <?php
                            foreach ($v_ips as $ip => $value) {
                                $display_ip = empty($value['NAT']) ? $ip : "{$value['NAT']}";
                                echo "<option value='{$display_ip}'>" . htmlentities($display_ip) . "</option>\n";
                            }
                            ?>
                        </select>
                        <?php $v_ip_value = htmlentities(trim($v_ip, "'")); ?>
                        <input
                            type="text"
                            class="form-control list-editor"
                            name="v_ip"
                            id="v_ip"
                            value="<?= $v_ip_value ?>">
                    </div>
                </div>
                <?php
                $can_edit_dns_templates = ($_SESSION['userContext'] === 'admin')
                    || ($_SESSION['userContext'] === 'user' && $_SESSION['POLICY_USER_EDIT_DNS_TEMPLATES'] === 'yes');
                ?>
                <?php if ($can_edit_dns_templates) { ?>
                    <?php $can_edit_dns_templates_note = ''; ?>
                    <div class="u-mb10">
                        <label for="v_template" class="form-label">
                            <?php
                            $template_optional = "<span class='optional'>"
                                . strtoupper($_SESSION['DNS_SYSTEM'])
                                . "</span>";
                            $template_label = _("Template") . $template_optional;
                            ?>
                            <?= $template_label ?>
                        </label>
                        <select class="form-select" name="v_template" id="v_template">
                            <?php
                            foreach ($templates as $key => $value) {
                                $opt_value = htmlentities($value);
                                $svalue = "'" . $value . "'";
                                $opt_selected = '';
                                if ((!empty($v_template) && ($value == $v_template)) || ($svalue == $v_template)) {
                                    $opt_selected = ' selected';
                                }
                                printf(
                                    "\t\t\t\t<option value=\"%s\"%s>%s</option>\n",
                                    $opt_value,
                                    $opt_selected,
                                    $opt_value
                                );
                            }
                            ?>
                        </select>
                    </div>
                <?php } ?>
                <div class="u-mb20 u-mt20">
                    <button x-on:click="showAdvanced = !showAdvanced" type="button" class="button button-secondary">
                        <?= _("Advanced Options") ?>
                    </button>
                </div>
                <div x-cloak x-show="showAdvanced" id="advtable">
                    <?php if (
                        $_SESSION["DNS_CLUSTER_SYSTEM"] == "hestia-zone"
                        && $_SESSION["SUPPORT_DNSSEC"] == "yes"
) { ?>
                        <?php $dnssec_checked = ($v_dnssec === 'yes') ? ' checked' : ''; ?>
                        <div class="form-check u-mb10">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="v_dnssec"
                                id="v_dnssec"
                                value="yes"
                                <?= $dnssec_checked ?>>
                            <label for="v_dnssec">
                                <?= _("Enable DNSSEC") ?>
                            </label>
                        </div>
                    <?php } ?>

                    <div class="u-mb10">
                        <label for="v_exp" class="form-label">
                            <?= _("Expiration Date") ?> <span class="optional">(<?= _("YYYY-MM-DD") ?>)</span>
                        </label>
                        <?php $v_exp_value = htmlentities(trim($v_exp, "'")); ?>
                        <input type="text" class="form-control" name="v_exp" id="v_exp" value="<?= $v_exp_value ?>">
                    </div>
                    <div class="u-mb10">
                        <label for="v_ttl" class="form-label"><?= _("TTL") ?></label>
                        <?php $v_ttl_value = htmlentities(trim($v_ttl, "'")); ?>
                        <input type="text" class="form-control" name="v_ttl" id="v_ttl" value="<?= $v_ttl_value ?>">
                    </div>
                    <p class="form-label u-mb10"><?= _("Name Servers") ?></p>
                    <div class="u-mb5">
                        <?php $v_ns1_value = htmlentities(trim($v_ns1, "'")); ?>
                        <input type="text" class="form-control" name="v_ns1" value="<?= $v_ns1_value ?>">
                    </div>
                    <div class="u-mb5">
                        <?php $v_ns2_value = htmlentities(trim($v_ns2, "'")); ?>
                        <input type="text" class="form-control" name="v_ns2" value="<?= $v_ns2_value ?>">
                    </div>
                    <?php require $_SERVER["HESTIA"] . "/web/templates/includes/extra-ns-fields.php"; ?>
                    <button type="button" class="form-link u-mt20 js-add-ns" <?php if ($v_ns8) {
                                                                                    echo 'style="display:none;"';
                                                                             } ?>>
                        <?= _("Add Name Server") ?>
                    </button>
                </div>
            <?php } ?>
        </div>

    </form>

</div>
