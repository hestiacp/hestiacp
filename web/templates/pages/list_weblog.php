<!doctype html>
<html lang="en">

<head>
	<!-- Load necessary CSS and JavaScript from source -->
	<?php require ''.$_SERVER['HESTIA'].'/web/templates/includes/title.php'; ?>
	<?php require ''.$_SERVER['HESTIA'].'/web/templates/includes/css.php'; ?>
	<?php require ''.$_SERVER['HESTIA'].'/web/templates/includes/js.php'; ?>
</head>

<body>
	<header class="top-bar">
		<div class="l-center top-bar-inner">
			<div class="top-bar-left">
				<a href="/" class="top-bar-logo" title="<?=_('Hestia Control Panel');?>">
					<img src="/images/logo-header.svg" alt="<?=_('Hestia Control Panel');?>" width="54" height="29">
				</a>
			</div>
			<div class="top-bar-right">
				<nav class="top-bar-nav">
					<button type="button" class="top-bar-nav-link u-hide-tablet js-toggle-top-bar-menu" title="<?=_('Toggle menu');?>">
						<i class="fas fa-bars"></i>
						<span class="u-hidden"><?=_('Toggle menu');?></span>
					</button>
					<ul class="top-bar-nav-list animate__animated animate__fadeIn">
						<li class="top-bar-nav-item">
							<a class="top-bar-nav-link" href="/list/web/">
								<i class="fas fa-circle-left"></i>
								<span class="top-bar-nav-link-label"><?=_('Back');?></span>
							</a>
						</li>
						<li class="top-bar-nav-item">
							<a class="top-bar-nav-link <?php if($_GET['type'] == 'access') echo 'active' ?>" href="/list/web-log/?domain=<?=htmlentities($_GET['domain'])?>&type=access&token=<?=$_SESSION['token']?>">
								<i class="fas fa-eye"></i>
								<span class="top-bar-nav-link-label"><?=_('Access Log');?></span>
							</a>
						</li>
						<li class="top-bar-nav-item">
							<a class="top-bar-nav-link <?php if($_GET['type'] == 'access') echo 'active' ?>" href="/download/web-log/?domain=<?=htmlentities($_GET['domain'])?>&type=access&&token=<?=$_SESSION['token']?>" title="<?=_('Download');?>">
								<i class="fas fa-download"></i>
								<span class="u-hidden"><?=_('Download');?></span>
							</a>
						</li>
						<li class="top-bar-nav-item">
							<a class="top-bar-nav-link <?php if($_GET['type'] == 'error') echo 'active' ?>" href="/list/web-log/?domain=<?=htmlentities($_GET['domain'])?>&type=error&token=<?=$_SESSION['token']?>">
								<i class="fas fa-circle-exclamation"></i>
								<span class="top-bar-nav-link-label"><?=_('Error Log');?></span>
							</a>
						</li>
						<li class="top-bar-nav-item">
							<a class="top-bar-nav-link <?php if($_GET['type'] == 'error') echo 'active' ?>" href="/download/web-log/?domain=<?=htmlentities($_GET['domain'])?>&type=error&token=<?=$_SESSION['token']?>" title="<?=_('Download');?>">
								<i class="fas fa-download"></i>
								<span class="u-hidden"><?=_('Download');?></span>
							</a>
						</li>
						<li class="top-bar-nav-item">
							<a class="top-bar-nav-link" href="javascript:location.reload();" title="<?=_('Refresh');?>">
								<i class="fas fa-arrow-rotate-right"></i>
								<span class="u-hidden"><?=_('Refresh');?></span>
							</a>
						</li>
						<li class="top-bar-nav-item">
							<a class="top-bar-nav-link" href="/edit/user/" title="<?=htmlentities($user)?>">
								<i class="fas fa-circle-user"></i>
								<span class="u-hidden"><?=htmlentities($user)?></span>
							</a>
						</li>
						<li class="top-bar-nav-item">
							<a class="top-bar-nav-link top-bar-nav-link-logout" href="/logout/?token=<?=$_SESSION['token']?>" title="<?=_('Log out');?>">
								<i class="fas fa-right-from-bracket"></i>
								<span class="u-hidden"><?=_('Log out');?></span>
							</a>
						</li>
					</ul>
				</nav>
			</div>
		</div>
	</header>

	<button type="button" class="button button-secondary button-circle button-floating button-floating-top js-to-top" title="<?=_('Top');?>">
		<i class="fas fa-arrow-up"></i>
		<span class="u-hidden"><?=_('Top');?></span>
	</button>

	<div class="l-center" style="padding-top: 80px;"><?=sprintf(_('Last 70 lines of %s.%s.log'),htmlentities($_GET['domain']),htmlentities($type)) ;?></div>
	<pre class="l-center console-output">
