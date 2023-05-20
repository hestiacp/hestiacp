<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/add/web/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add Web Domain") ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<b>
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = ('Name'); } else { $label = _('Date'); } ?>
						<?=$label?> <i class="fas fa-arrow-down-a-z"></i>
					</b>
				</button>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn js-sorting-menu u-hidden">
					<li data-entity="sort-bandwidth" data-sort-as-int="1">
						<span class="name"><?= _("Bandwidth") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-disk" data-sort-as-int="1">
						<span class="name"><?= _("Disk") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-ip" data-sort-as-int="1">
						<span class="name"><?= _("IP Address") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/web/" method="post">
						<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
						<select class="form-select" name="action">
							<option value=""><?= _("Apply to selected") ?></option>
							<?php if ($_SESSION["userContext"] === "admin") { ?>
								<option value="rebuild"><?= _("Rebuild") ?></option>
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

	<!-- Table header -->
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				<input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>" <?= $display_mode ?>>
			</div>
			<div class="clearfix l-unit__stat-col--left wide-4"><b><?= _("Name") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-4 u-text-right"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("IP Address") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Disk") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact"><b><?= _("Bandwidth") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("SSL") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact"><b><?= _("Statistics") ?></b></div>
		</div>
	</div>

	<!-- Begin web domain list item loop -->
	<?php
		foreach ($data as $key => $value) {
				++$i;
				if ($data[$key]['SUSPENDED'] == 'yes') {
						$status = 'suspended';
						$spnd_action = 'unsuspend';
						$spnd_action_title = _('Unsuspend');
						$spnd_icon = 'fa-play';
						$spnd_confirmation = _('Are you sure you want to unsuspend domain %s?');
				} else {
						$status = 'active';
						$spnd_action = 'suspend';
						$spnd_action_title = _('Suspend');
						$spnd_icon = 'fa-pause';
						$spnd_confirmation = _('Are you sure you want to suspend domain %s?');
				}
				if (!empty($data[$key]['SSL_HOME'])) {
						if ($data[$key]['SSL_HOME'] == 'same') {
								$ssl_home = 'public_html';
						} else {
								$ssl_home = 'public_shtml';
						}
				} else {
						$ssl_home = '';
				}
				$web_stats='no';
				if (!empty($data[$key]['STATS'])) {
						$web_stats=$data[$key]['STATS'];
				}
				$ftp_user='no';
				if (!empty($data[$key]['FTP_USER'])) {
						$ftp_user=$data[$key]['FTP_USER'];

				}
				if (strlen($ftp_user) > 24 ) {
						$ftp_user = str_replace(':', ', ', $ftp_user);
						$ftp_user = substr($ftp_user, 0, 24);
						$ftp_user = trim($ftp_user, ":");
						$ftp_user = str_replace(':', ', ', $ftp_user);
						$ftp_user = $ftp_user.", ...";
				} else {
						$ftp_user = str_replace(':', ', ', $ftp_user);
				}

				$backend_support='no';
				if (!empty($data[$key]['BACKEND'])) {
						$backend_support='yes';
				}

				$proxy_support='no';
				if (!empty($data[$key]['PROXY'])) {
						$proxy_support='yes';
				}
				if (strlen($data[$key]['PROXY_EXT']) > 24 ) {
						$proxy_ext_title = str_replace(',', ', ', $data[$key]['PROXY_EXT']);
						$proxy_ext = substr($data[$key]['PROXY_EXT'], 0, 24);
						$proxy_ext = trim($proxy_ext, ",");
						$proxy_ext = str_replace(',', ', ', $proxy_ext);
						$proxy_ext = $proxy_ext.", ...";
				} else {
						$proxy_ext_title = '';
						$proxy_ext = str_replace(',', ', ', $data[$key]['PROXY_EXT']);
				}
				if ($data[$key]['SUSPENDED'] === 'yes') {
					if ($data[$key]['SSL'] == 'no') {
						$icon_ssl = 'fas fa-circle-xmark';
					}
					if ($data[$key]['SSL'] == 'yes') {
						$icon_ssl = 'fas fa-circle-check';
					}
					if ($web_stats == 'no') {
						$icon_webstats = 'fas fa-circle-xmark';
					} else {
						$icon_webstats = 'fas fa-circle-check';
					}
				} else {
					if ($data[$key]['SSL'] == 'no') {
						$icon_ssl = 'fas fa-circle-xmark icon-red';
					}
					if ($data[$key]['SSL'] == 'yes') {
						$icon_ssl = 'fas fa-circle-check icon-green';
					}
					if ($web_stats == 'no') {
						$icon_webstats = 'fas fa-circle-xmark icon-red';
					} else {
						$icon_webstats = 'fas fa-circle-check icon-green';
					}
				}
			?>
			<div class="l-unit <?php if ($data[$key]['SUSPENDED'] == 'yes') echo 'l-unit--suspended'; ?> animate__animated animate__fadeIn js-sortable-unit"
				data-sort-ip="<?=str_replace('.', '', $data[$key]['IP'])?>"
				data-sort-date="<?=strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])?>"
				data-sort-name="<?=$key?>"
				data-sort-bandwidth="<?=$data[$key]['U_BANDWIDTH']?>"
				data-sort-disk="<?=$data[$key]['U_DISK']?>">
				<div class="l-unit__col l-unit__col--right">
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check<?=$i?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="domain[]" value="<?=$key?>" <?=$display_mode;?>>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-4 truncate">
						<b>
							<?php if ($read_only === 'true') {?>
								<?=$key?>
							<?php } else {
								$aliases = explode(',', $data[$key]['ALIAS']);
								$alias_new = array();
								foreach($aliases as $alias){
									if($alias != 'www.'.$key){
										$alias_new[] = trim($alias);
									}
								}
								?>
								<a href="/edit/web/?domain=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Edit Domain") ?>: <?=$key?>"><?=$key?><?php if( !empty($alias_new) && !empty($data[$key]['ALIAS']) ){ echo " <span class=\"hint\">(".implode(',',$alias_new).")"; } ?></a>
							<?php } ?>
						</b>
					</div>
					<!-- START QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<?php if (!empty($data[$key]['STATS'])) { ?>
									<div class="actions-panel__col actions-panel__logs shortcut-w" data-key-action="href"><a href="http://<?=$key?>/vstats/" rel="noopener" target="_blank" rel="noopener" title="<?= _("Statistics") ?>"><i class="fas fa-chart-bar icon-maroon icon-dim"></i></a></div>
								<?php } ?>
									<div class="actions-panel__col actions-panel__view" data-key-action="href"><a href="http://<?=$key?>/" rel="noopener" target="_blank"><i class="fas fa-square-up-right icon-lightblue icon-dim"></i></a></div>
								<?php if ($read_only === 'true') {?>
									<!-- Restrict ability to edit, delete, or suspend web domains when impersonating the 'admin' account -->
									&nbsp;
								<?php } else { ?>
									<?php if ($data[$key]['SUSPENDED'] == 'no') {?>
										<div class="actions-panel__col actions-panel__edit shortcut-enter" data-key-action="href"><a href="/edit/web/?domain=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Edit Domain") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a></div>
									<?php } ?>
									<div class="actions-panel__col actions-panel__logs shortcut-l" data-key-action="href"><a href="/list/web-log/?domain=<?=$key?>&type=access#" title="<?= _("Access Log") ?>"><i class="fas fa-binoculars icon-purple icon-dim"></i></a></div>
									<div class="actions-panel__col actions-panel__suspend shortcut-s" data-key-action="js">
										<a
											class="data-controls js-confirm-action"
											href="/<?=$spnd_action?>/web/?domain=<?=$key?>&token=<?=$_SESSION['token']?>"
											data-confirm-title="<?= $spnd_action_title ?>"
											data-confirm-message="<?= sprintf($spnd_confirmation, $key) ?>"
										>
											<i class="fas <?= $spnd_icon ?> icon-highlight icon-dim"></i>
										</a>
									</div>
									<div class="actions-panel__col actions-panel__delete shortcut-delete" data-key-action="js">
										<a
											class="data-controls js-confirm-action"
											href="/delete/web/?domain=<?=$key?>&token=<?=$_SESSION['token']?>"
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
					<div class="clearfix l-unit__stat-col--left u-text-center"><?=empty($ips[$data[$key]['IP']]['NAT']) ? $data[$key]['IP'] : "{$ips[$data[$key]['IP']]['NAT']}"; ?></div>
					<div class="clearfix l-unit__stat-col--left u-text-center"><b><?=humanize_usage_size($data[$key]['U_DISK'])?></b> <span class="u-text-small"><?=humanize_usage_measure($data[$key]['U_DISK'])?></span></div>
					<div class="clearfix l-unit__stat-col--left u-text-center compact"><b><?=humanize_usage_size($data[$key]['U_BANDWIDTH'])?></b> <span class="u-text-small"><?=humanize_usage_measure($data[$key]['U_BANDWIDTH'])?></span></div>
					<div class="clearfix l-unit__stat-col--left u-text-center">
						<i class="fas <?=$icon_ssl;?>"></i>
					</div>
					<div class="clearfix l-unit__stat-col--left u-text-center compact">
						<i class="fas <?=$icon_webstats;?>"></i>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d web domain", "%d web domains", $i), $i); ?>
		</p>
	</div>
</footer>
