<div id="token" token="<?=$_SESSION['token']?>"></div>

<header class="top-bar">
	<div class="l-center top-bar-inner">

		<!-- Logo / Usage Statistics wrapper -->
		<div class="top-bar-left">

			<!-- Logo / Home Button -->
			<a href="<?=htmlspecialchars($home_url)?>" class="top-bar-logo" title="<?=_('Hestia Control Panel');?>">
				<img src="/images/logo-header.svg" alt="<?=_('Hestia Control Panel');?>" width="54" height="29">
			</a>

			<!-- Usage Statistics -->
			<div class="top-bar-usage">
				<?php
					if (isset($_SESSION['look'])) {
						$user_icon = 'fa-binoculars';
					} else if ($_SESSION['userContext'] === 'admin') {
						$user_icon = 'fa-user-tie';
					} else {
						$user_icon = 'fa-user';
					}
				?>
				<div class="top-bar-usage-inner">
					<span class="top-bar-usage-item">
						<i class="fas <?=$user_icon;?>" title="<?=_('Logged in as');?>: <?=htmlspecialchars($panel[$user]['NAME'])?>"></i>
						<b><?=htmlspecialchars($user)?></b>
					</span>
					<span class="top-bar-usage-item">
						<i class="fas fa-hard-drive" title="<?=_('Disk');?>: <?=humanize_usage_size($panel[$user]['U_DISK'])?> <?=humanize_usage_measure($panel[$user]['U_DISK'])?>"></i>
						<b><?=humanize_usage_size($panel[$user]['U_DISK'])?></b> <?=humanize_usage_measure($panel[$user]['U_DISK'])?>
					</span>
					<span class="top-bar-usage-item">
						<i class="fas fa-right-left" title="<?=_('Bandwidth');?>: <?=humanize_usage_size($panel[$user]['U_BANDWIDTH'])?> <?=humanize_usage_measure($panel[$user]['U_BANDWIDTH'])?>"></i>
						<b><?=humanize_usage_size($panel[$user]['U_BANDWIDTH'])?></b> <?=humanize_usage_measure($panel[$user]['U_BANDWIDTH'])?>
					</span>
				</div>
			</div>

		</div>

		<!-- Notifications / Menu wrapper -->
		<div class="top-bar-right">

			<!-- Notifications -->
			<?php if (($_SESSION['userContext'] === 'admin') && (isset($_SESSION['look']) && ($user == 'admin'))) {?>
				<!-- Do not show notifications panel when impersonating 'admin' user -->
			<?php } else { ?>
				<div class="top-bar-notifications">
					<button type="button" class="top-bar-nav-link js-notifications" title="<?=_('Notifications');?>">
						<i class="fas fa-bell <?php if($panel[$user]['NOTIFICATIONS'] == 'yes') echo 'animate__animated animate__swing status-icon orange' ?>"></i>
						<span class="u-hidden"><?=_('Notifications');?></span>
					</button>
					<ul class="notification-container animate__animated animate__fadeIn u-hidden"></ul>
				</div>
			<?php } ?>

			<!-- Menu -->
			<nav class="top-bar-nav">

				<button type="button" class="top-bar-nav-link u-hide-desktop js-toggle-top-bar-menu" title="<?=_('Toggle menu');?>">
					<i class="fas fa-bars"></i>
					<span class="u-hidden"><?=_('Toggle menu');?></span>
				</button>

				<ul class="top-bar-nav-list animate__animated animate__fadeIn">

					<!-- File Manager -->
					<?php if ((isset($_SESSION['FILE_MANAGER'])) && (!empty($_SESSION['FILE_MANAGER'])) && ($_SESSION['FILE_MANAGER'] == "true")) {?>
						<?php if (($_SESSION['userContext'] === 'admin') && (isset($_SESSION['look']) && ($_SESSION['look'] === 'admin') && ($_SESSION['POLICY_SYSTEM_PROTECTED_ADMIN'] == 'yes'))) {?>
							<!-- Hide file manager when impersonating admin-->
						<?php } else { ?>
							<li class="top-bar-nav-item">
								<a title="<?=_('File manager');?>" class="top-bar-nav-link <?php if($TAB == 'FM') echo 'active' ?>" href="/fm/">
									<i class="fas fa-folder-open"></i>
									<span class="top-bar-nav-link-label"><?=_('File manager');?></span>
								</a>
							</li>
						<?php } ?>
					<?php } ?>

					<!-- Server Settings -->
					<?php if (($_SESSION['userContext'] === 'admin') && ($_SESSION['POLICY_SYSTEM_HIDE_SERVICES'] !== 'yes') || ($_SESSION['user'] === 'admin')) {?>
						<?php if (($_SESSION['userContext'] === 'admin') && (!empty($_SESSION['look']))) {?>
							<!-- Hide 'Server Settings' button when impersonating 'admin' or other users -->
						<?php } else { ?>
							<li class="top-bar-nav-item">
								<a title="<?=_('Server');?>" class="top-bar-nav-link <?php if(in_array($TAB, ['SERVER', 'IP', 'RRD', 'FIREWALL'])) echo 'active' ?>" href="/list/server/">
									<i class="fas fa-gear"></i>
									<span class="top-bar-nav-link-label"><?=_('Server');?></span>
								</a>
							</li>
						<?php } ?>
					<?php } ?>

					<!-- Edit User -->
					<?php if (($_SESSION['userContext'] === 'admin') && (isset($_SESSION['look']) && ($user == 'admin'))) {?>
						<!-- Hide 'edit user' entry point from other administrators for default 'admin' account-->
						<li class="top-bar-nav-item">
							<a title="<?=_('Logs');?>" class="top-bar-nav-link <?php if($TAB == 'LOG') echo 'active' ?>" href="/list/log/">
								<i class="fas fa-clock-rotate-left"></i>
								<span class="top-bar-nav-link-label"><?=_('Logs');?></span>
							</a>
						</li>
					<?php } else { ?>
						<?php if ($panel[$user]['SUSPENDED'] === 'no') {?>
							<li class="top-bar-nav-item">
								<a title="<?=htmlspecialchars($user)?> (<?=htmlspecialchars($panel[$user]['NAME'])?>)" class="top-bar-nav-link" href="/edit/user/?user=<?=$user; ?>&token=<?=$_SESSION['token']?>">
									<i class="fas fa-circle-user"></i>
									<span class="top-bar-nav-link-label"><?=htmlspecialchars($user)?> (<?=htmlspecialchars($panel[$user]['NAME'])?>)</span>
								</a>
							</li>
						<?php } ?>
					<?php } ?>

					<!-- Statistics -->
					<li class="top-bar-nav-item">
						<a title="<?=_('Statistics');?>" class="top-bar-nav-link <?php if($TAB == 'STATS') echo 'active' ?>" href="/list/stats/">
							<i class="fas fa-chart-line"></i>
							<span class="top-bar-nav-link-label"><?=_('Statistics');?></span>
						</a>
					</li>

					<!-- Help / Documentation -->
					<li class="top-bar-nav-item">
						<a title="<?=_('Help');?>" class="top-bar-nav-link" href="https://docs.hestiacp.com/" target="_blank" rel="noopener">
							<i class="fas fa-circle-question"></i>
							<span class="top-bar-nav-link-label"><?=_('Help');?></span>
						</a>
					</li>

					<!-- Logout -->
					<?php if (isset($_SESSION['look']) && (!empty($_SESSION['look']))) { ?>
						<li class="top-bar-nav-item">
							<a title="<?=_('Log out');?> (<?=$user?>)" class="top-bar-nav-link top-bar-nav-link-logout" href="/logout/?token=<?=$_SESSION['token']?>">
								<i class="fas fa-circle-up"></i>
								<span class="top-bar-nav-link-label"><?=_('Log out');?> (<?=$user?>)</span>
							</a>
						</li>
					<?php } else { ?>
						<li class="top-bar-nav-item">
							<a title="<?=_('Log out');?>" class="top-bar-nav-link top-bar-nav-link-logout" href="/logout/?token=<?=$_SESSION['token']?>">
								<i class="fas fa-right-from-bracket"></i>
								<span class="top-bar-nav-link-label"><?=_('Log out');?></span>
							</a>
						</li>
					<?php } ?>

				</ul>
			</nav>

		</div>

	</div>
