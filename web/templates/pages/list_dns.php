<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== 'true') {?>
				<a href="/add/dns/" class="button button-secondary" id="btn-create"><i class="fas fa-circle-plus icon-green"></i><?= _("Add DNS Domain") ?></a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<a href="#" class="toolbar-sorting-toggle" title="<?= _("Sort items") ?>">
					<?= _("sort by") ?>:
					<b>
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?=$label;?> <i class="fas fa-arrow-down-a-z"></i>
					</b>
				</a>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn u-hidden">
					<li entity="sort-date" sort_as_int="1"><span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-expire" sort_as_int="1"><span class="name"><?= _("Expire") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-ip"><span class="name"><?= _("IP address") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-name"><span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-records"><span class="name"><?= _("Records") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
				</ul>
				<?php if ($read_only !== 'true') {?>
					<form x-bind="BulkEdit" action="/bulk/dns/" method="post">
						<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
						<select class="form-select" name="action">
							<option value=""><?= _("apply to selected") ?></option>
							<?php if ($_SESSION['userContext'] === 'admin') {?>
								<option value="rebuild"><?= _("rebuild") ?></option>
							<?php } ?>
							<option value="suspend"><?= _("suspend") ?></option>
							<option value="unsuspend"><?= _("unsuspend") ?></option>
							<option value="delete"><?= _("delete") ?></option>
						</select>
						<button type="submit" class="toolbar-input-submit" title="<?= _("apply to selected") ?>">
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
				<input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>" <?=$display_mode;?>>
			</div>
			<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("Name") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-right"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact"><b><?= _("Records_DNS") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Template") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact"><b><?= _("TTL") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("SOA") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-3"><b><?= _("DNSSEC") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Expiration Date") ?></b></div>
		</div>
	</div>

	<!-- Begin DNS zone list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;
			if ($data[$key]['SUSPENDED'] == 'yes') {
				$status = 'suspended';
				$spnd_action = 'unsuspend';
				$spnd_icon = 'fa-play';
				$spnd_confirmation = _('UNSUSPEND_DOMAIN_CONFIRMATION');
			} else {
				$status = 'active';
				$spnd_action = 'suspend';
				$spnd_icon = 'fa-pause';
				$spnd_confirmation = _('SUSPEND_DOMAIN_CONFIRMATION');
			}
			if ($data[$key]['DNSSEC'] !== 'yes') {
				$dnssec_icon = 'fa-circle-xmark';
			} else {
				$dnssec_icon = 'fa-circle-check';
			}
		?>
		<div class="l-unit <?php if ($status == 'suspended') echo ' l-unit--suspended'; ?> animate__animated animate__fadeIn" v_unit_id="<?=htmlentities($key);?>"
			v_section="dns" sort-ip="<?=str_replace('.', '', $data[$key]['IP'])?>" sort-date="<?=strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])?>" sort-name="<?=htmlentities($key);?>"
			sort-expire="<?=strtotime($data[$key]['EXP'])?>" sort-records="<?=(int)$data[$key]['RECORDS']?>">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?=$i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="domain[]" value="<?=$key?>" <?=$display_mode;?>>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-3 truncate">
					<b><a href="/list/dns/?domain=<?=htmlentities($key);?>&token=<?=$_SESSION['token']?>" title="<?= _("DNS records") ?>: <?=htmlentities($key);?>"><?=htmlentities($key);?></a></b>
					<?=empty($data[$key]['SRC'])? '' : '<br>⇢ <span style="font-size:11px;">' . htmlspecialchars($data[$key]['SRC'], ENT_QUOTES) . '</span>'; ?>
				</div>
				<!-- START QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left u-text-right">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<?php if ($read_only === 'true') {?>
								<!-- Restrict administrators from editing domain items when impersonating the 'admin' user -->
								&nbsp;
							<?php } else { ?>
								<?php if ($data[$key]['SUSPENDED'] == 'no') {?>
									<div class="actions-panel__col actions-panel__logs shortcut-n" key-action="href"><a href="/add/dns/?domain=<?=htmlentities($key);?>&token=<?=$_SESSION['token']?>" title="<?= _("Add DNS Record") ?>"><i class="fas fa-circle-plus icon-green icon-dim"></i></a></div>
									<div class="actions-panel__col actions-panel__logs shortcut-enter" key-action="href"><a href="/edit/dns/?domain=<?=htmlentities($key);?>&token=<?=$_SESSION['token']?>" title="<?= _("Editing DNS Domain") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a></div>
									<?php if($data[$key]['DNSSEC'] == "yes"){?><div class="actions-panel__col actions-panel__logs shortcut-enter" key-action="href"><a href="/list/dns/?domain=<?=htmlentities($key);?>&action=dnssec&token=<?=$_SESSION['token']?>" title="<?= _("View Public DNSSEC key") ?>"><i class="fas fa-key icon-orange icon-dim"></i></a></div>
									<?php } ?>
								<?php } ?>
								<div class="actions-panel__col actions-panel__edit shortcut-l" key-action="href"><a href="/list/dns/?domain=<?=htmlentities($key);?>&token=<?=$_SESSION['token']?>" title="<?= _("DNS records") ?>"><i class="fas fa-list icon-lightblue icon-dim"></i></a></div>
								<div class="actions-panel__col actions-panel__suspend shortcut-s" key-action="js">
									<a id="<?=$spnd_action ?>_link_<?=$i?>" class="data-controls do_<?=$spnd_action?>" title="<?=_($spnd_action)?>">
										<i class="fas <?=$spnd_icon?> icon-highlight icon-dim do_<?=$spnd_action?>"></i>
										<input type="hidden" name="<?=$spnd_action?>_url" value="/<?=$spnd_action?>/dns/?domain=<?=htmlentities($key);?>&token=<?=$_SESSION['token']?>">
										<div id="<?=$spnd_action?>_dialog_<?=$i?>" class="dialog js-confirm-dialog-suspend" title="<?= _("Confirmation") ?>">
											<p><?=sprintf($spnd_confirmation,$key)?></p>
										</div>
									</a>
								</div>
								<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
									<a id="delete_link_<?=$i?>" class="data-controls do_delete" title="<?= _("delete") ?>">
										<i class="fas fa-trash icon-red icon-dim do_delete"></i>
										<input type="hidden" name="delete_url" value="/delete/dns/?domain=<?=htmlentities($key);?>&token=<?=$_SESSION['token']?>">
										<div id="delete_dialog_<?=$i?>" class="dialog js-confirm-dialog-delete" title="<?= _("Confirmation") ?>">
											<p><?=sprintf(_('DELETE_DOMAIN_CONFIRMATION'),$key)?></p>
										</div>
									</a>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<!-- END QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left u-text-center compact">
					<?php if ($data[$key]['RECORDS']) echo '<span>'.$data[$key]['RECORDS'].'</span>';?>
				</div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?=$data[$key]['TPL']?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact"><?=$data[$key]['TTL']?></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><?=$data[$key]['SOA']?></div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact-3">
					<i class="fas <?=$dnssec_icon;?>"></i>
				</div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?=$data[$key]['EXP']?></b></div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext('%d DNS zone', '%d DNS zones', $i),$i); ?>
		</p>
	</div>
</footer>
