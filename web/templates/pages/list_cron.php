<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/add/cron/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add Cron Job") ?>
				</a>
				<?php if ($panel[$user_plain]["CRON_REPORTS"] == "yes") { ?>
					<a class="button button-secondary" href="/delete/cron/reports/?token=<?= $_SESSION["token"] ?>">
						<i class="fas fa-toggle-off icon-green"></i><?= _("Disable Notifications") ?>
					</a>
				<?php } else { ?>
					<a class="button button-secondary" href="/add/cron/reports/?token=<?= $_SESSION["token"] ?>">
						<i class="fas fa-toggle-off"></i><?= _("Enable Notifications") ?>
					</a>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<b>
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Command'); } else { $label = _('Date'); } ?>
						<?=$label;?> <i class="fas fa-arrow-down-a-z"></i>
					</b>
				</button>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn js-sorting-menu u-hidden">
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Command") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/cron/" method="post">
						<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
						<select class="form-select" name="action">
							<option value=""><?= _("Apply to selected") ?></option>
							<?php if($panel[$user_plain]['CRON_REPORTS'] == 'yes') echo '<option value="delete-cron-reports">' . _('Disable Notifications') . '</option>'; ?>
							<?php if($panel[$user_plain]['CRON_REPORTS'] == 'no') echo '<option value="add-cron-reports">' . _('Enable Notifications') . '</option>'; ?>
							<option value="suspend"><?= _("Suspend") ?></option>
							<option value="unsuspend"><?= _("Unsuspend") ?></option>
							<option value="delete"><?= _("Delete") ?></option>
						</select>
						<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
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

<div class="container">
	<div class="units">
		<div class="header units-header">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>" <?= $display_mode ?>>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-5"><b><?= _("Command") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact-2 u-text-right"><b>&nbsp;</b></div>
				<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><b><?= _("Minute") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><b><?= _("Hour") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><b><?= _("Day") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><b><?= _("Month") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact-3 u-text-center"><b><?= _("Day of Week") ?></b></div>
			</div>
		</div>

		<!-- Begin cron job list item loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;
				if ($data[$key]['SUSPENDED'] == 'yes') {
					$status = 'suspended';
					$spnd_action = 'unsuspend';
					$spnd_action_title = _('Unsuspend');
					$spnd_icon = 'fa-play';
					$spnd_confirmation = _('Are you sure you want to unsuspend the cron job?') ;
				} else {
					$status = 'active';
					$spnd_action = 'suspend';
					$spnd_action_title = _('Suspend');
					$spnd_icon = 'fa-pause';
					$spnd_confirmation = _('Are you sure you want to suspend the cron job?') ;
				}
			?>
			<div class="l-unit <?php if($status == 'suspended') echo 'l-unit--suspended'; ?> animate__animated animate__fadeIn js-unit"
				data-sort-date="<?=strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])?>"
				data-sort-name="<?=htmlspecialchars($data[$key]['CMD'], ENT_NOQUOTES)?>">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="job[]" value="<?= $key ?>" <?= $display_mode ?>>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-5 truncate">
						<?php if ($read_only === "true" || $data[$key]["SUSPENDED"] == "yes") { ?>
							<b><?= htmlspecialchars($data[$key]["CMD"], ENT_NOQUOTES) ?></b>
						<?php } else { ?>
							<b><a href="/edit/cron/?job=<?=$data[$key]['JOB']?>&token=<?=$_SESSION['token']?>" title="<?= _("Edit Cron Job") ?>: <?=htmlspecialchars($data[$key]['CMD'], ENT_NOQUOTES)?>"><?=htmlspecialchars($data[$key]['CMD'], ENT_NOQUOTES)?></a></b>
						<?php } ?>
					</div>
					<!-- START QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left compact-2 u-text-right">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<?php if ($read_only === "true") { ?>
									<!-- Restrict other administrators from editing, deleting, or suspending 'admin' user cron jobs -->
									&nbsp;
								<?php } else { ?>
									<?php if ($data[$key]['SUSPENDED'] == 'no') {?>
										<div class="actions-panel__col actions-panel__download shortcut-enter" data-key-action="href"><a href="/edit/cron/?job=<?=$data[$key]['JOB']?>&token=<?=$_SESSION['token']?>" title="<?= _("Edit Cron Job") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a></div>
									<?php } ?>
									<div class="actions-panel__col actions-panel__suspend shortcut-s" data-key-action="js">
										<a
											class="data-controls js-confirm-action"
											href="/<?= $spnd_action ?>/cron/?job=<?= $data[$key]["JOB"] ?>&token=<?= $_SESSION["token"] ?>"
											data-confirm-title="<?= $spnd_action_title ?>"
											data-confirm-message="<?= sprintf($spnd_confirmation, $key) ?>"
										>
											<i class="fas <?= $spnd_icon ?> icon-highlight icon-dim"></i>
										</a>
									</div>
									<div class="actions-panel__col actions-panel__delete shortcut-delete" data-key-action="js">
										<a
											class="data-controls js-confirm-action"
											href="/delete/cron/?job=<?= $data[$key]["JOB"] ?>&token=<?= $_SESSION["token"] ?>"
											data-confirm-title="<?= _("Delete") ?>"
											data-confirm-message="<?= sprintf(_("Are you sure you want to delete the cron job?"), $key) ?>"
										>
											<i class="fas fa-trash icon-red icon-dim"></i>
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
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d cron job", "%d cron jobs", $i), $i); ?>
		</p>
	</div>
</footer>
