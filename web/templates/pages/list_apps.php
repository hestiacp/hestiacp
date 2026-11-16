<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/web/">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Web Domains")) ?>
			</a>
			<?php if ($read_only !== true) { ?>
				<a href="/add/app/?<?= tohtml(http_build_query($user_query)) ?>" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= tohtml( _("Add App")) ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= tohtml( _("Sort items")) ?>">
					<?= tohtml( _("Sort by")) ?>:
					<span class="u-text-bold">
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?= tohtml($label) ?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-cpu" data-sort-as-int="1">
						<span class="name"><?= tohtml( _("CPU")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= tohtml( _("Date")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-memory" data-sort-as-int="1">
						<span class="name"><?= tohtml( _("Memory")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= tohtml( _("Name")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-uptime" data-sort-as-int="1">
						<span class="name"><?= tohtml( _("Uptime")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= tohtml( _("Apps")) ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell"></div>
			<div class="units-table-cell"><?= tohtml( _("Domain")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Runtime")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Status")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("PID")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("CPU")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Memory")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Uptime")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Restarts")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Port")) ?></div>
		</div>

		<!-- Begin app list item loop -->
		<?php
			$i = 0;
			$runtime_labels = ["node" => "Node.js", "python" => "Python", "custom" => _("Custom")];
			foreach ($data as $key => $value) {
				++$i;
				$app_state = $value["STATE"] ?? "";
				$state = get_web_app_state($app_state);
				$is_up = in_array($app_state, ["active", "activating", "reloading"], true);
				$is_suspended = ($value["SUSPENDED"] ?? "no") === "yes";
				$runtime = $runtime_labels[$value["RUNTIME"] ?? ""] ?? ($value["RUNTIME"] ?? "");
				$version = $value["VERSION"] ?? "";
				if (($value["RUNTIME"] ?? "") === "node" && $version !== "" && $version !== "system") {
					$runtime .= " " . $version;
				}
				$cpu = is_numeric($value["CPU"] ?? null) ? (float) $value["CPU"] : 0;
				$memory = is_numeric($value["MEMORY"] ?? null) ? (int) $value["MEMORY"] : 0;
				$uptime = is_numeric($value["UPTIME"] ?? null) ? (int) $value["UPTIME"] : 0;
				$domain_query = array_merge(["domain" => $key], $user_query);
				$action_query = array_merge($domain_query, ["token" => $_SESSION["token"]]);
			?>
			<div class="units-table-row <?php if ($is_suspended) echo 'disabled'; ?> js-unit"
				data-sort-date="<?= tohtml(strtotime(($value["DATE"] ?? "") . " " . ($value["TIME"] ?? ""))) ?>"
				data-sort-name="<?= tohtml($key) ?>"
				data-sort-cpu="<?= tohtml($is_up ? $cpu * 100 : 0) ?>"
				data-sort-memory="<?= tohtml($is_up ? $memory : 0) ?>"
				data-sort-uptime="<?= tohtml($is_up ? $uptime : 0) ?>">
				<div class="units-table-cell u-text-center-desktop">
					<i class="fas <?= tohtml($state["icon"]) ?>" title="<?= tohtml($state["label"]) ?>"></i>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= tohtml( _("Domain")) ?>:</span>
					<a href="/edit/app/?<?= tohtml(http_build_query($domain_query)) ?>" title="<?= tohtml( _("Edit App")) ?>: <?= tohtml($key) ?>">
						<?= tohtml($key) ?>
					</a>
					<p class="hint u-max-width300 u-text-truncate" title="<?= tohtml($value["CMD"] ?? "") ?>"><?= tohtml($value["CMD"] ?? "") ?></p>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<?php if ($read_only !== true && !$is_suspended) { ?>
							<?php if ($is_up) { ?>
								<li class="units-table-row-action" data-key-action="js">
									<a
										class="units-table-row-action-link data-controls js-confirm-action"
										href="/restart/app/?<?= tohtml(http_build_query($action_query)) ?>"
										title="<?= tohtml( _("Restart")) ?>"
										data-confirm-title="<?= tohtml( _("Restart")) ?>"
										data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to restart the app of %s?"), $key)) ?>"
									>
										<i class="fas fa-arrow-rotate-left icon-highlight"></i>
										<span class="u-hide-desktop"><?= tohtml( _("Restart")) ?></span>
									</a>
								</li>
								<li class="units-table-row-action" data-key-action="js">
									<a
										class="units-table-row-action-link data-controls js-confirm-action"
										href="/stop/app/?<?= tohtml(http_build_query($action_query)) ?>"
										title="<?= tohtml( _("Stop")) ?>"
										data-confirm-title="<?= tohtml( _("Stop")) ?>"
										data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to stop the app of %s?"), $key)) ?>"
									>
										<i class="fas fa-stop icon-red"></i>
										<span class="u-hide-desktop"><?= tohtml( _("Stop")) ?></span>
									</a>
								</li>
							<?php } else { ?>
								<li class="units-table-row-action" data-key-action="href">
									<a
										class="units-table-row-action-link"
										href="/start/app/?<?= tohtml(http_build_query($action_query)) ?>"
										title="<?= tohtml( _("Start")) ?>"
									>
										<i class="fas fa-play icon-green"></i>
										<span class="u-hide-desktop"><?= tohtml( _("Start")) ?></span>
									</a>
								</li>
							<?php } ?>
						<?php } ?>
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="/edit/app/?<?= tohtml(http_build_query($domain_query)) ?>"
								title="<?= tohtml( _("Edit App")) ?>"
							>
								<i class="fas fa-pencil icon-orange"></i>
								<span class="u-hide-desktop"><?= tohtml( _("Edit App")) ?></span>
							</a>
						</li>
						<li class="units-table-row-action shortcut-l" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="/edit/app/?<?= tohtml(http_build_query($domain_query)) ?>#app-logs"
								title="<?= tohtml( _("View Logs")) ?>"
							>
								<i class="fas fa-binoculars icon-purple"></i>
								<span class="u-hide-desktop"><?= tohtml( _("View Logs")) ?></span>
							</a>
						</li>
						<?php if ($read_only !== true) { ?>
							<li class="units-table-row-action shortcut-delete" data-key-action="js">
								<a
									class="units-table-row-action-link data-controls js-confirm-action"
									href="/delete/app/?<?= tohtml(http_build_query($action_query)) ?>"
									title="<?= tohtml( _("Delete")) ?>"
									data-confirm-title="<?= tohtml( _("Delete")) ?>"
									data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to delete the app of %s? The app files will be kept."), $key)) ?>"
								>
									<i class="fas fa-trash icon-red"></i>
									<span class="u-hide-desktop"><?= tohtml( _("Delete")) ?></span>
								</a>
							</li>
						<?php } ?>
					</ul>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Runtime")) ?>:</span>
					<?= tohtml($runtime) ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= tohtml( _("Status")) ?>:</span>
					<?= tohtml($is_suspended ? _("Suspended") : $state["label"]) ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("PID")) ?>:</span>
					<?= tohtml($is_up && !empty($value["PID"]) ? $value["PID"] : "-") ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("CPU")) ?>:</span>
					<?= tohtml($is_up ? number_format($cpu, 1) . " %" : "-") ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Memory")) ?>:</span>
					<?= tohtml($is_up ? humanize_bytes($memory) : "-") ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Uptime")) ?>:</span>
					<?= tohtml($is_up ? humanize_duration($uptime) : "-") ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Restarts")) ?>:</span>
					<?= tohtml($value["RESTARTS"] ?? "0") ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Port")) ?>:</span>
					<?= tohtml($value["PORT"] ?? "") ?>
				</div>
			</div>
		<?php } ?>
	</div>

	<div class="units-table-footer">
		<p>
			<?php printf(ngettext("%d app", "%d apps", $i), $i); ?>
		</p>
	</div>

</div>
