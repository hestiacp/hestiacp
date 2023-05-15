<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/add/mail/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add Mail Domain") ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<b>
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?=$label;?> <i class="fas fa-arrow-down-a-z"></i>
					</b>
				</button>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn u-hidden">
					<li entity="sort-accounts" sort_as_int="1"><span class="name"><?= _("Accounts") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-date" sort_as_int="1"><span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-disk" sort_as_int="1"><span class="name"><?= _("Disk") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-name"><span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
				</ul>
				<?php if ($read_only !== 'true') {?>
					<form x-data x-bind="BulkEdit" action="/bulk/mail/" method="post">
						<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
						<select class="form-select" name="action">
							<option value=""><?= _("Apply to selected") ?></option>
							<?php if ($_SESSION['userContext'] === 'admin') {?>
								<option value="rebuild"><?= _("Rebuild All") ?></option>
							<?php } ?>
							<option value="suspend"><?= _("Suspend") ?></option>
							<option value="unsuspend"><?= _("Unsuspend") ?></option>
							<option value="delete"><?= _("Delete") ?></option>
						</select>
						<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
							<i class="fas fa-arrow-right"></i>
						</button>
					</form>
				<?php } ?>
			</div>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
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
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				<input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>" <?= $display_mode ?>>
			</div>
			<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("Name") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-right compact-5"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-2"><b><?= _("Accounts") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-2"><b><?= _("Disk") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-3"><b><?= _("Anti-Virus") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-3"><b><?= _("Spam Filter") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-3"><b><?= _("DKIM") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-3"><b><?= _("SSL") ?></b></div>
		</div>
	</div>

	<!-- Begin mail domain list item loop -->
	<?php
		list($http_host, $port) = explode(':', $_SERVER["HTTP_HOST"].":");
		$webmail = "webmail";
		if (!empty($_SESSION['WEBMAIL_ALIAS'])) $webmail = $_SESSION['WEBMAIL_ALIAS'];
		foreach ($data as $key => $value) {
			++$i;
			if ($data[$key]['SUSPENDED'] == 'yes') {
				$status = 'suspended';
				$spnd_action = 'unsuspend';
				$spnd_action_title = _('Unsuspend');
				$spnd_icon = 'fa-play';
				$spnd_confirmation = _('Are you sure you want to unsuspend domain %s?');
				if ($data[$key]['ANTIVIRUS'] == 'no') {
					$antivirus_icon = 'fa-circle-xmark';
				} else {
					$antivirus_icon = 'fa-circle-check';
				}
				if ($data[$key]['ANTISPAM'] == 'no') {
					$antispam_icon = 'fa-circle-xmark';
				} else {
					$antispam_icon = 'fa-circle-check';
				}
				if ($data[$key]['DKIM'] == 'no') {
					$dkim_icon = 'fa-circle-xmark';
				} else {
					$dkim_icon = 'fa-circle-check';
				}
				if ($data[$key]['SSL'] == 'no') {
					$ssl_icon = 'fa-circle-xmark';
				} else {
					$ssl_icon = 'fa-circle-check';
				}
			} else {
				$status = 'active';
				$spnd_action = 'suspend';
				$spnd_action_title = _('Suspend');
				$spnd_icon = 'fa-pause';
				$spnd_confirmation = _('Are you sure you want to suspend domain %s?');
				if ($data[$key]['ANTIVIRUS'] == 'no') {
					$antivirus_icon = 'fa-circle-xmark icon-red';
				} else {
					$antivirus_icon = 'fa-circle-check icon-green';
				}
				if ($data[$key]['ANTISPAM'] == 'no') {
					$antispam_icon = 'fa-circle-xmark icon-red';
				} else {
					$antispam_icon = 'fa-circle-check icon-green';
				}
				if ($data[$key]['DKIM'] == 'no') {
					$dkim_icon = 'fa-circle-xmark icon-red';
				} else {
					$dkim_icon = 'fa-circle-check icon-green';
				}
				if ($data[$key]['SSL'] == 'no') {
					$ssl_icon = 'fa-circle-xmark icon-red';
				} else {
					$ssl_icon = 'fa-circle-check icon-green';
				}
			}
			if (empty($data[$key]['CATCHALL'])) {
				$data[$key]['CATCHALL'] = '/dev/null';
			}
		?>
		<div class="l-unit <?php if ($status == 'suspended') echo 'l-unit--suspended'; ?> animate__animated animate__fadeIn" v_unit_id="<?=$key?>" v_section="mail"
			sort-date="<?=strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])?>" sort-name="<?=$key?>" sort-disk="<?=$data[$key]['U_DISK']?>"
			sort-accounts="<?=$data[$key]['ACCOUNTS']?>">
			<div class="l-unit__col l-unit__col--right">
				<div>
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check<?=$i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="domain[]" value="<?=$key?>" <?=$display_mode;?>>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-3 truncate"><b><a href="?domain=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Mail Accounts") ?>: <?=$key?>"><?=$key?></a></b></div>
					<!-- START QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left u-text-right compact-5">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<?php if ($read_only === 'true') {?>
									<!-- Restrict ability to edit, delete, or suspend domain items when impersonating 'admin' account -->
									<div class="actions-panel__col actions-panel__edit shortcut-l" data-key-action="href"><a href="?domain=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Mail Accounts") ?>"><i class="fas fa-users icon-blue icon-dim"></i></a></div>
									<div class="actions-panel__col actions-panel__edit shortcut-l" data-key-action="href"><a href="?domain=<?=$key?>&dns=1&token=<?=$_SESSION['token']?>" title="<?= _("DNS Records") ?>"><i class="fas fa-book-atlas icon-blue icon-dim"></i></a></div>
									<?php if ($data[$key]['SUSPENDED'] == 'no') {?>
										<div class="actions-panel__col actions-panel__edit" data-key-action="href"><a href="http://<?=$webmail;?>.<?=$key?>/" target="_blank" title="<?= _("Open Webmail") ?>"><i class="fas fa-paper-plane icon-lightblue icon-dim"></i></a></div>
									<?php } ?>
								<?php } else { ?>
									<?php if ($data[$key]['SUSPENDED'] == 'no') {?>
										<div class="actions-panel__col actions-panel__logs shortcut-n" data-key-action="href"><a href="/add/mail/?domain=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Add Mail Account") ?>"><i class="fas fa-circle-plus icon-green icon-dim"></i></a></div>
										<?php if($_SESSION['WEBMAIL_SYSTEM']){?>
											<?php if (!empty($data[$key]['WEBMAIL'])) {?>
												<div class="actions-panel__col actions-panel__edit" data-key-action="href"><a href="http://<?=$webmail;?>.<?=$key?>/" target="_blank" title="<?= _("Open Webmail") ?>"><i class="fas fa-paper-plane icon-lightblue icon-dim"></i></a></div>
											<?php } ?>
										<?php } ?>
										<div class="actions-panel__col actions-panel__logs shortcut-enter" data-key-action="href"><a href="/edit/mail/?domain=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Edit Mail Domain") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a></div>
									<?php } ?>
									<div class="actions-panel__col actions-panel__edit shortcut-l" data-key-action="href"><a href="?domain=<?=$key?>&dns=1&token=<?=$_SESSION['token']?>" title="<?= _("DNS Records") ?>"><i class="fas fa-book-atlas icon-blue icon-dim"></i></a></div>
									<div class="actions-panel__col actions-panel__suspend shortcut-s" data-key-action="js">
										<a
											class="data-controls js-confirm-action"
											href="/<?=$spnd_action?>/mail/?domain=<?=$key?>&token=<?=$_SESSION['token']?>"
											data-confirm-title="<?= $spnd_action_title ?>"
											data-confirm-message="<?= sprintf($spnd_confirmation, $key) ?>"
										>
											<i class="fas <?= $spnd_icon ?> icon-highlight icon-dim"></i>
										</a>
									</div>
									<div class="actions-panel__col actions-panel__delete shortcut-delete" data-key-action="js">
										<a
											class="data-controls js-confirm-action"
											href="/delete/mail/?domain=<?=$key?>&token=<?=$_SESSION['token']?>"
											data-confirm-title="<?= _("Delete") ?>"
											data-confirm-message="<?= sprintf(_('Are you sure you want to delete domain %s?'), $key) ?>"
										>
											<i class="fas fa-trash icon-red icon-dim"></i>
										</a>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
					<!-- END QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left u-text-center compact-2"><b>
							<?php
								if ($data[$key]['ACCOUNTS']) {
									$mail_accounts = htmlentities($data[$key]['ACCOUNTS']);
								} else {
									$mail_accounts = '0';
								}
							?>
							<span><?=$mail_accounts;?></span>
						</b>
					</div>
					<div class="clearfix l-unit__stat-col--left u-text-center compact-2"><b>
							<?=humanize_usage_size($data[$key]['U_DISK'])?></b> <span class="u-text-small"><?=humanize_usage_measure($data[$key]['U_DISK'])?></span>
					</div>
					<div class="clearfix l-unit__stat-col--left u-text-center compact-3">
						<i class="fas <?=$antivirus_icon;?>"></i>
					</div>
					<div class="clearfix l-unit__stat-col--left u-text-center compact-3">
						<i class="fas <?=$antispam_icon;?>"></i>
					</div>
					<div class="clearfix l-unit__stat-col--left u-text-center compact-3">
						<i class="fas <?=$dkim_icon;?>"></i>
					</div>
					<div class="clearfix l-unit__stat-col--left u-text-center compact-3">
						<i class="fas <?=$ssl_icon;?>"></i>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext('%d mail domain', '%d mail domains', $i),$i); ?>
		</p>
	</div>
</footer>
