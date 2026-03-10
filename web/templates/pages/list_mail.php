<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/add/mail/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= tohtml( _("Add Mail Domain")) ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= tohtml( _("Sort items")) ?>">
					<?= tohtml( _("Sort by")) ?>:
					<span class="u-text-bold">
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?= tohtml($label) ?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-accounts" data-sort-as-int="1">
						<span class="name"><?= tohtml( _("Accounts")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= tohtml( _("Date")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-disk" data-sort-as-int="1">
						<span class="name"><?= tohtml( _("Disk")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= tohtml( _("Name")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/mail/" method="post">
						<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
						<select class="form-select" name="action">
							<option value=""><?= tohtml( _("Apply to selected")) ?></option>
							<?php if ($_SESSION["userContext"] === "admin") { ?>
								<option value="rebuild"><?= tohtml( _("Rebuild All")) ?></option>
							<?php } ?>
							<option value="suspend"><?= tohtml( _("Suspend")) ?></option>
							<option value="unsuspend"><?= tohtml( _("Unsuspend")) ?></option>
							<option value="delete"><?= tohtml( _("Delete")) ?></option>
						</select>
						<button type="submit" class="toolbar-input-submit" title="<?= tohtml( _("Apply to selected")) ?>">
							<i class="fas fa-arrow-right"></i>
						</button>
					</form>
				<?php } ?>
			</div>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<input type="search" class="form-control js-search-input" name="q" value="<?= tohtml($_POST['q'] ?? '') ?>" title="<?= tohtml( _("Search")) ?>">
					<button type="submit" class="toolbar-input-submit" title="<?= tohtml( _("Search")) ?>">
						<i class="fas fa-magnifying-glass"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= tohtml( _("Mail Domains")) ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= tohtml( _("Select all")) ?>" <?= tohtml($display_mode) ?>>
			</div>
			<div class="units-table-cell"><?= tohtml( _("Name")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Accounts")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Disk")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Anti-Virus")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Spam Filter")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("DKIM")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("SSL")) ?></div>
		</div>

		<!-- Begin mail domain list item loop -->
		<?php
			list($http_host, $port) = explode(':', $_SERVER["HTTP_HOST"].":");
			$webmail = "webmail";
			if (!empty($_SESSION['WEBMAIL_ALIAS'])) $webmail = $_SESSION['WEBMAIL_ALIAS'];
			foreach ($data as $key => $value) {
				++$i;
				if ($data[$key]['SUSPENDED'] == 'yes') {
					$status = 'suspended';
					$spnd_action = 'unsuspend';
					$spnd_action_title = _('Unsuspend');
					$spnd_icon = 'fa-play';
					$spnd_icon_class = 'icon-green';
					$spnd_confirmation = _('Are you sure you want to unsuspend domain %s?');
					if ($data[$key]['ANTIVIRUS'] == 'no') {
						$antivirus_icon = 'fa-circle-xmark';
						$antivirus_title = _('Disabled');
					} else {
						$antivirus_icon = 'fa-circle-check';
						$antivirus_title = _('Enabled');
					}
					if ($data[$key]['ANTISPAM'] == 'no') {
						$antispam_icon = 'fa-circle-xmark';
						$antispam_title = _('Disabled');
					} else {
						$antispam_icon = 'fa-circle-check';
						$antispam_title = _('Enabled');
					}
					if ($data[$key]['DKIM'] == 'no') {
						$dkim_icon = 'fa-circle-xmark';
						$dkim_title = _('Disabled');
					} else {
						$dkim_icon = 'fa-circle-check';
						$dkim_title = _('Enabled');
					}
					if ($data[$key]['SSL'] == 'no') {
						$ssl_icon = 'fa-circle-xmark';
						$ssl_title = _('Disabled');
					} else {
						$ssl_icon = 'fa-circle-check';
						$ssl_title = _('Enabled');
					}
				} else {
					$status = 'active';
					$spnd_action = 'suspend';
					$spnd_action_title = _('Suspend');
					$spnd_icon = 'fa-pause';
					$spnd_icon_class = 'icon-highlight';
					$spnd_confirmation = _('Are you sure you want to suspend domain %s?');
					if ($data[$key]['ANTIVIRUS'] == 'no') {
						$antivirus_icon = 'fa-circle-xmark icon-red';
						$antivirus_title = _('Disabled');
					} else {
						$antivirus_icon = 'fa-circle-check icon-green';
						$antivirus_title = _('Enabled');
					}
					if ($data[$key]['ANTISPAM'] == 'no') {
						$antispam_icon = 'fa-circle-xmark icon-red';
						$antispam_title = _('Disabled');
					} else {
						$antispam_icon = 'fa-circle-check icon-green';
						$antispam_title = _('Enabled');
					}
					if ($data[$key]['DKIM'] == 'no') {
						$dkim_icon = 'fa-circle-xmark icon-red';
						$dkim_title = _('Disabled');
					} else {
						$dkim_icon = 'fa-circle-check icon-green';
						$dkim_title = _('Enabled');
					}
					if ($data[$key]['SSL'] == 'no') {
						$ssl_icon = 'fa-circle-xmark icon-red';
						$ssl_title = _('Disabled');
					} else {
						$ssl_icon = 'fa-circle-check icon-green';
						$ssl_title = _('Enabled');
					}
				}
				if (empty($data[$key]['CATCHALL'])) {
					$data[$key]['CATCHALL'] = '/dev/null';
				}
			?>
			<div class="units-table-row <?php if ($status == 'suspended') echo 'disabled'; ?> js-unit"
				data-sort-date="<?= tohtml(strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])) ?>"
				data-sort-name="<?= tohtml($key) ?>"
				data-sort-disk="<?= tohtml($data[$key]["U_DISK"]) ?>"
				data-sort-accounts="<?= tohtml($data[$key]["ACCOUNTS"]) ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= tohtml($i) ?>" class="js-unit-checkbox" type="checkbox" title="<?= tohtml( _("Select")) ?>" name="domain[]" value="<?= tohtml($key) ?>" <?= tohtml($display_mode) ?>>
						<label for="check<?= tohtml($i) ?>" class="u-hide-desktop"><?= tohtml( _("Select")) ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("Name")) ?>:</span>
					<a href="?<?= tohtml(http_build_query(["domain" => $key, "token" => $_SESSION["token"]])) ?>" title="<?= tohtml( _("Mail Accounts")) ?>: <?= tohtml($key) ?>">
						<?= tohtml($key) ?>
					</a>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<?php if ($read_only === "true") { ?>
							<li class="units-table-row-action shortcut-l" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="?<?= tohtml(http_build_query(["domain" => $key, "token" => $_SESSION["token"]])) ?>"
									title="<?= tohtml( _("Mail Accounts")) ?>"
								>
									<i class="fas fa-users icon-blue"></i>
									<span class="u-hide-desktop"><?= tohtml( _("Mail Accounts")) ?></span>
								</a>
							</li>
							<li class="units-table-row-action shortcut-l" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="?<?= tohtml(http_build_query(["domain" => $key, "dns" => '1', "token" => $_SESSION["token"]])) ?>"
									title="<?= tohtml( _("DNS Records")) ?>"
								>
									<i class="fas fa-book-atlas icon-blue"></i>
									<span class="u-hide-desktop"><?= tohtml( _("DNS Records")) ?></span>
								</a>
							</li>
							<?php if ($data[$key]["SUSPENDED"] == "no") { ?>
								<li class="units-table-row-action" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="http://<?= tohtml($webmail) ?>.<?= tohtml($key) ?>/"
										target="_blank"
										title="<?= tohtml( _("Open Webmail")) ?>"
									>
										<i class="fas fa-paper-plane icon-lightblue"></i>
										<span class="u-hide-desktop"><?= tohtml( _("Open Webmail")) ?></span>
									</a>
								</li>
							<?php } ?>
						<?php } else { ?>
							<?php if ($data[$key]["SUSPENDED"] == "no") { ?>
								<li class="units-table-row-action shortcut-n" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="/add/mail/?<?= tohtml(http_build_query(["domain" => $key, "token" => $_SESSION["token"]])) ?>"
										title="<?= tohtml( _("Add Mail Account")) ?>"
									>
										<i class="fas fa-circle-plus icon-green"></i>
										<span class="u-hide-desktop"><?= tohtml( _("Add Mail Account")) ?></span>
									</a>
								</li>
								<?php if ($_SESSION["WEBMAIL_SYSTEM"]) { ?>
									<?php if (!empty($data[$key]["WEBMAIL"])) { ?>
										<li class="units-table-row-action" data-key-action="href">
											<a
												class="units-table-row-action-link"
												href="http://<?= tohtml($webmail) ?>.<?= tohtml($key) ?>/"
												target="_blank"
												title="<?= tohtml( _("Open Webmail")) ?>"
											>
												<i class="fas fa-paper-plane icon-lightblue"></i>
												<span class="u-hide-desktop"><?= tohtml( _("Open Webmail")) ?></span>
											</a>
										</li>
									<?php } ?>
								<?php } ?>
								<li class="units-table-row-action shortcut-enter" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="/edit/mail/?<?= tohtml(http_build_query(["domain" => $key, "token" => $_SESSION["token"]])) ?>"
										title="<?= tohtml( _("Edit Mail Domain")) ?>"
									>
										<i class="fas fa-pencil icon-orange"></i>
										<span class="u-hide-desktop"><?= tohtml( _("Edit Mail Domain")) ?></span>
									</a>
								</li>
							<?php } ?>
							<li class="units-table-row-action shortcut-l" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="?<?= tohtml(http_build_query(["domain" => $key, "dns" => '1', "token" => $_SESSION["token"]])) ?>"
									title="<?= tohtml( _("DNS Records")) ?>"
								>
									<i class="fas fa-book-atlas icon-blue"></i>
									<span class="u-hide-desktop"><?= tohtml( _("DNS Records")) ?></span>
								</a>
							</li>
							<li class="units-table-row-action shortcut-s" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/<?= tohtml($spnd_action) ?>/mail/?<?= tohtml(http_build_query(["domain" => $key, "token" => $_SESSION["token"]])) ?>"
									title="<?= tohtml($spnd_action_title) ?>"
									data-confirm-title="<?= tohtml($spnd_action_title) ?>"
									data-confirm-message="<?= tohtml(sprintf($spnd_confirmation, $key)) ?>"
								>
									<i class="fas <?= tohtml($spnd_icon) ?> <?= tohtml($spnd_icon_class) ?>"></i>
									<span class="u-hide-desktop"><?= tohtml($spnd_action_title) ?></span>
								</a>
							</li>
							<li class="units-table-row-action shortcut-delete" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/delete/mail/?<?= tohtml(http_build_query(["domain" => $key, "token" => $_SESSION["token"]])) ?>"
									title="<?= tohtml( _("Delete")) ?>"
									data-confirm-title="<?= tohtml( _("Delete")) ?>"
									data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to delete domain %s?"), $key)) ?>"
								>
									<i class="fas fa-trash icon-red"></i>
									<span class="u-hide-desktop"><?= tohtml( _("Delete")) ?></span>
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Accounts")) ?>:</span>
					<?php
						if ($data[$key]['ACCOUNTS']) {
							$mail_accounts = htmlentities($data[$key]['ACCOUNTS']);
						} else {
							$mail_accounts = '0';
						}
					?>
					<?= tohtml($mail_accounts) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Disk")) ?>:</span>
					<span class="u-text-bold">
						<?= tohtml(humanize_usage_size($data[$key]["U_DISK"])) ?>
					</span>
					<span class="u-text-small">
						<?= tohtml(humanize_usage_measure($data[$key]["U_DISK"])) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Anti-Virus")) ?>:</span>
					<i class="fas <?= tohtml($antivirus_icon) ?>" title="<?= tohtml($antivirus_title) ?>"></i>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Spam Filter")) ?>:</span>
					<i class="fas <?= tohtml($antispam_icon) ?>" title="<?= tohtml($antispam_title) ?>"></i>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("DKIM")) ?>:</span>
					<i class="fas <?= tohtml($dkim_icon) ?>" title="<?= tohtml($dkim_title) ?>"></i>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("SSL")) ?>:</span>
					<i class="fas <?= tohtml($ssl_icon) ?>" title="<?= tohtml($ssl_title) ?>"></i>
				</div>
			</div>
		<?php } ?>
	</div>

	<div class="units-table-footer">
		<p>
			<?php printf(ngettext("%d mail domain", "%d mail domains", $i), $i); ?>
		</p>
	</div>

</div>
