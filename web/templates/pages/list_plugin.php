<!-- Begin toolbar -->
<div class="toolbar">
    <div class="toolbar-inner">
        <div class="toolbar-buttons">
            <a href="/add/plugin/" class="button button-secondary js-button-create">
                <i class="fas fa-circle-plus icon-green"></i><?= _('Install plugin'); ?>
            </a>
        </div>

        <div class="toolbar-right">
            <div class="toolbar-sorting">
                <form x-data x-bind="BulkEdit" action="/bulk/plugin/" method="post">
                    <input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
                    <select class="form-select" name="action">
                        <option value=""><?= _("Apply to selected") ?></option>
                        <option value="enable"><?= _("Enable") ?></option>
                        <option value="disable"><?= _("Disable") ?></option>
                        <option value="delete"><?= _("Delete") ?></option>
                    </select>
                    <button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End toolbar -->

<div class="container units hestia-plugins">

    <!-- Table header -->
    <div class="header table-header">
        <div class="l-unit__col l-unit__col--right">
            <div class="clearfix l-unit__stat-col--left super-compact">
                <input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>" <?= $display_mode ?>>
            </div>
            <div class="clearfix l-unit__stat-col--left wide-6"><b><?= _("Name") ?></b></div>
            <div class="clearfix l-unit__stat-col--left compact-4 u-text-right"><b>&nbsp;</b></div>
            <div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Status") ?></b></div>
            <div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Version") ?></b></div>
            <div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Author") ?></b></div>
        </div>
    </div>

    <!-- Begin plugin list item loop -->
    <?php
    $i = 0;
    $plugins = hst_exec('v-list-plugins', 'json');
    ksort($plugins);
    foreach ($plugins as $plugin) {
        $plugin_name = $plugin['name'];
        $plugin_display_name = !empty($plugin['display-name']) ? $plugin['display-name'] : $plugin['name'];
        $plugin_version = (isset($plugin['version']) && is_string($plugin['version'])) ? $plugin['version'] : "";
        $plugin_desc = (isset($plugin['description']) && is_string($plugin['description'])) ? $plugin['description'] : "";
        $plugin_license = (isset($plugin['license']) && is_string($plugin['license'])) ? $plugin['license'] : "";
        $plugin_homepage = (isset($plugin['homepage']) && is_string($plugin['homepage'])) ? $plugin['homepage'] : "";
        $plugin_repository = (isset($plugin['repository']) && is_string($plugin['repository'])) ? $plugin['repository'] : "";
        $plugin_author_name = (isset($plugin['author']['name']) && is_string($plugin['author']['name'])) ? $plugin['author']['name'] : "";
        $plugin_author_email = (isset($plugin['author']['email']) && is_string($plugin['author']['email'])) ? $plugin['author']['email'] : "";
        $plugin_author_homepage = (isset($plugin['author']['homepage']) && is_string($plugin['author']['homepage'])) ? $plugin['author']['homepage'] : "";

        if (isset($plugin['enabled']) && $plugin['enabled'] == true) {
            $status = "enabled";
            $status_action = "suspend";
			$status_icon = 'fa-pause';
			$status_action_title = _('Disable');
			$status_confirmation = _("Are you sure you want to disable %s?");
        } else {
            $status = "disabled";
            $status_action = "unsuspend";
			$status_icon = 'fa-play';
			$status_action_title = _('Enable');
			$status_confirmation = _("Are you sure you want to enable %s?");
        }

        // Checks if the plugin has a web interface
        if ($status == 'enabled' && file_exists("/usr/local/hestia/web/plugin/$plugin_name/index.php")) {
            $plugin_web = "/plugin/$plugin_name/";
        } else {
            $plugin_web = "";
        }
        ?>
        <div class="l-unit <?php if ($status == 'disabled') echo 'l-unit--suspended';?> animate__animated animate__fadeIn" v_section="plugin" v_unit_id="<?=$plugin_name?>" id="plugin-unit-<?=$i?>">
            <div class="l-unit__col l-unit__col--right">
                <div class="clearfix l-unit__stat-col--left super-compact">
                    <input id="check<?=$i?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="plugin[]" value="<?=$plugin_name?>" <?=$display_mode;?>>
                </div>

                <div class="clearfix l-unit__stat-col--left wide-6 truncate">
                    <b>
                        <?php if (empty($plugin_web)) {
                            echo $plugin_display_name;
                        } else {?>
                            <a href="<?= $plugin_web ?>" title="<?= _('Go to plugin') ?>"><?=$plugin_display_name?></a>
                        <?php } ?>
                    </b>
                </div>

                <!-- START QUICK ACTION TOOLBAR AREA -->
                <div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
                    <div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
                        <div class="actions-panel clearfix">
                            <?php if (!empty($plugin_web)) { ?>
                            <div class="actions-panel__col actions-panel__view" data-key-action="href">
                                <a href="<?= $plugin_web ?>" title="<?= _('Go to plugin') ?>">
                                    <i class="fas fa-right-to-bracket icon-orange icon-dim"></i>
                                </a>
                            </div>
                            <?php } ?>

                            <?php if (!empty($plugin_homepage)) { ?>
                            <div class="actions-panel__col actions-panel__view" data-key-action="href">
                                <a href="<?= $plugin_homepage ?>" rel="noopener" target="_blank" title="<?= _('Plugin Homepage') ?>">
                                    <i class="fas fa-square-up-right icon-lightblue icon-dim"></i>
                                </a>
                            </div>
                            <?php } ?>

                            <?php if (!empty($plugin_repository)) { ?>
                            <div class="actions-panel__col actions-panel__view" data-key-action="href">
                                <a href="/update/plugin/?plugin=<?= urlencode($plugin_name) ?>&token=<?= $_SESSION['token'] ?>" title="<?= _('Update') ?>">
                                    <i class="fas fa-arrows-rotate icon-green icon-dim"></i>
                                </a>
                            </div>
                            <?php } ?>

                            <div class="actions-panel__col actions-panel__suspend shortcut-s" data-key-action="js">
                                <a
                                        class="data-controls js-confirm-action"
                                        href="/<?= $status_action ?>/plugin/?plugin=<?= urlencode($plugin_name) ?>&token=<?= $_SESSION['token'] ?>"
                                        title="<?= $status_action_title ?>"
                                        data-confirm-title="<?= $status_action_title ?>"
                                        data-confirm-message="<?= sprintf($status_confirmation, $plugin_name) ?>"
                                >
                                    <i class="fas <?= $status_icon ?> icon-highlight icon-dim"></i>
                                </a>
                            </div>
                            <div class="actions-panel__col actions-panel__delete shortcut-delete" data-key-action="js">
                                <a
                                        class="data-controls js-confirm-action"
                                        href="/delete/plugin/?plugin=<?= urlencode($plugin_name) ?>&token=<?= $_SESSION['token'] ?>"
                                        title="<?= _('Delete') ?>"
                                        data-confirm-title="<?= _("Delete") ?>"
                                        data-confirm-message="<?= sprintf(_('Are you sure you want to delete plugin %s?'), $plugin_name) ?>"
                                >
                                    <i class="fas fa-trash icon-red icon-dim"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END QUICK ACTION TOOLBAR AREA -->
                <div class="clearfix l-unit__stat-col--left u-text-center"><?= _($status) ?></div>
                <div class="clearfix l-unit__stat-col--left u-text-center"><?= $plugin_version ?></div>
                <div class="clearfix l-unit__stat-col--left u-text-center">
                    <?php if (!empty($plugin_author_homepage)) { ?>
                        <a href="<?= $plugin_author_homepage ?>" target="_blank"><?= $plugin_author_name ?></a>
                    <?php } else { ?>
                        <?= $plugin_author_name ?>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- /.l-unit -->
        <?php
        $i++;
    }
    ?>
</div>

<footer class="app-footer">
    <div class="container app-footer-inner">
        <p>
            <?php
            $total_plugins = count($plugins);
            printf(ngettext("%d plugin", "%d plugins", $total_plugins), $total_plugins);
            ?>
        </p>
    </div>
</footer>
