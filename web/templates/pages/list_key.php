<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/edit/user/?token=<?= $_SESSION["token"] ?>">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<?php if ($_SESSION["userContext"] === "admin" && isset($_GET["user"]) && $_GET["user"] !== "admin") { ?>
				<a href="/add/key/?user=<?= htmlentities($_GET["user"]) ?>" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add SSH Key") ?>
				</a>
			<?php } else { ?>
				<a href="/add/key/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add SSH Key") ?>
				</a>
			<?php } ?>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<div class="units js-units-container">
		<div class="header units-header">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("SSH ID") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact-2">
					&nbsp;
				</div>
				<div class="clearfix l-unit__stat-col--left wide-7"><b><?= _("SSH Key") ?></b></div>
			</div>
		</div>

		<!-- Begin SSH key list item loop -->
		<?php
			$i = 0;
				foreach ($data as $key => $value) {
				++$i;
			?>
			<div class="l-unit header animate__animated animate__fadeIn js-unit" style="<?php if ($data[$key]['ID'] === 'filemanager.ssh.key') { echo 'display: none;'; } ?>">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left wide-3"><b><?= htmlspecialchars($data[$key]["ID"]) ?></b></div>
					<div class="clearfix l-unit__stat-col--left text-left compact-2">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<div class="actions-panel__col actions-panel__delete shortcut-delete" data-key-action="js">
									<a
										class="data-controls js-confirm-action"
										<?php if ($_SESSION["userContext"] === "admin" && isset($_GET["user"]) && $_GET["user"] !== "admin") { ?>
											href="/delete/key/?user=<?= htmlentities($_GET["user"]) ?>&key=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
										<?php } else { ?>
											href="/delete/key/?key=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
										<?php } ?>
										data-confirm-title="<?= _("Delete") ?>"
										data-confirm-message="<?= sprintf(_("Are you sure you want to delete SSH key %s?"), $key) ?>"
									>
										<i class="fas fa-trash icon-red icon-dim"></i>
									</a>
								</div>
							</div>
						</div>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-7"><b><?= htmlspecialchars($data[$key]["KEY"], ENT_QUOTES) ?></b></div>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d SSH key", "%d SSH keys", $i), $i); ?>
		</p>
	</div>
</footer>
