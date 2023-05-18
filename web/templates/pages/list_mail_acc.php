<?php
$v_webmail_alias = "webmail";
if (!empty($_SESSION["WEBMAIL_ALIAS"])) {
	$v_webmail_alias = $_SESSION["WEBMAIL_ALIAS"];
}
?>
<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/mail/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<?php if ($read_only !== "true") { ?>
				<a href="/add/mail/?domain=<?= htmlentities($_GET["domain"]) ?>" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add Mail Account") ?>
				</a>
				<a href="/edit/mail/?domain=<?= htmlentities($_GET["domain"]) ?>" class="button button-secondary js-button-create">
					<i class="fas fa-pencil icon-blue"></i><?= _("Edit Mail Domain") ?>
				</a>
			<?php } ?>
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
					<li data-entity="sort-date" sort_as_int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-disk" sort_as_int="1">
						<span class="name"><?= _("Disk") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-quota" sort_as_int="1">
						<span class="name"><?= _("Quota") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/mail/" method="post">
						<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
						<input type="hidden" value="<?= htmlspecialchars($_GET["domain"]) ?>" name="domain">
						<select class="form-select" name="action">
							<option value=""><?= _("Apply to selected") ?></option>
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

<div class="container units">
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div>
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>" <?= $display_mode ?>>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("Name") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-right compact-4"><b>&nbsp;</b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact"><b><?= _("Disk") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Quota") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Aliases") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Forwarding") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Auto Reply") ?></b></div>
			</div>
		</div>
	</div>

	<!-- Begin mail account list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;
			if ($data[$key]['SUSPENDED'] == 'yes') {
				$status = 'suspended';
				$spnd_action = 'unsuspend';
				$spnd_action_title = _('Unsuspend');
				$spnd_icon = 'fa-play';
				$spnd_confirmation = _('Are you sure you want to unsuspend %s?');
				if ($data[$key]['ALIAS'] == '') {
					$alias_icon = 'fa-circle-minus';
				} else {
					$alias_icon = 'fa-circle-check';
				}
				if ($data[$key]['FWD'] == '') {
					$fwd_icon = 'fa-circle-minus';
				} else {
					$fwd_icon = 'fa-circle-check';
				}
				if ($data[$key]['AUTOREPLY'] == 'no') {
					$autoreply_icon = 'fa-circle-minus';
				} else {
					$autoreply_icon = 'fa-circle-check';
				}
			} else {
				$status = 'active';
				$spnd_action = 'suspend';
				$spnd_action_title = _('Suspend');
				$spnd_icon = 'fa-pause';
				$spnd_confirmation = _('Are you sure you want to suspend %s?');
				if ($data[$key]['ALIAS'] == '') {
					$alias_icon = 'fa-circle-minus';
				} else {
					$alias_icon = 'fa-circle-check icon-green';
				}
				if ($data[$key]['FWD'] == '') {
					$fwd_icon = 'fa-circle-minus';
				} else {
					$fwd_icon = 'fa-circle-check icon-green';
				}
				if ($data[$key]['AUTOREPLY'] == 'no') {
					$autoreply_icon = 'fa-circle-minus';
				} else {
					$autoreply_icon = 'fa-circle-check icon-green';
				}
			}
		?>
		<div class="l-unit <?php if ($status == 'suspended') echo 'l-unit--suspended';?> animate__animated animate__fadeIn"
			v_unit_id="<?=$key."@".htmlentities($_GET['domain']);?>" v_section="mail_acc" sort-date="<?=strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])?>" sort-name="<?=$key?>" sort-disk="<?=$data[$key]['U_DISK']?>"
			sort-quota="<?=$data[$key]['QUOTA']?>" >
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?=$i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="account[]" value="<?=$key?>" <?=$display_mode;?>>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-3 truncate">
					<?php if (($read_only === 'true') || ($data[$key]['SUSPENDED'] == 'yes')) { ?>
						<b><?=$key."@".htmlentities($_GET['domain']);?></b>
					<?php } else { ?>
						<b><a href="/edit/mail/?domain=<?=htmlspecialchars($_GET['domain'])?>&account=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Edit Mail Account") ?>: <?=$key?>@<?=htmlspecialchars($_GET['domain'])?>"><?=$key."@".htmlentities($_GET['domain']);?></a></b>
					<?php } ?>
				</div>
				<!-- START QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left u-text-right compact-4">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<?php if ($read_only === 'true') { ?>
								<!-- Restrict the ability to edit, delete, or suspend domain items when impersonating 'admin' account -->
								<?php if ($data[$key]['SUSPENDED'] == 'yes') { ?>
									&nbsp;
								<?php } else { ?>
									<div class="actions-panel__col actions-panel__edit" data-key-action="href"><a href="http://<?=$v_webmail_alias;?>.<?=htmlspecialchars($_GET['domain'])?>/?_user=<?=$key?>@<?=htmlspecialchars($_GET['domain'])?>" target="_blank" title="<?= _("Open Webmail") ?>"><i class="fas fa-envelope-open-text icon-maroon icon-dim"></i></a></div>
								<?php } ?>
							<?php } else { ?>
								<?php if ($data[$key]['SUSPENDED'] == 'no') { ?>
									<?php if($_SESSION['WEBMAIL_SYSTEM']){?>
										<?php if (!empty($data[$key]['WEBMAIL'])) { ?>
											<div class="actions-panel__col actions-panel__edit" data-key-action="href"><a href="http://<?=$v_webmail_alias;?>.<?=htmlspecialchars($_GET['domain'])?>/?_user=<?=$key?>@<?=htmlspecialchars($_GET['domain'])?>" target="_blank" title="<?= _("Open Webmail") ?>"><i class="fas fa-envelope-open-text icon-maroon icon-dim"></i></a></div>
										<?php } ?>
									<?php } ?>
								<div class="actions-panel__col actions-panel__logs shortcut-enter" data-key-action="href"><a href="/edit/mail/?domain=<?=htmlspecialchars($_GET['domain'])?>&account=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Edit Mail Account") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a></div>
								<?php } ?>
								<div class="actions-panel__col actions-panel__suspend shortcut-s" data-key-action="js">
									<a
										class="data-controls js-confirm-action"
										href="/<?=$spnd_action?>/mail/?domain=<?=htmlspecialchars($_GET['domain'])?>&account=<?=$key?>&token=<?=$_SESSION['token']?>"
										data-confirm-title="<?= $spnd_action_title ?>"
										data-confirm-message="<?= sprintf($spnd_confirmation, $key) ?>"
									>
										<i class="fas <?= $spnd_icon ?> icon-highlight icon-dim"></i>
									</a>
								</div>
								<div class="actions-panel__col actions-panel__delete shortcut-delete" data-key-action="js">
									<a
										class="data-controls js-confirm-action"
										href="/delete/mail/?domain=<?=htmlspecialchars($_GET['domain'])?>&account=<?=$key?>&token=<?=$_SESSION['token']?>"
										data-confirm-title="<?= _("Delete") ?>"
										data-confirm-message="<?= sprintf(_('Are you sure you want to delete %s?'), $key) ?>"
									>
										<i class="fas fa-trash icon-red icon-dim"></i>
									</a>
								</div>
							<?php } ?>
						</div>
					</div>
					<!-- END QUICK ACTION TOOLBAR AREA -->
				</div>

				<div class="clearfix l-unit__stat-col--left u-text-center compact"><b><?= humanize_usage_size($data[$key]["U_DISK"]) ?></b> <span class="u-text-small"><?= humanize_usage_measure($data[$key]["U_DISK"]) ?></span></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?=humanize_usage_size($data[$key]["QUOTA"]) ?></b> <span class="u-text-small"><?= humanize_usage_measure($data[$key]["QUOTA"]) ?></span></div>
				<div class="clearfix l-unit__stat-col--left u-text-center">
					<i class="fas <?= $alias_icon ?>"></i>
				</div>
				<div class="clearfix l-unit__stat-col--left u-text-center">
					<i class="fas <?= $fwd_icon ?>"></i>
				</div>
				<div class="clearfix l-unit__stat-col--left u-text-center">
					<i class="fas <?= $autoreply_icon ?>"></i>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d mail account", "%d mail accounts", $i), $i); ?>
		</p>
	</div>
</footer>
