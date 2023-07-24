<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($_SESSION["userContext"] === "admin" && $_SESSION["look"] === "admin") { ?>
				<a href="/list/user/" class="button button-secondary button-back js-button-back">
					<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
				</a>
			<?php } elseif ($_SESSION["userContext"] === "admin" && htmlentities($_GET["user"]) === "system") { ?>
				<a href="/list/server/" class="button button-secondary button-back js-button-back">
					<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
				</a>
			<?php } else { ?>
				<?php if ($_SESSION["userContext"] === "admin" && $_SESSION['look'] !== '' && $_GET["user"] !== "admin") { ?>
					<a href="/edit/user/?user=<?= htmlentities($_SESSION["look"]) ?>&token=<?= $_SESSION["token"] ?>" class="button button-secondary button-back js-button-back">
						<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
					</a>
				<?php } else { ?>
					<a href="/edit/user/?user=<?= htmlentities($_SESSION["user"]) ?>&token=<?= $_SESSION["token"] ?>" class="button button-secondary button-back js-button-back">
						<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
					</a>
				<?php } ?>
			<?php } ?>
			<?php if ($_SESSION['DEMO_MODE'] != "yes"){
			if (($_SESSION['userContext'] === 'admin') && (htmlentities($_GET['user']) !== 'admin')) { ?>
				<?php if (($_SESSION['userContext'] === 'admin') && ($_GET['user'] != '') && (htmlentities($_GET['user']) !== 'admin')) { ?>
					<?php if (htmlentities($_GET['user']) !== 'system') { ?>
						<a href="/list/log/auth/?user=<?= htmlentities($_GET['user']); ?>&token=<?= $_SESSION['token'] ?>" class="button button-secondary button-back js-button-back" title="<?= _("Login History") ?>">
							<i class="fas fa-binoculars icon-green"></i><?= _("Login History") ?>
						</a>
					<?php } ?>
				<?php } else { ?>
					<a href="/list/log/auth/" class="button button-secondary button-back js-button-back" title="<?= _("Login History") ?>">
						<i class="fas fa-binoculars icon-green"></i><?= _("Login History") ?>
					</a>
				<?php } ?>
			<?php } ?>
			<?php if ($_SESSION["userContext"] === "user") { ?>
				<a href="/list/log/auth/" class="button button-secondary button-back js-button-back" title="<?= _("Login History") ?>">
					<i class="fas fa-binoculars icon-green"></i><?= _("Login History") ?>
				</a>
			<?php }
			} ?>
		</div>
		<div class="toolbar-buttons">
			<a href="javascript:location.reload();" class="button button-secondary"><i class="fas fa-arrow-rotate-right icon-green"></i><?= _("Refresh") ?></a>
			<?php if ($_SESSION["userContext"] === "admin" && $_SESSION["look"] === "admin" && $_SESSION["POLICY_SYSTEM_PROTECTED_ADMIN"] === "yes") { ?>
				<!-- Hide delete buttons-->
			<?php } else { ?>
				<?php if ($_SESSION["userContext"] === "admin" || ($_SESSION["userContext"] === "user" && $_SESSION["POLICY_USER_DELETE_LOGS"] !== "no")) { ?>
					<a
						class="button button-secondary button-danger data-controls js-confirm-action"
						<?php if ($_SESSION["userContext"] === "admin" && isset($_GET["user"])) { ?>
							href="/delete/log/?user=<?= htmlentities($_GET["user"]) ?>&token=<?= $_SESSION["token"] ?>"
						<?php } else { ?>
							href="/delete/log/?token=<?= $_SESSION["token"] ?>"
						<?php } ?>
						data-confirm-title="<?= _("Delete") ?>"
						data-confirm-message="<?= _("Are you sure you want to delete the logs?") ?>"
					>
						<i class="fas fa-circle-xmark icon-red"></i><?= _("Delete") ?>
					</a>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Logs") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell"></div>
			<div class="units-table-cell"><?= _("Date") ?></div>
			<div class="units-table-cell"><?= _("Time") ?></div>
			<div class="units-table-cell"><?= _("Category") ?></div>
			<div class="units-table-cell"><?= _("Message") ?></div>
		</div>

		<!-- Begin log history entry loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;

				if ($data[$key]['LEVEL'] === 'Info') {
					$level_icon = 'fa-info-circle icon-blue';
					$level_title = _('Information');
				}
				if ($data[$key]['LEVEL'] === 'Warning') {
					$level_icon = 'fa-triangle-exclamation icon-orange';
					$level_title = _('Warning');
				}
				if ($data[$key]['LEVEL'] === 'Error') {
					$level_icon = 'fa-circle-xmark icon-red';
					$level_title = _('Error');
				}
			?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell u-text-center-desktop">
					<i class="fas <?= $level_icon ?>" title="<?= $level_title ?>"></i>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Date") ?>:</span>
					<time datetime="<?= htmlspecialchars($data[$key]["DATE"]) ?>">
						<?= translate_date($data[$key]["DATE"]) ?>
					</time>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Time") ?>:</span>
					<time datetime="<?= htmlspecialchars($data[$key]["TIME"]) ?>">
						<?= htmlspecialchars($data[$key]["TIME"]) ?>
					</time>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Category") ?>:</span>
					<?= htmlspecialchars($data[$key]["CATEGORY"]) ?>
				</div>
				<div class="units-table-cell">
					<span class="u-hide-desktop u-text-bold"><?= _("Message") ?>:</span>
					<?= htmlspecialchars($data[$key]["MESSAGE"], ENT_QUOTES) ?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d log record", "%d log records", $i), $i); ?>
		</p>
	</div>
</footer>
