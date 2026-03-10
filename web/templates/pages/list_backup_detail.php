<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/backup/">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
			<a href="/schedule/restore/?<?= tohtml(http_build_query(array("token" => $_SESSION["token"], "backup" => $_GET["backup"]))) ?>" class="button button-secondary">
				<i class="fas fa-arrow-rotate-left icon-green"></i><?= tohtml( _("Restore All")) ?>
			</a>
		</div>
		<div class="toolbar-right">
			<form x-data x-bind="BulkEdit" action="/bulk/restore/" method="post">
				<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
				<input type="hidden" name="backup" value="<?= tohtml($_GET["backup"]) ?>">
				<select class="form-select" name="action">
					<option value=""><?= tohtml( _("Apply to selected")) ?></option>
					<option value="restore"><?= tohtml( _("Restore")) ?></option>
				</select>
				<button type="submit" class="toolbar-input-submit" title="<?= tohtml( _("Apply to selected")) ?>">
					<i class="fas fa-arrow-right"></i>
				</button>
			</form>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
						<input type="search" class="form-control js-search-input" name="q" value="<?= tohtml($_GET['q'] ?? '') ?>" title="<?= tohtml( _("Search")) ?>">
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

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= tohtml( _("Backup Details")) ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= tohtml( _("Select all")) ?>" <?= tohtml($display_mode) ?>>
			</div>
			<div class="units-table-cell"><?= tohtml( _("Type")) ?></div>
			<div class="units-table-cell"><?= tohtml( _("Details")) ?></div>
			<div class="units-table-cell"><?= tohtml( _("Restore")) ?></div>
		</div>

			<!-- List web domains -->
			<?php
				$item_count = 0;
				$backup = $_GET['backup'];
				$web_index = 0;
				$web = explode(',',$data[$backup]['WEB']);
				foreach ($web as $key) {
					if (!empty($key)) {
						++$web_index;
						++$item_count;
				?>
				<div class="units-table-row js-unit">
					<div class="units-table-cell">
						<div>
							<input id="check-web<?= tohtml($web_index) ?>" class="js-unit-checkbox" type="checkbox" name="web[]" value="<?= tohtml($key) ?>">
							<label for="check-web<?= tohtml($web_index) ?>" class="u-hide-desktop"><?= tohtml( _("Select")) ?></label>
						</div>
					</div>
				<div class="units-table-cell units-table-heading-cell">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Type")) ?>:</span>
					<?= tohtml( _("Web Domain")) ?>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("Details")) ?>:</span>
					<?= tohtml($key) ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/schedule/restore/?<?= tohtml(http_build_query(array("backup" => $backup, "type" => "web", "object" => $key, "token" => $_SESSION["token"]))) ?>"
								title="<?= tohtml( _("Restore")) ?>"
								data-confirm-title="<?= tohtml( _("Restore")) ?>"
								data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to restore %s?"), $key)) ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-green"></i>
								<span class="u-hide-desktop"><?= tohtml( _("Restore")) ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }} ?>

		<!-- List mail domains -->
			<?php
				$mail_index = 0;
				$mail = explode(',',$data[$backup]['MAIL']);
				foreach ($mail as $key) {
					if (!empty($key)) {
						++$mail_index;
						++$item_count;
				?>
				<div class="units-table-row js-unit">
					<div class="units-table-cell">
						<div>
							<input id="check-mail<?= tohtml($mail_index) ?>" class="js-unit-checkbox" type="checkbox" name="mail[]" value="<?= tohtml($key) ?>">
							<label for="check-mail<?= tohtml($mail_index) ?>" class="u-hide-desktop"><?= tohtml( _("Select")) ?></label>
						</div>
					</div>
				<div class="units-table-cell units-table-heading-cell">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Type")) ?>:</span>
					<?= tohtml( _("Mail Domain")) ?>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("Details")) ?>:</span>
					<?= tohtml($key) ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/schedule/restore/?<?= tohtml(http_build_query(array("backup" => $backup, "type" => "mail", "object" => $key, "token" => $_SESSION["token"]))) ?>"
								title="<?= tohtml( _("Restore")) ?>"
								data-confirm-title="<?= tohtml( _("Restore")) ?>"
								data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to restore %s?"), $key)) ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-green"></i>
								<span class="u-hide-desktop"><?= tohtml( _("Restore")) ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }} ?>

		<!-- List DNS zones -->
			<?php
				$dns_index = 0;
				$dns = explode(',',$data[$backup]['DNS']);
				foreach ($dns as $key) {
					if (!empty($key)) {
						++$dns_index;
						++$item_count;
				?>
				<div class="units-table-row js-unit">
					<div class="units-table-cell">
						<div>
							<input id="check-dns<?= tohtml($dns_index) ?>" class="js-unit-checkbox" type="checkbox" name="dns[]" value="<?= tohtml($key) ?>">
							<label for="check-dns<?= tohtml($dns_index) ?>" class="u-hide-desktop"><?= tohtml( _("Select")) ?></label>
						</div>
					</div>
				<div class="units-table-cell units-table-heading-cell">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Type")) ?>:</span>
					<?= tohtml( _("DNS Zone")) ?>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("Details")) ?>:</span>
					<?= tohtml($key) ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/schedule/restore/?<?= tohtml(http_build_query(array("backup" => $backup, "type" => "dns", "object" => $key, "token" => $_SESSION["token"]))) ?>"
								title="<?= tohtml( _("Restore")) ?>"
								data-confirm-title="<?= tohtml( _("Restore")) ?>"
								data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to restore %s?"), $key)) ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-green"></i>
								<span class="u-hide-desktop"><?= tohtml( _("Restore")) ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }} ?>

		<!-- List Databases -->
			<?php
				$db_index = 0;
				$db = explode(',',$data[$backup]['DB']);
				foreach ($db as $key) {
					if (!empty($key)) {
						++$db_index;
						++$item_count;
				?>
				<div class="units-table-row js-unit">
					<div class="units-table-cell">
						<div>
							<input id="check-db<?= tohtml($db_index) ?>" class="js-unit-checkbox" type="checkbox" name="db[]" value="<?= tohtml($key) ?>">
							<label for="check-db<?= tohtml($db_index) ?>" class="u-hide-desktop"><?= tohtml( _("Select")) ?></label>
						</div>
					</div>
				<div class="units-table-cell units-table-heading-cell">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Type")) ?>:</span>
					<?= tohtml( _("Database")) ?>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("Details")) ?>:</span>
					<?= tohtml($key) ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/schedule/restore/?<?= tohtml(http_build_query(array("backup" => $backup, "type" => "db", "object" => $key, "token" => $_SESSION["token"]))) ?>"
								title="<?= tohtml( _("Restore")) ?>"
								data-confirm-title="<?= tohtml( _("Restore")) ?>"
								data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to restore %s?"), $key)) ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-green"></i>
								<span class="u-hide-desktop"><?= tohtml( _("Restore")) ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }} ?>

			<!-- List Cron Jobs -->
			<?php
				$cron_index = 0;
				if (!empty($data[$backup]["CRON"])) {
					++$cron_index;
					++$item_count;
			?>
				<div class="units-table-row js-unit">
					<div class="units-table-cell">
						<div>
							<input id="check-cron<?= tohtml($cron_index) ?>" class="js-unit-checkbox" type="checkbox" name="cron" value="yes">
							<label for="check-cron<?= tohtml($cron_index) ?>" class="u-hide-desktop"><?= tohtml( _("Select")) ?></label>
						</div>
					</div>
				<div class="units-table-cell units-table-heading-cell">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Type")) ?>:</span>
					<?= tohtml( _("Cron Jobs")) ?>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("Details")) ?>:</span>
					<?= tohtml( _("Jobs")) ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/schedule/restore/?<?= tohtml(http_build_query(array("backup" => $backup, "type" => "cron", "object" => "records", "token" => $_SESSION["token"]))) ?>"
									title="<?= tohtml( _("Restore")) ?>"
									data-confirm-title="<?= tohtml( _("Restore")) ?>"
									data-confirm-message="<?= tohtml( _("Are you sure you want to restore cron jobs?")) ?>"
								>
									<i class="fas fa-arrow-rotate-left icon-green"></i>
									<span class="u-hide-desktop"><?= tohtml( _("Restore")) ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<?php } ?>

		<!-- List user directories -->
			<?php
				$udir_index = 0;
				$udir = explode(',',$data[$backup]['UDIR']);
				foreach ($udir as $key) {
					if (!empty($key)) {
						++$udir_index;
						++$item_count;
				?>
				<div class="units-table-row js-unit">
					<div class="units-table-cell">
						<div>
							<input id="check-udir<?= tohtml($udir_index) ?>" class="js-unit-checkbox" type="checkbox" name="udir[]" value="<?= tohtml($key) ?>">
							<label for="check-udir<?= tohtml($udir_index) ?>" class="u-hide-desktop"><?= tohtml( _("Select")) ?></label>
						</div>
					</div>
				<div class="units-table-cell units-table-heading-cell">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Type")) ?>:</span>
					<?= tohtml( _("User Directory")) ?>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("Details")) ?>:</span>
					<?= tohtml($key) ?>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/schedule/restore/?<?= tohtml(http_build_query(array("backup" => $backup, "type" => "udir", "object" => $key, "token" => $_SESSION["token"]))) ?>"
								title="<?= tohtml( _("Restore")) ?>"
								data-confirm-title="<?= tohtml( _("Restore")) ?>"
								data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to restore %s?"), $key)) ?>"
							>
								<i class="fas fa-arrow-rotate-left icon-green"></i>
								<span class="u-hide-desktop"><?= tohtml( _("Restore")) ?></span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		<?php }} ?>
	</div>

	<div class="units-table-footer">
		<p>
				<?php printf(ngettext("%d item", "%d items", $item_count), $item_count); ?>
			</p>
		</div>

</div>
