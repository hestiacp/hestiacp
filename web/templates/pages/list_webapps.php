<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/edit/web/?domain=<?= htmlentities($v_domain) ?>">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<div class="form-container form-container-wide">
		<h1 class="u-mb20"><?= _("Quick Install App") ?></h1>
		<?php show_alert_message($_SESSION); ?>
		<div class="cards">
			<!-- List available web apps -->
			<?php foreach ($v_web_apps as $webapp): ?>
				<div class="card <?= $webapp["enabled"] ? "" : "disabled" ?>">
					<div class="card-thumb">
						<img src="/src/app/WebApp/Installers/<?= $webapp["name"] ?>/<?= $webapp["thumbnail"] ?>" alt="<?= $webapp["name"] ?>">
					</div>
					<div class="card-content">
						<p class="card-title"><?= $webapp["name"] ?></p>
						<p class="u-mb10"><?= _("Version") ?>: <?= $webapp["version"] ?></p>
						<a class="button" href="/add/webapp/?app=<?= $webapp["name"] ?>&domain=<?= htmlentities($v_domain) ?>">
							<?= _("Setup") ?>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

</div>
