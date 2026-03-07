<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($_SESSION["userContext"] === "admin" && $_SESSION["look"] == '') { ?>
				<a class="button button-secondary" href='/list/stats/'><i class="fas fa-binoculars icon-lightblue"></i><?= tohtml( _("Overall Statistics")) ?></a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<?php if ($_SESSION["userContext"] === "admin" && $_SESSION["look"] == '') { ?>
				<form x-data x-bind="BulkEdit" action="/list/stats/" method="get">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<select class="form-select" name="user">
						<option value=""><?= tohtml( _("Show Per User")) ?></option>
						<?php
							foreach ($users as $key => $value) {
								if (($_SESSION['POLICY_SYSTEM_HIDE_ADMIN'] === 'yes') && ($value === 'admin')) {
									// Hide admin user from statistics list
								} else {
								echo "\t\t\t\t<option value=\"".$value."\"";
								if ((!empty($v_user)) && ( $value == $_GET['user'])){
									echo ' selected';
								}
									echo ">".$value."</option>\n";
								}
							}
						?>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= tohtml( _("Apply to selected")) ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			<?php } ?>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<input type="search" class="form-control js-search-input" name="q" value="<? echo isset($_POST['q']) ? htmlspecialchars($_POST['q']) : '' ?>" title="<?= tohtml( _("Search")) ?>">
					<button type="submit" class="toolbar-input-submit" title="<?= tohtml( _("Search")) ?>">
						<i class="fas fa-magnifying-glass"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<!-- Begin statistics list item loop -->
	<div class="stats">
		<?php foreach ($data as $key => $value) {
		++$i; ?>
			<div class="stats-item">

				<div class="stats-item-header">
					<i class="fas fa-chart-bar icon-dim stats-item-header-icon u-mr10"></i>
					<h2 class="stats-item-header-title">
						<?php $date = new DateTime($key);
						echo _($date -> format('M')) .' '.$date -> format('Y') ?>
					</h2>
				</div>

				<div class="stats-item-content">

					<div class="stats-item-summary">
						<h3 class="stats-item-summary-title">
							<span class="u-text-bold">
								<i class="fas fa-right-left icon-dim icon-large u-mr5" title="<?= tohtml( _("Bandwidth")) ?>"></i>
								<?= tohtml( _("Bandwidth")) ?>
							</span>
							<span class="u-mr10">
								<span class="u-text-bold"><?= tohtml(humanize_usage_size($data[$key]["U_BANDWIDTH"])) ?></span>
								<?= tohtml(humanize_usage_measure($data[$key]["U_BANDWIDTH"])) ?> / <span class="u-text-bold"><?= tohtml(humanize_usage_size($data[$key]["BANDWIDTH"])) ?></span>
									<?= tohtml(humanize_usage_measure($data[$key]["BANDWIDTH"])) ?>
							</span>
						</h3>
						<ul class="stats-item-summary-list u-mb10">
							<li class="stats-item-summary-list-item">
								<span>
									<?php if ($_SESSION["userContext"] === "admin" || ($_SESSION["userContext"] === "user" && $data[$key]["IP_OWNED"] != "0")) { ?>
										<?= tohtml( _("IP Addresses")) ?>:
									<?php } ?>
								</span>
								<span>
									<span class="u-text-bold"><?= tohtml($data[$key]["IP_OWNED"]) ?></span>
									<?= tohtml( _("IPs")) ?>
								</span>
							</li>
						</ul>
						<h3 class="stats-item-summary-title">
							<span class="u-text-bold">
								<i class="fas fa-hard-drive icon-dim icon-large u-mr5" title="Disk"></i>
								<?= tohtml( _("Disk")) ?>
							</span>
							<span class="u-mr10">
								<span class="u-text-bold"><?= tohtml(humanize_usage_size($data[$key]["U_DISK"])) ?></span>
								<?= tohtml(humanize_usage_measure($data[$key]["U_DISK"])) ?> / <span class="u-text-bold"><?= tohtml(humanize_usage_size($data[$key]["DISK_QUOTA"])) ?></span>
										<?= tohtml(humanize_usage_measure($data[$key]["DISK_QUOTA"])) ?>
								</span>
							</span>
						</h3>
						<ul class="stats-item-summary-list">
							<li class="stats-item-summary-list-item">
								<span>
									<?= tohtml( _("Web")) ?>:
								</span>
								<span>
									<span class="u-text-bold"><?= tohtml(humanize_usage_size($data[$key]["U_DISK_WEB"])) ?></span>
									<?= tohtml(humanize_usage_measure($data[$key]["U_DISK_WEB"])) ?>
								</span>
							</li>
							<li class="stats-item-summary-list-item u-mb5">
								<span>
									<?= tohtml( _("Databases")) ?>:
								</span>
								<span>
									<span class="u-text-bold"><?= tohtml(humanize_usage_size($data[$key]["U_DISK_DB"])) ?></span>
									<?= tohtml(humanize_usage_measure($data[$key]["U_DISK_DB"])) ?>
								</span>
							</li>
							<li class="stats-item-summary-list-item">
								<span>
									<?= tohtml( _("Mail")) ?>:
								</span>
								<span>
									<span class="u-text-bold"><?= tohtml(humanize_usage_size($data[$key]["U_DISK_MAIL"])) ?></span>
									<?= tohtml(humanize_usage_measure($data[$key]["U_DISK_MAIL"])) ?>
								</span>
							</li>
							<li class="stats-item-summary-list-item">
								<span>
									<?= tohtml( _("User Directory")) ?>:
								</span>
								<span>
									<span class="u-text-bold"><?= tohtml(humanize_usage_size($data[$key]["U_DISK_DIRS"])) ?></span>
									<?= tohtml(humanize_usage_measure($data[$key]["U_DISK_DIRS"])) ?>
								</span>
							</li>
						</ul>
					</div>

					<ul class="stats-item-list">
						<li class="stats-item-list-item">
							<span class="stats-item-list-item-label">
								<?= tohtml( _("Web Domains")) ?>:
							</span>
							<span class="stats-item-list-item-value">
								<?= tohtml($data[$key]["U_WEB_DOMAINS"]) ?>
							</span>
						</li>
						<li class="stats-item-list-item">
							<span class="stats-item-list-item-label">
								<?= tohtml( _("Mail Domains")) ?>:
							</span>
							<span class="stats-item-list-item-value">
								<?= tohtml($data[$key]["U_MAIL_DOMAINS"]) ?>
							</span>
						</li>
						<li class="stats-item-list-item">
							<span class="stats-item-list-item-label">
								<?= tohtml( _("SSL Domains")) ?>:
							</span>
							<span class="stats-item-list-item-value">
								<?= tohtml($data[$key]["U_WEB_SSL"]) ?>
							</span>
						</li>
						<li class="stats-item-list-item">
							<span class="stats-item-list-item-label">
								<?= tohtml( _("Mail Accounts")) ?>:
							</span>
							<span class="stats-item-list-item-value">
								<?= tohtml($data[$key]["U_MAIL_ACCOUNTS"]) ?>
							</span>
						</li>
						<li class="stats-item-list-item">
							<span class="stats-item-list-item-label">
								<?= tohtml( _("Web Aliases")) ?>:
							</span>
							<span class="stats-item-list-item-value">
								<?= tohtml($data[$key]["U_WEB_ALIASES"]) ?>
							</span>
						</li>
						<li class="stats-item-list-item">
							<span class="stats-item-list-item-label">
								<?= tohtml( _("Databases")) ?>:
							</span>
							<span class="stats-item-list-item-value">
								<?= tohtml($data[$key]["U_DATABASES"]) ?>
							</span>
						</li>
						<li class="stats-item-list-item">
							<span class="stats-item-list-item-label">
								<?= tohtml( _("DNS Zones")) ?>:
							</span>
							<span class="stats-item-list-item-value">
								<?= tohtml($data[$key]["U_DNS_DOMAINS"]) ?>
							</span>
						</li>
						<li class="stats-item-list-item">
							<span class="stats-item-list-item-label">
								<?= tohtml( _("Cron Jobs")) ?>:
							</span>
							<span class="stats-item-list-item-value">
								<?= tohtml($data[$key]["U_CRON_JOBS"]) ?>
							</span>
						</li>
						<li class="stats-item-list-item">
							<span class="stats-item-list-item-label">
								<?= tohtml( _("DNS Records")) ?>:
							</span>
							<span class="stats-item-list-item-value">
								<?= tohtml($data[$key]["U_DNS_RECORDS"]) ?>
							</span>
						</li>
						<li class="stats-item-list-item">
							<span class="stats-item-list-item-label">
								<?= tohtml( _("Backups")) ?>:
							</span>
							<span class="stats-item-list-item-value">
								<?= tohtml($data[$key]["U_BACKUPS"]) ?>
							</span>
						</li>
					</ul>

				</div>

			</div>
		<?php } ?>
	</div>

	<div class="units-table-footer">
		<p>
			<?php printf(ngettext("%d month", "%d months", $i), $i); ?>
		</p>
	</div>

</div>
