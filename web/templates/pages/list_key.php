<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/edit/user/?token=<?=$_SESSION['token']?>"><i class="fas fa-arrow-left status-icon blue"></i><?=_('Back');?></a>
						<?php if (($_SESSION['userContext'] === 'admin') && (isset($_GET['user'])) && ($_GET['user'] !== 'admin')) { ?>
						<a href="/add/key/?user=<?=htmlentities($_GET['user']);?>" class="button button-secondary" id="btn-create"><i class="fas fa-circle-plus status-icon green"></i><?=_('Add SSH Key');?></a>
						<?php } else { ?>
			<a href="/add/key/" class="button button-secondary" id="btn-create"><i class="fas fa-circle-plus status-icon green"></i><?=_('Add SSH Key');?></a>
						<?php } ?>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="l-center units">
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left wide-3"><b><?=_('SSH_ID');?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-2">
				&nbsp;
			</div>
			<div class="clearfix l-unit__stat-col--left wide-7"><b><?=_('SSH KEY');?></b></div>
		</div>
	</div>

	<!-- Begin SSH key list item loop -->
	<?php
		$i = 0;
			foreach ($data as $key => $value) {
			++$i;
		?>
		<div class="l-unit header animate__animated animate__fadeIn" style="<?php if ($data[$key]['ID'] === 'filemanager.ssh.key'){ echo 'display: none;'; }?>">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left wide-3"><b><?=htmlspecialchars($data[$key]['ID']);?></b></div>
				<div class="clearfix l-unit__stat-col--left text-left compact-2">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
								<a id="delete_link_<?=$i?>" class="data-controls do_delete" title="<?=_('delete');?>">
									<i class="fas fa-trash status-icon red status-icon dim do_delete"></i>
									<?php if (($_SESSION['userContext'] === 'admin') && (isset($_GET['user'])) && ($_GET['user'] !== 'admin')) { ?>
										<input type="hidden" name="delete_url" value="/delete/key/?user=<?=htmlentities($_GET['user']);?>&key=<?=$key?>&token=<?=$_SESSION['token']?>">
									<?php } else { ?>
										<input type="hidden" name="delete_url" value="/delete/key/?key=<?=$key?>&token=<?=$_SESSION['token']?>">
									<?php } ?>
									<div id="delete_dialog_<?=$i?>" class="dialog js-confirm-dialog-delete" title="<?=_('Confirmation');?>">
										<p><?=sprintf(_('DELETE_KEY_CONFIRM'),$key)?></p>
									</div>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-7"><b><?=htmlspecialchars($data[$key]['KEY'], ENT_QUOTES);?></b></div>
			</div>
		</div>
	<?php } ?>
</div>

<div id="vstobjects">
	<div class="l-separator"></div>
	<div class="l-center">
		<div class="l-unit-ft">
			<div class="l-unit__col l-unit__col--right">
				<?php printf(ngettext('%d SSH Key', '%d SSH Keys', $i),$i); ?>
			</div>
		</div>
	</div>
</div>
