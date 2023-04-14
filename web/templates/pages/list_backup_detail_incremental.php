<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/backup/incremental/"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<a href="/schedule/restore/incremental/?token=<?= $_SESSION["token"] ?>&snapshot=<?= htmlentities($_GET["snapshot"]) ?>" class="button button-secondary"><i class="fas fa-arrow-rotate-left icon-green"></i><?= _("Restore All") ?></a>
		</div>
		<div class="toolbar-right">
			<form x-data x-bind="BulkEdit" action="/bulk/restore/" method="post">
				<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
				<input type="hidden" name="backup" value="<?= htmlentities($_GET["snapshot"]) ?>">
				<select class="form-select" name="action">
					<option value=""><?= _("apply to selected") ?></option>
					<option value="restore"><?= _("restore") ?></option>
				</select>
				<button type="submit" class="toolbar-input-submit">
					<i class="fas fa-arrow-right"></i>
				</button>
			</form>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<input type="search" class="form-control js-search-input" name="q" value="<? echo isset($_POST['q']) ? htmlspecialchars($_POST['q']) : '' ?>">
					<button type="submit" class="toolbar-input-submit" value=""><i class="fas fa-magnifying-glass"></i></button>
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
					<input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4"><b><?= _("Type") ?></b></div>
				<div class="clearfix l-unit__stat-col--left wide-7"><b><?= _("Details") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-right compact-4"><b><?= _("Restore") ?></b></div>
			</div>
		</div>
	</div>

	<!-- List web domains -->
	<?php
		$web = explode(',',$data['snapshot']['WEB']);
		foreach ($web as $key) {
			if (!empty($key)) {
				++$i;
		?>
		<div class="l-unit animate__animated animate__fadeIn">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?= $i ?>" class="ch-toggle" type="checkbox" name="web[]" value="<?= $key ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4">
					<div class="l-unit__stat-col l-unit__stat-col--left"><?= _("Web domain") ?></div>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-7">
					<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><?= $key ?></b></div>
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__list shortcut-enter" key-action="href">
								<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=web&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
									<i class="fas fa-arrow-rotate-left icon-green icon-dim u-mr5"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php }} ?>

	<!-- List mail domains -->
	<?php
		$mail = explode(',',$data['snapshot']['MAIL']);
		foreach ($mail as $key) {
			if (!empty($key)) {
		?>
		<div class="l-unit">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check2<?= $i ?>" class="ch-toggle" type="checkbox" name="mail[]" value="<?= $key ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4">
					<div class="l-unit__stat-col l-unit__stat-col--left"><?= _("Mail domain") ?></div>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-7">
					<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><?= $key ?></b></div>
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__list shortcut-enter" key-action="href">
								<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=mail&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
									<i class="fas fa-arrow-rotate-left icon-green icon-dim"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php }} ?>

	<!-- List DNS zones -->
	<?php
		$dns = explode(',',$data['snapshot']['DNS']);
		foreach ($dns as $key) {
			if (!empty($key)) {
		?>
		<div class="l-unit">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check3<?= $i ?>" class="ch-toggle" type="checkbox" name="dns[]" value="<?= $key ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4">
					<div class="l-unit__stat-col l-unit__stat-col--left"><?= _("DNS domain") ?></div>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-7">
					<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><?= $key ?></b></div>
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__list shortcut-enter" key-action="href">
								<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=dns&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
									<i class="fas fa-arrow-rotate-left icon-green icon-dim"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php }} ?>

	<!-- List Databases -->
	<?php
		$db = explode(',',$data['snapshot']['DB']);
		foreach ($db as $key) {
			if (!empty($key)) {
		?>
		<div class="l-unit">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check4<?= $i ?>" class="ch-toggle" type="checkbox" name="db[]" value="<?= $key ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4">
					<div class="l-unit__stat-col l-unit__stat-col--left"><?= _("Database") ?></div>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-7">
					<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><?= $key ?></b></div>
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__list shortcut-enter" key-action="href">
								<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>>&type=db&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
									<i class="fas fa-arrow-rotate-left icon-green icon-dim"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php }} ?>

	<!-- List Cron Jobs -->
	<?php if (!empty($data['snapshot']["CRON"])) {
 	if (!empty($key)) { ?>
		<div class="l-unit">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check5<?= $i ?>" class="ch-toggle" type="checkbox" name="check" value="<?= $key ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4">
					<div class="l-unit__stat-col l-unit__stat-col--left"><?= _("Cron Records") ?></div>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-7">
					<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><?= "cron " . _("records") ?></b></div>
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__list shortcut-enter" key-action="href">
								<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=cron&object=records&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
									<i class="fas fa-arrow-rotate-left icon-green icon-dim"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php }} ?>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d item", "%d items", $i), $i); ?>
		</p>
	</div>
</footer>
