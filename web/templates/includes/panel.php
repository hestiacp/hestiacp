<?php hst_do_action("panel_init", $user, $panel); ?>
<div id="token" token="<?= $_SESSION["token"] ?>"></div>

<header class="app-header">

	<div class="top-bar">
		<div class="container top-bar-inner">

			<!-- Logo / Usage Statistics wrapper -->
			<div class="top-bar-left">

				<!-- Logo / Home Button -->
				<a href="/" class="top-bar-logo" title="<?= htmlentities($_SESSION["APP_NAME"]) ?>">
					<img src="/images/logo-header.svg" alt="<?= htmlentities($_SESSION["APP_NAME"]) ?>" width="54" height="29">
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
							<i class="fas <?= $user_icon ?>" title="<?= _("Logged in as") ?>: <?= htmlspecialchars($panel[$user]["NAME"]) ?>"></i>
							<b><?= htmlspecialchars($user) ?></b>
						</span>
						<span class="top-bar-usage-item">
							<i class="fas fa-hard-drive" title="<?= _("Disk") ?>: <?= humanize_usage_size($panel[$user]["U_DISK"]) ?> <?= humanize_usage_measure($panel[$user]["U_DISK"]) ?>"></i>
							<b><?= humanize_usage_size($panel[$user]["U_DISK"]) ?></b> <?= humanize_usage_measure($panel[$user]["U_DISK"]) ?>
						</span>
						<span class="top-bar-usage-item">
							<i class="fas fa-right-left" title="<?= _("Bandwidth") ?>: <?= humanize_usage_size($panel[$user]["U_BANDWIDTH"]) ?> <?= humanize_usage_measure($panel[$user]["U_BANDWIDTH"]) ?>"></i>
							<b><?= humanize_usage_size($panel[$user]["U_BANDWIDTH"]) ?></b> <?= humanize_usage_measure($panel[$user]["U_BANDWIDTH"]) ?>
						</span>
					</div>
				</div>

			</div>

			<!-- Notifications / Menu wrapper -->
			<div class="top-bar-right">

				<!-- Notifications -->
				<?php
				$impersonatingAdmin = ($_SESSION['userContext'] === 'admin') && (isset($_SESSION['look']) && ($user == 'admin'));
				// Do not show notifications panel when impersonating 'admin' user
				if (!$impersonatingAdmin) { ?>
					<div x-data="notifications" class="top-bar-notifications">
						<button
							x-on:click="toggle()"
							x-bind:class="open && 'active'"
							class="top-bar-menu-link"
							type="button"
							title="<?= _("Notifications") ?>"
						>
							<i
								x-bind:class="{
									'animate__animated animate__swing icon-orange': (!initialized && <?= $panel[$user]["NOTIFICATIONS"] == "yes" ? "true" : "false" ?>) || notifications.length != 0,
									'fas fa-bell': true
								}"
							></i>
							<span class="u-hidden"><?= _("Notifications") ?></span>
						</button>
						<ul
							x-cloak
							x-show="open"
							x-on:click.outside="open = false"
							class="top-bar-notifications-list"
						>
							<template x-if="initialized && notifications.length == 0">
								<li class="top-bar-notification-item empty">
									<i class="fas fa-bell-slash icon-dim"></i>
									<p><?= _("No notifications") ?></p>
								</li>
							</template>
							<template x-for="notification in notifications" :key="notification.ID">
								<li
									x-bind:id="`notification-${notification.ID}`"
									x-bind:class="notification.ACK && 'unseen'"
									class="top-bar-notification-item"
								>
									<div class="top-bar-notification-header">
										<p x-text="notification.TOPIC" class="top-bar-notification-title"></p>
										<button
											x-on:click="remove(notification.ID)"
											type="button"
											class="top-bar-notification-delete"
											title="<?= _("Delete notification") ?>"
										>
											<i class="fas fa-xmark"></i>
										</button>
									</div>
									<div x-html="notification.NOTICE"></div>
									<p class="top-bar-notification-timestamp">
										<time
											:datetime="`${notification.DATE}T${notification.TIME}`"
											x-text="`${notification.TIME} ${notification.DATE}`"
										></time>
									</p>
								</li>
							</template>
							<template x-if="initialized && notifications.length > 2">
								<li>
									<button
										x-on:click="removeAll()"
										type="button"
										class="top-bar-notification-delete-all"
									>
										<i class="fas fa-check"></i>
										<?= _("Delete all notifications") ?>
									</button>
								</li>
							</template>
						</ul>
					</div>
				<?php } ?>

				<!-- Menu -->
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

					<ul x-cloak x-show="open" x-on:click.outside="open = false" class="top-bar-menu-list">
                        <?php hst_do_action("render_header_menu"); ?>
					</ul>
				</nav>

			</div>

		</div>
	</div>

	<nav x-data="{ open: false }" class="main-menu">
		<div class="container">
			<button x-on:click="open = !open" type="button" class="main-menu-toggle">
				<i class="fas fa-bars"></i>
				<span
					x-text="open ? '<?= _("Collapse main menu") ?>' : '<?= _("Expand main menu") ?>'"
					class="main-menu-toggle-label"
				>
					<?= _("Expand main menu") ?>
				</span>
			</button>
			<ul x-cloak x-show="open" class="main-menu-list">
                <?php hst_do_action("render_menu"); ?>
			</ul>
		</div>
	</nav>

</header>

<main class="app-content">
<?php hst_do_action("pre_load_template"); ?>
