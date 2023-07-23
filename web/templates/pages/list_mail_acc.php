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
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<span class="u-text-bold">
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?= $label ?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-disk" data-sort-as-int="1">
						<span class="name"><?= _("Disk") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-quota" data-sort-as-int="1">
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

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Mail Accounts") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>" <?= $display_mode ?>>
			</div>
			<div class="units-table-cell"><?= _("Name") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("Disk") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Quota") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Aliases") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Forwarding") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Auto Reply") ?></div>
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
					$spnd_icon_class = 'icon-green';
					$spnd_confirmation = _('Are you sure you want to unsuspend %s?');
					if ($data[$key]['ALIAS'] == '') {
						$alias_icon = 'fa-circle-minus';
						$alias_title = _('No aliases');
					} else {
						$alias_icon = 'fa-circle-check';
						$alias_title = _('Aliases used');
					}
					if ($data[$key]['FWD'] == '') {
						$fwd_icon = 'fa-circle-minus';
						$fwd_title = _('Disabled');
					} else {
						$fwd_icon = 'fa-circle-check';
						$fwd_title = _('Enabled');
					}
					if ($data[$key]['AUTOREPLY'] == 'no') {
						$autoreply_icon = 'fa-circle-minus';
						$autoreply_title = _('Disabled');
					} else {
						$autoreply_icon = 'fa-circle-check';
						$autoreply_title = _('Enabled');
					}
				} else {
					$status = 'active';
					$spnd_action = 'suspend';
					$spnd_action_title = _('Suspend');
					$spnd_icon = 'fa-pause';
					$spnd_icon_class = 'icon-highlight';
					$spnd_confirmation = _('Are you sure you want to suspend %s?');
					if ($data[$key]['ALIAS'] == '') {
						$alias_icon = 'fa-circle-minus';
						$alias_title = _('No aliases');
					} else {
						$alias_icon = 'fa-circle-check icon-green';
						$alias_title = _('Aliases used');
					}
					if ($data[$key]['FWD'] == '') {
						$fwd_icon = 'fa-circle-minus';
						$fwd_title = _('Disabled');
					} else {
						$fwd_icon = 'fa-circle-check icon-green';
						$fwd_title = _('Enabled');
					}
					if ($data[$key]['AUTOREPLY'] == 'no') {
						$autoreply_icon = 'fa-circle-minus';
						$autoreply_title = _('Disabled');
					} else {
						$autoreply_icon = 'fa-circle-check icon-green';
						$autoreply_title = _('Enabled');
					}
				}
			?>
			<div class="units-table-row <?php if ($status == 'suspended') echo 'disabled'; ?> js-unit"
				data-sort-date="<?= strtotime($data[$key]['DATE'].' '.$data[$key]['TIME']) ?>"
				data-sort-name="<?= $key ?>"
				data-sort-disk="<?= $data[$key]["U_DISK"] ?>"
				data-sort-quota="<?= $data[$key]["QUOTA"] ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="account[]" value="<?= $key ?>" <?= $display_mode ?>>
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Name") ?>:</span>
					<?php if ($read_only === "true" || $data[$key]["SUSPENDED"] == "yes") { ?>
						<?= $key . "@" . htmlentities($_GET["domain"]) ?>
					<?php } else { ?>
						<a href="/edit/mail/?domain=<?= htmlspecialchars($_GET['domain']) ?>&account=<?= $key ?>&token=<?= $_SESSION['token'] ?>" title="<?= _("Edit Mail Account") ?>: <?= $key ?>@<?= htmlspecialchars($_GET['domain']) ?>">
							<?= $key."@".htmlentities($_GET['domain']); ?>
						</a>
					<?php } ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<?php if ($read_only === "true") { ?>
							<!-- Restrict the ability to edit, delete, or suspend domain items when impersonating 'admin' account -->
							<?php if ($data[$key]["SUSPENDED"] != "yes") { ?>
								<li class="units-table-row-action" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="http://<?= $v_webmail_alias ?>.<?= htmlspecialchars($_GET["domain"]) ?>/?_user=<?= $key ?>@<?= htmlspecialchars($_GET["domain"]) ?>"
										target="_blank"
										title="<?= _("Open Webmail") ?>"
									>
										<i class="fas fa-envelope-open-text icon-maroon"></i>
										<span class="u-hide-desktop"><?= _("Open Webmail") ?></span>
									</a>
								</li>
							<?php } ?>
						<?php } else { ?>
							<?php if ($data[$key]["SUSPENDED"] == "no") { ?>
								<?php if ($_SESSION["WEBMAIL_SYSTEM"]) { ?>
									<?php if (!empty($data[$key]["WEBMAIL"])) { ?>
										<li class="units-table-row-action" data-key-action="href">
											<a
												class="units-table-row-action-link"
												href="http://<?= $v_webmail_alias ?>.<?= htmlspecialchars($_GET["domain"]) ?>/?_user=<?= $key ?>@<?= htmlspecialchars($_GET["domain"]) ?>"
												target="_blank"
												title="<?= _("Open Webmail") ?>"
											>
												<i class="fas fa-envelope-open-text icon-maroon"></i>
												<span class="u-hide-desktop"><?= _("Open Webmail") ?></span>
											</a>
										</li>
									<?php } ?>
								<?php } ?>
								<li class="units-table-row-action shortcut-enter" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="/edit/mail/?domain=<?= htmlspecialchars($_GET["domain"]) ?>&account=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
										title="<?= _("Edit Mail Account") ?>"
									>
										<i class="fas fa-pencil icon-orange"></i>
										<span class="u-hide-desktop"><?= _("Edit Mail Account") ?></span>
									</a>
								</li>
							<?php } ?>
							<li class="units-table-row-action shortcut-s" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/<?= $spnd_action ?>/mail/?domain=<?= htmlspecialchars($_GET["domain"]) ?>&account=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= $spnd_action_title ?>"
									data-confirm-title="<?= $spnd_action_title ?>"
									data-confirm-message="<?= sprintf($spnd_confirmation, $key) ?>"
								>
									<i class="fas <?= $spnd_icon ?> <?= $spnd_icon_class ?>"></i>
									<span class="u-hide-desktop"><?= $spnd_action_title ?></span>
								</a>
							</li>
							<li class="units-table-row-action shortcut-delete" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/delete/mail/?domain=<?= htmlspecialchars($_GET["domain"]) ?>&account=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("Delete") ?>"
									data-confirm-title="<?= _("Delete") ?>"
									data-confirm-message="<?= sprintf(_("Are you sure you want to delete %s?"), $key) ?>"
								>
									<i class="fas fa-trash icon-red"></i>
									<span class="u-hide-desktop"><?= _("Delete") ?></span>
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Disk") ?>:</span>
					<span class="u-text-bold">
						<?= humanize_usage_size($data[$key]["U_DISK"]) ?>
					</span>
					<span class="u-text-small">
						<?= humanize_usage_measure($data[$key]["U_DISK"]) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Quota") ?>:</span>
					<span class="u-text-bold">
						<?= humanize_usage_size($data[$key]["QUOTA"]) ?>
					</span>
					<span class="u-text-small">
						<?= humanize_usage_measure($data[$key]["QUOTA"]) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Aliases") ?>:</span>
					<i class="fas <?= $alias_icon ?>" title="<?= $alias_title ?>"></i>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Forwarding") ?>:</span>
					<i class="fas <?= $fwd_icon ?>" title="<?= $fwd_title ?>"></i>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Auto Reply") ?>:</span>
					<i class="fas <?= $autoreply_icon ?>" title="<?= $autoreply_title ?>"></i>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d mail account", "%d mail accounts", $i), $i); ?>
		</p>
	</div>
</footer>
