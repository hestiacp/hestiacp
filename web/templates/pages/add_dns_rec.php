<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <?php $back_href = '/list/dns/?domain=' . $v_domain_clean . '&token=' . $token; ?>
            <a class="button button-secondary button-back js-button-back" href="<?= $back_href ?>">
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

    <form id="main-form" name="v_add_dns_rec" method="post">
        <?php
        $token = $_SESSION['token'];
        $v_domain_clean = htmlentities(trim($v_domain, "'"));
        $v_rec_clean = htmlentities(trim($v_rec, "'"));
        $v_val_clean = htmlentities(trim($v_val, "'"));
        $v_priority_clean = htmlentities(trim($v_priority, "'"));
        $v_ttl_clean = htmlentities(trim($v_ttl, "'"));
        ?>
        <input type="hidden" name="token" value="<?= $token ?>">
        <input type="hidden" name="ok_rec" value="add">

        <div class="form-container">
            <h1 class="u-mb20"><?= _("Add DNS Record") ?></h1>
            <?php show_alert_message($_SESSION); ?>
            <div class="u-mb10">
                <label for="v_domain" class="form-label"><?= _("Domain") ?></label>
                <input
                    type="text"
                    class="form-control js-dns-record-domain"
                    name="v_domain"
                    id="v_domain"
                    value="<?= $v_domain_clean ?>"
                    disabled>
                <input type="hidden" name="v_domain" value="<?= $v_domain_clean ?>">
            </div>
            <div class="u-mb10">
                <label for="v_rec" class="form-label"><?= _("Record") ?></label>
                <input
                    type="text"
                    class="form-control js-dns-record-input"
                    name="v_rec"
                    id="v_rec"
                    value="<?= $v_rec_clean ?>">
                <small class="hint"></small>
            </div>
            <div class="u-mb10">
                <label for="v_type" class="form-label"><?= _("Type") ?></label>
                <?php
                $dns_types = [
                    'A',
                    'AAAA',
                    'CAA',
                    'CNAME',
                    'DNSKEY',
                    'DS',
                    'IPSECKEY',
                    'KEY',
                    'MX',
                    'NS',
                    'PTR',
                    'SPF',
                    'SRV',
                    'TLSA',
                    'TXT',
                ];
                ?>
                <select class="form-select" name="v_type" id="v_type">
                    <?php foreach ($dns_types as $dt) { ?>
                        <option
                            value="<?= $dt ?>"
                            <?= $v_type === $dt ? 'selected' : '' ?>>
                            <?= $dt ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="u-mb10">
                <label for="v_val" class="form-label"><?= _("IP or Value") ?></label>
                <div class="u-pos-relative">
                    <select
                        class="form-select"
                        tabindex="-1"
                        onchange="this.nextElementSibling.value=this.value">
                        <?php
                        foreach ($v_ips as $ip => $value) {
                            $display_ip = empty($value['NAT']) ? $ip : $value['NAT'];
                            ?>
                            <option
                                value="<?= $display_ip ?>">
                                <?= htmlentities($display_ip) ?>
                            </option>
                        <?php } ?>
                    </select>
                    <input
                        type="text"
                        class="form-control list-editor"
                        name="v_val"
                        id="v_val"
                        value="<?= $v_val_clean ?>"
                        required>
                </div>
            </div>
            <div class="u-mb10">
                <label for="v_priority" class="form-label">
                    <?= _("Priority") ?> <span class="optional">(<?= _("Optional") ?>)</span>
                </label>
                <input
                    type="text"
                    class="form-control"
                    name="v_priority"
                    id="v_priority"
                    value="<?= $v_priority_clean ?>">
            </div>
            <div class="u-mb10">
                <label for="v_ttl" class="form-label">
                    <?= _("TTL") ?> <span class="optional">(<?= _("Optional") ?>)</span>
                </label>
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
