<?php
$v_laravel_active_tab = $v_laravel_command_result["type"] ?: "dashboard";
$v_laravel_domain_url = $v_laravel_site_url ?? "http://" . $v_domain;
$v_laravel_masked_webhook = preg_replace("/secret=[A-Za-z0-9]+/", "secret=********", $v_laravel_webhook_url);
$v_laravel_url_query ??= ["domain" => $v_domain];
$v_laravel_url_query_html = tohtml(http_build_query($v_laravel_url_query));
?>
<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/edit/web/?<?= $v_laravel_url_query_html ?>">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
			<a class="button button-secondary" href="<?= tohtml($v_laravel_domain_url) ?>" target="_blank" rel="noopener">
				<i class="fas fa-up-right-from-square icon-blue"></i><?= tohtml( _("Open Site")) ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<form method="post">
				<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
				<input type="hidden" name="action" value="deploy">
				<button type="submit" class="button">
					<i class="fas fa-rocket icon-green"></i><?= tohtml( _("Deploy")) ?>
				</button>
			</form>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">
	<div class="laravel-page">
		<div class="laravel-page-header">
			<h1><?= tohtml(sprintf(_("Laravel application for %s"), $v_domain)) ?></h1>
			<p class="laravel-page-subtitle"><?= tohtml($v_laravel["APP_ROOT"] ?? "") ?></p>
		</div>
		<?php show_alert_message($_SESSION); ?>

		<div class="laravel-layout">
			<aside class="laravel-sidebar">
				<div class="laravel-brand">
					<i class="fab fa-laravel icon-red"></i>
					<div>
						<strong>Laravel</strong>
						<span><?= tohtml($v_laravel["SOURCE_TYPE"] ?? "") ?></span>
					</div>
				</div>

				<ul class="values-list laravel-sidebar-list">
					<li class="values-list-item"><span class="values-list-label"><?= tohtml( _("App root")) ?></span><span class="values-list-value"><?= tohtml($v_laravel["APP_ROOT"] ?? "") ?></span></li>
					<li class="values-list-item"><span class="values-list-label"><?= tohtml( _("PHP")) ?></span><span class="values-list-value"><?= tohtml($v_laravel["PHP_VERSION"] ?? "") ?></span></li>
					<li class="values-list-item"><span class="values-list-label"><?= tohtml( _("Environment")) ?></span><span class="values-list-value"><?= tohtml($v_laravel_env_summary["APP_ENV"] ?: _("Not set")) ?></span></li>
					<li class="values-list-item"><span class="values-list-label"><?= tohtml( _("Debug")) ?></span><span class="values-list-value"><?= tohtml($v_laravel_env_summary["APP_DEBUG"] ?: _("Not set")) ?></span></li>
					<li class="values-list-item"><span class="values-list-label"><?= tohtml( _("Branch")) ?></span><span class="values-list-value"><?= tohtml($v_laravel["GIT_BRANCH"] ?? ($v_laravel["BRANCH"] ?? "")) ?></span></li>
					<li class="values-list-item"><span class="values-list-label"><?= tohtml( _("Commit")) ?></span><span class="values-list-value"><?= tohtml($v_laravel["GIT_COMMIT"] ?? "") ?></span></li>
				</ul>

				<div class="laravel-badges">
					<span class="laravel-badge <?= ($v_laravel["MAINTENANCE"] ?? "") === "yes" ? "is-warning" : "is-ok" ?>"><?= tohtml( _("Maintenance")) ?>: <?= tohtml($v_laravel["MAINTENANCE"] ?? "no") ?></span>
					<span class="laravel-badge <?= ($v_laravel["SCHEDULER"] ?? "") === "yes" ? "is-ok" : "" ?>"><?= tohtml( _("Scheduler")) ?>: <?= tohtml($v_laravel["SCHEDULER"] ?? "no") ?></span>
					<span class="laravel-badge <?= ($v_laravel["QUEUE"] ?? "") === "yes" ? "is-ok" : "" ?>"><?= tohtml( _("Queue")) ?>: <?= tohtml($v_laravel["QUEUE"] ?? "no") ?></span>
				</div>

				<div class="laravel-sidebar-actions">
					<a href="/edit/web/?<?= $v_laravel_url_query_html ?>">
						<i class="fas fa-globe icon-blue"></i><?= tohtml( _("Manage domain")) ?>
					</a>
					<a href="#tab-laravel-logs">
						<i class="fas fa-file-lines icon-purple"></i><?= tohtml( _("Logs")) ?>
					</a>
				</div>
			</aside>

			<div class="laravel-workbench">
				<div class="tabs laravel-tabs js-tabs">
					<div class="tabs-items" role="tablist">
						<button type="button" class="tabs-item" id="tab-laravel-dashboard" role="tab" tabindex="<?= $v_laravel_active_tab === "dashboard" ? "0" : "-1" ?>" aria-selected="<?= $v_laravel_active_tab === "dashboard" ? "true" : "false" ?>"><?= tohtml( _("Dashboard")) ?></button>
						<button type="button" class="tabs-item" id="tab-laravel-artisan" role="tab" tabindex="<?= $v_laravel_active_tab === "artisan" ? "0" : "-1" ?>" aria-selected="<?= $v_laravel_active_tab === "artisan" ? "true" : "false" ?>">Artisan</button>
						<button type="button" class="tabs-item" id="tab-laravel-composer" role="tab" tabindex="<?= $v_laravel_active_tab === "composer" ? "0" : "-1" ?>" aria-selected="<?= $v_laravel_active_tab === "composer" ? "true" : "false" ?>">Composer</button>
						<button type="button" class="tabs-item" id="tab-laravel-node" role="tab" tabindex="<?= $v_laravel_active_tab === "node" ? "0" : "-1" ?>" aria-selected="<?= $v_laravel_active_tab === "node" ? "true" : "false" ?>">Node.js</button>
						<button type="button" class="tabs-item" id="tab-laravel-deployment" role="tab" tabindex="-1" aria-selected="false"><?= tohtml( _("Deployment")) ?></button>
						<button type="button" class="tabs-item" id="tab-laravel-environment" role="tab" tabindex="-1" aria-selected="false"><?= tohtml( _("Environment")) ?></button>
						<button type="button" class="tabs-item" id="tab-laravel-scheduler" role="tab" tabindex="-1" aria-selected="false"><?= tohtml( _("Scheduler")) ?></button>
						<button type="button" class="tabs-item" id="tab-laravel-queue" role="tab" tabindex="-1" aria-selected="false"><?= tohtml( _("Queue")) ?></button>
						<button type="button" class="tabs-item" id="tab-laravel-logs" role="tab" tabindex="-1" aria-selected="false"><?= tohtml( _("Logs")) ?></button>
					</div>

					<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-laravel-dashboard" tabindex="0" <?= $v_laravel_active_tab === "dashboard" ? "" : "hidden" ?>>
						<div class="laravel-status-grid">
							<?php foreach ($v_laravel_status_cards as $card) { ?>
								<div class="laravel-status-card is-<?= tohtml($card["tone"]) ?>">
									<i class="fas <?= tohtml($card["icon"]) ?>"></i>
									<div>
										<span><?= tohtml($card["state"]) ?></span>
										<strong><?= tohtml($card["value"]) ?></strong>
										<small><?= tohtml($card["label"]) ?></small>
									</div>
								</div>
							<?php } ?>
						</div>

						<div class="laravel-panel-grid">
							<section class="laravel-panel">
								<h2><?= tohtml( _("Recommended next actions")) ?></h2>
								<?php if (empty($v_laravel_recommendations)) { ?>
									<p class="laravel-muted"><?= tohtml( _("No immediate Laravel recommendations.")) ?></p>
								<?php } else { ?>
									<ul class="laravel-check-list">
										<?php foreach ($v_laravel_recommendations as $recommendation) { ?>
											<li><i class="fas fa-triangle-exclamation icon-orange"></i><?= tohtml($recommendation) ?></li>
										<?php } ?>
									</ul>
								<?php } ?>
							</section>

							<section class="laravel-panel">
								<h2><?= tohtml( _("Deployment access")) ?></h2>
								<label for="laravel-webhook-dashboard" class="form-label"><?= tohtml( _("Webhook URL")) ?></label>
								<div class="laravel-secret">
									<input class="form-control js-copy-input" id="laravel-webhook-dashboard" type="password" readonly value="<?= tohtml($v_laravel_webhook_url) ?>" aria-label="<?= tohtml( _("Webhook URL")) ?>">
									<button type="button" class="button button-secondary js-copy-button">
										<i class="fas fa-copy icon-blue"></i><?= tohtml( _("Copy")) ?>
									</button>
									<button type="button" class="button button-secondary js-laravel-secret-toggle" data-target="laravel-webhook-dashboard" data-label-show="<?= tohtml( _("Reveal")) ?>" data-label-hide="<?= tohtml( _("Hide")) ?>">
										<i class="fas fa-eye icon-purple"></i><?= tohtml( _("Reveal")) ?>
									</button>
								</div>
								<p class="laravel-muted"><?= tohtml($v_laravel_masked_webhook) ?></p>
							</section>

							<section class="laravel-panel">
								<h2><?= tohtml( _("Application controls")) ?></h2>
								<form method="post" class="laravel-toggle-form">
									<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
									<label class="form-check">
										<input class="form-check-input" type="checkbox" name="maintenance" id="maintenance" <?php if (($v_laravel["MAINTENANCE"] ?? "") === "yes") echo "checked"; ?>>
										<span><?= tohtml( _("Maintenance mode")) ?></span>
									</label>
									<button class="button button-secondary" name="action" value="maintenance">
										<i class="fas fa-power-off icon-orange"></i><?= tohtml( _("Apply")) ?>
									</button>
								</form>
							</section>
						</div>
					</div>

					<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-laravel-artisan" tabindex="0" <?= $v_laravel_active_tab === "artisan" ? "" : "hidden" ?>>
						<section class="laravel-panel">
							<h2>Artisan</h2>
							<form method="post" class="laravel-command-form">
								<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
								<input type="hidden" name="action" value="command">
								<input type="hidden" name="command_type" value="artisan">
								<label for="laravel-artisan-command" class="form-label"><?= tohtml( _("Command")) ?></label>
								<div class="laravel-command-row">
									<span class="laravel-command-prefix">php artisan</span>
									<input class="form-control" type="text" name="command" id="laravel-artisan-command" value="about">
									<button type="submit" class="button">
										<i class="fas fa-play icon-green"></i><?= tohtml( _("Run")) ?>
									</button>
								</div>
								<div class="laravel-presets">
									<?php foreach ($v_laravel_command_presets["artisan"] as $preset) { ?>
										<button type="submit" class="button button-secondary" name="command" value="<?= tohtml($preset) ?>"><?= tohtml($preset) ?></button>
									<?php } ?>
								</div>
							</form>
							<?php if ($v_laravel_command_result["type"] === "artisan") { ?>
								<div class="laravel-command-result is-<?= tohtml($v_laravel_command_result["status"]) ?>">
									<strong><?= tohtml(ucfirst($v_laravel_command_result["status"])) ?></strong>
									<span><?= tohtml("php artisan " . $v_laravel_command_result["command"]) ?> · <?= tohtml($v_laravel_command_result["time"]) ?></span>
									<pre class="laravel-console"><?= tohtml($v_laravel_command_result["output"]) ?></pre>
								</div>
							<?php } ?>
						</section>
					</div>

					<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-laravel-composer" tabindex="0" <?= $v_laravel_active_tab === "composer" ? "" : "hidden" ?>>
						<section class="laravel-panel">
							<h2>Composer</h2>
							<form method="post" class="laravel-command-form">
								<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
								<input type="hidden" name="action" value="command">
								<input type="hidden" name="command_type" value="composer">
								<label for="laravel-composer-command" class="form-label"><?= tohtml( _("Command")) ?></label>
								<div class="laravel-command-row">
									<span class="laravel-command-prefix">composer</span>
									<input class="form-control" type="text" name="command" id="laravel-composer-command" value="install --no-dev --optimize-autoloader">
									<button type="submit" class="button">
										<i class="fas fa-play icon-green"></i><?= tohtml( _("Run")) ?>
									</button>
								</div>
								<div class="laravel-presets">
									<?php foreach ($v_laravel_command_presets["composer"] as $preset) { ?>
										<button type="submit" class="button button-secondary" name="command" value="<?= tohtml($preset) ?>"><?= tohtml($preset) ?></button>
									<?php } ?>
								</div>
							</form>
							<?php if ($v_laravel_command_result["type"] === "composer") { ?>
								<div class="laravel-command-result is-<?= tohtml($v_laravel_command_result["status"]) ?>">
									<strong><?= tohtml(ucfirst($v_laravel_command_result["status"])) ?></strong>
									<span><?= tohtml("composer " . $v_laravel_command_result["command"]) ?> · <?= tohtml($v_laravel_command_result["time"]) ?></span>
									<pre class="laravel-console"><?= tohtml($v_laravel_command_result["output"]) ?></pre>
								</div>
							<?php } ?>
						</section>
					</div>

					<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-laravel-node" tabindex="0" <?= $v_laravel_active_tab === "node" ? "" : "hidden" ?>>
						<section class="laravel-panel">
							<h2>Node.js</h2>
							<form method="post" class="laravel-command-form">
								<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
								<input type="hidden" name="action" value="command">
								<input type="hidden" name="command_type" value="node">
								<div class="laravel-command-row">
									<select class="form-select laravel-package-manager" name="package_manager" aria-label="<?= tohtml( _("Package manager")) ?>">
										<option value="npm">npm</option>
										<option value="yarn">yarn</option>
										<option value="pnpm">pnpm</option>
									</select>
									<input class="form-control" type="text" name="command" id="laravel-node-command" value="run build">
									<button type="submit" class="button">
										<i class="fas fa-play icon-green"></i><?= tohtml( _("Run")) ?>
									</button>
								</div>
								<div class="laravel-presets">
									<?php foreach ($v_laravel_command_presets["node"] as $preset) { ?>
										<button type="submit" class="button button-secondary" name="command" value="<?= tohtml($preset) ?>"><?= tohtml($preset) ?></button>
									<?php } ?>
								</div>
							</form>
							<?php if ($v_laravel_command_result["type"] === "node") { ?>
								<div class="laravel-command-result is-<?= tohtml($v_laravel_command_result["status"]) ?>">
									<strong><?= tohtml(ucfirst($v_laravel_command_result["status"])) ?></strong>
									<span><?= tohtml($v_laravel_command_result["command"]) ?> · <?= tohtml($v_laravel_command_result["time"]) ?></span>
									<pre class="laravel-console"><?= tohtml($v_laravel_command_result["output"]) ?></pre>
								</div>
							<?php } ?>
						</section>
					</div>

					<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-laravel-deployment" tabindex="0" hidden>
						<section class="laravel-panel">
							<div class="laravel-panel-header">
								<div>
									<h2><?= tohtml( _("Deployment")) ?></h2>
									<p><?= tohtml(sprintf(_("Mode: %s"), $v_laravel_deploy_summary["mode"])) ?></p>
								</div>
								<form method="post">
									<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
									<input type="hidden" name="action" value="deploy">
									<button type="submit" class="button">
										<i class="fas fa-rocket icon-green"></i><?= tohtml( _("Deploy Now")) ?>
									</button>
								</form>
							</div>
							<div class="laravel-deploy-meta">
								<span><?= tohtml( _("Source")) ?>: <?= tohtml($v_laravel_deploy_summary["source"]) ?></span>
								<span><?= tohtml( _("Branch")) ?>: <?= tohtml($v_laravel_deploy_summary["branch"] ?: _("Not set")) ?></span>
								<span><?= tohtml( _("Commit")) ?>: <?= tohtml($v_laravel_deploy_summary["commit"] ?: _("Not set")) ?></span>
							</div>
							<label for="laravel-webhook-deploy" class="form-label"><?= tohtml( _("Webhook URL")) ?></label>
							<div class="laravel-secret">
								<input class="form-control js-copy-input" id="laravel-webhook-deploy" type="password" readonly value="<?= tohtml($v_laravel_webhook_url) ?>" aria-label="<?= tohtml( _("Webhook URL")) ?>">
								<button type="button" class="button button-secondary js-copy-button">
									<i class="fas fa-copy icon-blue"></i><?= tohtml( _("Copy")) ?>
								</button>
								<button type="button" class="button button-secondary js-laravel-secret-toggle" data-target="laravel-webhook-deploy" data-label-show="<?= tohtml( _("Reveal")) ?>" data-label-hide="<?= tohtml( _("Hide")) ?>">
									<i class="fas fa-eye icon-purple"></i><?= tohtml( _("Reveal")) ?>
								</button>
							</div>
							<h3><?= tohtml( _("Default deployment scenario")) ?></h3>
							<ul class="laravel-check-list">
								<?php foreach ($v_laravel_deploy_summary["scenario"] as $step) { ?>
									<li><i class="fas fa-check icon-green"></i><?= tohtml($step) ?></li>
								<?php } ?>
							</ul>
							<details class="laravel-details">
								<summary><?= tohtml( _("Edit deployment script")) ?></summary>
								<form method="post" class="u-mt10">
									<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
									<input type="hidden" name="action" value="deploy_script">
									<textarea class="form-control laravel-console laravel-editor" name="deploy_script" id="deploy_script"><?= tohtml($v_laravel_deploy_script) ?></textarea>
									<button type="submit" class="button button-secondary u-mt10">
										<i class="fas fa-floppy-disk icon-purple"></i><?= tohtml( _("Save Script")) ?>
									</button>
								</form>
							</details>
						</section>
					</div>

					<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-laravel-environment" tabindex="0" hidden>
						<section class="laravel-panel">
							<h2><?= tohtml( _("Environment variables (.env)")) ?></h2>
							<div class="alert alert-info u-mb10" role="alert">
								<i class="fas fa-info"></i>
								<p><?= tohtml( _("This file may contain secrets. Only update values you intend to change.")) ?></p>
							</div>
							<form method="post">
								<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
								<input type="hidden" name="action" value="env">
								<textarea class="form-control laravel-console laravel-editor" name="env" id="env"><?= tohtml($v_laravel_env) ?></textarea>
								<button type="submit" class="button button-secondary u-mt10">
									<i class="fas fa-floppy-disk icon-purple"></i><?= tohtml( _("Save .env")) ?>
								</button>
							</form>
						</section>
					</div>

					<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-laravel-scheduler" tabindex="0" hidden>
						<section class="laravel-panel">
							<h2><?= tohtml( _("Scheduler")) ?></h2>
							<p><?= tohtml( _("Enable Laravel's managed schedule runner for this application.")) ?></p>
							<form method="post" class="laravel-toggle-form">
								<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
								<label class="form-check">
									<input class="form-check-input" type="checkbox" name="scheduler" id="scheduler" <?php if (($v_laravel["SCHEDULER"] ?? "") === "yes") echo "checked"; ?>>
									<span><?= tohtml( _("Scheduled tasks enabled")) ?></span>
								</label>
								<button class="button button-secondary" name="action" value="scheduler">
									<i class="fas fa-clock icon-green"></i><?= tohtml( _("Apply")) ?>
								</button>
							</form>
						</section>
					</div>

					<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-laravel-queue" tabindex="0" hidden>
						<section class="laravel-panel">
							<h2><?= tohtml( _("Queue worker")) ?></h2>
							<form method="post" class="laravel-queue-grid">
								<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
								<label class="form-check laravel-queue-enable">
									<input class="form-check-input" type="checkbox" name="queue" id="queue" <?php if (($v_laravel["QUEUE"] ?? "") === "yes") echo "checked"; ?>>
									<span><?= tohtml( _("Queue worker enabled")) ?></span>
								</label>
								<div>
									<label for="queue_connection" class="form-label"><?= tohtml( _("Queue connection")) ?></label>
									<input class="form-control" type="text" name="queue_connection" id="queue_connection" value="<?= tohtml($v_laravel["QUEUE_CONNECTION"] ?? "database") ?>">
								</div>
								<div>
									<label for="queue_timeout" class="form-label"><?= tohtml( _("Timeout")) ?></label>
									<input class="form-control" type="text" name="queue_timeout" id="queue_timeout" value="<?= tohtml($v_laravel["QUEUE_TIMEOUT"] ?? "60") ?>">
								</div>
								<div>
									<label for="queue_max_jobs" class="form-label"><?= tohtml( _("Max jobs")) ?></label>
									<input class="form-control" type="text" name="queue_max_jobs" id="queue_max_jobs" value="<?= tohtml($v_laravel["QUEUE_MAX_JOBS"] ?? "0") ?>">
								</div>
								<div>
									<label for="queue_max_time" class="form-label"><?= tohtml( _("Max time")) ?></label>
									<input class="form-control" type="text" name="queue_max_time" id="queue_max_time" value="<?= tohtml($v_laravel["QUEUE_MAX_TIME"] ?? "0") ?>">
								</div>
								<label class="form-check">
									<input class="form-check-input" type="checkbox" name="queue_stop_when_empty" id="queue_stop_when_empty" <?php if (($v_laravel["QUEUE_STOP_WHEN_EMPTY"] ?? "") === "yes") echo "checked"; ?>>
									<span><?= tohtml( _("Stop when empty")) ?></span>
								</label>
								<button class="button button-secondary" name="action" value="queue">
									<i class="fas fa-gears icon-purple"></i><?= tohtml( _("Apply Queue")) ?>
								</button>
							</form>
						</section>

						<section class="laravel-panel">
							<div class="laravel-panel-header">
								<div>
									<h2><?= tohtml( _("Failed Jobs")) ?></h2>
									<p><?= $v_laravel_failed_jobs_summary["empty"] ? tohtml( _("No failed jobs found.")) : tohtml( _("Failed jobs need attention.")) ?></p>
								</div>
								<form method="post" class="laravel-inline-actions">
									<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
									<button class="button button-secondary" name="action" value="retry_failed" <?php if ($v_laravel_failed_jobs_summary["empty"]) echo "disabled"; ?>>
										<i class="fas fa-rotate-right icon-green"></i><?= tohtml( _("Retry All")) ?>
									</button>
									<button class="button button-secondary" name="action" value="flush_failed" <?php if ($v_laravel_failed_jobs_summary["empty"]) echo "disabled"; ?>>
										<i class="fas fa-trash icon-red"></i><?= tohtml( _("Flush All")) ?>
									</button>
								</form>
							</div>
							<?php if (!$v_laravel_failed_jobs_summary["empty"]) { ?>
								<pre class="laravel-console"><?= tohtml($v_laravel_failed_jobs_summary["output"]) ?></pre>
							<?php } ?>
						</section>
					</div>

					<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-laravel-logs" tabindex="0" hidden>
						<section class="laravel-panel">
							<div class="laravel-panel-header">
								<div>
									<h2><?= tohtml( _("Logs")) ?></h2>
									<p><?= tohtml(sprintf(_("%s, showing %d lines"), $v_laravel_log_summary["name"], $v_laravel_log_summary["lines"])) ?></p>
								</div>
								<a class="button button-secondary" href="/edit/laravel/?<?= $v_laravel_url_query_html ?>#tab-laravel-logs">
									<i class="fas fa-rotate icon-blue"></i><?= tohtml( _("Refresh")) ?>
								</a>
							</div>
							<?php if ($v_laravel_log_summary["empty"]) { ?>
								<p class="laravel-muted"><?= tohtml( _("No Laravel log entries found.")) ?></p>
							<?php } else { ?>
								<pre class="laravel-console laravel-log-console"><?= tohtml($v_laravel_log) ?></pre>
							<?php } ?>
						</section>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
