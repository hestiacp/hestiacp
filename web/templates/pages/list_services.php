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
			<div class="actions-panel" data-key-action="js">
				<a
					class="button button-secondary button-danger data-controls js-confirm-action"
					href="/restart/system/?hostname=<?= $sys["sysinfo"]["HOSTNAME"] ?>&token=<?= $_SESSION["token"] ?>&system_reset_token=<?= time() ?>"
					data-confirm-title="<?= _("Restart") ?>"
					data-confirm-message="<?= sprintf(_("Are you sure you want to restart %s?"), "Server") ?>"
				>
					<i class="fas fa-arrow-rotate-left icon-red"></i><?= _("Restart") ?>
				</a>
			</div>
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
						&nbsp;v<?= $sys["sysinfo"]["HESTIA"] ?>
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

	<div class="units">

		<div class="units-header">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>">
				</div>

				<div class="clearfix l-unit__stat-col--left wide-2"><b><?= _("Service") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-right compact-2">&nbsp;</div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("Description") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Uptime") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("CPU") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Memory") ?></b></div>
			</div>
		</div>

		<!-- Begin services status list item loop -->
		<?php
			foreach ($data as $key => $value) {
			++$i;
				if ($data[$key]['STATE'] == 'running') {
					$status = 'active';
					$action = 'stop';
					$spnd_icon = 'fa-stop';
					$state_icon = 'fa-circle-check icon-green';
				} else {
					$status = 'suspended';
					$action = 'start';
					$spnd_icon = 'fa-play';
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
			<div class="l-unit <?php if ($status == 'suspended') echo 'l-unit--suspended';?> animate__animated animate__fadeIn"
				data-sort-name="<?=strtolower($key)?>"
				data-sort-memory="<?=$data[$key]['MEM']?>"
				data-sort-cpu="<?=$cpu;?>"
				data-sort-uptime="<?=$data[$key]['RTIME']?>">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check<?= $i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="service[]" value="<?= $key ?>">
					</div>
					<div class="clearfix l-unit__stat-col--left wide-2">
						<i class="fas <?= $state_icon ?> u-mr5"></i>
						<b><a href="/edit/server/<? echo $edit_url ?>/" title="<?= _("Edit") ?>: <?= $key ?>"><?= $key ?></a></b>
					</div>
					<div class="clearfix l-unit__stat-col--left u-text-center compact-2">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__edit shortcut-enter" data-key-action="href">
								<a href="/edit/server/<? echo $edit_url ?>/" title="<?= _("Edit") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a>
							</div>
							<div class="actions-panel__col actions-panel__stop shortcut-s" data-key-action="js">
								<a
									class="data-controls js-confirm-action"
									href="/restart/service/?srv=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									data-confirm-title="<?= _("Restart") ?>"
									data-confirm-message="<?= sprintf(_('Are you sure you want to restart %s?'), $key) ?>"
								>
									<i class="fas fa-arrow-rotate-left icon-highlight icon-dim"></i>
								</a>
							</div>
							<div class="actions-panel__col actions-panel__delete shortcut-delete" data-key-action="js">
								<a
									class="data-controls js-confirm-action"
									href="/<?= $action ?>/service/?srv=<?=$key?>&token=<?=$_SESSION['token']?>"
									data-confirm-title="<?= _($action) ?>"
									data-confirm-message="<?php if ($action == 'stop'){ echo sprintf(_('Are you sure you want to stop service %s?'), $key); } else { echo sprintf(_('Are you sure you want to start service %s?'), $key); }?>"
								>
									<i class="fas <?= $spnd_icon ?> icon-red icon-dim"></i>
								</a>
							</div>
						</div>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-3"><?=_($data[$key]['SYSTEM'])?></div>
					<div class="clearfix l-unit__stat-col--left u-text-center"><b><?=humanize_time($data[$key]['RTIME'])?></b></div>
					<div class="clearfix l-unit__stat-col--left u-text-center"><b><?=$cpu?></b></div>
					<div class="clearfix l-unit__stat-col--left u-text-center"><b><?=$data[$key]['MEM']?> <?= _("MB") ?></b></div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<footer class="app-footer">
</footer>
