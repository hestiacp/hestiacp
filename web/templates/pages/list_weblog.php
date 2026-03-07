<!doctype html>
<html lang="en">

<head>
	<!-- Load necessary CSS and JavaScript from source -->
	<?php require $_SERVER["HESTIA"] . "/web/templates/includes/title.php"; ?>
	<?php require $_SERVER["HESTIA"] . "/web/templates/includes/css.php"; ?>
	<?php require $_SERVER["HESTIA"] . "/web/templates/includes/js.php"; ?>
</head>

<body class="page-weblog">

	<div class="app">

		<header class="app-header">
			<div class="top-bar">
				<div class="container top-bar-inner">
					<div class="top-bar-left">
						<a href="/" class="top-bar-logo" title="<?= tohtml($_SESSION['APP_NAME']) ?>">
							<img src="<?php if ( !empty($_SESSION['LOGO_HEADER'])){ echo $_SESSION['LOGO_HEADER']; } else{ echo "/images/logo-header.svg"; } ?>" alt="<?= tohtml($_SESSION['APP_NAME']) ?>" width="54" height="29">
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
										<a class="top-bar-menu-link" href="/list/web/" title="<?= tohtml( _("Back")) ?>">
											<i class="fas fa-circle-left"></i>
											<span class="top-bar-menu-link-label"><?= tohtml( _("Back")) ?></span>
										</a>
									</li>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if ($_GET['type'] == 'access') echo 'active' ?>" href="/list/web-log/?domain=<?= tohtml($_GET['domain']) ?>&type=access&token=<?= tohtml($_SESSION['token']) ?>" title="<?= tohtml( _("View Logs")) ?>">
											<i class="fas fa-eye"></i>
											<span class="top-bar-menu-link-label"><?= tohtml( _("View Logs")) ?></span>
										</a>
									</li>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if ($_GET['type'] == 'access') echo 'active' ?>" href="/download/web-log/?domain=<?= tohtml($_GET['domain']) ?>&type=access&&token=<?= tohtml($_SESSION['token']) ?>" title="<?= tohtml( _("Download")) ?>">
											<i class="fas fa-download"></i>
											<span class="u-hidden"><?= tohtml( _("Download")) ?></span>
										</a>
									</li>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if ($_GET['type'] == 'error') echo 'active' ?>" href="/list/web-log/?domain=<?= tohtml($_GET['domain']) ?>&type=error&token=<?= tohtml($_SESSION['token']) ?>" title="<?= tohtml( _("Error Log")) ?>">
											<i class="fas fa-circle-exclamation"></i>
											<span class="top-bar-menu-link-label"><?= tohtml( _("Error Log")) ?></span>
										</a>
									</li>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link <?php if ($_GET['type'] == 'error') echo 'active' ?>" href="/download/web-log/?domain=<?= tohtml($_GET['domain']) ?>&type=error&token=<?= tohtml($_SESSION['token']) ?>" title="<?= tohtml( _("Download")) ?>">
											<i class="fas fa-download"></i>
											<span class="u-hidden"><?= tohtml( _("Download")) ?></span>
										</a>
									</li>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link" href="javascript:location.reload();" title="<?= tohtml( _("Refresh")) ?>">
											<i class="fas fa-arrow-rotate-right"></i>
											<span class="u-hidden"><?= tohtml( _("Refresh")) ?></span>
										</a>
									</li>
									<li class="top-bar-menu-item">
										<a class="top-bar-menu-link" href="/list/user/" title="<?= tohtml($user) ?>">
											<i class="fas fa-circle-user"></i>
											<span class="u-hidden"><?= tohtml($user) ?></span>
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

				<p class="u-text-center u-mb20">
					<?= tohtml(sprintf(_("Last 70 lines of %s.%s.log"), $_GET["domain"], $type)) ?>
				</p>
				<pre class="console-output">
