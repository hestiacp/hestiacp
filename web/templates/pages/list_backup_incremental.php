<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/schedule/backup/?token=<?= $_SESSION["token"] ?>" class="button button-secondary"><i class="fas fa-circle-plus icon-green"></i><?= _("Create Backup") ?></a>
				<a href="/list/backup/exclusions/" class="button button-secondary"><i class="fas fa-folder-minus icon-orange"></i><?= _("backup exclusions") ?></a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<?php if ($read_only !== "true") { ?>
				<form x-data x-bind="BulkEdit" action="/bulk/backup/" method="post">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<select class="form-select" name="action">
						<option value=""><?= _("apply to selected") ?></option>
						<option value="delete"><?= _("delete") ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= _("apply to selected") ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			<?php } ?>
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
				<div class="clearfix l-unit__stat-col--left wide-4"><b><?= _("Snapshot") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact-4 u-text-right"><b>&nbsp;</b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Date") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Type") ?></b></div>
			</div>
		</div>
	</div>

	<!-- Begin user backup list item loop -->
	<?php
		$i =0;
		foreach ($data as $key => $value) {
			$i++;
	?>
		<div class="l-unit animate__animated animate__fadeIn">
			<div class="l-unit__col l-unit__col--right">
				<div>
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check<?= $i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="backup[]" value="<?= $key ?>" <?= $display_mode ?>>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-4 truncate">
					<b>
						<?php if ($read_only === "true") { ?>
							<?= $value['short_id'] ?>
						<?php } else { ?>
							<a href="/list/backup/incremental/?snapshot=<?= $value['short_id'] ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("restore") ?>"><?= $value['short_id'] ?></a>
						<?php } ?>
					</b>
					</div>
					<!-- START QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
						<?php if ($_SESSION["userContext"] === "admin" && $_SESSION["look"] === "admin" && $read_only === "true") { ?>
							<!-- Restrict ability to restore or delete backups when impersonating 'admin' account -->
							&nbsp;
						<?php } else { ?>
							<div class="actions-panel__col actions-panel__list shortcut-enter" key-action="href"><a href="/list/backup/incremental/?snapshot=<?= $value['short_id'] ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("restore") ?>"><i class="fas fa-arrow-rotate-left icon-green icon-dim"></i></a></div>
						<?php } ?>
					</div>
					<!-- END QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= translate_date($value['time']) ?></b></div>
					<div class="clearfix l-unit__stat-col--left u-text-center">Restic</div>
				</div>
			</div>
		</div>
	<?php
	} ?>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d backup", "%d backups", $i), $i); ?>
		</p>
	</div>
</footer>
