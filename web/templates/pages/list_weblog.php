<!doctype html>
<html lang="en">

<head>
    <!-- Load necessary CSS and JavaScript from source -->
    <?php require $_SERVER["HESTIA"] . "/web/templates/includes/title.php"; ?>
    <?php require $_SERVER["HESTIA"] . "/web/templates/includes/css.php"; ?>
    <?php require $_SERVER["HESTIA"] . "/web/templates/includes/js.php"; ?>
</head>

<body class="page-weblog">

    <div class="app">

        <header class="app-header">
            <div class="top-bar">
                <div class="container top-bar-inner">
                    <?php
                    $domain_param = isset($_GET['domain']) ? htmlentities($_GET['domain']) : '';
                    $token_param = isset($_SESSION['token']) ? $_SESSION['token'] : '';
                    $access_list_href = sprintf(
                        '/list/web-log/?domain=%s&type=access&token=%s',
                        $domain_param,
                        $token_param
                    );
                    $access_download_href = sprintf(
                        '/download/web-log/?domain=%s&type=access&token=%s',
                        $domain_param,
                        $token_param
                    );
                    $error_list_href = sprintf(
                        '/list/web-log/?domain=%s&type=error&token=%s',
                        $domain_param,
                        $token_param
                    );
                    $error_download_href = sprintf(
                        '/download/web-log/?domain=%s&type=error&token=%s',
                        $domain_param,
                        $token_param
                    );
                    $logout_href = '/logout/?token=' . $token_param;

                    $access_active = (isset($_GET['type']) && $_GET['type'] == 'access') ? 'active' : '';
                    $error_active = (isset($_GET['type']) && $_GET['type'] == 'error') ? 'active' : '';

                    $logo_src = !empty($_SESSION['LOGO_HEADER']) ? $_SESSION['LOGO_HEADER'] : '/images/logo-header.svg';
                    $open_label = _('Open menu');
                    $close_label = _('Close menu');
                    $log_title = sprintf(
                        _('Last 70 lines of %s.%s.log'),
                        htmlentities($_GET['domain']),
                        htmlentities($type)
                    );
                    ?>
                    <div class="top-bar-left">
                        <a href="/" class="top-bar-logo" title="<?= htmlentities($_SESSION['APP_NAME']); ?>">
                            <img
                                src="<?= $logo_src ?>"
                                alt="<?= htmlentities($_SESSION['APP_NAME']); ?>"
                                width="54"
                                height="29">
                        </a>
                    </div>
                    <div class="top-bar-right">
                        <nav x-data="{ open: false }" class="top-bar-menu">
                            <button
                                type="button"
                                class="top-bar-menu-link u-hide-tablet"
                                x-on:click="open = !open">
                                <i class="fas fa-bars"></i>
                                <span
                                    class="u-hidden"
                                    x-text="open ? '<?= $close_label ?>' : '<?= $open_label ?>'">
                                    <?= $open_label ?>
                                </span>
                            </button>
                            <div
                                x-cloak
                                x-show="open"
                                x-on:click.outside="open = false"
                                class="top-bar-menu-panel">
                                <ul class="top-bar-menu-list">
                                    <li class="top-bar-menu-item">
                                        <a
                                            class="top-bar-menu-link"
                                            href="/list/web/"
                                            title="<?= _("Back") ?>">
                                            <i class="fas fa-circle-left"></i>
                                            <span class="top-bar-menu-link-label"><?= _("Back") ?></span>
                                        </a>
                                    </li>
                                    <li class="top-bar-menu-item">
                                        <a
                                            class="top-bar-menu-link <?= $access_active ?>"
                                            href="<?= $access_list_href ?>"
                                            title="<?= _("View Logs") ?>">
                                            <i class="fas fa-eye"></i>
                                            <span class="top-bar-menu-link-label"><?= _("View Logs") ?></span>
                                        </a>
                                    </li>
                                    <li class="top-bar-menu-item">
                                        <a
                                            class="top-bar-menu-link <?= $access_active ?>"
                                            href="<?= $access_download_href ?>"
                                            title="<?= _("Download") ?>">
                                            <i class="fas fa-download"></i>
                                            <span class="u-hidden"><?= _("Download") ?></span>
                                        </a>
                                    </li>
                                    <li class="top-bar-menu-item">
                                        <a
                                            class="top-bar-menu-link <?= $error_active ?>"
                                            href="<?= $error_list_href ?>"
                                            title="<?= _("Error Log") ?>">
                                            <i class="fas fa-circle-exclamation"></i>
                                            <span class="top-bar-menu-link-label"><?= _("Error Log") ?></span>
                                        </a>
                                    </li>
                                    <li class="top-bar-menu-item">
                                        <a
                                            class="top-bar-menu-link <?= $error_active ?>"
                                            href="<?= $error_download_href ?>"
                                            title="<?= _("Download") ?>">
                                            <i class="fas fa-download"></i>
                                            <span class="u-hidden"><?= _("Download") ?></span>
                                        </a>
                                    </li>
                                    <li class="top-bar-menu-item">
                                        <a
                                            class="top-bar-menu-link"
                                            href="javascript:location.reload();"
                                            title="<?= _("Refresh") ?>">
                                            <i class="fas fa-arrow-rotate-right"></i>
                                            <span class="u-hidden"><?= _("Refresh") ?></span>
                                        </a>
                                    </li>
                                    <li class="top-bar-menu-item">
                                        <a
                                            class="top-bar-menu-link"
                                            href="/list/user/"
                                            title="<?= htmlentities($user) ?>">
                                            <i class="fas fa-circle-user"></i>
                                            <span class="u-hidden"><?= htmlentities($user) ?></span>
                                        </a>
                                    </li>
                                    <li class="top-bar-menu-item">
                                        <a
                                            class="top-bar-menu-link top-bar-menu-link-logout"
                                            href="<?= $logout_href ?>"
                                            title="<?= _("Log out") ?>">

                                            <i class="fas fa-right-from-bracket"></i>
                                            <span class="u-hidden"><?= _("Log out") ?></span>
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

                <p class="u-text-center u-mb20">
                    <?= $log_title ?>
                </p>
                <pre class="console-output">
