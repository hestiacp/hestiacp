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
					<a target="_blank" href="https://hestiacp.com/docs/server-administration/databases.html#why-i-can-t-use-http-ip-phpmyadmin">
						<i class="fas fa-circle-question"></i>
					</a>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<b>
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?=$label;?> <i class="fas fa-arrow-down-a-z"></i>
					</b>
				</button>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn js-sorting-menu u-hidden">
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
	<div class="units">
		<div class="header units-header">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>" <?= $display_mode ?>>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("Name") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-right compact-3"><b>&nbsp;</b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Disk") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact"><b><?= _("Type") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center wide"><b><?= _("Username") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Hostname") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Charset") ?></b></div>
			</div>
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
					$spnd_confirmation = _('Are you sure you want to unsuspend database %s?') ;
				} else {
					$status = 'active';
					$spnd_action = 'suspend';
					$spnd_action_title = _('Suspend');
					$spnd_icon = 'fa-pause';
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
			<div class="l-unit <?php if($status == 'suspended') echo 'l-unit--suspended'; ?> animate__animated animate__fadeIn js-unit"
				data-sort-date="<?=strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])?>"
				data-sort-name="<?=$key?>"
				data-sort-disk="<?=$data[$key]['U_DISK']?>"
				data-sort-user="<?=$data[$key]['DBUSER']?>"
				data-sort-server="<?=$data[$key]['HOST']?>"
				data-sort-charset="<?=$data[$key]['CHARSET']?>">
				<div class="l-unit__col l-unit__col--right">
					<div>
						<div class="clearfix l-unit__stat-col--left super-compact">
							<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="database[]" value="<?= $key ?>" <?= $display_mode ?>>
						</div>
						<div class="clearfix l-unit__stat-col--left wide-3 truncate">
							<?php if ($read_only === "true" || $data[$key]["SUSPENDED"] == "yes") { ?>
								<b><?= $key ?></b>
							<?php } else { ?>
								<b><a href="/edit/db/?database=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Edit Database") ?>: <?= $key ?>"><?= $key ?></a></b>
							<?php } ?>
						</div>
						<!-- START QUICK ACTION TOOLBAR AREA -->
						<div class="clearfix l-unit__stat-col--left u-text-right compact-3">
							<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
								<div class="actions-panel clearfix">
									<?php if ($read_only === "true") { ?>
										<!-- Restrict the ability to edit, delete, or suspend domain items when impersonating 'admin' user -->
										&nbsp;
									<?php } else { ?>
										<?php if ($data[$key]['SUSPENDED'] == 'no') {?>
											<div class="actions-panel__col actions-panel__logs shortcut-enter" data-key-action="href"><a href="/edit/db/?database=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Edit Database") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a></div>
										<?php } ?>
										<?php if ($data[$key]['TYPE'] == 'mysql' && isset($_SESSION['PHPMYADMIN_KEY']) && $_SESSION['PHPMYADMIN_KEY'] != '' && !ipUsed()) { $time = time(); ?>
											<div class="actions-panel__col actions-panel__logs shortcut-enter" data-key-action="href"><a target="_blank" href="<?=$db_myadmin_link;?>hestia-sso.php?database=<?=$key;?>&user=<?=$user_plain;?>&exp=<?=$time;?>&hestia_token=<?=password_hash($key.$user_plain.$_SESSION['user_combined_ip'].$time.$_SESSION['PHPMYADMIN_KEY'], PASSWORD_DEFAULT)?>" title="phpMyAdmin"><i class="fas fa-right-to-bracket icon-orange icon-dim"></i></a></div>
										<?php } ?>
										<div class="actions-panel__col actions-panel__suspend shortcut-s" data-key-action="js">
											<a
												class="data-controls js-confirm-action"
												href="/<?=$spnd_action?>/db/?database=<?=$key?>&token=<?=$_SESSION['token']?>"
												data-confirm-title="<?= $spnd_action_title ?>"
												data-confirm-message="<?= sprintf($spnd_confirmation, $key) ?>"
											>
												<i class="fas <?= $spnd_icon ?> icon-highlight icon-dim"></i>
											</a>
										</div>
										<div class="actions-panel__col actions-panel__delete shortcut-delete" data-key-action="js">
											<a
												class="data-controls js-confirm-action"
												href="/delete/db/?database=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
												data-confirm-title="<?= _("Delete") ?>"
												data-confirm-message="<?= sprintf(_("Are you sure you want to delete database %s?"), $key) ?>"
											>
												<i class="fas fa-trash icon-red icon-dim"></i>
											</a>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
						<!-- END QUICK ACTION TOOLBAR AREA -->
						<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= humanize_usage_size($data[$key]["U_DISK"]) ?></b> <span class="u-text-small"><?= humanize_usage_measure($data[$key]["U_DISK"]) ?></span></div>
						<div class="clearfix l-unit__stat-col--left u-text-center compact"><?= $data[$key]["TYPE"] ?></div>
						<div class="clearfix l-unit__stat-col--left u-text-center wide"><b><?= $data[$key]["DBUSER"] ?></b></div>
						<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= $data[$key]["HOST"] ?></b></div>
						<div class="clearfix l-unit__stat-col--left u-text-center"><?= $data[$key]["CHARSET"] ?></div>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d database", "%d databases", $i), $i); ?>
		</p>
	</div>
</footer>
