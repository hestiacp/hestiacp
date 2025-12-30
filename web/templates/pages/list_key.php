<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <?php
            $back_token = $_SESSION['token'];
            if (
                $_SESSION["userContext"] === "admin"
                && $_SESSION['look'] !== ''
                && $_GET["user"] !== $_SESSION['ROOT_USER']
            ) {
                $back_user = htmlentities($_SESSION["look"]);
            } else {
                $back_user = htmlentities($_SESSION["user"]);
            }

            $back_href = '/edit/user/?user=' . $back_user . '&token=' . $back_token;
            ?>
            <a
                class="button button-secondary button-back js-button-back"
                href="<?= $back_href ?>">
                <i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
            </a>

            <?php if ($_SESSION["userContext"] === "admin" && isset($_GET["user"]) && $_GET["user"] !== "admin") { ?>
                <?php $add_key_href = '/add/key/?user=' . htmlentities($_GET["user"]); ?>
            <?php } else { ?>
                <?php $add_key_href = '/add/key/'; ?>
            <?php } ?>
            <a
                class="button button-secondary js-button-create"
                href="<?= $add_key_href ?>">
                <i class="fas fa-circle-plus icon-green"></i><?= _("Add SSH Key") ?>
            </a>
        </div>
    </div>
</div>
<!-- End toolbar -->

<div class="container">

    <h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("SSH Keys") ?></h1>

    <div class="units-table js-units-container">
        <div class="units-table-header">
            <div class="units-table-cell"><?= _("SSH ID") ?></div>
            <div class="units-table-cell"></div>
            <div class="units-table-cell"><?= _("SSH Key") ?></div>
        </div>

        <!-- Begin SSH key list item loop -->
        <?php
        $i = 0;
        foreach ($data as $key => $value) {
            ++$i;
            $row_style = ($data[$key]['ID'] === 'filemanager.ssh.key') ? 'display: none;' : '';
            $delete_href = (($_SESSION["userContext"] === "admin") && isset($_GET["user"]) && $_GET["user"] !== "admin")
                ? "/delete/key/?user=" . htmlentities($_GET["user"]) . "&key=" . $key . "&token=" . $_SESSION["token"]
                : "/delete/key/?key=" . $key . "&token=" . $_SESSION["token"];
            $delete_confirm_message = sprintf(_("Are you sure you want to delete SSH key %s?"), $key);
            ?>
            <div class="units-table-row js-unit" style="<?= $row_style ?>">
                <div class="units-table-cell units-table-heading-cell u-text-bold">
                    <span class="u-hide-desktop"><?= _("SSH ID") ?>:</span>
                    <span class="u-text-break">
                        <?= htmlspecialchars($data[$key]["ID"]) ?>
                    </span>
                </div>
                <div class="units-table-cell">
                    <ul class="units-table-row-actions">
                        <li class="units-table-row-action shortcut-delete" data-key-action="js">
                            <a
                                class="units-table-row-action-link data-controls js-confirm-action"
                                href="<?= $delete_href ?>"
                                title="<?= _("Delete") ?>"
                                data-confirm-title="<?= _("Delete") ?>"
                                data-confirm-message="<?= $delete_confirm_message ?>">
                                <i class="fas fa-trash icon-red"></i>
                                <span class="u-hide-desktop"><?= _("Delete") ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="units-table-cell u-text-bold">
                    <span class="u-hide-desktop"><?= _("SSH Key") ?>:</span>
                    <span class="u-text-break">
                        <?= htmlspecialchars($data[$key]["KEY"], ENT_QUOTES) ?>
                    </span>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="units-table-footer">
        <p>
            <?php printf(ngettext("%d SSH key", "%d SSH keys", $i), $i); ?>
        </p>
    </div>

</div>
