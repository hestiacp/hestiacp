<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a class="button button-secondary button-back js-button-back" href="/list/server/">
                <i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
            </a>
            <a href="/edit/server/php/" class="button button-secondary">
                <i class="fas fa-pencil icon-orange"></i><?= _("Configure") ?> PHP
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

    <form id="main-form" name="v_configure_server" method="post">
        <input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
        <input type="hidden" name="save" value="save">

        <?php
        $worker_processes_val = htmlentities($v_worker_processes);
        $worker_connections_val = htmlentities($v_worker_connections);
        $client_max_body_size_val = htmlentities($v_client_max_body_size);
        $send_timeout_val = htmlentities($v_send_timeout);
        $proxy_connect_timeout_val = htmlentities($v_proxy_connect_timeout);
        $proxy_send_timeout_val = htmlentities($v_proxy_send_timeout);
        $proxy_read_timeout_val = htmlentities($v_proxy_read_timeout);
        $gzip_val = htmlentities($v_gzip);
        $gzip_comp_level_val = htmlentities($v_gzip_comp_level);
        $charset_val = htmlentities($v_charset);
        ?>

        <div class="form-container">
            <h1 class="u-mb20"><?= _("Configure Server") ?>: <?= $v_service_name ?></h1>
            <?php show_alert_message($_SESSION); ?>
            <div class="js-basic-options">
                <div class="u-mb10">
                    <label for="v_worker_processes" class="form-label">worker_processes</label>
                    <input
                        type="text"
                        class="form-control"
                        data-regexp="worker_processes"
                        data-prev-value="<?= $worker_processes_val ?>"
                        name="v_worker_processes"
                        id="v_worker_processes"
                        value="<?= $worker_processes_val ?>">
                </div>
                <div class="u-mb10">
                    <label for="v_worker_connections" class="form-label">worker_connections</label>
                    <input
                        type="text"
                        class="form-control"
                        data-regexp="worker_connections"
                        data-prev-value="<?= $worker_connections_val ?>"
                        name="v_worker_connections"
                        id="v_worker_connections"
                        value="<?= $worker_connections_val ?>">
                </div>
                <div class="u-mb10">
                    <label for="v_client_max_body_size" class="form-label">client_max_body_size</label>
                    <input
                        type="text"
                        class="form-control"
                        data-regexp="client_max_body_size"
                        data-prev-value="<?= $client_max_body_size_val ?>"
                        name="v_client_max_body_size"
                        id="v_client_max_body_size"
                        value="<?= $client_max_body_size_val ?>">
                </div>
                <div class="u-mb10">
                    <label for="v_send_timeout" class="form-label">send_timeout</label>
                    <input
                        type="text"
                        class="form-control"
                        data-regexp="send_timeout"
                        data-prev-value="<?= $send_timeout_val ?>"
                        name="v_send_timeout"
                        id="v_send_timeout"
                        value="<?= $send_timeout_val ?>">
                </div>
                <div class="u-mb10">
                    <label for="v_proxy_connect_timeout" class="form-label">proxy_connect_timeout</label>
                    <input
                        type="text"
                        class="form-control"
                        data-regexp="proxy_connect_timeout"
                        data-prev-value="<?= $proxy_connect_timeout_val ?>"
                        name="v_proxy_connect_timeout"
                        id="v_proxy_connect_timeout"
                        value="<?= $proxy_connect_timeout_val ?>">
                </div>
                <div class="u-mb10">
                    <label for="v_proxy_send_timeout" class="form-label">proxy_send_timeout</label>
                    <input
                        type="text"
                        class="form-control"
                        data-regexp="proxy_send_timeout"
                        data-prev-value="<?= $proxy_send_timeout_val ?>"
                        name="v_proxy_send_timeout"
                        id="v_proxy_send_timeout"
                        value="<?= $proxy_send_timeout_val ?>">
                </div>
                <div class="u-mb10">
                    <label for="v_proxy_read_timeout" class="form-label">proxy_read_timeout</label>
                    <input
                        type="text"
                        class="form-control"
                        data-regexp="proxy_read_timeout"
                        data-prev-value="<?= $proxy_read_timeout_val ?>"
                        name="v_proxy_read_timeout"
                        id="v_proxy_read_timeout"
                        value="<?= $proxy_read_timeout_val ?>">
                </div>
                <div class="u-mb10">
                    <label for="v_gzip" class="form-label">gzip</label>
                    <input
                        type="text"
                        class="form-control"
                        data-regexp="gzip"
                        data-prev-value="<?= $gzip_val ?>"
                        name="v_gzip"
                        id="v_gzip"
                        value="<?= $gzip_val ?>">
                </div>
                <div class="u-mb10">
                    <label for="v_gzip_comp_level" class="form-label">gzip_comp_level</label>
                    <input
                        type="text"
                        class="form-control"
                        data-regexp="gzip_comp_level"
                        data-prev-value="<?= $gzip_comp_level_val ?>"
                        name="v_gzip_comp_level"
                        id="v_gzip_comp_level"
                        value="<?= $gzip_comp_level_val ?>">
                </div>
                <div class="u-mb20">
                    <label for="v_charset" class="form-label">charset</label>
                    <input
                        type="text"
                        class="form-control"
                        data-regexp="charset"
                        data-prev-value="<?= $charset_val ?>"
                        name="v_charset"
                        id="v_charset"
                        value="<?= $charset_val ?>">
                </div>
                <div class="u-mb20">
                    <button type="button" class="button button-secondary js-toggle-options">
                        <?= _("Advanced Options") ?>
                    </button>
                </div>
            </div>
            <?php $v_adv_class = empty($v_adv) ? 'u-hidden' : ''; ?>
            <div class="js-advanced-options <?= $v_adv_class ?>">
                <div class="u-mb20">
                    <button type="button" class="button button-secondary js-toggle-options">
                        <?= _("Basic Options") ?>
                    </button>
                </div>
                <div class="u-mb20">
                    <label for="v_config" class="form-label"><?= $v_config_path ?></label>
                    <textarea
                        class="form-control u-min-height600 u-allow-resize u-console js-advanced-textarea"
                        name="v_config"
                        id="v_config"><?= $v_config ?></textarea>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="v_restart" id="v_restart" checked>
                    <label for="v_restart">
                        <?= _("Restart") ?>
                    </label>
                </div>
            </div>
        </div>

    </form>

</div>
