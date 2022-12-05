<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/add/user/" class="button button-secondary" id="btn-create"><i class="fas fa-circle-plus status-icon green"></i><?=_('Add User');?></a>
			<a href="/list/package/" class="button button-secondary"><i class="fas fa-box-open status-icon orange"></i><?=_('Packages');?></a>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<a href="#" class="toolbar-sorting-toggle" title="<?=_('Sort items');?>">
					<?=_('sort by');?>:
					<b>
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?=$label;?> <i class="fas fa-arrow-down-a-z"></i>
					</b>
				</a>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn u-hidden">
					<li entity="sort-bandwidth" sort_as_int="1"><span class="name"><?=_('Bandwidth');?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-date" sort_as_int="1"><span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?=_('Date');?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-disk" sort_as_int="1"><span class="name"><?=_('Disk');?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-name"><span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?=_('Name');?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
				</ul>
				<form action="/bulk/user/" method="post" x-bind="BulkEdit">
					<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
					<select class="form-select" name="action">
						<option value=""><?=_('apply to selected');?></option>
						<option value="rebuild"><?=_('rebuild');?></option>
						<option value="rebuild user"><?=_('rebuild user');?></option>
						<option value="rebuild web"><?=_('rebuild web');?></option>
						<option value="rebuild dns"><?=_('rebuild dns');?></option>
						<option value="rebuild mail"><?=_('rebuild mail');?></option>
						<option value="rebuild db"><?=_('rebuild db');?></option>
						<option value="rebuild cron"><?=_('rebuild cron');?></option>
						<option value="update counters"><?=_('update counters');?></option>
						<option value="suspend"><?=_('suspend');?></option>
						<option value="unsuspend"><?=_('unsuspend');?></option>
						<option value="delete"><?=_('delete');?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?=_('apply to selected');?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			</div>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
					<input type="search" class="form-control js-search-input" name="q" value="<? echo isset($_POST['q']) ? htmlspecialchars($_POST['q']) : '' ?>" title="<?=_('Search');?>">
					<button type="submit" class="toolbar-input-submit" title="<?=_('Search');?>">
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
	<div class="table-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				<input id="toggle-all" type="checkbox" name="toggle-all" value="toggle-all" title="<?=_('Select all');?>">
			</div>
			<div class="clearfix l-unit__stat-col--left wide-3"><b><?=_('Name');?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-3"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left text-center"><b><?=_('Package');?></b></div>
			<div class="clearfix l-unit__stat-col--left text-center super-compact"><b><?=_('IPs');?></b></div>
			<div class="clearfix l-unit__stat-col--left text-center super-compact"><b><i class="fas fa-hard-drive" title="<?=_('Disk');?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left text-center compact"><b><i class="fas fa-right-left" title="<?=_('Bandwidth');?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left text-center super-compact"><b><i class="fas fa-earth-americas" title="<?=_('Web Domains');?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left text-center super-compact"><b><i class="fas fa-book-atlas" title="<?=_('DNS Domains');?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left text-center super-compact"><b><i class="fas fa-envelopes-bulk" title="<?=_('Mail Domains');?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left text-center super-compact"><b><i class="fas fa-database" title="<?=_('Databases');?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left text-center super-compact"><b><i class="fas fa-clock" title="<?=_('Cron Jobs');?>"></i></b></div>
			<div class="clearfix l-unit__stat-col--left text-center super-compact"><b><i class="fas fa-file-zipper" title="<?=_('Backups');?>"></i></b></div>
		</div>
	</div>

	<!-- Begin user list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;
			if ($data[$key]['SUSPENDED'] == 'yes') {
				$status = 'suspended';
				$spnd_action = 'unsuspend';
				$spnd_icon = 'fa-play';
				$spnd_confirmation = _('UNSUSPEND_USER_CONFIRMATION');
			} else {
				$status = 'active';
				$spnd_action = 'suspend';
				$spnd_icon = 'fa-pause';
				$spnd_confirmation = _('SUSPEND_USER_CONFIRMATION');
			}
		?>
		<div class="l-unit <?php if ($status == 'suspended') echo 'l-unit--suspended';?> animate__animated animate__fadeIn" v_section="user"
			v_unit_id="<?=$key?>" sort-date="<?=strtotime($data[$key]['DATE'].' '.$data[$key]['TIME'])?>" sort-name="<?=strtolower($key)?>"
			sort-bandwidth="<?=$data[$key]['U_BANDWIDTH']?>" sort-disk="<?=$data[$key]['U_DISK']?>">
			<div class="l-unit__col l-unit__col--right" style="<?php if (($_SESSION['POLICY_SYSTEM_HIDE_ADMIN'] === 'yes') && ($_SESSION['user'] !== 'admin') && ($key === 'admin')) { echo 'display: none';} else {echo 'display: table-cell';}?>">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?=$i?>" class="ch-toggle" type="checkbox" title="<?=_('Select');?>" name="user[]" value="<?=$key?>">
				</div>
				<div class="clearfix l-unit__stat-col--left wide-3 userlist-username">
					<?php if ($key == $user_plain) { ?>
						<b><a href="/edit/user/?user=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?=_('Editing User');?>"><?=$key?> <span style="font-weight: normal !important;">(<?=$data[$key]['NAME'];?>)</span></a></b>
					<?php } else { ?>
						<b><a href="/login/?loginas=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?=_('login as');?> <?=$key?>"><?=$key?> <span style="font-weight: normal !important;">(<?=$data[$key]['NAME'];?>)</span></a></b>
					<?php } ?>
					<br>
					<div class="userlist-email"><b><?=_('Email');?>:</b> <?=$data[$key]['CONTACT']?></div>
				</div>
				<!-- START QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left text-right compact-3">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<?php if ($key == $user_plain) { ?>
								<i class="fas fa-user-check status-icon status-icon dim icon-large" title="<?=$key?> (<?=$data[$key]['NAME']?>)"></i>
							<?php } else { ?>
								<a href="/login/?loginas=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?=_('login as');?> <?=$key?>"><i class="fas fa-right-to-bracket status-icon green status-icon dim icon-large"></i></a>
							<?php } ?>
							<?php if (($_SESSION['userContext'] === 'admin') && ($key == 'admin') && ($_SESSION['user'] != 'admin')) { ?>
								<!-- Hide edit button from admin user when logged in with another admin user -->
								&nbsp;
							<?php } else { ?>
								<div class="actions-panel__col actions-panel__edit shortcut-enter" key-action="href"><a href="/edit/user/?user=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?=_('Editing User');?>"><i class="fas fa-pencil status-icon orange status-icon dim"></i></a></div>
							<?php } ?>
							<?php if ($key == 'admin') { ?>
								<!-- Hide suspend and delete buttons in the user list for primary 'admin' account -->
							<?php } else { ?>
								<?php if ($key == $user_plain) { ?>
									<!-- Hide suspend and delete buttons in the user list for current user -->
								<?php } else { ?>
								<div class="actions-panel__col actions-panel__suspend shortcut-s" key-action="js">
									<a id="<?=$spnd_action ?>_link_<?=$i?>" class="data-controls do_<?=$spnd_action?>" title="<?=_($spnd_action)?>">
										<i class="fas <?=$spnd_icon?> status-icon highlight status-icon dim do_<?=$spnd_action?>"></i>
										<input type="hidden" name="<?=$spnd_action?>_url" value="/<?=$spnd_action?>/user/?user=<?=$key?>&token=<?=$_SESSION['token']?>">
										<div id="<?=$spnd_action?>_dialog_<?=$i?>" class="dialog js-confirm-dialog-suspend" title="<?=_('Confirmation');?>">
											<p><?=sprintf($spnd_confirmation,$key)?></p>
										</div>
									</a>
								</div>
								<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
									<a id="delete_link_<?=$i?>" class="data-controls do_delete" title="<?=_('delete');?>">
										<i class="fas fa-trash status-icon red status-icon dim do_delete"></i>
										<input type="hidden" name="delete_url" value="/delete/user/?user=<?=$key?>&token=<?=$_SESSION['token']?>">
										<div id="delete_dialog_<?=$i?>" class="dialog js-confirm-dialog-delete" title="<?=_('Confirmation');?>">
											<p><?=sprintf(_('DELETE_USER_CONFIRMATION'),$key)?></p>
										</div>
									</a>
								</div>
								<?php } ?>
							<?php } ?>
						</div>
					</div>
				</div>
				<!-- END QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left text-center">
					<b>
						<?php if ($data[$key]['PACKAGE'] === 'default' ){?>
							<?=$data[$key]['PACKAGE']?>
						<?php } else { ?>
							<a href="/edit/package/?package=<?=$data[$key]['PACKAGE']?>&token=<?=$_SESSION['token']?>" title="<?=_('Edit Package');?>"><?=$data[$key]['PACKAGE']?></a>
						<?php } ?>
					</b>
				</div>
				<div class="clearfix l-unit__stat-col--left text-center super-compact"><?=$data[$key]['IP_OWNED']?></div>
				<div class="clearfix l-unit__stat-col--left text-center super-compact"><b><?=humanize_usage_size($data[$key]['U_DISK'])?></b> <span class="u-text-small"><?=humanize_usage_measure($data[$key]['U_DISK'])?></span></div>
				<div class="clearfix l-unit__stat-col--left text-center compact"><b><?=humanize_usage_size($data[$key]['U_BANDWIDTH'])?></b> <span class="u-text-small"><?=humanize_usage_measure($data[$key]['U_BANDWIDTH'])?></span></div>
				<div class="clearfix l-unit__stat-col--left text-center super-compact" title="<?=$data[$key]['U_WEB_DOMAINS']?> <?=_('Web Domains');?>"><span class="jump-top badge gray raised"><b><?=$data[$key]['U_WEB_DOMAINS']?></b></span></div>
				<div class="clearfix l-unit__stat-col--left text-center super-compact" title="<?=$data[$key]['U_DNS_DOMAINS']?> <?=_('DNS Domains');?>"><span class="jump-top badge gray raised"><b><?=$data[$key]['U_DNS_DOMAINS']?></b></span></div>
				<div class="clearfix l-unit__stat-col--left text-center super-compact" title="<?=$data[$key]['U_MAIL_DOMAINS']?> <?=_('Mail Domains');?>"><span class="jump-top badge gray raised"><b><?=$data[$key]['U_MAIL_DOMAINS']?></b></span></div>
				<div class="clearfix l-unit__stat-col--left text-center super-compact" title="<?=$data[$key]['U_DATABASES']?> <?=_('Databases');?>"><span class="jump-top badge gray raised"><b><?=$data[$key]['U_DATABASES']?></b></span></div>
				<div class="clearfix l-unit__stat-col--left text-center super-compact" title="<?=$data[$key]['U_CRON_JOBS']?> <?=_('Cron Jobs');?>"><span class="jump-top badge gray raised"><b><?=$data[$key]['U_CRON_JOBS']?></b></span></div>
				<div class="clearfix l-unit__stat-col--left text-center super-compact" title="<?=$data[$key]['U_BACKUPS']?> <?=_('Backups');?>"><span class="jump-top badge gray raised"><b><?=$data[$key]['U_BACKUPS']?></b></span></div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container">
		<div class="l-unit-ft">
			<div class="l-unit__col l-unit__col--right">
				<?php printf(ngettext('%d user account', '%d user accounts', $i),$i); ?>
			</div>
		</div>
	</div>
</footer>
