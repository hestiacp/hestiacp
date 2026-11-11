<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/app/?<?= tohtml(http_build_query($user_query)) ?>">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<a href="/edit/web/?<?= tohtml(http_build_query($domain_query)) ?>" class="button button-secondary">
				<i class="fas fa-earth-americas icon-blue"></i><?= tohtml( _("Edit Domain")) ?>
			</a>
			<?php if ($read_only !== true) { ?>
				<button type="submit" class="button" form="main-form">
					<i class="fas fa-floppy-disk icon-purple"></i><?= tohtml( _("Save")) ?>
				</button>
			<?php } ?>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">
	<?php
		$action_query = array_merge($domain_query, ["token" => $_SESSION["token"]]);
		$app_x_data = [
			"runtime" => $v_runtime,
			"dir" => $v_dir,
			"home" => "/home/" . $v_username . "/web/" . $v_domain . "/",
			"placeholders" => [
				"node" => "npm start",
				"python" => "gunicorn app:app",
				"custom" => "./start.sh",
			],
		];
	?>

	<div class="form-container form-container-wide">
		<h1 class="u-mb20"><?= tohtml( _("Edit App")) ?></h1>
		<?php show_alert_message($_SESSION); ?>

		<!-- Begin status -->
		<h2 class="u-mb10"><?= tohtml( _("Status")) ?></h2>
		<ul class="values-list u-mb20">
			<li class="values-list-item">
				<span class="values-list-label"><?= tohtml( _("State")) ?></span>
				<span class="values-list-value">
					<i class="fas <?= tohtml($v_state_info["icon"]) ?> u-mr5"></i>
					<span class="u-text-bold"><?= tohtml($v_suspended ? _("Suspended") : $v_state_info["label"]) ?></span>
					<?php if (!empty($v_state)) { ?>
						<span class="hint">(<?= tohtml($v_state . (!empty($app["SUBSTATE"]) ? " / " . $app["SUBSTATE"] : "")) ?>)</span>
					<?php } ?>
				</span>
			</li>
			<li class="values-list-item">
				<span class="values-list-label"><?= tohtml( _("PID")) ?></span>
				<span class="values-list-value"><?= tohtml($v_is_up && !empty($v_pid) ? $v_pid : "-") ?></span>
			</li>
			<li class="values-list-item">
				<span class="values-list-label"><?= tohtml( _("CPU")) ?></span>
				<span class="values-list-value"><?= tohtml($v_is_up ? number_format($v_cpu, 1) . " %" : "-") ?></span>
			</li>
			<li class="values-list-item">
				<span class="values-list-label"><?= tohtml( _("Memory")) ?></span>
				<span class="values-list-value"><?= tohtml($v_is_up ? humanize_bytes($v_memory) : "-") ?></span>
			</li>
			<li class="values-list-item">
				<span class="values-list-label"><?= tohtml( _("Uptime")) ?></span>
				<span class="values-list-value"><?= tohtml($v_is_up ? humanize_duration($v_uptime) : "-") ?></span>
			</li>
			<li class="values-list-item">
				<span class="values-list-label"><?= tohtml( _("Restarts")) ?></span>
				<span class="values-list-value"><?= tohtml($v_restarts) ?></span>
			</li>
			<li class="values-list-item">
				<span class="values-list-label"><?= tohtml( _("Port")) ?></span>
				<span class="values-list-value"><?= tohtml($v_port) ?></span>
			</li>
			<li class="values-list-item">
				<span class="values-list-label"><?= tohtml( _("Desired State")) ?></span>
				<span class="values-list-value"><?= tohtml($v_status === "running" ? _("Running") : _("Stopped")) ?></span>
			</li>
		</ul>
		<?php if ($read_only !== true && !$v_suspended) { ?>
			<div class="u-mb20">
				<?php if ($v_is_up) { ?>
					<a
						class="button button-secondary data-controls js-confirm-action"
						href="/restart/app/?<?= tohtml(http_build_query($action_query)) ?>"
						data-confirm-title="<?= tohtml( _("Restart")) ?>"
						data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to restart the app of %s?"), $v_domain)) ?>"
					>
						<i class="fas fa-arrow-rotate-left icon-highlight"></i><?= tohtml( _("Restart")) ?>
					</a>
					<a
						class="button button-secondary data-controls js-confirm-action"
						href="/stop/app/?<?= tohtml(http_build_query($action_query)) ?>"
						data-confirm-title="<?= tohtml( _("Stop")) ?>"
						data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to stop the app of %s?"), $v_domain)) ?>"
					>
						<i class="fas fa-stop icon-red"></i><?= tohtml( _("Stop")) ?>
					</a>
				<?php } else { ?>
					<a class="button button-secondary" href="/start/app/?<?= tohtml(http_build_query($action_query)) ?>">
						<i class="fas fa-play icon-green"></i><?= tohtml( _("Start")) ?>
					</a>
				<?php } ?>
				<a
					class="button button-secondary data-controls js-confirm-action"
					href="/update/app/?<?= tohtml(http_build_query($action_query)) ?>"
					data-confirm-title="<?= tohtml( _("Install Dependencies / Build")) ?>"
					data-confirm-message="<?= tohtml( _("Run the install / build command now? This can take a few minutes.")) ?>"
				>
					<i class="fas fa-box-open icon-blue"></i><?= tohtml( _("Install Dependencies / Build")) ?>
				</a>
				<a
					class="button button-secondary button-danger data-controls js-confirm-action"
					href="/delete/app/?<?= tohtml(http_build_query($action_query)) ?>"
					data-confirm-title="<?= tohtml( _("Delete")) ?>"
					data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to delete the app of %s? The app files will be kept."), $v_domain)) ?>"
				>
					<i class="fas fa-trash icon-red"></i><?= tohtml( _("Delete")) ?>
				</a>
			</div>
		<?php } ?>
		<!-- End status -->

		<?php if ($v_deps_output !== null) { ?>
			<h2 class="u-mb10"><?= tohtml( _("Install / Build Output")) ?></h2>
			<pre class="console-output u-max-height600 u-overflow-auto u-mb20"><?= tohtml($v_deps_output) ?></pre>
		<?php } ?>

		<!-- Begin settings -->
		<form
			x-data="<?= tohtml(json_encode($app_x_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR)) ?>"
			id="main-form"
			name="v_edit_app"
			method="post"
			class="u-mb20"
		>
			<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
			<input type="hidden" name="save" value="save">

			<h2 class="u-mb10"><?= tohtml( _("Settings")) ?></h2>
			<div class="u-mb10">
				<label for="v_domain" class="form-label"><?= tohtml( _("Domain")) ?></label>
				<input type="text" class="form-control" id="v_domain" value="<?= tohtml($v_domain) ?>" disabled>
			</div>
			<div class="u-mb10">
				<label for="v_runtime" class="form-label"><?= tohtml( _("Runtime")) ?></label>
				<select x-model="runtime" class="form-select" name="v_runtime" id="v_runtime">
					<?php foreach ($runtimes as $key => $label) { ?>
						<option value="<?= tohtml($key) ?>"<?= $key === $v_runtime ? " selected" : "" ?>><?= tohtml($label) ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_version" class="form-label"><?= tohtml( _("Version")) ?></label>
				<select
					x-show="runtime === 'node'"
					x-bind:disabled="runtime !== 'node'"
					class="form-select"
					name="v_version"
					id="v_version"
					<?= $v_runtime !== "node" ? "x-cloak disabled" : "" ?>
				>
					<?php foreach ($node_versions as $key => $version) {
						$version_label = (string) $key === "system" ? _("System") : "Node.js " . $key;
						if ($version !== "") {
							$version_label .= " (" . $version . ")";
						}
						?>
						<option value="<?= tohtml($key) ?>"<?= (string) $key === (string) $v_version ? " selected" : "" ?>><?= tohtml($version_label) ?></option>
					<?php } ?>
				</select>
				<select
					x-show="runtime !== 'node'"
					x-bind:disabled="runtime === 'node'"
					class="form-select"
					name="v_version"
					aria-label="<?= tohtml( _("Version")) ?>"
					<?= $v_runtime === "node" ? "x-cloak disabled" : "" ?>
				>
					<option value="system"><?= tohtml( _("System")) ?></option>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_cmd" class="form-label">
					<?= tohtml( _("Start Command")) ?>
					<span x-show="runtime === 'node'" class="optional">(<?= tohtml( _("default")) ?>: npm start)</span>
				</label>
				<input
					type="text"
					class="form-control"
					name="v_cmd"
					id="v_cmd"
					value="<?= tohtml($v_cmd) ?>"
					x-bind:placeholder="placeholders[runtime]"
					placeholder="npm start"
				>
				<small class="hint">
					<?= tohtml( _("The app must listen on 127.0.0.1 and the port given in the PORT environment variable.")) ?>
				</small>
			</div>
			<div class="u-mb10">
				<label for="v_dir" class="form-label"><?= tohtml( _("App Directory")) ?></label>
				<input x-model="dir" type="text" class="form-control" name="v_dir" id="v_dir" value="<?= tohtml($v_dir) ?>" placeholder="app">
				<small class="hint">
					<span x-text="home + (dir || 'app').replace(/^\/+/, '')"><?= tohtml($app_x_data["home"] . $v_dir) ?></span>
				</small>
			</div>
			<div class="u-mb10">
				<label for="v_install" class="form-label">
					<?= tohtml( _("Install / Build Command")) ?> <span class="optional">(<?= tohtml( _("optional")) ?>)</span>
				</label>
				<input type="text" class="form-control" name="v_install" id="v_install" value="<?= tohtml($v_install) ?>" placeholder="npm ci && npm run build">
				<small class="hint">
					<?= tohtml( _("Leave empty to use the runtime default: npm ci (or npm install without a lock file) for Node.js, a virtualenv with pip install -r requirements.txt for Python, nothing for custom commands.")) ?>
				</small>
			</div>
		</form>
		<!-- End settings -->

		<!-- Begin environment variables -->
		<form id="env-form" name="v_edit_app_env" method="post" class="u-mb20">
			<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
			<input type="hidden" name="save_env" value="save">

			<h2 class="u-mb10"><?= tohtml( _("Environment Variables")) ?></h2>
			<div class="u-mb10">
				<label for="v_env" class="form-label u-hidden-visually"><?= tohtml( _("Environment Variables")) ?></label>
				<textarea class="form-control u-min-height300 u-console" name="v_env" id="v_env" placeholder="NODE_ENV=production"<?= $read_only === true ? " disabled" : "" ?>><?= tohtml($v_env) ?></textarea>
				<small class="hint">
					<?= tohtml( _("One KEY=VALUE per line, lines starting with # are ignored. PORT and HOST are set automatically. Saving restarts the app if it is running.")) ?>
				</small>
			</div>
			<?php if ($read_only !== true) { ?>
				<button type="submit" class="button button-secondary">
					<i class="fas fa-floppy-disk icon-purple"></i><?= tohtml( _("Save Environment Variables")) ?>
				</button>
			<?php } ?>
		</form>
		<!-- End environment variables -->

		<!-- Begin logs -->
		<div class="u-side-by-side u-mb10">
			<h2 id="app-logs"><?= tohtml( _("Logs")) ?></h2>
			<a href="/edit/app/?<?= tohtml(http_build_query($domain_query)) ?>#app-logs" class="button button-secondary">
				<i class="fas fa-arrows-rotate icon-green"></i><?= tohtml( _("Refresh")) ?>
			</a>
		</div>
		<p class="hint u-mb10"><?= tohtml(sprintf(_("Last %s lines"), 200)) ?></p>
		<?php if (trim($v_log) === "") { ?>
			<p class="u-mb20"><?= tohtml( _("No log entries yet.")) ?></p>
		<?php } else { ?>
			<pre class="console-output u-max-height600 u-overflow-auto u-mb20" x-data x-init="$el.scrollTop = $el.scrollHeight"><?= tohtml($v_log) ?></pre>
		<?php } ?>
		<!-- End logs -->
	</div>

</div>
