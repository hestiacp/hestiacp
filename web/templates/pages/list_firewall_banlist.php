<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/firewall/"><i class="fas fa-arrow-left status-icon blue"></i><?=_('Back');?></a>
			<a href="/add/firewall/banlist/" class="button button-secondary" id="btn-create"><i class="fas fa-circle-plus status-icon green"></i><?=_('Ban IP Address');?></a>
		</div>
		<div class="toolbar-right">
			<form action="/bulk/firewall/banlist/" method="post" id="objects">
				<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
				<select class="form-select" name="action">
					<option value=""><?=_('apply to selected');?></option>
					<option value="delete"><?=_('delete') ?></option>
				</select>
				<button type="submit" class="toolbar-input-submit" title="<?=_('apply to selected');?>">
					<i class="fas fa-arrow-right"></i>
				</button>
			</form>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container units">
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				<input id="toggle-all" type="checkbox" name="toggle-all" value="toggle-all" title="<?=_('Select all');?>">
			</div>
			<div class="clearfix l-unit__stat-col--left wide-3"><b><?=_('IP address');?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-4"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left text-center compact-5"><b><?=_('Date');?></b></div>
			<div class="clearfix l-unit__stat-col--left text-center compact-5"><b><?=_('Time');?></b></div>
			<div class="clearfix l-unit__stat-col--left wide text-center"><b><?=_('Comment');?></b></div>
		</div>
	</div>

	<!-- Begin banned IP address list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;
			$ip = $key;
		?>
		<div class="l-unit animate__animated animate__fadeIn">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?=$i ?>" class="ch-toggle" type="checkbox" title="<?=_('Select');?>" name="ipchain[]" value="<?=$ip . ':' . $value['CHAIN'] ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><?=$ip?></b></div>
				<!-- START QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left compact-4">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
								<a id="delete_link_<?=$i?>" class="data-controls do_delete">
									<i class="fas fa-trash status-icon red status-icon dim do_delete"></i>
									<input type="hidden" name="delete_url" value="/delete/firewall/banlist/?ip=<?=$ip?>&chain=<?=$value['CHAIN']?>&token=<?=$_SESSION['token']?>">
									<div id="delete_dialog_<?=$i?>" class="dialog js-confirm-dialog-delete" title="<?=_('Confirmation');?>">
										<p><?=sprintf(_('DELETE_IP_CONFIRMATION'),$key)?></p>
									</div>
								</a>
							</div>
						</div>
					</div>
				</div>
				<!-- END QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left text-center compact-5"><?=_($data[$key]['DATE'])?></div>
				<div class="clearfix l-unit__stat-col--left text-center compact-5"><?=$data[$key]['TIME']?></div>
				<div class="clearfix l-unit__stat-col--left text-center wide"><b><?=_($value['CHAIN'])?></b></div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container">
		<div class="l-unit-ft">
			<div class="l-unit__col l-unit__col--right">
				<?php
					if ( $i == 0) {
						echo _('There are currently no banned IP addresses.');
					} else {
						printf(ngettext('%d banned IP address', '%d banned IP addresses', $i),$i);
					}
				?>
			</div>
			<div class="l-unit__col l-unit__col--right back clearfix">
			</div>
		</div>
	</div>
</footer>
