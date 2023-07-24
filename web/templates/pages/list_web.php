<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/add/web/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add Web Domain") ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<span class="u-text-bold">
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = ('Name'); } else { $label = _('Date'); } ?>
						<?= $label?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-bandwidth" data-sort-as-int="1">
						<span class="name"><?= _("Bandwidth") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-disk" data-sort-as-int="1">
						<span class="name"><?= _("Disk") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-ip" data-sort-as-int="1">
						<span class="name"><?= _("IP Address") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/web/" method="post">
						<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
						<select class="form-select" name="action">
							<option value=""><?= _("Apply to selected") ?></option>
							<?php if ($_SESSION["userContext"] === "admin") { ?>
								<option value="rebuild"><?= _("Rebuild") ?></option>
							<?php } ?>
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

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Web Domains") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>" <?= $display_mode ?>>
			</div>
			<div class="units-table-cell"><?= _("Name") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("IP Address") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Disk") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Bandwidth") ?></div>
			<div class="units-table-cell u-text-center"><?= _("SSL") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Statistics") ?></div>
		</div>

		<!-- Begin web domain list item loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;
				if ($data[$key]['SUSPENDED'] == 'yes') {
					$status = 'suspended';
					$spnd_action = 'unsuspend';
					$spnd_action_title = _('Unsuspend');
					$spnd_icon = 'fa-play';
					$spnd_icon_class = 'icon-green';
					$spnd_confirmation = _('Are you sure you want to unsuspend domain %s?');
				} else {
					$status = 'active';
					$spnd_action = 'suspend';
					$spnd_action_title = _('Suspend');
					$spnd_icon = 'fa-pause';
					$spnd_icon_class = 'icon-highlight';
					$spnd_confirmation = _('Are you sure you want to suspend domain %s?');
				}
				if (!empty($data[$key]['SSL_HOME'])) {
					if ($data[$key]['SSL_HOME'] == 'same') {
						$ssl_home = 'public_html';
					} else {
						$ssl_home = 'public_shtml';
					}
				} else {
					$ssl_home = '';
				}
				$web_stats='no';
				if (!empty($data[$key]['STATS'])) {
					$web_stats=$data[$key]['STATS'];
				}
				$ftp_user='no';
				if (!empty($data[$key]['FTP_USER'])) {
					$ftp_user=$data[$key]['FTP_USER'];
				}
				if (strlen($ftp_user) > 24 ) {
					$ftp_user = str_replace(':', ', ', $ftp_user);
					$ftp_user = substr($ftp_user, 0, 24);
					$ftp_user = trim($ftp_user, ":");
					$ftp_user = str_replace(':', ', ', $ftp_user);
					$ftp_user = $ftp_user.", ...";
				} else {
					$ftp_user = str_replace(':', ', ', $ftp_user);
				}

				$backend_support='no';
				if (!empty($data[$key]['BACKEND'])) {
					$backend_support='yes';
				}

				$proxy_support='no';
				if (!empty($data[$key]['PROXY'])) {
					$proxy_support='yes';
				}
				if (strlen($data[$key]['PROXY_EXT']) > 24 ) {
					$proxy_ext_title = str_replace(',', ', ', $data[$key]['PROXY_EXT']);
					$proxy_ext = substr($data[$key]['PROXY_EXT'], 0, 24);
					$proxy_ext = trim($proxy_ext, ",");
					$proxy_ext = str_replace(',', ', ', $proxy_ext);
					$proxy_ext = $proxy_ext.", ...";
				} else {
					$proxy_ext_title = '';
					$proxy_ext = str_replace(',', ', ', $data[$key]['PROXY_EXT']);
				}
				if ($data[$key]['SUSPENDED'] === 'yes') {
					if ($data[$key]['SSL'] == 'no') {
						$icon_ssl = 'fas fa-circle-xmark';
						$title_ssl = _('Disabled');
					}
					if ($data[$key]['SSL'] == 'yes') {
						$icon_ssl = 'fas fa-circle-check';
						$title_ssl = _('Enabled');
					}
					if ($web_stats == 'no') {
						$icon_webstats = 'fas fa-circle-xmark';
						$title_webstats = _('Disabled');
					} else {
						$icon_webstats = 'fas fa-circle-check';
						$title_webstats = _('Enabled');
					}
				} else {
					if ($data[$key]['SSL'] == 'no') {
						$icon_ssl = 'fas fa-circle-xmark icon-red';
						$title_ssl = _('Disabled');
					}
					if ($data[$key]['SSL'] == 'yes') {
						$icon_ssl = 'fas fa-circle-check icon-green';
						$title_ssl = _('Enabled');
					}
					if ($web_stats == 'no') {
						$icon_webstats = 'fas fa-circle-xmark icon-red';
						$title_webstats = _('Disabled');
					} else {
						$icon_webstats = 'fas fa-circle-check icon-green';
						$title_webstats = _('Enabled');
					}
				}
			?>
			<div class="units-table-row <?php if ($data[$key]['SUSPENDED'] == 'yes') echo 'disabled'; ?> js-unit"
				data-sort-ip="<?= str_replace('.', '', $data[$key]['IP']) ?>"
				data-sort-date="<?= strtotime($data[$key]['DATE'].' '.$data[$key]['TIME']) ?>"
				data-sort-name="<?= $key ?>"
				data-sort-bandwidth="<?= $data[$key]['U_BANDWIDTH'] ?>"
				data-sort-disk="<?= $data[$key]['U_DISK'] ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="domain[]" value="<?= $key ?>" <?= $display_mode ?>>
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Name") ?>:</span>
					<?php if ($read_only === "true") { ?>
						<?= $key ?>
					<?php } else {
						$aliases = explode(',', $data[$key]['ALIAS']);
						$alias_new = array();
						foreach($aliases as $alias){
							if ($alias != 'www.'.$key) {
								$alias_new[] = trim($alias);
							}
						}
						?>
						<a href="/edit/web/?domain=<?= $key ?>&token=<?= $_SESSION['token'] ?>" title="<?= _("Edit Domain") ?>: <?= $key ?>">
							<?= $key ?>
							<?php
								if (!empty($alias_new) && !empty($data[$key]['ALIAS'])) {
									$aliases = implode(', ', $alias_new);
									echo "<p class='hint u-max-width300 u-text-truncate'>($aliases)</p>";
								}
							?>
						</a>
					<?php } ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<?php if (!empty($data[$key]["STATS"])) { ?>
							<li class="units-table-row-action shortcut-w" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="http://<?= $key ?>/vstats/"
									target="_blank"
									rel="noopener"
									title="<?= _("Statistics") ?>"
								>
									<i class="fas fa-chart-bar icon-maroon"></i>
									<span class="u-hide-desktop"><?= _("Statistics") ?></span>
								</a>
							</li>
						<?php } ?>
						<li class="units-table-row-action" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="http://<?= $key ?>/"
								target="_blank"
								rel="noopener"
								title="<?= _("Visit") ?>"
							>
								<i class="fas fa-square-up-right icon-lightblue"></i>
								<span class="u-hide-desktop"><?= _("Visit") ?></span>
							</a>
						</li>
						<?php if ($read_only !== "true") { ?>
							<?php if ($data[$key]["SUSPENDED"] == "no") { ?>
								<li class="units-table-row-action shortcut-enter" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="/edit/web/?domain=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
										title="<?= _("Edit Domain") ?>"
									>
										<i class="fas fa-pencil icon-orange"></i>
										<span class="u-hide-desktop"><?= _("Edit Domain") ?></span>
									</a>
								</li>
							<?php } ?>
							<li class="units-table-row-action shortcut-l" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="/list/web-log/?domain=<?= $key ?>&type=access#"
									title="<?= _("Access Log") ?>"
								>
									<i class="fas fa-binoculars icon-purple"></i>
									<span class="u-hide-desktop"><?= _("Access Log") ?></span>
								</a>
							</li>
							<li class="units-table-row-action shortcut-s" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/<?= $spnd_action ?>/web/?domain=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
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
									href="/delete/web/?domain=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("Delete") ?>"
									data-confirm-title="<?= _("Delete") ?>"
									data-confirm-message="<?= sprintf(_("Are you sure you want to delete domain %s?"), $key) ?>"
								>
									<i class="fas fa-trash icon-red"></i>
									<span class="u-hide-desktop"><?= _("Delete") ?></span>
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("IP Address") ?>:</span>
					<?= empty($ips[$data[$key]["IP"]]["NAT"]) ? $data[$key]["IP"] : "{$ips[$data[$key]["IP"]]["NAT"]}" ?>
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
					<span class="u-hide-desktop u-text-bold"><?= _("Bandwidth") ?>:</span>
					<span class="u-text-bold">
						<?= humanize_usage_size($data[$key]["U_BANDWIDTH"]) ?>
					</span>
					<span class="u-text-small">
						<?= humanize_usage_measure($data[$key]["U_BANDWIDTH"]) ?>
					</span>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("SSL") ?>:</span>
					<i class="fas <?= $icon_ssl ?>" title="<?= $title_ssl ?>"></i>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Statistics") ?>:</span>
					<i class="fas <?= $icon_webstats ?>" title="<?= $title_webstats ?>"></i>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d web domain", "%d web domains", $i), $i); ?>
		</p>
	</div>
</footer>
