<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/edit/server/" class="button button-secondary">
				<i class="fas fa-gear icon-maroon"></i><?= _("Configure") ?>
			</a>
			<a href="/list/rrd/" class="button button-secondary">
				<i class="fas fa-chart-area icon-blue"></i><?= _("Task Monitor") ?>
			</a>
			<a href="/list/updates/" class="button button-secondary">
				<i class="fas fa-arrows-rotate icon-green"></i><?= _("Updates") ?>
			</a>
			<?php if (!empty($_SESSION["FIREWALL_SYSTEM"]) && $_SESSION["FIREWALL_SYSTEM"] == "iptables") { ?>
				<a href="/list/firewall/" class="button button-secondary">
					<i class="fas fa-shield-halved icon-red"></i><?= _("Firewall") ?>
				</a>
			<?php } ?>
			<a href="/list/log/?user=system&token=<?= $_SESSION["token"] ?>" class="button button-secondary">
				<i class="fas fa-binoculars icon-orange"></i><?= _("Logs") ?>
			</a>
			<a
				class="button button-secondary button-danger data-controls js-confirm-action"
				href="/restart/system/?hostname=<?= $sys["sysinfo"]["HOSTNAME"] ?>&token=<?= $_SESSION["token"] ?>&system_reset_token=<?= time() ?>"
				data-confirm-title="<?= _("Restart") ?>"
				data-confirm-message="<?= sprintf(_("Are you sure you want to restart %s?"), "Server") ?>"
			>
				<i class="fas fa-arrow-rotate-left icon-red"></i><?= _("Restart") ?>
			</a>
		</div>
		<div class="toolbar-right">
			<form x-data x-bind="BulkEdit" action="/bulk/service/" method="post">
				<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
				<select class="form-select" name="action">
					<option value=""><?= _("Apply to selected") ?></option>
					<option value="stop"><?= _("Stop") ?></option>
					<option value="start"><?= _("Start") ?></option>
					<option value="restart"><?= _("Restart") ?></option>
				</select>
				<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
					<i class="fas fa-arrow-right"></i>
				</button>
			</form>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<div class="server-summary">
		<div class="server-summary-icon">
			<i class="fas fa-server"></i>
		</div>
		<div class="server-summary-content">
			<h1 class="server-summary-title"><?= $sys["sysinfo"]["HOSTNAME"] ?></h1>
			<ul class="server-summary-list">
				<li class="server-summary-item">
					<span class="server-summary-list-label">Hestia Control Panel</span>
					<span class="server-summary-list-value">
						<?php if ($sys["sysinfo"]["RELEASE"] != "release") { ?>
							<i class="fas fa-flask icon-red" title="<?= $sys["sysinfo"]["RELEASE"] ?>"></i>
						<?php } ?>
						<?php if ($sys["sysinfo"]["RELEASE"] == "release") { ?>
							<i class="fas fa-cube" title="<?= _("Production Release") ?>"></i>
						<?php } ?>
						v<?= $sys["sysinfo"]["HESTIA"] ?>
					</span>
				</li>
				<li class="server-summary-item">
					<span class="server-summary-list-label"><?= _("Operating System") ?></span>
					<span class="server-summary-list-value">
						<?= $sys["sysinfo"]["OS"] ?> <?= $sys["sysinfo"]["VERSION"] ?> (<?= $sys["sysinfo"]["ARCH"] ?>)
					</span>
				</li>
				<li class="server-summary-item">
					<span class="server-summary-list-label"><?= _("Load Average") ?></span>
					<span class="server-summary-list-value">
						<?= $sys["sysinfo"]["LOADAVERAGE"] ?>
					</span>
				</li>
				<li class="server-summary-item">
					<span class="server-summary-list-label"><?= _("Uptime") ?></span>
					<span class="server-summary-list-value">
						<?= humanize_time($sys["sysinfo"]["UPTIME"]) ?>
					</span>
				</li>
			</ul>
		</div>
	</div>

	<h1 class="u-text-center u-hide-desktop u-pr30 u-mb20 u-pl30"><?= _("Services") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>">
			</div>
			<div class="units-table-cell"><?= _("Service") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell"><?= _("Description") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Uptime") ?></div>
			<div class="units-table-cell u-text-center"><?= _("CPU") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Memory") ?></div>
		</div>

		<!-- Begin services status list item loop -->
		<?php
			foreach ($data as $key => $value) {
			++$i;
				if ($data[$key]['STATE'] == 'running') {
					$status = 'active';
					$action = 'stop';
					$action_text = _('Stop');
					$spnd_icon = 'fa-stop';
					$spnd_icon_class = 'icon-red';
					$state_icon = 'fa-circle-check icon-green';
				} else {
					$status = 'suspended';
					$action = 'start';
					$action_text = _('Start');
					$spnd_icon = 'fa-play';
					$spnd_icon_class = 'icon-green';
					$state_icon = 'fa-circle-minus icon-red';
				}
				if (in_array($key, $phpfpm)){
					$edit_url="php";
				} else {
					$edit_url=$key;
				}

				$cpu = $data[$key]['CPU'] / 10;
				$cpu = number_format($cpu, 1);
				if ($cpu == '0.0')	$cpu = 0;
			?>
			<div class="units-table-row <?php if ($status == 'suspended') echo 'disabled'; ?> js-unit"
				data-sort-name="<?= strtolower($key) ?>"
				data-sort-memory="<?= $data[$key]["MEM"] ?>"
				data-sort-cpu="<?= $cpu ?>"
				data-sort-uptime="<?= $data[$key]["RTIME"] ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="service[]" value="<?= $key ?>">
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Service") ?>:</span>
					<i class="fas <?= $state_icon ?> u-mr5"></i>
					<a href="/edit/server/<? echo $edit_url ?>/" title="<?= _("Edit") ?>: <?= $key ?>">
						<?= $key ?>
					</a>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="/edit/server/<? echo $edit_url ?>/"
								title="<?= _("Edit") ?>"
							>
								<i class="fas fa-pencil icon-orange"></i>
								<span class="u-hide-desktop"><?= _("Edit") ?></span>
							</a>
						</li>
						<li class="units-table-row-action shortcut-s" data-key-action="js">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/restart/service/?srv=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Restart") ?>"
								data-confirm-title="<?= _("Restart") ?>"
								data-confirm-message="<?= sprintf(_("Are you sure you want to restart %s?"), $key) ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-highlight"></i>
								<span class="u-hide-desktop"><?= _("Restart") ?></span>
							</a>
						</li>
						<li class="units-table-row-action shortcut-delete" data-key-action="js">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/<?= $action ?>/service/?srv=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?=$action_text ?>"
								data-confirm-message="<?php if ($action == 'stop'){ echo sprintf(_('Are you sure you want to stop service %s?'), $key); } else { echo sprintf(_('Are you sure you want to start service %s?'), $key); }?>"
							>
								<i class="fas <?= $spnd_icon ?> <?= $spnd_icon_class ?>"></i>
								<span class="u-hide-desktop"><?=$action_text ?></span>
							</a>
						</li>
					</ul>
				</div>
				<div class="units-table-cell">
					<span class="u-hide-desktop u-text-bold"><?= _("Description") ?>:</span>
					<?= _($data[$key]["SYSTEM"]) ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Uptime") ?>:</span>
					<?= humanize_time($data[$key]["RTIME"]) ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("CPU") ?>:</span>
					<?= $cpu ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Memory") ?>:</span>
					<?= $data[$key]["MEM"] ?> <?= _("MB") ?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
</footer>
