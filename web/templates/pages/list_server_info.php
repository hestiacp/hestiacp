<!doctype html>
<html lang="en">

<head>
    <!-- Load necessary CSS and JavaScript from source -->
    <?php require $_SERVER["HESTIA"] . "/web/templates/includes/title.php"; ?>
    <?php require $_SERVER["HESTIA"] . "/web/templates/includes/css.php"; ?>
    <?php require $_SERVER["HESTIA"] . "/web/templates/includes/js.php"; ?>
</head>

<body class="page-server-info">

    <div class="app">

        <header class="app-header">
            <div class="top-bar">
                <div class="container top-bar-inner">
                    <div class="top-bar-left">
                        <a href="/" class="top-bar-logo" title="<?= _("Hestia Control Panel") ?>">
                            <img
                                src="/images/logo-header.svg"
                                alt="<?= _("Hestia Control Panel") ?>"
                                width="54"
                                height="29">
                        </a>
                    </div>
                    <div class="top-bar-right">
                        <nav x-data="{ open: false }" class="top-bar-menu">
                            <?php
                            $open_menu_open = _("Open menu");
                            $open_menu_close = _("Close menu");
                            ?>
                            <button
                                type="button"
                                class="top-bar-menu-link u-hide-tablet"
                                x-on:click="open = !open">
                                <i class="fas fa-bars"></i>
                                <span
                                    class="u-hidden"
                                    x-text="open ? '<?= $open_menu_close ?>' : '<?= $open_menu_open ?>'">
                                    <?= $open_menu_open ?>
                                </span>
                            </button>
                            <div
                                x-cloak
                                x-show="open"
                                x-on:click.outside="open = false"
                                class="top-bar-menu-panel">
                                <ul class="top-bar-menu-list">
                                    <?php
                                    $cpu_active = isset($_GET['cpu']) ? 'active' : '';
                                    $mem_active = isset($_GET['mem']) ? 'active' : '';
                                    ?>
                                    <li class="top-bar-menu-item">
                                        <a class="top-bar-menu-link" href="/list/rrd/" title="<?= _("Back") ?>">
                                            <i class="fas fa-circle-left"></i>
                                            <span class="top-bar-menu-link-label"><?= _("Back") ?></span>
                                        </a>
                                    </li>
                                    <li class="top-bar-menu-item">
                                        <a class="top-bar-menu-link <?= $cpu_active ?>" href="/list/server/?cpu">
                                            <i class="fas fa-microchip"></i>
                                            <span class="top-bar-menu-link-label"><?= _("CPU") ?></span>
                                        </a>
                                    </li>
                                    <li class="top-bar-menu-item">
                                        <a class="top-bar-menu-link <?= $mem_active ?>" href="/list/server/?mem">
                                            <i class="fas fa-memory"></i>
                                            <span class="top-bar-menu-link-label"><?= _("RAM") ?></span>
                                        </a>
                                    </li>
                                    <?php
                                    $disk_active = isset($_GET['disk']) ? 'active' : '';
                                    $net_active = isset($_GET['net']) ? 'active' : '';
                                    ?>
                                    <li class="top-bar-menu-item">
                                        <a class="top-bar-menu-link <?= $disk_active ?>" href="/list/server/?disk">
                                            <i class="fas fa-hard-drive"></i>
                                            <span class="top-bar-menu-link-label"><?= _("Disk") ?></span>
                                        </a>
                                    </li>
                                    <li class="top-bar-menu-item">
                                        <a class="top-bar-menu-link <?= $net_active ?>" href="/list/server/?net">
                                            <i class="fas fa-hard-drive"></i>
                                            <span class="top-bar-menu-link-label"><?= _("Network") ?></span>
                                        </a>
                                    </li>
                                    <?php
                                    $web_active = isset($_GET['web']) ? 'active' : '';
                                    $dns_active = isset($_GET['dns']) ? 'active' : '';
                                    $mail_active = isset($_GET['mail']) ? 'active' : '';
                                    $db_active = isset($_GET['db']) ? 'active' : '';
                                    ?>
                                    <?php
                                    if ((isset($_SESSION['WEB_SYSTEM'])) && (!empty($_SESSION['WEB_SYSTEM']))) {
                                        ?>
                                        <li class="top-bar-menu-item">
                                            <a
                                                class="top-bar-menu-link <?= $web_active ?>"
                                                href="/list/server/?web">
                                                <i class="fas fa-earth-europe"></i>
                                                <span class="top-bar-menu-link-label"><?= _("Web") ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <?php
                                    if ((isset($_SESSION['DNS_SYSTEM'])) && (!empty($_SESSION['DNS_SYSTEM']))) {
                                        ?>
                                        <li class="top-bar-menu-item">
                                            <a
                                                class="top-bar-menu-link <?= $dns_active ?>"
                                                href="/list/server/?dns">
                                                <i class="fas fa-book-atlas"></i>
                                                <span class="top-bar-menu-link-label"><?= _("DNS") ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <?php
                                    if ((isset($_SESSION['MAIL_SYSTEM'])) && (!empty($_SESSION['MAIL_SYSTEM']))) {
                                        ?>
                                        <li class="top-bar-menu-item">
                                            <a
                                                class="top-bar-menu-link <?= $mail_active ?>"
                                                href="/list/server/?mail">
                                                <i class="fas fa-envelopes-bulk"></i>
                                                <span class="top-bar-menu-link-label"><?= _("Mail") ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <?php if ((isset($_SESSION['DB_SYSTEM'])) && (!empty($_SESSION['DB_SYSTEM']))) { ?>
                                        <li class="top-bar-menu-item">
                                            <a
                                                class="top-bar-menu-link <?= $db_active ?>"
                                                href="/list/server/?db">
                                                <i class="fas fa-database"></i>
                                                <span class="top-bar-menu-link-label"><?= _("DB") ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <?php $refresh_title = _("Refresh"); ?>
                                    <li class="top-bar-menu-item">
                                        <a
                                            class="top-bar-menu-link"
                                            href="javascript:location.reload();"
                                            title="<?= $refresh_title ?>">
                                            <i class="fas fa-arrow-rotate-right"></i>
                                            <span class="u-hidden"><?= $refresh_title ?></span>
                                        </a>
                                    </li>
                                    <?php
                                    $logout_href = '/logout/?token=' . $_SESSION['token'];
                                    $logout_title = _("Log out");
                                    ?>
                                    <li class="top-bar-menu-item">
                                        <a
                                            class="top-bar-menu-link top-bar-menu-link-logout"
                                            href="<?= $logout_href ?>"
                                            title="<?= $logout_title ?>">
                                            <i class="fas fa-right-from-bracket"></i>
                                            <span class="u-hidden"><?= $logout_title ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </header>

        <main class="app-content">

            <div class="logs-container">

                <div class="container">
                    <pre class="console-output u-mt20">
