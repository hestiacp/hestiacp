<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/backup/"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<a href="/edit/backup/exclusions/" class="button button-secondary"><i class="fas fa-pencil icon-orange"></i><?= _("Editing Backup Exclusions") ?></a>
		</div>
		<div class="toolbar-right">
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
			<div class="clearfix l-unit__stat-col--left super-compact">&nbsp;</div>
			<div class="clearfix l-unit__stat-col--left wide-1"><b><?= _("Type") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact u-text-right"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("Value") ?></b></div>
		</div>
	</div>

	<div class="container units animate__animated animate__fadeIn">
		<!-- Begin list of backup exclusions by type -->
		<?php foreach ($data as $key => $value) { ?>
			<div class="l-unit header">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact">&nbsp;</div>
					<div class="clearfix l-unit__stat-col--left wide-1"><b><?= $key ?></b></div>
					<div class="clearfix l-unit__stat-col--left compact u-text-right"><b>&nbsp;</b></div>
					<div class="clearfix l-unit__stat-col--left wide-3">
						<?php
							if (empty($value)) echo _('no exclusions');
							foreach ($value as $ex_key => $ex_value) {
								echo '<b>'.$ex_key.' </b>'.$ex_value.'<br>';
							}
						?>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<footer class="app-footer">
</footer>
