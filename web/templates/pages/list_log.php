<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <?php
            $look = $_SESSION['look'] ?? '';
            $userContext = $_SESSION['userContext'] ?? '';
            $req_user = isset($_GET['user']) ? htmlentities($_GET['user']) : '';
            if ($userContext === 'admin' && $look === 'admin') {
                $back_href = '/list/user/';
            } elseif ($userContext === 'admin' && $req_user === 'system') {
                $back_href = '/list/server/';
            } else {
                $is_admin_look_non_admin = (
                    $userContext === 'admin'
                    && $look !== ''
                    && (isset($_GET['user']) ? $_GET['user'] : '') !== 'admin'
                );

                if ($is_admin_look_non_admin) {
                    $back_href = sprintf(
                        '/edit/user/?user=%s&token=%s',
                        htmlentities($_SESSION['look']),
                        $_SESSION['token']
                    );
                } else {
                    $back_href = sprintf(
                        '/edit/user/?user=%s&token=%s',
                        htmlentities($_SESSION['user']),
                        $_SESSION['token']
                    );
                }
            }
            ?>
            <a href="<?= $back_href ?>" class="button button-secondary button-back js-button-back">
                <i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
            </a>
            <?php
            $show_login_history = false;
            $login_history_href = '/list/log/auth/';
            if ($_SESSION['DEMO_MODE'] != 'yes') {
                $is_admin_user_for_history = (
                    $_SESSION['userContext'] === 'admin'
                    && (isset($_GET['user']) && htmlentities($_GET['user']) !== 'admin')
                );

                if ($is_admin_user_for_history) {
                    if (isset($_GET['user']) && $_GET['user'] != '' && htmlentities($_GET['user']) !== 'system') {
                        $login_history_href = sprintf(
                            '/list/log/auth/?user=%s&token=%s',
                            htmlentities($_GET['user']),
                            $_SESSION['token']
                        );
                    } else {
                        $login_history_href = '/list/log/auth/';
                    }
                    $show_login_history = true;
                } elseif ($_SESSION['userContext'] === 'user') {
                    $show_login_history = true;
                    $login_history_href = '/list/log/auth/';
                }
            }
            ?>
            <?php if ($show_login_history) {
                $login_history_title = _("Login History");
                ?>
                <a
                    href="<?= $login_history_href ?>"
                    class="button button-secondary button-back js-button-back"
                    title="<?= $login_history_title ?>">
                    <i class="fas fa-binoculars icon-green"></i><?= $login_history_title ?>
                </a>
            <?php } ?>
        </div>
        <div class="toolbar-buttons">
            <a
                href="javascript:location.reload();"
                class="button button-secondary">
                <i class="fas fa-arrow-rotate-right icon-green"></i><?= _("Refresh") ?>
            </a>
            <?php $hide_delete_buttons = (
                $_SESSION['userContext'] === 'admin'
                && $_SESSION['look'] === 'admin'
                && $_SESSION['POLICY_SYSTEM_PROTECTED_ADMIN'] === 'yes'
            ); ?>
            <?php if ($hide_delete_buttons) { ?>
                <!-- Hide delete buttons-->
            <?php } else { ?>
                <?php $can_delete_logs = (
                    $_SESSION['userContext'] === 'admin'
                    || (
                        $_SESSION['userContext'] === 'user'
                        && $_SESSION['POLICY_USER_DELETE_LOGS'] !== 'no'
                    )
                ); ?>
                <?php if ($can_delete_logs) {
                    if ($_SESSION['userContext'] === 'admin' && isset($_GET['user'])) {
                        $delete_href = sprintf(
                            '/delete/log/?user=%s&token=%s',
                            htmlentities($_GET['user']),
                            $_SESSION['token']
                        );
                    } else {
                        $delete_href = sprintf(
                            '/delete/log/?token=%s',
                            $_SESSION['token']
                        );
                    }
                    ?>
                    <?php $delete_confirm = _("Are you sure you want to delete the logs?"); ?>
                    <a
                        class="button button-secondary button-danger data-controls js-confirm-action"
                        href="<?= $delete_href ?>"
                        data-confirm-title="<?= _("Delete") ?>"
                        data-confirm-message="<?= $delete_confirm ?>">
                        <i class="fas fa-circle-xmark icon-red"></i>
                        <?= _("Delete") ?>
                    </a>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
<!-- End toolbar -->

<div class="container">

    <h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Logs") ?></h1>

    <div class="units-table js-units-container">
        <div class="units-table-header">
            <div class="units-table-cell"></div>
            <div class="units-table-cell"><?= _("Date") ?></div>
            <div class="units-table-cell"><?= _("Time") ?></div>
            <div class="units-table-cell"><?= _("Category") ?></div>
            <div class="units-table-cell"><?= _("Message") ?></div>
        </div>

        <!-- Begin log history entry loop -->
        <?php
        foreach ($data as $key => $value) {
            ++$i;

            if ($data[$key]['LEVEL'] === 'Info') {
                $level_icon = 'fa-info-circle icon-blue';
                $level_title = _('Information');
            }
            if ($data[$key]['LEVEL'] === 'Warning') {
                $level_icon = 'fa-triangle-exclamation icon-orange';
                $level_title = _('Warning');
            }
            if ($data[$key]['LEVEL'] === 'Error') {
                $level_icon = 'fa-circle-xmark icon-red';
                $level_title = _('Error');
            }
            ?>
            <div class="units-table-row js-unit">
                <div class="units-table-cell u-text-center-desktop">
                    <i class="fas <?= $level_icon ?>" title="<?= $level_title ?>"></i>
                </div>
                <div class="units-table-cell units-table-heading-cell u-text-bold">
                    <span class="u-hide-desktop"><?= _("Date") ?>:</span>
                    <time datetime="<?= htmlspecialchars($data[$key]["DATE"]) ?>" class="u-text-no-wrap">
                        <?= translate_date($data[$key]["DATE"]) ?>
                    </time>
                </div>
                <div class="units-table-cell u-text-bold">
                    <span class="u-hide-desktop"><?= _("Time") ?>:</span>
                    <time datetime="<?= htmlspecialchars($data[$key]["TIME"]) ?>">
                        <?= htmlspecialchars($data[$key]["TIME"]) ?>
                    </time>
                </div>
                <div class="units-table-cell u-text-bold">
                    <span class="u-hide-desktop"><?= _("Category") ?>:</span>
                    <span class="u-text-no-wrap">
                        <?= htmlspecialchars($data[$key]["CATEGORY"]) ?>
                    </span>
                </div>
                <div class="units-table-cell">
                    <span class="u-hide-desktop u-text-bold"><?= _("Message") ?>:</span>
                    <?= htmlspecialchars($data[$key]["MESSAGE"], ENT_QUOTES) ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="units-table-footer">
        <p>
            <?php printf(ngettext("%d log record", "%d log records", $i), $i); ?>
        </p>
    </div>

</div>