</header>

<div class="l-content">
	<div class="l-center">

		<nav class="main-menu">
			<ul class="main-menu-list">

				<!-- Users tab -->
				<?php if (($_SESSION['userContext'] == 'admin') && (empty($_SESSION['look']))) {?>
					<?php
						if (($_SESSION['user'] !== 'admin') && ($_SESSION['POLICY_SYSTEM_HIDE_ADMIN'] === 'yes')) {
							$user_count = $panel[$user]['U_USERS'] - 1;
						} else {
							$user_count = $panel[$user]['U_USERS'];
						}
					?>
					<li class="main-menu-item">
						<a class="main-menu-item-link <?php if(in_array($TAB, ['USER', 'LOG'])) echo 'active' ?>" href="/list/user/" title="<?=_('Users');?>: <?=$user_count;?>&#13;<?=_('Suspended');?>: <?=$panel[$user]['SUSPENDED_USERS']?>">
							<p class="main-menu-item-label"><?=_('USER');?><i class="fas fa-users u-ml10"></i></p>
							<ul class="main-menu-stats">
								<li>
									<?=_('users');?>: <span><?=htmlspecialchars($user_count);?></span>
								</li>
								<li>
									<?=_('spnd');?>: <span><?=$panel[$user]['SUSPENDED_USERS']?></span>
								</li>
							</ul>
						</a>
					</li>
				<?php } ?>

				<!-- Web tab -->
				<?php if ((isset($_SESSION['WEB_SYSTEM'])) && (!empty($_SESSION['WEB_SYSTEM']))) {?>
					<?php if($panel[$user]['WEB_DOMAINS'] != "0") { ?>
						<li class="main-menu-item">
							<a class="main-menu-item-link <?php if($TAB == 'WEB') echo 'active' ?>" href="/list/web/" title="<?=_('Domains');?>: <?=$panel[$user]['U_WEB_DOMAINS']?>&#13;<?=_('Aliases');?>: <?=$panel[$user]['U_WEB_ALIASES']?>&#13;<?=_('Limit')?>: <?=$panel[$user]['WEB_DOMAINS']=='unlimited' ? "∞" : $panel[$user]['WEB_DOMAINS']?>&#13;<?=_('Suspended');?>: <?=$panel[$user]['SUSPENDED_WEB']?>">
								<p class="main-menu-item-label"><?=_('WEB');?><i class="fas fa-earth-americas u-ml10"></i></p>
								<ul class="main-menu-stats">
									<li>
										<?=_('domains');?>: <span><?=$panel[$user]['U_WEB_DOMAINS']?> / <?=$panel[$user]['WEB_DOMAINS']=='unlimited' ? "<b>∞</b>" : $panel[$user]['WEB_DOMAINS']?> (<?=$panel[$user]['SUSPENDED_WEB']?>)</span>
									</li>
									<li>
										<?=_('aliases');?>: <span><?=$panel[$user]['U_WEB_ALIASES']?> / <?=$panel[$user]['WEB_ALIASES']=='unlimited' || $panel[$user]['WEB_DOMAINS']=='unlimited'	? "<b>∞</b>" : $panel[$user]['WEB_ALIASES'] * $panel[$user]['WEB_DOMAINS']?></span>
									</li>
								</ul>
							</a>
						</li>
					<?php } ?>
				<?php } ?>

				<!-- DNS tab -->
				<?php if ((isset($_SESSION['DNS_SYSTEM'])) && (!empty($_SESSION['DNS_SYSTEM']))) {?>
					<?php if($panel[$user]['DNS_DOMAINS'] != "0") { ?>
						<li class="main-menu-item">
							<a class="main-menu-item-link <?php if($TAB == 'DNS') echo 'active' ?>" href="/list/dns/" title="<?=_('Domains');?>: <?=$panel[$user]['U_DNS_DOMAINS']?>&#13;<?=_('Limit')?>: <?=$panel[$user]['DNS_DOMAINS']=='unlimited' ? "∞" : $panel[$user]['DNS_DOMAINS']?>&#13;<?=_('Suspended');?>: <?=$panel[$user]['SUSPENDED_DNS']?>">
								<p class="main-menu-item-label"><?=_('DNS');?><i class="fas fa-book-atlas u-ml10"></i></p>
								<ul class="main-menu-stats">
									<li>
										<?=_('zones');?>: <span><?=$panel[$user]['U_DNS_DOMAINS']?> / <?=$panel[$user]['DNS_DOMAINS']=='unlimited' ? "<b>∞</b>" : $panel[$user]['DNS_DOMAINS']?> (<?=$panel[$user]['SUSPENDED_DNS']?>)</span>
									</li>
									<li>
										<?=_('records');?>: <span><?=$panel[$user]['U_DNS_RECORDS']?> / <?=$panel[$user]['DNS_RECORDS']=='unlimited' || $panel[$user]['DNS_DOMAINS']=='unlimited' ? "<b>∞</b>" : $panel[$user]['DNS_RECORDS'] * $panel[$user]['DNS_DOMAINS']?></span>
									</li>
								</ul>
							</a>
						</li>
					<?php } ?>
				<?php } ?>

				<!-- Mail tab -->
				<?php if ((isset($_SESSION['MAIL_SYSTEM'])) && (!empty($_SESSION['MAIL_SYSTEM']))) {?>
					<?php if($panel[$user]['MAIL_DOMAINS'] != "0") { ?>
						<li class="main-menu-item">
							<a class="main-menu-item-link <?php if($TAB == 'MAIL') echo 'active' ?>" href="/list/mail/" title="<?=_('Domains');?>: <?=$panel[$user]['U_MAIL_DOMAINS']?>&#13;<?=_('Limit')?>: <?=$panel[$user]['MAIL_DOMAINS']=='unlimited' ? "∞" : $panel[$user]['MAIL_DOMAINS']?>&#13;<?=_('Suspended');?>: <?=$panel[$user]['SUSPENDED_MAIL']?>">
								<p class="main-menu-item-label"><?=_('MAIL');?><i class="fas fa-envelopes-bulk u-ml10"></i></p>
								<ul class="main-menu-stats">
									<li>
										<?=_('domains');?>: <span><?=$panel[$user]['U_MAIL_DOMAINS']?> / <?=$panel[$user]['MAIL_DOMAINS']=='unlimited' ? "<b>∞</b>" : $panel[$user]['MAIL_DOMAINS']?> (<?=$panel[$user]['SUSPENDED_MAIL']?>)</span>
									</li>
									<li>
										<?=_('accounts');?>: <span><?=$panel[$user]['U_MAIL_ACCOUNTS']?> / <?=$panel[$user]['MAIL_ACCOUNTS']=='unlimited' || $panel[$user]['MAIL_DOMAINS']=='unlimited' ? "<b>∞</b>" : $panel[$user]['MAIL_ACCOUNTS'] * $panel[$user]['MAIL_DOMAINS']?></span>
									</li>
								</ul>
							</a>
						</li>
					<?php } ?>
				<?php } ?>

				<!-- Databases tab -->
				<?php if ((isset($_SESSION['DB_SYSTEM'])) && (!empty($_SESSION['DB_SYSTEM']))) {?>
					<?php if($panel[$user]['DATABASES'] != "0") { ?>
						<li class="main-menu-item">
							<a class="main-menu-item-link <?php if($TAB == 'DB') echo 'active' ?>" href="/list/db/" title="<?=_('Databases');?>: <?=$panel[$user]['U_DATABASES']?>&#13;<?=_('Limit')?>: <?=$panel[$user]['DATABASES']=='unlimited' ? "∞" : $panel[$user]['DATABASES']?>&#13;<?=_('Suspended');?>: <?=$panel[$user]['SUSPENDED_DB']?>">
								<p class="main-menu-item-label"><?=_('DB');?><i class="fas fa-database u-ml10"></i></p>
								<ul class="main-menu-stats">
									<li>
										<?=_('databases');?>: <span><?=$panel[$user]['U_DATABASES']?> / <?=$panel[$user]['DATABASES']=='unlimited' ? "<b>∞</b>" : $panel[$user]['DATABASES']?> (<?=$panel[$user]['SUSPENDED_DB']?>)</span>
									</li>
								</ul>
							</a>
						</li>
					<?php } ?>
				<?php } ?>

				<!-- Cron tab -->
				<?php if ((isset($_SESSION['CRON_SYSTEM'])) && (!empty($_SESSION['CRON_SYSTEM']))) {?>
					<?php if($panel[$user]['CRON_JOBS'] != "0") { ?>
						<li class="main-menu-item">
							<a class="main-menu-item-link <?php if($TAB == 'CRON') echo 'active' ?>" href="/list/cron/" title="<?=_('Jobs');?>: <?=$panel[$user]['U_WEB_DOMAINS']?>&#13;<?=_('Limit')?>: <?=$panel[$user]['CRON_JOBS']=='unlimited' ? "∞" : $panel[$user]['CRON_JOBS']?>&#13;<?=_('Suspended');?>: <?=$panel[$user]['SUSPENDED_CRON']?>">
								<p class="main-menu-item-label"><?=_('CRON');?><i class="fas fa-clock u-ml10"></i></p>
								<ul class="main-menu-stats">
									<li>
										<?=_('jobs');?>: <span><?=$panel[$user]['U_CRON_JOBS']?> / <?=$panel[$user]['CRON_JOBS']=='unlimited' ? "<b>∞</b>" : $panel[$user]['CRON_JOBS']?> (<?=$panel[$user]['SUSPENDED_CRON']?>)</span>
									</li>
								</ul>
							</a>
						</li>
					<?php } ?>
				<?php } ?>

				<!-- Backups tab -->
				<?php if ((isset($_SESSION['BACKUP_SYSTEM'])) && (!empty($_SESSION['BACKUP_SYSTEM']))) {?>
					<?php if($panel[$user]['BACKUPS'] != "0") { ?>
						<li class="main-menu-item">
							<a class="main-menu-item-link <?php if($TAB == 'BACKUP') echo 'active' ?>" href="/list/backup/" title="<?=_('Backups');?>: <?=$panel[$user]['U_BACKUPS']?>&#13;<?=_('Limit')?>: <?=$panel[$user]['BACKUPS']=='unlimited' ? "∞" : $panel[$user]['BACKUPS']?>">
								<p class="main-menu-item-label"><?=_('BACKUP');?><i class="fas fa-file-zipper u-ml10"></i></p>
								<ul class="main-menu-stats">
									<li>
										<?=_('backups');?>: <span><?=$panel[$user]['U_BACKUPS']?> / <?=$panel[$user]['BACKUPS']=='unlimited' ? "<b>∞</b>" : $panel[$user]['BACKUPS']?></span>
									</li>
								</ul>
							</a>
						</li>
					<?php } ?>
				<?php } ?>

			</ul>
		</nav>

	</div>
