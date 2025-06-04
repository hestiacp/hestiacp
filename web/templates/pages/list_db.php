<?php
[$http_host, $port] = explode(":", $_SERVER["HTTP_HOST"] . ":");
$db_myadmin_link = "//" . $http_host . "/phpmyadmin/";
$db_pgadmin_link = "//" . $http_host . "/phppgadmin/";

if (!empty($_SESSION["DB_PMA_ALIAS"])) {
	$db_myadmin_link = "//" . $http_host . "/" . $_SESSION["DB_PMA_ALIAS"] . "/";
}
if (!empty($_SESSION["DB_PGA_ALIAS"])) {
	$db_pgadmin_link = "//" . $http_host . "/" . $_SESSION["DB_PGA_ALIAS"] . "/";
}
?>

<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/add/db/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add Database") ?>
				</a>
				<?php if ($_SESSION["DB_SYSTEM"] === "mysql" || $_SESSION["DB_SYSTEM"] === "mysql,pgsql" || $_SESSION["DB_SYSTEM"] === "pgsql,mysql") { ?>
					<a class="button button-secondary <?= ipUsed() ? "button-suspended" : "" ?>" href="<?= $db_myadmin_link ?>" target="_blank">
						<i class="fas fa-database icon-orange"></i>phpMyAdmin
					</a>
				<?php } ?>
				<?php if ($_SESSION["DB_SYSTEM"] === "pgsql" || $_SESSION["DB_SYSTEM"] === "mysql,pgsql" || $_SESSION["DB_SYSTEM"] === "pgsql,mysql") { ?>
					<a class="button button-secondary <?= ipUsed() ? "button-suspended" : "" ?>" href="<?= $db_pgadmin_link ?>" target="_blank">
						<i class="fas fa-database icon-orange"></i>phpPgAdmin
					</a>
				<?php } ?>
				<?php if (ipUsed()) { ?>
					<a target="_blank" href="https://DevITcp.com/docs/server-administration/databases.html#why-i-can-t-use-http-ip-phpmyadmin">
						<i class="fas fa-circle-question"></i>
					</a>
				<?php } ?>
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
					<li data-entity="sort-charset">
						<span class="name"><?= _("Charset") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
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
					<li data-entity="sort-server">
						<span class="name"><?= _("Host") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-user">
						<span class="name"><?= _("Username") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/db/" method="post">
						<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
						<select class="form-select" name="action">
							<option value=""><?= _("Apply to selected") ?></option>
							<?php if ($_SESSION["userContext"] === "admin") { ?>
								<option value="rebuild"><?= _("Rebuild All") ?></option>
								<option value="suspend"><?= _("Suspend All") ?></option>
								<option value="unsuspend"><?= _("Unsuspend All") ?></option>
							<?php } ?>
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

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Databases") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>" <?= $display_mode ?>>
			</div>
			<div class="units-table-cell"><?= _("Name") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("Disk") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Type") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Username") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Hostname") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Charset") ?></div>
		</div>

		<!-- Begin database list item loop -->
		<?php
			list($http_host, $port) = explode(':', $_SERVER["HTTP_HOST"].":");
			foreach ($data as $key => $value) {
				++$i;
				if ($data[$key]['SUSPENDED'] == 'yes') {
					$status = 'suspended';
					$spnd_action = 'unsuspend';
					$spnd_action_title = _('Unsuspend');
					$spnd_icon = 'fa-play';
					$spnd_icon_class = 'icon-green';
					$spnd_confirmation = _('Are you sure you want to unsuspend database %s?') ;
				} else {
					$status = 'active';
					$spnd_action = 'suspend';
					$spnd_action_title = _('Suspend');
					$spnd_icon = 'fa-pause';
					$spnd_icon_class = 'icon-highlight';
					$spnd_confirmation = _('Are you sure you want to suspend database %s?') ;
				}
				if ($data[$key]['HOST'] != 'localhost' ) $http_host = $data[$key]['HOST'];
				if ($data[$key]['TYPE'] == 'mysql') $db_admin = "phpMyAdmin";
				if ($data[$key]['TYPE'] == 'mysql') $db_admin_link = "https://".$http_host."/phpmyadmin/";
				if (($data[$key]['TYPE'] == 'mysql') && (!empty($_SESSION['DB_PMA_ALIAS']))) $db_admin_link = $_SESSION['DB_PMA_ALIAS'];
				if ($data[$key]['TYPE'] == 'pgsql') $db_admin = "phpPgAdmin";
				if ($data[$key]['TYPE'] == 'pgsql') $db_admin_link = "https://".$http_host."/phppgadmin/";
				if (($data[$key]['TYPE'] == 'pgsql') && (!empty($_SESSION['DB_PGA_ALIAS']))) $db_admin_link = $_SESSION['DB_PGA_ALIAS'];
			?>
			<div class="units-table-row <?php if ($data[$key]['SUSPENDED'] == 'yes') echo 'disabled'; ?> js-unit"
				data-sort-date="<?= strtotime($data[$key]['DATE'].' '.$data[$key]['TIME']) ?>"
				data-sort-name="<?= $key ?>"
				data-sort-disk="<?= $data[$key]["U_DISK"] ?>"
				data-sort-user="<?= $data[$key]["DBUSER"] ?>"
				data-sort-server="<?= $data[$key]["HOST"] ?>"
				data-sort-charset="<?= $data[$key]["CHARSET"] ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="database[]" value="<?= $key ?>" <?= $display_mode ?>>
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Name") ?>:</span>
					<?php if ($read_only === "true" || $data[$key]["SUSPENDED"] == "yes") { ?>
						<?= $key ?>
					<?php } else { ?>
						<a href="/edit/db/?database=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Edit Database") ?>: <?= $key ?>">
							<?= $key ?>
						</a>
					<?php } ?>
				</div>
				<div class="units-table-cell">
					<?php if (!$read_only) { ?>
						<ul class="units-table-row-actions">
							<?php if ($data[$key]["SUSPENDED"] == "no") { ?>
								<li class="units-table-row-action shortcut-enter" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="/edit/db/?database=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
										title="<?= _("Edit Database") ?>"
									>
										<i class="fas fa-pencil icon-orange"></i>
										<span class="u-hide-desktop"><?= _("Edit Database") ?></span>
									</a>
								</li>
							<?php } ?>
							<?php if ($data[$key]['TYPE'] == 'mysql' && isset($_SESSION['PHPMYADMIN_KEY']) && $_SESSION['PHPMYADMIN_KEY'] != '' && !ipUsed()) { $time = time(); ?>
								<li class="units-table-row-action shortcut-enter" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="<?= $db_myadmin_link?>DevIT-sso.php?database=<?= $key ?>&user=<?= $user_plain?>&exp=<?= $time?>&DevIT_token=<?=password_hash($key.$user_plain.$_SESSION['user_combined_ip'].$time.$_SESSION['PHPMYADMIN_KEY'], PASSWORD_DEFAULT) ?>"
										title="phpMyAdmin" target="_blank"
									>
										<i class="fas fa-right-to-bracket icon-orange"></i>
										<span class="u-hide-desktop">phpMyAdmin</span>
									</a>
								</li>
							<?php } ?>
							<li class="units-table-row-action shortcut-enter" data-key-action="href">
								<a
									class="units-table-row-action-link"
									href="/download/database/?database=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("Download Database") ?>"
								>
									<i class="fas fa-download icon-orange"></i>
									<span class="u-hide-desktop"><?= _("Download Database") ?></span>
								</a>
							</li>
							<li class="units-table-row-action shortcut-s" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/<?= $spnd_action ?>/db/?database=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
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
									href="/delete/db/?database=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
									title="<?= _("Delete") ?>"
									data-confirm-title="<?= _("Delete") ?>"
									data-confirm-message="<?= sprintf(_("Are you sure you want to delete database %s?"), $key) ?>"
								>
									<i class="fas fa-trash icon-red"></i>
									<span class="u-hide-desktop"><?= _("Delete") ?></span>
								</a>
							</li>
						</ul>
					<?php } ?>
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
					<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
					<?= $data[$key]["TYPE"] ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Username") ?>:</span>
					<?= $data[$key]["DBUSER"] ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Hostname") ?>:</span>
					<?= $data[$key]["HOST"] ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Charset") ?>:</span>
					<?= $data[$key]["CHARSET"] ?>
				</div>
			</div>
		<?php } ?>
	</div>

	<div class="units-table-footer">
		<p>
			<?php printf(ngettext("%d database", "%d databases", $i), $i); ?>
		</p>
	</div>

</div>
