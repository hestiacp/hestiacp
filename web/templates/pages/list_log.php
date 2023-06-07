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
				<?php if ($_SESSION["userContext"] === "admin" && isset($_GET["user"]) && $_GET["user"] !== "admin") { ?>
					<a href="/edit/user/?user=<?= htmlentities($_GET["user"]) ?>&token=<?= $_SESSION["token"] ?>" class="button button-secondary button-back js-button-back">
						<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
					</a>
				<?php } else { ?>
					<a href="/edit/user/?token=<?= $_SESSION["token"] ?>" class="button button-secondary button-back js-button-back">
						<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
					</a>
				<?php } ?>
			<?php } ?>
			<?php if ($_SESSION['DEMO_MODE'] != "yes"){
			if (($_SESSION['userContext'] === 'admin') && (htmlentities($_GET['user']) !== 'admin')) { ?>
				<?php if (($_SESSION['userContext'] === 'admin') && ($_GET['user'] != '') && (htmlentities($_GET['user']) !== 'admin')) { ?>
					<?php if (htmlentities($_GET['user']) !== 'system') {?>
						<a href="/list/log/auth/?user=<?=htmlentities($_GET['user']); ?>&token=<?=$_SESSION['token']?>" class="button button-secondary button-back js-button-back" title="<?= _("Login History") ?>">
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
					<div class="actions-panel" data-key-action="js">
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
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<div class="units js-units-container">
		<div class="header units-header">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact u-text-center">&nbsp;</div>
				<div class="clearfix l-unit__stat-col--left"><b><?= _("Date") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact-2"><b><?= _("Time") ?></b></div>
				<div class="clearfix l-unit__stat-col--left"><b><?= _("Category") ?></b></div>
				<div class="clearfix l-unit__stat-col--left"><b><?= _("Message") ?></b></div>
			</div>
		</div>

		<!-- Begin log history entry loop -->
		<?php
			foreach ($data as $key => $value) {
				++$i;

				if ($data[$key]['LEVEL'] === 'Info') {
					$level_icon = 'fa-info-circle icon-blue';
				}
				if ($data[$key]['LEVEL'] === 'Warning') {
					$level_icon = 'fa-triangle-exclamation icon-orange';
				}
				if ($data[$key]['LEVEL'] === 'Error') {
					$level_icon = 'fa-circle-xmark icon-red';
				}
			?>
			<div class="l-unit header animate__animated animate__fadeIn js-unit">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact u-text-center">
						<i class="fas <?= $level_icon ?>"></i>
					</div>
					<div class="clearfix l-unit__stat-col--left"><b><?= translate_date($data[$key]["DATE"]) ?></b></div>
					<div class="clearfix l-unit__stat-col--left compact-2"><b><?= htmlspecialchars($data[$key]["TIME"]) ?></b></div>
					<div class="clearfix l-unit__stat-col--left"><b><?= htmlspecialchars($data[$key]["CATEGORY"]) ?></b></div>
					<div class="clearfix l-unit__stat-col--left wide-7"><?= htmlspecialchars($data[$key]["MESSAGE"], ENT_QUOTES) ?></div>
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
