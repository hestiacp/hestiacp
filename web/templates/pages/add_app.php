<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/app/?<?= tohtml(http_build_query($user_query)) ?>">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<?php if ($read_only !== true && !empty($domains)) { ?>
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
		$app_x_data = [
			"runtime" => $v_runtime,
			"domain" => in_array($v_domain, $domains, true) ? $v_domain : ($domains[0] ?? ""),
			"dir" => $v_dir,
			"home" => "/home/" . $v_username . "/web/",
			"placeholders" => [
				"node" => "npm start",
				"python" => "gunicorn app:app",
				"custom" => "./start.sh",
			],
		];
	?>

	<form
		x-data="<?= tohtml(json_encode($app_x_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR)) ?>"
		id="main-form"
		name="v_add_app"
		method="post"
	>
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Add App")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<?php if (empty($domains)) { ?>
				<div class="alert alert-info u-mb20" role="alert">
					<i class="fas fa-info"></i>
					<p><?= tohtml( _("There is no web domain without an app. Add a web domain first.")) ?></p>
				</div>
				<?php if ($read_only !== true) { ?>
					<a href="/add/web/" class="button button-secondary">
						<i class="fas fa-circle-plus icon-green"></i><?= tohtml( _("Add Web Domain")) ?>
					</a>
				<?php } ?>
			<?php } else { ?>
				<p class="hint u-mb20">
					<?= tohtml( _("The app runs as a background service under your user account and is published on the domain through a reverse proxy. It must listen on 127.0.0.1 and the port given in the PORT environment variable.")) ?>
				</p>
				<div class="u-mb10">
					<label for="v_domain" class="form-label"><?= tohtml( _("Domain")) ?></label>
					<select x-model="domain" class="form-select" name="v_domain" id="v_domain" required>
						<?php foreach ($domains as $domain) { ?>
							<option value="<?= tohtml($domain) ?>"<?= $domain === $app_x_data["domain"] ? " selected" : "" ?>><?= tohtml($domain) ?></option>
						<?php } ?>
					</select>
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
							$version_label = $key === "system" ? _("System") : "Node.js " . $key;
							if ($version !== "") {
								$version_label .= " (" . $version . ")";
							}
							?>
							<option value="<?= tohtml($key) ?>"<?= (string) $key === $v_version ? " selected" : "" ?>><?= tohtml($version_label) ?></option>
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
				</div>
				<div class="u-mb10">
					<label for="v_dir" class="form-label"><?= tohtml( _("App Directory")) ?></label>
					<input x-model="dir" type="text" class="form-control" name="v_dir" id="v_dir" value="<?= tohtml($v_dir) ?>" placeholder="app">
					<small class="hint">
						<span x-text="home + domain + '/' + (dir || 'app').replace(/^\/+/, '')"><?= tohtml("/home/" . $v_username . "/web/" . $app_x_data["domain"] . "/" . $v_dir) ?></span>
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
				<div class="u-mb10">
					<label for="v_env" class="form-label">
						<?= tohtml( _("Environment Variables")) ?> <span class="optional">(<?= tohtml( _("optional")) ?>)</span>
					</label>
					<textarea class="form-control u-min-height100 u-console" name="v_env" id="v_env" placeholder="NODE_ENV=production"><?= tohtml($v_env) ?></textarea>
					<small class="hint">
						<?= tohtml( _("One KEY=VALUE per line, lines starting with # are ignored. PORT and HOST are set automatically.")) ?>
					</small>
				</div>
			<?php } ?>
		</div>

	</form>

</div>
