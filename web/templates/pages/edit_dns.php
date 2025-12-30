<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/dns/">
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

    <form id="main-form" name="v_edit_dns" method="post" class="<?= $v_status ?>">
        <?php
        $token = $_SESSION['token'];
        $v_domain_clean = htmlentities(trim($v_domain, "'"));
        $v_ip_clean = htmlentities(trim($v_ip, "'"));
        $v_exp_clean = htmlentities(trim($v_exp, "'"));
        $v_soa_clean = htmlentities(trim($v_soa, "'"));
        $v_ttl_clean = htmlentities(trim($v_ttl, "'"));
        $dns_system_label = isset($_SESSION['DNS_SYSTEM']) ? strtoupper($_SESSION['DNS_SYSTEM']) : '';
        $template_label = _("Template") . "<span class='optional'>" . $dns_system_label . "</span>";
        $dnssec_checked = ($v_dnssec === 'yes') ? ' checked' : '';
        $can_edit_templates = (
            $_SESSION['userContext'] === 'admin'
            || (($_SESSION['userContext'] === 'user') && ($_SESSION['POLICY_USER_EDIT_DNS_TEMPLATES'] === 'yes'))
        ) ? true : false;        ?>

        <div class="form-container">
            <h1 class="u-mb20"><?= _("Edit DNS Domain") ?></h1>
            <?php show_alert_message($_SESSION); ?>
            <div class="u-mb10">
                <label for="v_domain" class="form-label"><?= _("Domain") ?></label>
                <input
                    type="text"
                    class="form-control"
                    name="v_domain"
                    id="v_domain"
                    value="<?= $v_domain_clean ?>"
                    disabled
                    required>
                <input type="hidden" name="v_domain" value="<?= $v_domain_clean ?>">
            </div>
            <div class="u-mb10">
                <label for="v_ip" class="form-label"><?= _("IP Address") ?></label>
                <div class="u-pos-relative">
                    <select
                        class="form-select"
                        tabindex="-1"
                        onchange="this.nextElementSibling.value=this.value">
                        <option value="">clear</option>
                        <?php foreach ($v_ips as $ip => $value) {
                            $display_ip = empty($value['NAT']) ? $ip : $value['NAT'];
                            $ip_selected = !empty($v_ip) && ($v_ip == $ip || $v_ip == $display_ip)
                                ? 'selected'
                                : '';
                            ?>
                            <option
                                value="<?= $display_ip ?>"
                                <?= $ip_selected ?>><?= htmlentities($display_ip) ?></option>
                        <?php } ?>
                    </select>
                    <input
                        type="text"
                        class="form-control list-editor"
                        name="v_ip"
                        id="v_ip"
                        value="<?= $v_ip_clean ?>">
                </div>
            </div>
            <?php if ($can_edit_templates) { ?>
                <div class="u-mb10">
                    <label for="v_template" class="form-label">
                        <?= $template_label ?>
                    </label>
                    <select class="form-select" name="v_template" id="v_template">
                        <?php foreach ($templates as $key => $value) {
                            $option_value = htmlentities($value);
                            $svalue = "'" . $value . "'";
                            $selected = !empty($v_template)
                                && ($value == $v_template || $svalue == $v_template)
                                ? ' selected'
                                : '';
                            ?>
                            <option
                                value="<?= $option_value ?>"
                                <?= $selected ?>><?= $option_value ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>
            <?php if ($_SESSION["DNS_CLUSTER_SYSTEM"] == "hestia-zone" && $_SESSION["SUPPORT_DNSSEC"] == "yes") { ?>
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
                    <?= _("Expiration Date") ?><span class="optional">(<?= _("YYYY-MM-DD") ?>)</span>
                </label>
                <input
                    type="text"
                    class="form-control"
                    name="v_exp"
                    id="v_exp"
                    value="<?= $v_exp_clean ?>">
            </div>
            <div class="u-mb10">
                <label for="v_soa" class="form-label"><?= _("SOA") ?></label>
                <input
                    type="text"
                    class="form-control"
                    name="v_soa"
                    id="v_soa"
                    value="<?= $v_soa_clean ?>">
            </div>
            <div class="u-mb10">
                <label for="v_ttl" class="form-label"><?= _("TTL") ?></label>
                <input
                    type="text"
                    class="form-control"
                    name="v_ttl"
                    id="v_ttl"
                    value="<?= $v_ttl_clean ?>">
            </div>
        </div>

    </form>

</div>
