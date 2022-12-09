<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== 'true') {?>
				<a href="/add/cron/" class="button button-secondary" id="btn-create"><i class="fas fa-circle-plus status-icon green"></i><?= _("Add Cron Job") ?></a>
				<?php if($panel[$user_plain]['CRON_REPORTS'] == 'yes') { ?>
					<a class="button button-secondary" href="/delete/cron/reports/?token=<?=$_SESSION['token'];?>"><i class="fas fa-toggle-off status-icon green"></i><?= _("turn off notifications") ?></a>
				<?php } else { ?>
					<a class="button button-secondary" href="/add/cron/reports/?token=<?=$_SESSION['token'];?>"><i class="fas fa-toggle-off status-icon grey"></i><?= _("turn on notifications") ?></a>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<a href="#" class="toolbar-sorting-toggle" title="<?= _("Sort items") ?>">
					<?= _("sort by") ?>:
					<b>
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?=$label;?> <i class="fas fa-arrow-down-a-z"></i>
					</b>
				</a>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn u-hidden">
					<li entity="sort-name"><span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Command") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-date" sort_as_int="1"><span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
				</ul>
				<?php if ($read_only !== 'true') {?>
					<form x-bind="BulkEdit" action="/bulk/cron/" method="post">
						<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
						<select class="form-select" name="action">
							<option value=""><?= _("apply to selected") ?></option>
							<?php if($panel[$user_plain]['CRON_REPORTS'] == 'yes') echo '<option value="delete-cron-reports">'._('turn off notifications').'</option>'; ?>
							<?php if($panel[$user_plain]['CRON_REPORTS'] == 'no') echo '<option value="add-cron-reports">'._('turn on notifications').'</option>'; ?>
							<option value="suspend"><?= _("suspend") ?></option>
							<option value="unsuspend"><?= _("unsuspend") ?></option>
							<option value="delete"><?= _("delete") ?></option>
						</select>
						<button type="submit" class="toolbar-input-submit" title="<?= _("apply to selected") ?>">
							<i class="fas fa-arrow-right"></i>
						</button>
					</form>
				<?php } ?>
			</div>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<input type="search" class="form-control js-search-input" name="q" value="<? echo isset($_POST['q']) ? htmlspecialchars($_POST['q']) : '' ?>" title="<?= _("Search") ?>">
					<button type="submit" class="toolbar-input-submit" title="<?= _("Search") ?>">
						<i class="fas fa-magnifying-glass"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container units">
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				<input id="toggle-all" type="checkbox" name="toggle-all" value="toggle-all" title="<?= _("Select all") ?>" <?=$display_mode;?>>
			</div>
			<div class="clearfix l-unit__stat-col--left wide-5"><b><?= _("Command") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-2 u-text-right"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><b><?= _("Min") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><b><?= _("Hour") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><b><?= _("Day") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><b><?= _("Month") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><b><?= _("Day of week") ?></b></div>
		</div>
	</div>

	<!-- Begin cron job list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;
			if ($data[$key]['SUSPENDED'] == 'yes') {
				$status = 'suspended';
				$spnd_action = 'unsuspend';
				$spnd_icon = 'fa-play';
				$spnd_confirmation = _('UNSUSPEND_CRON_CONFIRMATION') ;
			} else {
				$status = 'active';
				$spnd_action = 'suspend';
				$spnd_icon = 'fa-pause';
				$spnd_confirmation = _('SUSPEND_CRON_CONFIRMATION') ;
			}
		?>
		<div class="l-unit <?php if($status == 'suspended') echo 'l-unit--suspended'; ?> animate__animated animate__fadeIn" v_unit_id="<?=$key?>" v_section="cron"
			sort-date="<?=strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])?>" sort-name="<?=htmlspecialchars($data[$key]['CMD'], ENT_NOQUOTES)?>">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?=$i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="job[]" value="<?=$key?>" <?=$display_mode;?>>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-5 truncate">
					<?php if (($read_only === 'true') || ($data[$key]['SUSPENDED'] == 'yes')) {?>
						<b><?=htmlspecialchars($data[$key]['CMD'], ENT_NOQUOTES)?></b>
					<?php } else { ?>
						<b><a href="/edit/cron/?job=<?=$data[$key]['JOB']?>&token=<?=$_SESSION['token']?>" title="<?= _("Editing Cron Job") ?>: <?=htmlspecialchars($data[$key]['CMD'], ENT_NOQUOTES)?>"><?=htmlspecialchars($data[$key]['CMD'], ENT_NOQUOTES)?></a></b>
					<?php } ?>
				</div>
				<!-- START QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left compact-2 u-text-right">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<?php if ($read_only === 'true') {?>
								<!-- Restrict other administrators from editing, deleting, or suspending 'admin' user cron jobs -->
								&nbsp;
							<?php } else { ?>
								<?php if ($data[$key]['SUSPENDED'] == 'no') {?>
									<div class="actions-panel__col actions-panel__download shortcut-enter" key-action="href"><a href="/edit/cron/?job=<?=$data[$key]['JOB']?>&token=<?=$_SESSION['token']?>" title="<?= _("Editing Cron Job") ?>"><i class="fas fa-pencil status-icon orange status-icon dim"></i></a></div>
								<?php } ?>
								<div class="actions-panel__col actions-panel__suspend shortcut-s" key-action="js">
									<a id="<?=$spnd_action ?>_link_<?=$i?>" class="data-controls do_<?=$spnd_action?>" title="<?=_($spnd_action)?>">
										<i class="fas <?=$spnd_icon?> status-icon highlight status-icon dim do_<?=$spnd_action?>"></i>
										<input type="hidden" name="<?=$spnd_action?>_url" value="/<?=$spnd_action?>/cron/?job=<?=$data[$key]['JOB']?>&token=<?=$_SESSION['token']?>">
										<div id="<?=$spnd_action?>_dialog_<?=$i?>" class="dialog js-confirm-dialog-suspend" title="<?= _("Confirmation") ?>">
											<p><?=sprintf($spnd_confirmation,$key)?></p>
										</div>
									</a>
								</div>
								<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
									<a id="delete_link_<?=$i?>" class="data-controls do_delete" title="<?= _("delete") ?>">
										<i class="fas fa-trash status-icon red status-icon dim do_delete"></i>
										<input type="hidden" name="delete_url" value="/delete/cron/?job=<?=$data[$key]['JOB']?>&token=<?=$_SESSION['token']?>">
										<div id="delete_dialog_<?=$i?>" class="dialog js-confirm-dialog-delete" title="<?= _("Confirmation") ?>">
											<p><?=sprintf(_('DELETE_CRON_CONFIRMATION'),$key)?></p>
										</div>
									</a>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<!-- END QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><?= $data[$key]["MIN"] ?></div>
				<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><?= $data[$key]["HOUR"] ?></div>
				<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><?= $data[$key]["DAY"] ?></div>
				<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><?= $data[$key]["MONTH"] ?></div>
				<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><?= $data[$key]["WDAY"] ?></div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container">
		<div class="l-unit-ft">
			<div class="l-unit__col l-unit__col--right">
				<?php printf(ngettext("%d cron job", "%d cron jobs", $i), $i); ?>
			</div>
		</div>
	</div>
</footer>
