<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== 'true') {?>
				<a href="/schedule/backup/?token=<?=$_SESSION['token']?>" class="button button-secondary"><i class="fas fa-circle-plus status-icon green"></i><?=_('Create Backup');?></a>
				<a href="/list/backup/exclusions/" class="button button-secondary"><i class="fas fa-folder-minus status-icon orange"></i><?=_('backup exclusions');?></a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<?php if ($read_only !== 'true') {?>
				<form x-bind="BulkEdit" action="/bulk/backup/" method="post">
					<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
					<select class="form-select" name="action">
						<option value=""><?=_('apply to selected');?></option>
						<option value="delete"><?=_('delete') ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?=_('apply to selected');?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			<?php } ?>
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
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div>
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="toggle-all" type="checkbox" name="toggle-all" value="toggle-all" title="<?=_('Select all');?>" <?=$display_mode;?>>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-4"><b><?=_('File Name');?></b></div>
				<div class="clearfix l-unit__stat-col--left compact-4 text-right"><b>&nbsp;</b></div>
				<div class="clearfix l-unit__stat-col--left text-center"><b><?=_('Date');?></b></div>
				<div class="clearfix l-unit__stat-col--left text-center"><b><?=_('Size');?></b></div>
				<div class="clearfix l-unit__stat-col--left text-center"><b><?=_('Type');?></b></div>
				<div class="clearfix l-unit__stat-col--left text-center"><b><?=_('Runtime');?></b></div>
			</div>
		</div>
	</div>

	<!-- Begin user backup list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;
			$web = _('no');
			$dns = _('no');
			$mail = _('no');
			$db = _('no');
			$cron = _('no');
			$udir = _('no');

			if (!empty($data[$key]['WEB'])) $web = _('yes');
			if (!empty($data[$key]['DNS'])) $dns = _('yes');
			if (!empty($data[$key]['MAIL'])) $mail = _('yes');
			if (!empty($data[$key]['DB'])) $db = _('yes');
			if (!empty($data[$key]['CRON'])) $cron = _('yes');
			if (!empty($data[$key]['UDIR'])) $udir = _('yes');
		?>
		<div class="l-unit animate__animated animate__fadeIn">
			<div class="l-unit__col l-unit__col--right">
				<div>
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check<?=$i ?>" class="ch-toggle" type="checkbox" title="<?=_('Select');?>" name="backup[]" value="<?=$key?>" <?=$display_mode;?>>
					</div>
					<div class="clearfix l-unit__stat-col--left wide-4 truncate">
						<b>
							<?php if ($read_only === 'true') {?>
								<?=$key?>
							<?php } else { ?>
								<a href="/list/backup/?backup=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?=_('restore');?>"><?=$key?></a>
							<?php } ?>
						</b>
					</div>
					<!-- START QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left compact-4 text-right">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<?php if (($_SESSION['userContext'] === 'admin') && ($_SESSION['look'] === 'admin') && ($read_only === 'true')) {?>
									<!-- Restrict ability to restore or delete backups when impersonating 'admin' account -->
									&nbsp;
								<?php } else { ?>
									<div class="actions-panel__col actions-panel__download shortcut-d" key-action="href"><a href="/download/backup/?backup=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?=_('download');?>"><i class="fas fa-file-arrow-down status-icon lightblue status-icon dim"></i></a></div>
									<?php if ($read_only !== 'true') {?>
										<div class="actions-panel__col actions-panel__list shortcut-enter" key-action="href"><a href="/list/backup/?backup=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?=_('restore');?>"><i class="fas fa-arrow-rotate-left status-icon green status-icon dim"></i></a></div>
										<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
											<a id="delete_link_<?=$i?>" class="data-controls do_delete" title="<?=_('delete');?>">
												<i class="fas fa-trash status-icon red status-icon dim do_delete"></i>
												<input type="hidden" name="delete_url" value="/delete/backup/?backup=<?=$key?>&token=<?=$_SESSION['token']?>">
												<div id="delete_dialog_<?=$i?>" class="dialog js-confirm-dialog-delete" title="<?=_('Confirmation');?>">
													<p><?=sprintf(_('DELETE_BACKUP_CONFIRMATION'),$key)?></p>
												</div>
											</a>
										</div>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					</div>
					<!-- END QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left text-center"><b><?=translate_date($data[$key]['DATE'])?></b></div>
					<div class="clearfix l-unit__stat-col--left text-center"><b><?=humanize_usage_size($data[$key]['SIZE'])?></b> <span class="u-text-small"><?=humanize_usage_measure($data[$key]['SIZE'])?></span></div>
					<div class="clearfix l-unit__stat-col--left text-center"><?=$data[$key]['TYPE']?></div>
					<div class="clearfix l-unit__stat-col--left text-center"><?=humanize_time($data[$key]['RUNTIME'])?></div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container">
		<div class="l-unit-ft">
			<div class="l-unit__col l-unit__col--right">
				<?php printf(ngettext('%d backup', '%d backups', $i),$i); ?>
			</div>
		</div>
	</div>
</footer>
