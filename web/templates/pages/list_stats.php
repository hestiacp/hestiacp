<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <?php if ($_SESSION["userContext"] === "admin" && $_SESSION["look"] == '') { ?>
                <?php $overall_stats_href = '/list/stats/'; ?>
                <a
                    class="button button-secondary"
                    href="<?= $overall_stats_href ?>">
                    <i class="fas fa-binoculars icon-lightblue"></i><?= _("Overall Statistics") ?>
                </a>
            <?php } ?>
        </div>
        <div class="toolbar-right">
            <?php if ($_SESSION["userContext"] === "admin" && $_SESSION["look"] == '') { ?>
                <form x-data x-bind="BulkEdit" action="/list/stats/" method="get">
                    <input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
                    <select class="form-select" name="user">
                        <option value=""><?= _("Show Per User") ?></option>
                        <?php
                        foreach ($users as $key => $value) {
                            if (($_SESSION['POLICY_SYSTEM_HIDE_ADMIN'] === 'yes') && ($value === 'admin')) {
                                // Hide admin user from statistics list
                            } else {
                                $option_selected = ((!empty($v_user)) && ($value == $_GET['user'])) ? ' selected' : '';
                                printf(
                                    "\t\t\t\t<option value=\"%s\"%s>%s</option>\n",
                                    $value,
                                    $option_selected,
                                    $value
                                );
                            }
                        }
                        ?>
                    </select>
                    <button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            <?php } ?>
            <div class="toolbar-search">
                <form action="/search/" method="get">
                    <input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
                    <?php $search_q = isset($_POST['q']) ? htmlspecialchars($_POST['q']) : ''; ?>
                    <input
                        type="search"
                        class="form-control js-search-input"
                        name="q"
                        value="<?= $search_q ?>"
                        title="<?= _("Search") ?>">
                    <button type="submit" class="toolbar-input-submit" title="<?= _("Search") ?>">
                        <i class="fas fa-magnifying-glass"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End toolbar -->

