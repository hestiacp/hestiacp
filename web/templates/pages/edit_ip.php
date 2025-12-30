<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/ip/">
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

    <form
        <?php $show_user_table = empty($v_dedicated) ? 'true' : 'false'; ?>
        x-data="{ showUserTable: <?= $show_user_table ?> }"
        id="main-form"
        name="v_edit_ip"
        method="post">
        <input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
        <input type="hidden" name="save" value="save">

        <div class="form-container">
            <h1 class="u-mb20"><?= _("Edit IP Address") ?></h1>
            <?php show_alert_message($_SESSION); ?>
            <?php
            $v_ip_clean = htmlentities(trim($v_ip, "'"));
            $v_netmask_clean = htmlentities(trim($v_netmask, "'"));
            $v_interface_clean = htmlentities(trim($v_interface, "'"));
            ?>
            <div class="u-mb10">
                <label for="v_ip" class="form-label"><?= _("IP Address") ?></label>
                <input
                    type="text"
                    class="form-control"
                    name="v_ip"
                    id="v_ip"
                    value="<?= $v_ip_clean ?>"
                    disabled>
                <input type="hidden" name="v_ip" value="<?= $v_ip_clean ?>">
            </div>
            <div class="u-mb10">
                <label for="v_netmask" class="form-label"><?= _("Netmask") ?></label>
                <input
                    type="text"
                    class="form-control"
                    name="v_netmask"
                    id="v_netmask"
                    value="<?= $v_netmask_clean ?>"
                    disabled>
            </div>
            <div class="u-mb10">
                <label for="v_interface" class="form-label"><?= _("Interface") ?></label>
                <input
                    type="text"
                    class="form-control"
                    name="v_interface"
                    id="v_interface"
                    value="<?= $v_interface_clean ?>"
                    disabled>
            </div>
            <div class="form-check u-mb10">
                <input x-model="showUserTable" class="form-check-input" type="checkbox" name="v_shared" id="v_shared">
                <label for="v_shared">
                    <?= _("Shared") ?>
                </label>
            </div>
            <div x-cloak x-show="!showUserTable" id="usrtable">
                <div class="u-mb10">
                    <label for="v_owner" class="form-label"><?= _("Assigned User") ?></label>
                    <select class="form-select" name="v_owner" id="v_owner">
                        <?php foreach ($users as $key => $value) {
                            $option_value = htmlentities($value);
                            $selected = (!empty($v_owner) && ($value == $v_owner)) ? ' selected' : '';
                            ?>
                            <option value="<?= $option_value ?>" <?= $selected ?>><?= $option_value ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <?php
            $v_name_clean = htmlentities(trim($v_name, "'"));
            $v_nat_clean = htmlentities(trim($v_nat, "'"));
            ?>
            <div class="u-mb10">
                <label for="v_name" class="form-label">
                    <?= _("Assigned Domain") ?> <span class="optional">(<?= _("Optional") ?>)</span>
                </label>
                <input
                    type="text"
                    class="form-control"
                    name="v_name"
                    id="v_name"
                    value="<?= $v_name_clean ?>">
            </div>
            <div class="u-mb10">
                <label for="v_nat" class="form-label">
                    <?= _("NAT IP Association") ?> <span class="optional">(<?= _("Optional") ?>)</span>
                </label>
                <input
                    type="text"
                    class="form-control"
                    name="v_nat"
                    id="v_nat"
                    value="<?= $v_nat_clean ?>">
            </div>
        </div>

    </form>

</div>
