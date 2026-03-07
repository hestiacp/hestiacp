<!doctype html>
<html lang="en">

<head>
	<!-- Load necessary CSS and JavaScript from source -->
	<?php require $_SERVER["HESTIA"] . "/web/templates/includes/title.php"; ?>
	<?php require $_SERVER["HESTIA"] . "/web/templates/includes/css.php"; ?>
	<?php require $_SERVER["HESTIA"] . "/web/templates/includes/js.php"; ?>
</head>

<body class="page-server-info">

	<div class="app">

		<header class="app-header">
			<div class="top-bar">
				<div class="container top-bar-inner">
					<div class="top-bar-left">
						<a href="/" class="top-bar-logo" title="<?= tohtml( _("Hestia Control Panel")) ?>">
							<img src="/images/logo-header.svg" alt="<?= tohtml( _("Hestia Control Panel")) ?>" width="54" height="29">
						</a>
					</div>
					<div class="top-bar-right">
						<nav x-data="{ open: false }" class="top-bar-menu">
							<button
								type="button"
								class="top-bar-menu-link u-hide-tablet"
								x-on:click="open = !open">
								<i class="fas fa-bars"></i>
								<span class="u-hidden" x-text="open ? '<?= tohtml( _("Close menu")) ?>' : '<?= tohtml( _("Open menu")) ?>'">
									<?= tohtml( _("Open menu")) ?>
								</span>
							</button>
							<div x-cloak x-show="open" x-on:click.outside="open = false" class="top-bar-menu-panel">
								<ul class="top-bar-menu-list">
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link" href="/list/rrd/" title="<?= tohtml( _("Back")) ?>">
											<i class="fas fa-circle-left"></i>
											<span class="top-bar-menu-link-label"><?= tohtml( _("Back")) ?></span>
										</a>
									</li>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if (isset($_GET['cpu'])) echo 'active' ?>" href="/list/server/?cpu">
											<i class="fas fa-microchip"></i>
											<span class="top-bar-menu-link-label"><?= tohtml( _("CPU")) ?></span>
										</a>
									</li>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if (isset($_GET['mem'])) echo 'active' ?>" href="/list/server/?mem">
											<i class="fas fa-memory"></i>
											<span class="top-bar-menu-link-label"><?= tohtml( _("RAM")) ?></span>
										</a>
									</li>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if (isset($_GET['disk'])) echo 'active' ?>" href="/list/server/?disk">
											<i class="fas fa-hard-drive"></i>
											<span class="top-bar-menu-link-label"><?= tohtml( _("Disk")) ?></span>
										</a>
									</li>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if (isset($_GET['net'])) echo 'active' ?>" href="/list/server/?net">
											<i class="fas fa-hard-drive"></i>
											<span class="top-bar-menu-link-label"><?= tohtml( _("Network")) ?></span>
										</a>
									</li>
									<?php if ((isset($_SESSION['WEB_SYSTEM'])) && (!empty($_SESSION['WEB_SYSTEM']))) { ?>
										<li class="top-bar-menu-item">
											<a class="top-bar-menu-link <?php if (isset($_GET['web'])) echo 'active' ?>" href="/list/server/?web">
												<i class="fas fa-earth-europe"></i>
												<span class="top-bar-menu-link-label"><?= tohtml( _("Web")) ?></span>
											</a>
										</li>
									<?php } ?>
									<?php if ((isset($_SESSION['DNS_SYSTEM'])) && (!empty($_SESSION['DNS_SYSTEM']))) { ?>
										<li class="top-bar-menu-item">
											<a class="top-bar-menu-link <?php if (isset($_GET['dns'])) echo 'active' ?>" href="/list/server/?dns">
												<i class="fas fa-book-atlas"></i>
												<span class="top-bar-menu-link-label"><?= tohtml( _("DNS")) ?></span>
											</a>
										</li>
									<?php } ?>
									<?php if ((isset($_SESSION['MAIL_SYSTEM'])) && (!empty($_SESSION['MAIL_SYSTEM']))) { ?>
										<li class="top-bar-menu-item">
											<a class="top-bar-menu-link <?php if (isset($_GET['mail'])) echo 'active' ?>" href="/list/server/?mail">
												<i class="fas fa-envelopes-bulk"></i>
												<span class="top-bar-menu-link-label"><?= tohtml( _("Mail")) ?></span>
											</a>
										</li>
									<?php } ?>
									<?php if ((isset($_SESSION['DB_SYSTEM'])) && (!empty($_SESSION['DB_SYSTEM']))) { ?>
										<li class="top-bar-menu-item">
											<a class="top-bar-menu-link <?php if (isset($_GET['db'])) echo 'active' ?>" href="/list/server/?db">
												<i class="fas fa-database"></i>
												<span class="top-bar-menu-link-label"><?= tohtml( _("DB")) ?></span>
											</a>
										</li>
									<?php } ?>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link" href="javascript:location.reload();" title="<?= tohtml( _("Refresh")) ?>">
											<i class="fas fa-arrow-rotate-right"></i>
											<span class="u-hidden"><?= tohtml( _("Refresh")) ?></span>
										</a>
									</li>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link top-bar-menu-link-logout" href="/logout/?token=<?= tohtml($_SESSION["token"]) ?>" title="<?= tohtml( _("Log out")) ?>">
											<i class="fas fa-right-from-bracket"></i>
											<span class="u-hidden"><?= tohtml( _("Log out")) ?></span>
										</a>
									</li>
								</ul>
							</div>
						</nav>
					</div>
				</div>
			</div>
		</header>

		<main class="app-content">

			<div class="logs-container">

				<div class="container">
					<pre class="console-output u-mt20">
