<!-- Begin toolbar -->
<?php $v_laravel_web_list_query = empty($v_laravel_user_query ?? []) ? "" : "?" . tohtml(http_build_query($v_laravel_user_query)); ?>
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/web/<?= $v_laravel_web_list_query ?>">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">
	<div class="laravel-page">
		<div class="laravel-page-header">
			<h1><?= tohtml( _("Laravel Apps")) ?></h1>
			<p class="laravel-page-subtitle"><?= tohtml( _("Manage registered Laravel applications from one place.")) ?></p>
		</div>
		<?php show_alert_message($_SESSION); ?>

		<?php if (empty($v_laravel_apps)) { ?>
			<div class="laravel-empty-state">
				<i class="fab fa-laravel icon-red"></i>
				<h2><?= tohtml( _("No Laravel applications registered")) ?></h2>
				<p><?= tohtml( _("Install Laravel through Quick Install or scan an existing application from a web domain.")) ?></p>
				<a class="button" href="/list/web/<?= $v_laravel_web_list_query ?>">
					<i class="fas fa-globe icon-blue"></i><?= tohtml( _("Open Web Domains")) ?>
				</a>
			</div>
		<?php } else { ?>
			<div class="laravel-app-grid">
				<?php foreach ($v_laravel_apps as $domain => $app) { ?>
					<?php $v_laravel_domain_query = array_merge($v_laravel_user_query ?? [], ["domain" => $domain]); ?>
					<article class="laravel-app-card">
						<div class="laravel-app-card-header">
							<div>
								<h2><a href="/edit/laravel/?<?= tohtml(http_build_query($v_laravel_domain_query)) ?>"><?= tohtml($domain) ?></a></h2>
								<p><?= tohtml($app["APP_ROOT"] ?? "") ?></p>
							</div>
							<i class="fab fa-laravel icon-red"></i>
						</div>
						<div class="laravel-app-meta">
							<span>PHP <?= tohtml($app["PHP_VERSION"] ?? "") ?></span>
							<span><?= tohtml( _("Source")) ?>: <?= tohtml($app["SOURCE_TYPE"] ?? "") ?></span>
							<span class="laravel-badge <?= ($app["SCHEDULER"] ?? "") === "yes" ? "is-ok" : "" ?>"><?= tohtml( _("Scheduler")) ?>: <?= tohtml($app["SCHEDULER"] ?? "no") ?></span>
							<span class="laravel-badge <?= ($app["QUEUE"] ?? "") === "yes" ? "is-ok" : "" ?>"><?= tohtml( _("Queue")) ?>: <?= tohtml($app["QUEUE"] ?? "no") ?></span>
						</div>
						<div class="laravel-app-actions">
							<a class="button" href="/edit/laravel/?<?= tohtml(http_build_query($v_laravel_domain_query)) ?>">
								<i class="fas fa-screwdriver-wrench icon-purple"></i><?= tohtml( _("Manage")) ?>
							</a>
							<a class="button button-secondary" href="/edit/web/?<?= tohtml(http_build_query($v_laravel_domain_query)) ?>">
								<i class="fas fa-globe icon-blue"></i><?= tohtml( _("Domain")) ?>
							</a>
						</div>
					</article>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</div>
