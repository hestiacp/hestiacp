<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <?php $edit_web_href = '/edit/web/?domain=' . htmlentities($v_domain); ?>
            <a
                class="button button-secondary button-back js-button-back"
                href="<?= $edit_web_href ?>">
                <i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
            </a>
        </div>
    </div>
</div>
<!-- End toolbar -->

<div class="container">

    <div class="form-container form-container-wide">
        <h1 class="u-mb20"><?= _("Quick Install App") ?></h1>
        <?php show_alert_message($_SESSION); ?>
        <div class="cards">
            <!-- List available web apps -->
            <?php foreach ($v_web_apps as $webapp) : ?>
                <div class="card <?= $webapp->isInstallable() ? "" : "disabled" ?>">
                    <div class="card-thumb">
                        <?php $webapp_src = "/src/app/WebApp/Installers/" . $webapp->name . "/" . $webapp->thumbnail; ?>
                        <?php $webapp_alt = $webapp->name; ?>
                        <img
                            src="<?= $webapp_src ?>"
                            alt="<?= htmlentities($webapp_alt) ?>">
                    </div>
                    <div class="card-content">
                        <p class="card-title"><?= $webapp->name ?></p>
                        <p class="u-mb10"><?= _("Version") ?>: <?= $webapp->version ?></p>
                        <?php
                        $add_webapp_app = urlencode($webapp->name);
                        $add_webapp_domain = htmlentities($v_domain);
                        $add_webapp_href = sprintf(
                            '/add/webapp/?app=%s&domain=%s',
                            $add_webapp_app,
                            $add_webapp_domain
                        );
                        ?>
                        <a
                            class="button"
                            href="<?= $add_webapp_href ?>">
                            <?= _("Setup") ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>
