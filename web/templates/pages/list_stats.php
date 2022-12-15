<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if (($_SESSION['userContext'] === 'admin') && (!isset($_SESSION['look']))) { ?>
				<a class="button button-secondary" href='/list/stats/'><i class="fas fa-binoculars icon-lightblue"></i><?= _("Overall Statistics") ?></a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<?php if (($_SESSION['userContext'] === 'admin') && (!isset($_SESSION['look']))) { ?>
				<form x-bind="BulkEdit" action="/list/stats/" method="get">
					<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
					<select class="form-select" name="user">
						<option value=""><?= _("show per user") ?></option>
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
					<button type="submit" class="toolbar-input-submit" title="<?= _("apply to selected") ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			<?php } ?>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
					<input type="search" class="form-control js-search-input" name="q" value="<? echo isset($_POST['q']) ? htmlspecialchars($_POST['q']) : '' ?>" title="<?= _("Search") ?>">
					<button type="submit" class="toolbar-input-submit" title="<?= _("Search") ?>">
						<i class="fas fa-magnifying-glass"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container units">

	<!-- Begin statistics list item loop -->
	<?php foreach ($data as $key => $value) {
 	++$i; ?>
		<div class="header animate__animated animate__fadeIn">
			<div class="l-unit">
				<div class="l-unit-toolbar clearfix">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--left">
					</div>
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right">
						<div class="actions-panel clearfix">
						</div>
					</div>
				</div>
				<div class="l-unit__col l-unit__col--left clearfix">
					<i class="fas fa-chart-bar icon-dim" style="font-size: 3em;margin-left: 20px;margin-top: 10px;"></i>
				</div>
				<div class="l-unit__col l-unit__col--right">
					<div class="l-unit__name separate">
						<?php $date = new DateTime($key);
						echo _($date -> format('M')) .' '.$date -> format('Y') ?>
					</div>
					<div class="l-unit__stats">
						<table>
							<tr>
								<td>
									<div class="l-unit__stat-cols clearfix">
										<div class="l-unit__stat-cols clearfix graph">
											<div class="l-unit__stat-col l-unit__stat-col--left">
												<i class="fas fa-right-left icon-dim icon-large icon-pad-right" title="<?= _("Bandwidth") ?>"></i><b><?= _("Bandwidth") ?></b>
											</div>
											<div class="l-unit__stat-col l-unit__stat-col--right u-text-right">
												<b><?= humanize_usage_size($data[$key]["U_BANDWIDTH"]) ?></b> <?= humanize_usage_measure($data[$key]["U_BANDWIDTH"]) ?>
											</div>
										</div>
										<div class="l-percent">
											<div class="l-percent__fill" style="width: <?= get_percentage($data[$key]["U_BANDWIDTH"], $data[$key]["BANDWIDTH"]) ?>%"></div>
										</div>
									</div>
								</td>
								<td>
									<div class="l-unit__stat-cols clearfix">
										<div class="l-unit__stat-col l-unit__stat-col--left u-text-right icon-pad-right u-text-italic"><?= _("Web Domains") ?>:</div>
										<div class="l-unit__stat-col l-unit__stat-col--right statistics-count">
											<b><?= $data[$key]["U_WEB_DOMAINS"] ?></b>
										</div>
									</div>
								</td>
								<td>
									<div class="l-unit__stat-cols clearfix last">
										<div class="l-unit__stat-col l-unit__stat-col--left u-text-right icon-pad-right u-text-italic"><?= _("Mail Domains") ?>:</div>
										<div class="l-unit__stat-col l-unit__stat-col--right statistics-count">
											<b><?= $data[$key]["U_MAIL_DOMAINS"] ?></b>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="l-unit__stat-cols clearfix u-text-small">
										<div class="u-text-right">
											<?php if ($_SESSION["userContext"] === "admin" || ($_SESSION["userContext"] === "user" && $data[$key]["IP_OWNED"] != "0")) { ?>
												<span style="float: left;font-weight:500;"><?= _("IP Addresses") ?>:</span><b><?= $data[$key]["IP_OWNED"] ?></b> <?= _("IPs") ?></span>
											<?php } ?>
										</div>
									</div>
								</td>
								<td>
									<div class="l-unit__stat-cols clearfix">
										<div class="l-unit__stat-col l-unit__stat-col--left u-text-right icon-pad-right u-text-italic"><?= _("SSL Domains") ?>:</div>
										<div class="l-unit__stat-col l-unit__stat-col--right statistics-count">
											<b><?= $data[$key]["U_WEB_SSL"] ?></b>
										</div>
									</div>
								</td>
								<td>
									<div class="l-unit__stat-cols clearfix last">
										<div class="l-unit__stat-col l-unit__stat-col--left u-text-right icon-pad-right u-text-italic"><?= _("Mail Accounts") ?>:</div>
										<div class="l-unit__stat-col l-unit__stat-col--right statistics-count">
											<b><?= $data[$key]["U_MAIL_ACCOUNTS"] ?></b>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="l-unit__stat-cols clearfix graph">
										<div class="l-unit__stat-col l-unit__stat-col--left"><i class="fas fa-hard-drive icon-dim icon-large icon-pad-right" title="<?= _("Disk") ?>"></i><b><?= _("Disk") ?></b></div>
										<div class="l-unit__stat-col l-unit__stat-col--right u-text-right">
											<b><?= humanize_usage_size($data[$key]["U_DISK"]) ?></b> <?= humanize_usage_measure($data[$key]["U_DISK"]) ?>
										</div>
									</div>
									<div class="l-percent">
										<div class="l-percent__fill" style="width: <?= get_percentage($data[$key]["U_DISK"], $data[$key]["DISK_QUOTA"]) ?>%"></div>
									</div>
								</td>
								<td>
									<div class="l-unit__stat-cols clearfix">
										<div class="l-unit__stat-col l-unit__stat-col--left u-text-right icon-pad-right u-text-italic"><?= _("Web Aliases") ?>:</div>
										<div class="l-unit__stat-col l-unit__stat-col--right statistics-count">
											<b><?= $data[$key]["U_WEB_ALIASES"] ?></b>
										</div>
									</div>
								</td>
								<td>
									<div class="l-unit__stat-cols clearfix last">
										<div class="l-unit__stat-col l-unit__stat-col--left u-text-right icon-pad-right u-text-italic"><?= _("Databases") ?>:</div>
										<div class="l-unit__stat-col l-unit__stat-col--right statistics-count">
											<b><?= $data[$key]["U_DATABASES"] ?></b>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="l-unit__stat-cols clearfix u-text-small">
										<div class="u-text-right">
											<span style="float: left;font-weight:500;"><?= _("Web") ?>:</span> <b><?= humanize_usage_size($data[$key]["U_DISK_WEB"]) ?></b> <?= humanize_usage_measure($data[$key]["U_DISK_WEB"]) ?>
										</div>
										<div class="u-text-right">
											<span style="float: left;font-weight:500;"><?= _("Databases") ?>:</span> <b><?= humanize_usage_size($data[$key]["U_DISK_DB"]) ?></b> <?= humanize_usage_measure($data[$key]["U_DISK_DB"]) ?>
										</div>
									</div>
								</td>
								<td>
									<div class="l-unit__stat-cols clearfix">
										<div class="l-unit__stat-col l-unit__stat-col--left u-text-right icon-pad-right u-text-italic"><?= _("DNS domains") ?>:</div>
										<div class="l-unit__stat-col l-unit__stat-col--right statistics-count">
											<b><?= $data[$key]["U_DNS_DOMAINS"] ?></b>
										</div>
									</div>
								</td>
								<td>
									<div class="l-unit__stat-cols clearfix last">
										<div class="l-unit__stat-col l-unit__stat-col--left u-text-right icon-pad-right u-text-italic"><?= _("Cron Jobs") ?>:</div>
										<div class="l-unit__stat-col l-unit__stat-col--right statistics-count">
											<b><?= $data[$key]["U_CRON_JOBS"] ?></b>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="l-unit__stat-cols clearfix u-text-small">
										<div class="u-text-right">
											<span style="float: left;font-weight:500;"><?= _("Mail") ?>:</span> <b><?= humanize_usage_size($data[$key]["U_DISK_MAIL"]) ?></b> <?= humanize_usage_measure($data[$key]["U_DISK_MAIL"]) ?>
										</div>
										<div class="u-text-right">
											<span style="float: left;font-weight:500;"><?= _("User Directories") ?>:</span> <b><?= humanize_usage_size($data[$key]["U_DISK_DIRS"]) ?></b> <?= humanize_usage_measure($data[$key]["U_DISK_DIRS"]) ?>
										</div>
									</div>
								</td>
								<td>
									<div class="l-unit__stat-cols clearfix">
										<div class="l-unit__stat-col l-unit__stat-col--left u-text-right icon-pad-right u-text-italic"><?= _("DNS records") ?>:</div>
										<div class="l-unit__stat-col l-unit__stat-col--right statistics-count">
											<b><?= $data[$key]["U_DNS_RECORDS"] ?></b>
										</div>
									</div>
								</td>
								<td>
									<div class="l-unit__stat-cols clearfix last">
										<div class="l-unit__stat-col l-unit__stat-col--left u-text-right icon-pad-right u-text-italic"><?= _("Backups") ?>:</div>
										<div class="l-unit__stat-col l-unit__stat-col--right statistics-count">
											<b><?= $data[$key]["U_BACKUPS"] ?></b>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d month", "%d months", $i), $i); ?>
		</p>
	</div>
</footer>
