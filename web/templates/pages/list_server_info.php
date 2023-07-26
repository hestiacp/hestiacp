<!doctype html>
<html lang="en">

<head>
	<!-- Load necessary CSS and JavaScript from source -->
	<?php require "" . $_SERVER["HESTIA"] . "/web/templates/includes/title.php"; ?>
	<?php require "" . $_SERVER["HESTIA"] . "/web/templates/includes/css.php"; ?>
	<?php require "" . $_SERVER["HESTIA"] . "/web/templates/includes/js.php"; ?>
</head>

<body>
	<header class="app-header">
		<div class="top-bar">
			<div class="container top-bar-inner">
				<div class="top-bar-left">
					<a href="/" class="top-bar-logo" title="<?= _("Hestia Control Panel") ?>">
						<img src="/images/logo-header.svg" alt="<?= _("Hestia Control Panel") ?>" width="54" height="29">
					</a>
				</div>
				<div class="top-bar-right">
					<nav x-data="{ open: false }" class="top-bar-menu">
						<button
							type="button"
							class="top-bar-menu-link u-hide-tablet"
							x-on:click="open = !open">
							<i class="fas fa-bars"></i>
							<span class="u-hidden" x-text="open ? '<?= _("Close menu") ?>' : '<?= _("Open menu") ?>'">
								<?= _("Open menu") ?>
							</span>
						</button>
						<div x-cloak x-show="open" x-on:click.outside="open = false" class="top-bar-menu-panel">
							<ul class="top-bar-menu-list">
								<li class="top-bar-menu-item">
									<a class="top-bar-menu-link" href="/list/rrd/" title="<?= _("Back") ?>">
										<i class="fas fa-circle-left"></i>
										<span class="top-bar-menu-link-label"><?= _("Back") ?></span>
									</a>
								</li>
								<li class="top-bar-menu-item">
									<a class="top-bar-menu-link <?php if (isset($_GET['cpu'])) echo 'active' ?>" href="/list/server/?cpu">
										<i class="fas fa-microchip"></i>
										<span class="top-bar-menu-link-label"><?= _("CPU") ?></span>
									</a>
								</li>
								<li class="top-bar-menu-item">
									<a class="top-bar-menu-link <?php if (isset($_GET['mem'])) echo 'active' ?>" href="/list/server/?mem">
										<i class="fas fa-memory"></i>
										<span class="top-bar-menu-link-label"><?= _("RAM") ?></span>
									</a>
								</li>
								<li class="top-bar-menu-item">
									<a class="top-bar-menu-link <?php if (isset($_GET['disk'])) echo 'active' ?>" href="/list/server/?disk">
										<i class="fas fa-hard-drive"></i>
										<span class="top-bar-menu-link-label"><?= _("Disk") ?></span>
									</a>
								</li>
								<li class="top-bar-menu-item">
									<a class="top-bar-menu-link <?php if (isset($_GET['net'])) echo 'active' ?>" href="/list/server/?net">
										<i class="fas fa-hard-drive"></i>
										<span class="top-bar-menu-link-label"><?= _("Network") ?></span>
									</a>
								</li>
								<?php if ((isset($_SESSION['WEB_SYSTEM'])) && (!empty($_SESSION['WEB_SYSTEM']))) { ?>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if (isset($_GET['web'])) echo 'active' ?>" href="/list/server/?web">
											<i class="fas fa-earth-europe"></i>
											<span class="top-bar-menu-link-label"><?= _("Web") ?></span>
										</a>
									</li>
								<?php } ?>
								<?php if ((isset($_SESSION['DNS_SYSTEM'])) && (!empty($_SESSION['DNS_SYSTEM']))) { ?>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if (isset($_GET['dns'])) echo 'active' ?>" href="/list/server/?dns">
											<i class="fas fa-book-atlas"></i>
											<span class="top-bar-menu-link-label"><?= _("DNS") ?></span>
										</a>
									</li>
								<?php } ?>
								<?php if ((isset($_SESSION['MAIL_SYSTEM'])) && (!empty($_SESSION['MAIL_SYSTEM']))) { ?>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if (isset($_GET['mail'])) echo 'active' ?>" href="/list/server/?mail">
											<i class="fas fa-envelopes-bulk"></i>
											<span class="top-bar-menu-link-label"><?= _("Mail") ?></span>
										</a>
									</li>
								<?php } ?>
								<?php if ((isset($_SESSION['DB_SYSTEM'])) && (!empty($_SESSION['DB_SYSTEM']))) { ?>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if (isset($_GET['db'])) echo 'active' ?>" href="/list/server/?db">
											<i class="fas fa-database"></i>
											<span class="top-bar-menu-link-label"><?= _("DB") ?></span>
										</a>
									</li>
								<?php } ?>
								<li class="top-bar-menu-item">
									<a class="top-bar-menu-link" href="javascript:location.reload();" title="<?= _("Refresh") ?>">
										<i class="fas fa-arrow-rotate-right"></i>
										<span class="u-hidden"><?= _("Refresh") ?></span>
									</a>
								</li>
								<li class="top-bar-menu-item">
									<a class="top-bar-menu-link top-bar-menu-link-logout" href="/logout/?token=<?= $_SESSION["token"] ?>" title="<?= _("Log out") ?>">
										<i class="fas fa-right-from-bracket"></i>
										<span class="u-hidden"><?= _("Log out") ?></span>
									</a>
								</li>
							</ul>
						</div>
					</nav>
				</div>
			</div>
		</div>
	</header>

	<a
		href="#top"
		class="button button-secondary button-circle button-floating button-floating-top"
		title="<?= _("Top") ?>"
	>
		<i class="fas fa-arrow-up"></i>
		<span class="u-hidden"><?= _("Top") ?></span>
	</a>

	<div class="container">
		<pre class="console-output u-mt20">