<div class="container">

    <!-- Begin statistics list item loop -->
    <div class="stats">
        <?php foreach ($data as $key => $value) {
            ++$i; ?>
            <div class="stats-item">

                <div class="stats-item-header">
                    <i class="fas fa-chart-bar icon-dim stats-item-header-icon u-mr10"></i>
                    <h2 class="stats-item-header-title">
                        <?php $date = new DateTime($key);
                        echo _($date->format('M')) . ' ' . $date->format('Y') ?>
                    </h2>
                </div>

                <div class="stats-item-content">

                    <div class="stats-item-summary">
                        <h3 class="stats-item-summary-title">
                            <span class="u-text-bold">
                                <i
                                    class="fas fa-right-left icon-dim icon-large u-mr5"
                                    title="<?= _("Bandwidth") ?>">
                                </i>
                                <?= _("Bandwidth") ?>
                            </span>
                            <?php
                            $u_bw_size    = humanize_usage_size($data[$key]['U_BANDWIDTH']);
                            $u_bw_meas    = humanize_usage_measure($data[$key]['U_BANDWIDTH']);
                            $bw_size      = humanize_usage_size($data[$key]['BANDWIDTH']);
                            $bw_meas      = humanize_usage_measure($data[$key]['BANDWIDTH']);
                            ?>
                            <span class="u-mr10">
                                <span class="u-text-bold"><?= $u_bw_size ?></span>
                                <?= $u_bw_meas ?> / <span class="u-text-bold"><?= $bw_size ?></span>
                                <?= $bw_meas ?>
                            </span>
                        </h3>
                        <ul class="stats-item-summary-list u-mb10">
                            <li class="stats-item-summary-list-item">
                                <span>
                                    <?php
                                    $show_ip_addresses = (
                                        $_SESSION['userContext'] === 'admin'
                                    ) || (
                                        $_SESSION['userContext'] === 'user' && $data[$key]['IP_OWNED'] != '0'
                                    );
                                    ?>
                                    <?php if ($show_ip_addresses) { ?>
                                        <?= _("IP Addresses") ?>:
                                    <?php } ?>
                                </span>
                                <span>
                                    <span class="u-text-bold"><?= $data[$key]["IP_OWNED"] ?></span>
                                    <?= _("IPs") ?>
                                </span>
                            </li>
                        </ul>
                        <h3 class="stats-item-summary-title">
                            <span class="u-text-bold">
                                <i class="fas fa-hard-drive icon-dim icon-large u-mr5" title="Disk"></i>
                                <?= _("Disk") ?>
                            </span>
                            <?php
                            $u_disk_size       = humanize_usage_size($data[$key]['U_DISK']);
                            $u_disk_meas       = humanize_usage_measure($data[$key]['U_DISK']);
                            $disk_quota_size   = humanize_usage_size($data[$key]['DISK_QUOTA']);
                            $disk_quota_meas   = humanize_usage_measure($data[$key]['DISK_QUOTA']);
                            ?>
                            <?php
                            $u_disk_web_size = humanize_usage_size($data[$key]['U_DISK_WEB']);
                            $u_disk_web_meas = humanize_usage_measure($data[$key]['U_DISK_WEB']);
                            $u_disk_db_size = humanize_usage_size($data[$key]['U_DISK_DB']);
                            $u_disk_db_meas = humanize_usage_measure($data[$key]['U_DISK_DB']);
                            $u_disk_mail_size = humanize_usage_size($data[$key]['U_DISK_MAIL']);
                            $u_disk_mail_meas = humanize_usage_measure($data[$key]['U_DISK_MAIL']);
                            $u_disk_dirs_size = humanize_usage_size($data[$key]['U_DISK_DIRS']);
                            $u_disk_dirs_meas = humanize_usage_measure($data[$key]['U_DISK_DIRS']);
                            ?>
                            <span class="u-mr10">
                                <span class="u-text-bold"><?= $u_disk_size ?></span>
                                <?= $u_disk_meas ?> / <span class="u-text-bold"><?= $disk_quota_size ?></span>
                                <?= $disk_quota_meas ?>
                            </span>
                            </span>
                        </h3>
                        <ul class="stats-item-summary-list">
                            <li class="stats-item-summary-list-item">
                                <span>
                                    <?= _("Web") ?>:
                                </span>
                                <span>
                                    <span class="u-text-bold"><?= $u_disk_web_size ?></span>
                                    <?= $u_disk_web_meas ?>
                                </span>
                            </li>
                            <li class="stats-item-summary-list-item u-mb5">
                                <span>
                                    <?= _("Databases") ?>:
                                </span>
                                <span>
                                    <span class="u-text-bold"><?= $u_disk_db_size ?></span>
                                    <?= $u_disk_db_meas ?>
                                </span>
                            </li>
                            <li class="stats-item-summary-list-item">
                                <span>
                                    <?= _("Mail") ?>:
                                </span>
                                <span>
                                    <span class="u-text-bold"><?= $u_disk_mail_size ?></span>
                                    <?= $u_disk_mail_meas ?>
                                </span>
                            </li>
                            <li class="stats-item-summary-list-item">
                                <span>
                                    <?= _("User Directory") ?>:
                                </span>
                                <span>
                                    <span class="u-text-bold"><?= $u_disk_dirs_size ?></span>
                                    <?= $u_disk_dirs_meas ?>
                                </span>
                            </li>
                        </ul>
                    </div>

                    <ul class="stats-item-list">
                        <li class="stats-item-list-item">
                            <span class="stats-item-list-item-label">
                                <?= _("Web Domains") ?>:
                            </span>
                            <span class="stats-item-list-item-value">
                                <?= $data[$key]["U_WEB_DOMAINS"] ?>
                            </span>
                        </li>
                        <li class="stats-item-list-item">
                            <span class="stats-item-list-item-label">
                                <?= _("Mail Domains") ?>:
                            </span>
                            <span class="stats-item-list-item-value">
                                <?= $data[$key]["U_MAIL_DOMAINS"] ?>
                            </span>
                        </li>
                        <li class="stats-item-list-item">
                            <span class="stats-item-list-item-label">
                                <?= _("SSL Domains") ?>:
                            </span>
                            <span class="stats-item-list-item-value">
                                <?= $data[$key]["U_WEB_SSL"] ?>
                            </span>
                        </li>
                        <li class="stats-item-list-item">
                            <span class="stats-item-list-item-label">
                                <?= _("Mail Accounts") ?>:
                            </span>
                            <span class="stats-item-list-item-value">
                                <?= $data[$key]["U_MAIL_ACCOUNTS"] ?>
                            </span>
                        </li>
                        <li class="stats-item-list-item">
                            <span class="stats-item-list-item-label">
                                <?= _("Web Aliases") ?>:
                            </span>
                            <span class="stats-item-list-item-value">
                                <?= $data[$key]["U_WEB_ALIASES"] ?>
                            </span>
                        </li>
                        <li class="stats-item-list-item">
                            <span class="stats-item-list-item-label">
                                <?= _("Databases") ?>:
                            </span>
                            <span class="stats-item-list-item-value">
                                <?= $data[$key]["U_DATABASES"] ?>
                            </span>
                        </li>
                        <li class="stats-item-list-item">
                            <span class="stats-item-list-item-label">
                                <?= _("DNS Zones") ?>:
                            </span>
                            <span class="stats-item-list-item-value">
                                <?= $data[$key]["U_DNS_DOMAINS"] ?>
                            </span>
                        </li>
                        <li class="stats-item-list-item">
                            <span class="stats-item-list-item-label">
                                <?= _("Cron Jobs") ?>:
                            </span>
                            <span class="stats-item-list-item-value">
                                <?= $data[$key]["U_CRON_JOBS"] ?>
                            </span>
                        </li>
                        <li class="stats-item-list-item">
                            <span class="stats-item-list-item-label">
                                <?= _("DNS Records") ?>:
                            </span>
                            <span class="stats-item-list-item-value">
                                <?= $data[$key]["U_DNS_RECORDS"] ?>
                            </span>
                        </li>
                        <li class="stats-item-list-item">
                            <span class="stats-item-list-item-label">
                                <?= _("Backups") ?>:
                            </span>
                            <span class="stats-item-list-item-value">
                                <?= $data[$key]["U_BACKUPS"] ?>
                            </span>
                        </li>
                    </ul>

                </div>

            </div>
        <?php } ?>
    </div>

    <div class="units-table-footer">
        <p>
            <?php printf(ngettext("%d month", "%d months", $i), $i); ?>
        </p>
    </div>

</div>
