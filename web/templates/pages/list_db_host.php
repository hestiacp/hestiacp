<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/list/server/" class="button button-secondary button-back js-button-back">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
			<a href="/add/db-host/" class="button button-secondary js-button-create">
				<i class="fas fa-circle-plus icon-green"></i><?= tohtml( _("Add Database Server")) ?>
			</a>
			<a href="/list/server/?db" class="button button-secondary">
				<i class="fas fa-chart-line icon-blue"></i><?= tohtml( _("Status")) ?>
			</a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= tohtml( _("Database Servers")) ?></h1>
	<?php show_alert_message($_SESSION); ?>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell"><?= tohtml( _("Endpoint")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Type")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Username")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Databases")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Limit")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Charset")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Status")) ?></div>
		</div>

		<?php
			foreach ($data as $key => $value) {
				++$i;
				$value["PORT"] = $value["PORT"] ?? ($value["TYPE"] === "pgsql" ? "5432" : "3306");
				$value["ENDPOINT"] = $value["ENDPOINT"] ?? $value["HOST"] . ":" . $value["PORT"];
				$status = $value["SUSPENDED"] === "yes" ? "suspended" : "active";
				$spnd_title = $value["SUSPENDED"] === "yes" ? _("Unsuspend") : _("Suspend");
				$spnd_icon = $value["SUSPENDED"] === "yes" ? "fa-play icon-green" : "fa-pause icon-highlight";
				$spnd_url = $value["SUSPENDED"] === "yes" ? "/unsuspend/db-host/?" : "/suspend/db-host/?";
				$delete_disabled = (int) $value["U_DB_BASES"] > 0;
				$endpoint_query = [
					"type" => $value["TYPE"],
					"host" => $value["HOST"],
					"port" => $value["PORT"],
					"token" => $_SESSION["token"],
				];
			?>
			<div class="units-table-row <?= tohtml($status === "suspended" ? "disabled" : "") ?> js-unit"
				data-sort-name="<?= tohtml($value["ENDPOINT"]) ?>"
				data-sort-type="<?= tohtml($value["TYPE"]) ?>">
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("Endpoint")) ?>:</span>
					<a href="/edit/db-host/?<?= tohtml(http_build_query($endpoint_query)) ?>" title="<?= tohtml( _("Edit Database Server")) ?>: <?= tohtml($value["ENDPOINT"]) ?>">
						<?= tohtml($value["ENDPOINT"]) ?>
					</a>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="/edit/db-host/?<?= tohtml(http_build_query($endpoint_query)) ?>"
								title="<?= tohtml( _("Edit")) ?>"
							>
								<i class="fas fa-pencil icon-orange"></i>
								<span class="u-hide-desktop"><?= tohtml( _("Edit")) ?></span>
							</a>
						</li>
						<li class="units-table-row-action shortcut-s" data-key-action="js">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="<?= tohtml($spnd_url . http_build_query($endpoint_query)) ?>"
								title="<?= tohtml($spnd_title) ?>"
								data-confirm-title="<?= tohtml($spnd_title) ?>"
								data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to %s database server %s?"), strtolower($spnd_title), $value["ENDPOINT"])) ?>"
							>
								<i class="fas <?= tohtml($spnd_icon) ?>"></i>
								<span class="u-hide-desktop"><?= tohtml($spnd_title) ?></span>
							</a>
						</li>
						<li class="units-table-row-action shortcut-delete" data-key-action="js">
							<a
								class="units-table-row-action-link data-controls js-confirm-action <?= tohtml((int) $value["U_DB_BASES"] > 0 ? "disabled" : "") ?>"
								href="<?= tohtml($delete_disabled ? "#" : "/delete/db-host/?" . http_build_query($endpoint_query)) ?>"
								title="<?= tohtml( _("Delete")) ?>"
								data-confirm-title="<?= tohtml( _("Delete")) ?>"
								data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to delete database server %s?"), $value["ENDPOINT"])) ?>"
							>
								<i class="fas fa-trash icon-red"></i>
								<span class="u-hide-desktop"><?= tohtml( _("Delete")) ?></span>
							</a>
						</li>
					</ul>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Type")) ?>:</span>
					<?= tohtml($value["TYPE"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Username")) ?>:</span>
					<?= tohtml($value["USER"] ?? "") ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Databases")) ?>:</span>
					<?= tohtml($value["U_DB_BASES"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Limit")) ?>:</span>
					<?= tohtml($value["MAX_DB"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Charset")) ?>:</span>
					<?= tohtml($value["TYPE"] === "pgsql" ? ($value["TPL"] ?? "template1") : $value["CHARSETS"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Status")) ?>:</span>
					<?= tohtml($value["SUSPENDED"] === "yes" ? _("Suspended") : _("Active")) ?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>
