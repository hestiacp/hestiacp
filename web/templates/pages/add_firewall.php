<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/firewall/">
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

    <form id="main-form" name="v_add_ip" method="post">
        <input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
        <input type="hidden" name="ok" value="Add">

        <div class="form-container">
            <h1 class="u-mb20"><?= _("Add Firewall Rule") ?></h1>
            <?php show_alert_message($_SESSION); ?>
            <div class="u-mb10">
                <label for="v_action" class="form-label"><?= _("Action") ?></label>
                <?php
                $v_action_trim = trim($v_action, "'");
                $selected_drop = ($v_action_trim === 'DROP') ? ' selected' : '';
                $selected_accept = ($v_action_trim === 'ACCEPT') ? ' selected' : '';
                ?>
                <select class="form-select" name="v_action" id="v_action">
                    <option value="DROP" <?= $selected_drop ?>><?= _("DROP") ?></option>
                    <option value="ACCEPT" <?= $selected_accept ?>><?= _("ACCEPT") ?></option>
                </select>
            </div>
            <div class="u-mb10">
                <label for="v_protocol" class="form-label"><?= _("Protocol") ?></label>
                <?php
                $v_protocol_trim = trim($v_protocol, "'");
                $selected_tcp = ($v_protocol_trim === 'TCP') ? 'selected' : '';
                $selected_udp = ($v_protocol_trim === 'UDP') ? 'selected' : '';
                $selected_icmp = ($v_protocol_trim === 'ICMP') ? 'selected' : '';
                ?>
                <select
                    class="form-select"
                    name="v_protocol"
                    id="v_protocol">
                    <option value="TCP" <?= $selected_tcp ?>>
                        TCP
                    </option>
                    <option value="UDP" <?= $selected_udp ?>>
                        UDP
                    </option>
                    <option value="ICMP" <?= $selected_icmp ?>>
                        ICMP
                    </option>
                </select>
            </div>
            <div class="u-mb10">
                <label for="v_port" class="form-label">
                    <?= _("Port") ?> <span class="optional">(<?= _("Ranges and lists are acceptable") ?>)</span>
                </label>
                <?php $v_port_html = htmlentities(trim($v_port, "'")); ?>
                <?php $ports_placeholder = _("All ports: 0, Range: 80-82, List: 80,443,8080,8443"); ?>
                <input
                    type="text"
                    class="form-control"
                    name="v_port"
                    id="v_port"
                    value="<?= $v_port_html ?>"
                    placeholder="<?= $ports_placeholder ?>">
            </div>
            <div class="u-mb10">
                <label for="v_ip" class="form-label">
                    <?= _("IP Address / IPset IP List") ?>
                    <span class="optional">(<?= _("Support CIDR format") ?>)</span>
                </label>
                <div class="u-pos-relative">
                    <select
                        class="form-select js-ip-list-select"
                        tabindex="-1"
                        onchange="this.nextElementSibling.value=this.value"
                        <?php $ipset_lists_attr = htmlspecialchars($ipset_lists_json, ENT_QUOTES, 'UTF-8'); ?>
                        data-ipset-lists="<?= $ipset_lists_attr ?>">
                        <option value=""><?= _("Clear") ?></option>
                    </select>
                    <?php $v_ip_html = htmlentities(trim($v_ip, "'")); ?>
                    <input
                        type="text"
                        class="form-control list-editor"
                        name="v_ip"
                        id="v_ip"
                        value="<?= $v_ip_html ?>">
                </div>
            </div>
            <div class="u-mb10">
                <label for="v_comment" class="form-label">
                    <?= _("Comment") ?> <span class="optional">(<?= _("Optional") ?>)</span>
                </label>
                <?php $v_comment_html = htmlentities(trim($v_comment, "'")); ?>
                <input
                    type="text"
                    class="form-control"
                    name="v_comment"
                    id="v_comment"
                    maxlength="255"
                    value="<?= $v_comment_html ?>">
            </div>
        </div>

    </form>

</div>
