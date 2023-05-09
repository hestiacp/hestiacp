<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/add/user/" class="button button-secondary js-button-create">
				<i class="fas fa-circle-plus icon-green"></i><?= _("Add User") ?>
			</a>
			<a href="/list/package/" class="button button-secondary">
				<i class="fas fa-box-open icon-orange"></i><?= _("Packages") ?>
			</a>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<b>
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?=$label;?> <i class="fas fa-arrow-down-a-z"></i>
					</b>
				</button>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn u-hidden">
					<li entity="sort-bandwidth" sort_as_int="1"><span class="name"><?= _("Bandwidth") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-date" sort_as_int="1"><span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-disk" sort_as_int="1"><span class="name"><?= _("Disk") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-name"><span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
				</ul>
				<form x-data x-bind="BulkEdit" action="/bulk/user/" method="post">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<select class="form-select" name="action">
						<option value=""><?= _("Apply to selected") ?></option>
						<option value="rebuild"><?= _("Rebuild All") ?></option>
						<option value="rebuild user"><?= _("Rebuild User Profile") ?></option>
						<option value="rebuild web"><?= _("Rebuild Web Domains") ?></option>
						<option value="rebuild dns"><?= _("Rebuild DNS Zones") ?></option>
						<option value="rebuild mail"><?= _("Rebuild Mail Domains") ?></option>
						<option value="rebuild db"><?= _("Rebuild Databases") ?></option>
						<option value="rebuild cron"><?= _("Rebuild Cron Jobs") ?></option>
						<option value="update counters"><?= _("Update Usage Counters") ?></option>
						<option value="suspend"><?= _("Suspend") ?></option>
						<option value="unsuspend"><?= _("Unsuspend") ?></option>
						<option value="delete"><?= _("Delete") ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
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

	<!-- Table header -->
	<div class="table-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				<input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>">
			</div>
			<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("Name") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-3"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center width"><b><?= _("Package") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center super-compact"><b><?= _("IPs") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-6"><b><i class="fas fa-hard-drive" title="<?= _("Disk") ?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-6"><b><i class="fas fa-right-left" title="<?= _("Bandwidth") ?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center super-compact"><b><i class="fas fa-earth-americas" title="<?= _("Web Domains") ?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center super-compact"><b><i class="fas fa-book-atlas" title="<?= _("DNS Zones") ?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center super-compact"><b><i class="fas fa-envelopes-bulk" title="<?= _("Mail Domains") ?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center super-compact"><b><i class="fas fa-database" title="<?= _("Databases") ?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center super-compact"><b><i class="fas fa-file-zipper" title="<?= _("Backups") ?>"></i></b></div>
		</div>
	</div>

	<!-- Begin user list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;
			if ($data[$key]['SUSPENDED'] == 'yes') {
				$status = 'suspended';
				$spnd_action = 'unsuspend';
				$spnd_action_title = _('Unsuspend');
				$spnd_icon = 'fa-play';
				$spnd_confirmation = _('Are you sure you want to unsuspend user %s?');
			} else {
				$status = 'active';
				$spnd_action = 'suspend';
				$spnd_action_title = _('Suspend');
				$spnd_icon = 'fa-pause';
				$spnd_confirmation = _('Are you sure you want to suspend user %s?');
			}
		?>
		<div class="l-unit <?php if ($status == 'suspended') echo 'l-unit--suspended';?> animate__animated animate__fadeIn" v_section="user"
			v_unit_id="<?=$key?>" sort-date="<?=strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])?>" sort-name="<?=strtolower($key)?>"
			sort-bandwidth="<?=$data[$key]['U_BANDWIDTH']?>" sort-disk="<?=$data[$key]['U_DISK']?>">
			<div class="l-unit__col l-unit__col--right" style="<?php if (($_SESSION['POLICY_SYSTEM_HIDE_ADMIN'] === 'yes') && ($_SESSION['user'] !== 'admin') && ($key === 'admin')) { echo 'display: none';} ?>">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?= $i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="user[]" value="<?= $key ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left wide-3 userlist-username">
					<?php if ($key == $user_plain) { ?>
						<b><a href="/edit/user/?user=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Edit User") ?>"><?=$key?> <span style="font-weight: normal !important;">(<?=$data[$key]['NAME'];?>)</span></a></b>
					<?php } else { ?>
						<b><a href="/login/?loginas=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Log in as") ?> <?=$key?>"><?=$key?> <span style="font-weight: normal !important;">(<?=$data[$key]['NAME'];?>)</span></a></b>
					<?php } ?>
					<br>
					<div class="userlist-email"><b><?= _("Email") ?>:</b> <?= $data[$key]["CONTACT"] ?></div>
				</div>
				<!-- START QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left u-text-right compact-3">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<?php if ($key == $user_plain) { ?>
								<i class="fas fa-user-check icon-dim" title="<?= $key ?> (<?= $data[$key]["NAME"] ?>)"></i>
							<?php } else { ?>
								<a href="/login/?loginas=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Log in as") ?> <?= $key ?>"><i class="fas fa-right-to-bracket icon-green icon-dim"></i></a>
							<?php } ?>
							<?php if ($_SESSION["userContext"] === "admin" && $key == "admin" && $_SESSION["user"] != "admin") { ?>
								<!-- Hide edit button from admin user when logged in with another admin user -->
								&nbsp;
							<?php } else { ?>
								<div class="actions-panel__col actions-panel__edit shortcut-enter" data-key-action="href"><a href="/edit/user/?user=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Edit User") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a></div>
							<?php } ?>
							<?php if ($key == "admin") { ?>
								<!-- Hide suspend and delete buttons in the user list for primary 'admin' account -->
							<?php } else { ?>
								<?php if ($key == $user_plain) { ?>
									<!-- Hide suspend and delete buttons in the user list for current user -->
								<?php } else { ?>
								<div class="actions-panel__col actions-panel__suspend shortcut-s" data-key-action="js">
									<a
										class="data-controls js-confirm-action"
										href="/<?= $spnd_action ?>/user/?user=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
										data-confirm-title="<?= $spnd_action_title ?>"
										data-confirm-message="<?= sprintf($spnd_confirmation, $key) ?>"
									>
										<i class="fas <?= $spnd_icon ?> icon-highlight icon-dim"></i>
									</a>
								</div>
								<div class="actions-panel__col actions-panel__delete shortcut-delete" data-key-action="js">
									<a
										class="data-controls js-confirm-action"
										href="/delete/user/?user=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
										data-confirm-title="<?= _("Delete") ?>"
										data-confirm-message="<?= sprintf(_('Are you sure you want to delete user %s?'), $key) ?>"
									>
										<i class="fas fa-trash icon-red icon-dim"></i>
									</a>
								</div>
								<?php } ?>
							<?php } ?>
						</div>
					</div>
				</div>
				<!-- END QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left u-text-center width">
					<b>
						<?php if ($data[$key]["PACKAGE"] === "system") { ?>
							<?= $data[$key]["PACKAGE"] ?>
						<?php } else { ?>
							<a href="/edit/package/?package=<?= $data[$key]["PACKAGE"] ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Edit Package") ?>"><?= $data[$key]["PACKAGE"] ?></a>
						<?php } ?>
					</b>
				</div>
				<div class="clearfix l-unit__stat-col--left u-text-center super-compact"><?= $data[$key]["IP_OWNED"] ?></div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact-6"><b><?= humanize_usage_size($data[$key]["U_DISK"],1) ?></b><span class="u-text-small"><?= humanize_usage_measure($data[$key]["U_DISK"]) ?></span> / <b><?= humanize_usage_size($data[$key]["DISK_QUOTA"],1) ?></b><span class="u-text-small"><?= humanize_usage_measure($data[$key]["DISK_QUOTA"]) ?></span></div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact-6"><b><?= humanize_usage_size($data[$key]["U_BANDWIDTH"],1) ?></b><span class="u-text-small"><?= humanize_usage_measure($data[$key]["U_BANDWIDTH"]) ?></span> / <b><?= humanize_usage_size($data[$key]["BANDWIDTH"],1) ?></b><span class="u-text-small"><?= humanize_usage_measure($data[$key]["BANDWIDTH"]) ?></span></div>
				<div class="clearfix l-unit__stat-col--left u-text-center super-compact" title="<?= $data[$key]["U_WEB_DOMAINS"] ?> <?= _("Web Domains") ?>"><span class="badge"><b><?= $data[$key]["U_WEB_DOMAINS"] ?></b></span></div>
				<div class="clearfix l-unit__stat-col--left u-text-center super-compact" title="<?= $data[$key]["U_DNS_DOMAINS"] ?> <?= _("DNS Zones") ?>"><span class="badge"><b><?= $data[$key]["U_DNS_DOMAINS"] ?></b></span></div>
				<div class="clearfix l-unit__stat-col--left u-text-center super-compact" title="<?= $data[$key]["U_MAIL_DOMAINS"] ?> <?= _("Mail Domains") ?>"><span class="badge"><b><?= $data[$key]["U_MAIL_DOMAINS"] ?></b></span></div>
				<div class="clearfix l-unit__stat-col--left u-text-center super-compact" title="<?= $data[$key]["U_DATABASES"] ?> <?= _("Databases") ?>"><span class="badge"><b><?= $data[$key]["U_DATABASES"] ?></b></span></div>
				<div class="clearfix l-unit__stat-col--left u-text-center super-compact" title="<?= $data[$key]["U_BACKUPS"] ?> <?= _("Backups") ?>"><span class="badge"><b><?= $data[$key]["U_BACKUPS"] ?></b></span></div>
			</div>
		</div>
	<?php } ?>
</div>
<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d user account", "%d user accounts", $i), $i); ?>
		</p>
	</div>
</footer>
