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
				<a href="/add/db/" class="button button-secondary" id="btn-create">
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
					<a target="_blank" href="https://docs.hestiacp.com/admin_docs/database.html#why-i-can-t-use-http-ip-phpmyadmin">
						<i class="fas fa-circle-question"></i>
					</a>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle" title="<?= _("Sort items") ?>">
					<?= _("sort by") ?>:
					<b>
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?=$label;?> <i class="fas fa-arrow-down-a-z"></i>
					</b>
				</button>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn u-hidden">
					<li entity="sort-charset"><span class="name"><?= _("Charset") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-date" sort_as_int="1"><span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-disk" sort_as_int="1"><span class="name"><?= _("Disk") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-name"><span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-server"><span class="name"><?= _("Host") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-user"><span class="name"><?= _("Username") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
				</ul>
				<?php if ($read_only !== 'true') {?>
					<form x-bind="BulkEdit" action="/bulk/db/" method="post">
						<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
						<select class="form-select" name="action">
							<option value=""><?= _("apply to selected") ?></option>
							<?php if ($_SESSION['userContext'] === 'admin') {?>
								<option value="rebuild"><?= _("rebuild") ?></option>
								<option value="suspend"><?= _("suspend") ?></option>
								<option value="unsuspend"><?= _("unsuspend") ?></option>
							<?php } ?>
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
				<input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>" <?=$display_mode;?>>
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
				$spnd_icon = 'fa-play';
				$spnd_confirmation = _('UNSUSPEND_DATABASE_CONFIRMATION') ;
			} else {
				$status = 'active';
				$spnd_action = 'suspend';
				$spnd_icon = 'fa-pause';
				$spnd_confirmation = _('SUSPEND_DATABASE_CONFIRMATION') ;
			}
			if ($data[$key]['HOST'] != 'localhost' ) $http_host = $data[$key]['HOST'];
			if ($data[$key]['TYPE'] == 'mysql') $db_admin = "phpMyAdmin";
			if ($data[$key]['TYPE'] == 'mysql') $db_admin_link = "https://".$http_host."/phpmyadmin/";
			if (($data[$key]['TYPE'] == 'mysql') && (!empty($_SESSION['DB_PMA_ALIAS']))) $db_admin_link = $_SESSION['DB_PMA_ALIAS'];
			if ($data[$key]['TYPE'] == 'pgsql') $db_admin = "phpPgAdmin";
			if ($data[$key]['TYPE'] == 'pgsql') $db_admin_link = "https://".$http_host."/phppgadmin/";
			if (($data[$key]['TYPE'] == 'pgsql') && (!empty($_SESSION['DB_PGA_ALIAS']))) $db_admin_link = $_SESSION['DB_PGA_ALIAS'];
		?>
		<div class="l-unit <?php if($status == 'suspended') echo 'l-unit--suspended'; ?> animate__animated animate__fadeIn" v_unit_id="<?=$key?>" v_section="db"
			sort-date="<?=strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])?>" sort-name="<?=$key?>" sort-disk="<?=$data[$key]['U_DISK']?>"
			sort-user="<?=$data[$key]['DBUSER']?>" sort-server="<?=$data[$key]['HOST']?>" sort-charset="<?=$data[$key]['CHARSET']?>">
			<div class="l-unit__col l-unit__col--right">
				<div>
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check<?=$i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="database[]" value="<?=$key?>" <?=$display_mode;?>>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-3 truncate">
						<?php if (($read_only === 'true') || ($data[$key]['SUSPENDED'] == 'yes')) {?>
							<b><?=$key?></b>
						<?php } else { ?>
							<b><a href="/edit/db/?database=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Editing Database") ?>: <?=$key?>"><?=$key?></a></b>
						<?php } ?>
					</div>
					<!-- START QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left u-text-right compact-3">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<?php if ($read_only === 'true') {?>
									<!-- Restrict the ability to edit, delete, or suspend domain items when impersonating 'admin' user -->
									&nbsp;
								<?php } else { ?>
									<?php if ($data[$key]['SUSPENDED'] == 'no') {?>
										<div class="actions-panel__col actions-panel__logs shortcut-enter" key-action="href"><a href="/edit/db/?database=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Editing Database") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a></div>
									<?php } ?>
									<?php if ($data[$key]['TYPE'] == 'mysql' && isset($_SESSION['PHPMYADMIN_KEY']) && $_SESSION['PHPMYADMIN_KEY'] != '' && !ipUsed()) { $time = time(); ?>
										<div class="actions-panel__col actions-panel__logs shortcut-enter" key-action="href"><a target="_blank" href="<?=$db_myadmin_link;?>/hestia-sso.php?database=<?=$key;?>&user=<?=$user_plain;?>&exp=<?=$time;?>&hestia_token=<?=password_hash($key.$user_plain.$_SESSION['user_combined_ip'].$time.$_SESSION['PHPMYADMIN_KEY'], PASSWORD_DEFAULT)?>" title="<?= _("phpMyAdmin") ?>"><i class="fas fa-right-to-bracket icon-orange icon-dim"></i></a></div>
									<?php } ?>
									<div class="actions-panel__col actions-panel__suspend shortcut-s" key-action="js">
										<a id="<?=$spnd_action ?>_link_<?=$i?>" class="data-controls do_<?=$spnd_action?>" title="<?=_($spnd_action)?>">
											<i class="fas <?=$spnd_icon?> icon-highlight icon-dim do_<?=$spnd_action?>"></i>
											<input type="hidden" name="<?=$spnd_action?>_url" value="/<?=$spnd_action?>/db/?database=<?=$key?>&token=<?=$_SESSION['token']?>">
											<div id="<?=$spnd_action?>_dialog_<?=$i?>" class="dialog js-confirm-dialog-suspend" title="<?= _("Confirmation") ?>">
												<p><?=sprintf($spnd_confirmation,$key)?></p>
											</div>
										</a>
									</div>
									<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
										<a id="delete_link_<?=$i?>" class="data-controls do_delete" title="<?= _("delete") ?>">
											<i class="fas fa-trash icon-red icon-dim do_delete"></i>
											<input type="hidden" name="delete_url" value="/delete/db/?database=<?=$key?>&token=<?=$_SESSION['token']?>">
											<div id="delete_dialog_<?=$i?>" class="dialog js-confirm-dialog-delete" title="<?= _("Confirmation") ?>">
												<p><?=sprintf(_('DELETE_DATABASE_CONFIRMATION'),$key)?></p>
											</div>
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

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d SQL database", "%d SQL databases", $i), $i); ?>
		</p>
	</div>
</footer>
