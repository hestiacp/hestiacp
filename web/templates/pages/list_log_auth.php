<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($_SESSION["userContext"] === "admin" && isset($_GET["user"]) && htmlentities($_GET["user"]) !== "admin") { ?>
				<a href="/list/log/?user=<?= htmlentities($_GET["user"]) ?>&token=<?= $_SESSION["token"] ?>" class="button button-secondary button-back js-button-back">
					<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
				</a>
			<?php } else { ?>
				<a href="/list/log/" class="button button-secondary button-back js-button-back">
					<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
				</a>
			<?php } ?>
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
							href="/delete/log/auth/?user=<?= htmlentities($_GET["user"]) ?>&token=<?= $_SESSION["token"] ?>"
						<?php } else { ?>
							href="/delete/log/auth/?token=<?= $_SESSION["token"] ?>"
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

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Auth Log") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell"><?= _("Status") ?></div>
			<div class="units-table-cell"><?= _("Date") ?></div>
			<div class="units-table-cell"><?= _("Time") ?></div>
			<div class="units-table-cell"><?= _("IP Address") ?></div>
			<div class="units-table-cell"><?= _("Browser") ?></div>
		</div>

		<!-- Begin log history entry loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;

				if ($data[$key]['ACTION'] == 'login') {
					if ($data[$key]['ACTIVE'] === 'yes') {
						$action_icon = 'fa-right-to-bracket icon-maroon';
					} else {
						$action_icon = ' fa-right-to-bracket icon-dim';
					}
				}
				if ($data[$key]['STATUS'] == 'success')	{
					$status_icon = 'fa-circle-check icon-green';
					$status_title = _('Success');
				} else {
					$status_icon = 'fa-circle-minus icon-red';
					$status_title = _('Failed');
				}
			?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell u-text-center-desktop">
					<i class="fas <?= $status_icon ?> u-mr5" title="<?= $status_title ?>"></i>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Date") ?>:</span>
					<time class="u-text-no-wrap" datetime="<?= htmlspecialchars($data[$key]["DATE"]) ?>">
						<?= translate_date($data[$key]["DATE"]) ?>
					</time>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Time") ?>:</span>
					<time datetime="<?= htmlspecialchars($data[$key]["TIME"]) ?>">
						<?= htmlspecialchars($data[$key]["TIME"]) ?>
					</time>
				</div>
				<div class="units-table-cell">
					<span class="u-hide-desktop u-text-bold"><?= _("IP Address") ?>:</span>
					<?= htmlspecialchars($data[$key]["IP"]) ?>
				</div>
				<div class="units-table-cell">
					<span class="u-hide-desktop u-text-bold"><?= _("Browser") ?>:</span>
					<?= htmlspecialchars($data[$key]["USER_AGENT"]) ?>
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
