<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/backup/"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<a href="/schedule/restore/?token=<?= $_SESSION["token"] ?>&backup=<?= htmlentities($_GET["backup"]) ?>" class="button button-secondary"><i class="fas fa-arrow-rotate-left icon-green"></i><?= _("Restore All") ?></a>
		</div>
		<div class="toolbar-right">
			<form x-data x-bind="BulkEdit" action="/bulk/restore/" method="post">
				<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
				<input type="hidden" name="backup" value="<?= htmlentities($_GET["backup"]) ?>">
				<select class="form-select" name="action">
					<option value=""><?= _("Apply to selected") ?></option>
					<option value="restore"><?= _("Restore") ?></option>
				</select>
				<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
					<i class="fas fa-arrow-right"></i>
				</button>
			</form>
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
	<div class="units js-units-container">
		<div class="header units-header">
			<div class="l-unit__col l-unit__col--right">
				<div>
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>">
					</div>
					<div class="clearfix l-unit__stat-col--left compact-4"><b><?= _("Type") ?></b></div>
					<div class="clearfix l-unit__stat-col--left wide-7"><b><?= _("Details") ?></b></div>
					<div class="clearfix l-unit__stat-col--left u-text-right compact-4"><b><?= _("Restore") ?></b></div>
				</div>
			</div>
		</div>

		<!-- List web domains -->
		<?php
			$backup = htmlentities($_GET['backup']);
			$web = explode(',',$data[$backup]['WEB']);
			foreach ($web as $key) {
				if (!empty($key)) {
					++$i;
			?>
			<div class="l-unit animate__animated animate__fadeIn">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="web[]" value="<?= $key ?>">
					</div>
					<div class="clearfix l-unit__stat-col--left compact-4">
						<div class="l-unit__stat-col l-unit__stat-col--left"><?= _("Web Domain") ?></div>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-7">
						<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><?= $key ?></b></div>
					</div>
					<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<div class="actions-panel__col actions-panel__list shortcut-enter" data-key-action="href">
									<a href="/schedule/restore/?backup=<?= $backup ?>&type=web&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
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
			$mail = explode(',',$data[$backup]['MAIL']);
			foreach ($mail as $key) {
				if (!empty($key)) {
			?>
			<div class="l-unit">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check2<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="mail[]" value="<?= $key ?>">
					</div>
					<div class="clearfix l-unit__stat-col--left compact-4">
						<div class="l-unit__stat-col l-unit__stat-col--left"><?= _("Mail Domain") ?></div>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-7">
						<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><?= $key ?></b></div>
					</div>
					<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<div class="actions-panel__col actions-panel__list shortcut-enter" data-key-action="href">
									<a href="/schedule/restore/?backup=<?= $backup ?>&type=mail&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
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
			$dns = explode(',',$data[$backup]['DNS']);
			foreach ($dns as $key) {
				if (!empty($key)) {
			?>
			<div class="l-unit">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check3<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="dns[]" value="<?= $key ?>">
					</div>
					<div class="clearfix l-unit__stat-col--left compact-4">
						<div class="l-unit__stat-col l-unit__stat-col--left"><?= _("DNS Zone") ?></div>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-7">
						<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><?= $key ?></b></div>
					</div>
					<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<div class="actions-panel__col actions-panel__list shortcut-enter" data-key-action="href">
									<a href="/schedule/restore/?backup=<?= $backup ?>&type=dns&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
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
			$db = explode(',',$data[$backup]['DB']);
			foreach ($db as $key) {
				if (!empty($key)) {
			?>
			<div class="l-unit">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check4<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="db[]" value="<?= $key ?>">
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
								<div class="actions-panel__col actions-panel__list shortcut-enter" data-key-action="href">
									<a href="/schedule/restore/?backup=<?= $backup ?>&type=db&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
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
		<?php if (!empty($data[$backup]["CRON"])) {
		if (!empty($key)) { ?>
			<div class="l-unit">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check5<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="check" value="<?= $key ?>">
					</div>
					<div class="clearfix l-unit__stat-col--left compact-4">
						<div class="l-unit__stat-col l-unit__stat-col--left"><?= _("Cron Jobs") ?></div>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-7">
						<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><?= _("Jobs") ?></b></div>
					</div>
					<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<div class="actions-panel__col actions-panel__list shortcut-enter" data-key-action="href">
									<a href="/schedule/restore/?backup=<?= $backup ?>&type=cron&object=records&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
										<i class="fas fa-arrow-rotate-left icon-green icon-dim"></i>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php }} ?>

		<!-- List user directories -->
		<?php
			$udir = explode(',',$data[$backup]['UDIR']);
			foreach ($udir as $key) {
				if (!empty($key)) {
			?>
			<div class="l-unit">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check6<?= $i ?>" class="js-unit-checkbox" type="checkbox" name="udir[]" value="<?= $key ?>">
					</div>
					<div class="clearfix l-unit__stat-col--left compact-4">
						<div class="l-unit__stat-col l-unit__stat-col--left"><?= _("User Directory") ?></div>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-7">
						<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><?= $key ?></b></div>
					</div>
					<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<div class="actions-panel__col actions-panel__list shortcut-enter" data-key-action="href">
									<a href="/schedule/restore/?backup=<?= $backup ?>&type=udir&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
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
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d item", "%d items", $i), $i); ?>
		</p>
	</div>
</footer>
