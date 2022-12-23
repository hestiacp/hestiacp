<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/edit/server/" class="button button-secondary">
				<i class="fas fa-gear icon-maroon"></i><?= _("Configure") ?>
			</a>
			<a href="/list/rrd/" class="button button-secondary">
				<i class="fas fa-chart-area icon-blue"></i><?= _("Graphs") ?>
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
			<div class="actions-panel" key-action="js">
				<a class="data-controls do_servicerestart button button-secondary button-danger">
					<i class="do_servicerestart fas fa-arrow-rotate-left icon-red"></i><?= _("Restart") ?>
					<input type="hidden" name="servicerestart_url" value="/restart/system/?hostname=<?= $sys["sysinfo"]["HOSTNAME"] ?>&token=<?= $_SESSION["token"] ?>&system_reset_token=<?= time() ?>">
					<div class="dialog js-confirm-dialog-servicerestart" title="<?= _("Confirmation") ?>">
						<p><?= sprintf(_("RESTART_CONFIRMATION"), "Server") ?></p>
					</div>
				</a>
			</div>
		</div>
		<div class="toolbar-right">
			<form x-bind="BulkEdit" action="/bulk/service/" method="post">
				<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
				<select class="form-select" name="action">
					<option value=""><?= _("apply to selected") ?></option>
					<option value="stop"><?= _("stop") ?></option>
					<option value="start"><?= _("start") ?></option>
					<option value="restart"><?= _("restart") ?></option>
				</select>
				<button type="submit" class="toolbar-input-submit" title="<?= _("apply to selected") ?>">
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
					<span class="server-summary-list-label"><?= _("Hestia Control Panel") ?></span>
					<span class="server-summary-list-value">
						<?php if ($sys["sysinfo"]["RELEASE"] != "release") { ?>
							<i class="fas fa-flask icon-red" title="<?= $sys["sysinfo"]["RELEASE"] ?>"></i>
						<?php } ?>
						<?php if ($sys["sysinfo"]["RELEASE"] == "release") { ?>
							<i class="fas fa-cube" title="<?= _("Production release") ?>"></i>
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

		<div class="table-header">
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
				if(in_array($key, $phpfpm)){
					$edit_url="php";
				} else {
					$edit_url=$key;
				}

				$cpu = $data[$key]['CPU'] / 10;
				$cpu = number_format($cpu, 1);
				if ($cpu == '0.0')	$cpu = 0;
			?>
			<div class="l-unit <?php if ($status == 'suspended') echo 'l-unit--suspended';?> animate__animated animate__fadeIn" sort-name="<?=strtolower($key)?>"
				sort-memory="<?=$data[$key]['MEM']?>" sort-cpu="<?=$cpu;?>" sort-uptime="<?=$data[$key]['RTIME']?>">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check<?=$i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="service[]" value="<?=$key?>">
					</div>
					<div class="clearfix l-unit__stat-col--left wide-2">
						<i class="fas <?=$state_icon;?> u-mr5"></i>
						<b><a href="/edit/server/<? echo $edit_url ?>/" title="<?= _("edit") ?>: <?=$key?>"><?=$key?></a></b>
					</div>
					<div class="clearfix l-unit__stat-col--left u-text-center compact-2">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__edit shortcut-enter" key-action="href">
								<a href="/edit/server/<? echo $edit_url ?>/" title="<?= _("edit") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a>
							</div>
							<div class="actions-panel__col actions-panel__stop shortcut-s" key-action="js">
								<a id="restart_link_<?=$i?>" class="data-controls do_servicerestart" title="<?= _("restart") ?>">
									<i class="do_servicerestart data-controls fas fa-arrow-rotate-left icon-highlight icon-dim"></i>
									<input type="hidden" name="servicerestart_url" value="/restart/service/?srv=<?=$key?>&token=<?=$_SESSION['token']?>">
									<div id="restart_link_dialog_<?=$i?>" class="dialog js-confirm-dialog-servicerestart" title="<?= _("Confirmation") ?>">
										<p><?=sprintf(_('RESTART_CONFIRMATION'),$key); ?></p>
									</div>
								</a>
							</div>
							<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
								<a id="delete_link_<?=$i?>" class="data-controls do_servicestop" title="<?=_($action)?>">
									<i class="do_servicestop fas <?=$spnd_icon?> icon-red icon-dim"></i>
									<input type="hidden" name="servicestop_url" value="/<?=$action ?>/service/?srv=<?=$key?>&token=<?=$_SESSION['token']?>">
									<div id="delete_dialog_<?=$i?>" class="dialog js-confirm-dialog-servicestop" title="<?= _("Confirmation") ?>">
										<p><?php if($action == 'stop'){ echo sprintf(_('Are you sure you want to stop service'),$key); }else{ echo sprintf(_('Are you sure you want to start service'),$key); }?></p>
									</div>
								</a>
							</div>
						</div>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-3"><?=_($data[$key]['SYSTEM'])?></div>
					<div class="clearfix l-unit__stat-col--left u-text-center"><b><?=humanize_time($data[$key]['RTIME'])?></b></div>
					<div class="clearfix l-unit__stat-col--left u-text-center"><b><?=$cpu?></b></div>
					<div class="clearfix l-unit__stat-col--left u-text-center"><b><?=$data[$key]['MEM']?> <?= _("mb") ?></b></div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<footer class="app-footer">
</footer>
