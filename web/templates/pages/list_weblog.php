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
					<a href="/" class="top-bar-logo" title="<?= htmlentities($_SESSION['APP_NAME']);?>">
						<img src="<?php if ( !empty($_SESSION['LOGO_HEADER'])){ echo $_SESSION['LOGO_HEADER']; } else{ echo "/images/logo-header.svg"; } ?>" alt="<?= htmlentities($_SESSION['APP_NAME']);?>" width="54" height="29">
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
						<ul x-cloak x-show="open" class="top-bar-menu-list">
							<li class="top-bar-menu-item">
								<a class="top-bar-menu-link" href="/list/web/">
									<i class="fas fa-circle-left"></i>
									<span class="top-bar-menu-link-label"><?= _("Back") ?></span>
								</a>
							</li>
							<li class="top-bar-menu-item">
								<a class="top-bar-menu-link <?php if($_GET['type'] == 'access') echo 'active' ?>" href="/list/web-log/?domain=<?=htmlentities($_GET['domain'])?>&type=access&token=<?=$_SESSION['token']?>">
									<i class="fas fa-eye"></i>
									<span class="top-bar-menu-link-label"><?= _("Access Log") ?></span>
								</a>
							</li>
							<li class="top-bar-menu-item">
								<a class="top-bar-menu-link <?php if($_GET['type'] == 'access') echo 'active' ?>" href="/download/web-log/?domain=<?=htmlentities($_GET['domain'])?>&type=access&&token=<?=$_SESSION['token']?>" title="<?= _("Download") ?>">
									<i class="fas fa-download"></i>
									<span class="u-hidden"><?= _("Download") ?></span>
								</a>
							</li>
							<li class="top-bar-menu-item">
								<a class="top-bar-menu-link <?php if($_GET['type'] == 'error') echo 'active' ?>" href="/list/web-log/?domain=<?=htmlentities($_GET['domain'])?>&type=error&token=<?=$_SESSION['token']?>">
									<i class="fas fa-circle-exclamation"></i>
									<span class="top-bar-menu-link-label"><?= _("Error Log") ?></span>
								</a>
							</li>
							<li class="top-bar-menu-item">
								<a class="top-bar-menu-link <?php if($_GET['type'] == 'error') echo 'active' ?>" href="/download/web-log/?domain=<?=htmlentities($_GET['domain'])?>&type=error&token=<?=$_SESSION['token']?>" title="<?= _("Download") ?>">
									<i class="fas fa-download"></i>
									<span class="u-hidden"><?= _("Download") ?></span>
								</a>
							</li>
							<li class="top-bar-menu-item">
								<a class="top-bar-menu-link" href="javascript:location.reload();" title="<?= _("Refresh") ?>">
									<i class="fas fa-arrow-rotate-right"></i>
									<span class="u-hidden"><?= _("Refresh") ?></span>
								</a>
							</li>
							<li class="top-bar-menu-item">
								<a class="top-bar-menu-link" href="/edit/user/" title="<?= htmlentities($user) ?>">
									<i class="fas fa-circle-user"></i>
									<span class="u-hidden"><?= htmlentities($user) ?></span>
								</a>
							</li>
							<li class="top-bar-menu-item">
								<a class="top-bar-menu-link top-bar-menu-link-logout" href="/logout/?token=<?= $_SESSION["token"] ?>" title="<?= _("Log out") ?>">
									<i class="fas fa-right-from-bracket"></i>
									<span class="u-hidden"><?= _("Log out") ?></span>
								</a>
							</li>
						</ul>
					</nav>
				</div>
			</div>
		</div>
	</header>

	<a
		href="#top"
		class="button button-secondary button-circle button-floating button-floating-top "
		title="<?= _("Top") ?>"
	>
		<i class="fas fa-arrow-up"></i>
		<span class="u-hidden"><?= _("Top") ?></span>
	</a>

	<div class="container" style="padding-top: 80px;"><?= sprintf(_("Last 70 lines of %s.%s.log"), htmlentities($_GET["domain"]), htmlentities($type)) ?></div>
	<pre class="container console-output">
