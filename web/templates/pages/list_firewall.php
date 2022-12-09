<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/edit/server/"><i class="fas fa-arrow-left status-icon blue"></i><?= _("Back") ?></a>
			<a href="/add/firewall/" class="button button-secondary" id="btn-create"><i class="fas fa-circle-plus status-icon green"></i><?= _("Add Rule") ?></a>
			<?php if (!empty($_SESSION["FIREWALL_EXTENSION"])): ?>
				<a class="button button-secondary" href="/list/firewall/banlist/"><i class="fas fa-eye status-icon red"></i><?= _("list fail2ban") ?></a>
				<a class="button button-secondary" href="/list/firewall/ipset/"><i class="fas fa-list status-icon blue"></i><?= _("list ipset") ?></a>
			<?php endif; ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<a href="#" class="toolbar-sorting-toggle" title="<?= _("Sort items") ?>">
					<?= _("sort by") ?>: <b><?= _("Action") ?> <i class="fas fa-arrow-up-a-z"></i></b>
				</a>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn u-hidden">
					<li entity="sort-action"><span class="name"><?= _("Action") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up active"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-protocol"><span class="name"><?= _("Protocol") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-port"><span class="name"><?= _("Port") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-ip" sort_as_int="1"><span class="name"><?= _("IP address") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-comment"><span class="name"><?= _("Comment") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
				</ul>
				<form x-bind="BulkEdit" action="/bulk/firewall/" method="post">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<select class="form-select" name="action">
						<option value=""><?= _("apply to selected") ?></option>
						<option value="delete"><?= _("delete") ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= _("apply to selected") ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container units compact">
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				<input id="toggle-all" type="checkbox" name="toggle-all" value="toggle-all" title="<?= _("Select all") ?>">
			</div>
			<div class="clearfix l-unit__stat-col--left wide-1"><b><?= _("Action") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-2 u-text-right"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("Comment") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Protocol") ?></b></div>
			<div class="clearfix l-unit__stat-col--left wide-3 u-text-center"><b><?= _("Port") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("IP address") ?></b></div>
		</div>
	</div>

	<!-- Begin firewall chain/action list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;
			if ($data[$key]['SUSPENDED'] == 'yes') {
				$status = 'suspended';
				$spnd_action = 'unsuspend';
				$spnd_icon = 'fa-play';
				$spnd_confirmation = _('UNSUSPEND_RULE_CONFIRMATION') ;
			} else {
				$status = 'active';
				$spnd_action = 'suspend';
				$spnd_icon = 'fa-pause';
				$spnd_confirmation = _('SUSPEND_RULE_CONFIRMATION') ;
			}
		?>
		<div class="l-unit<?php if ($status == 'suspended') echo ' l-unit--suspended';?> animate__animated animate__fadeIn" v_unit_id="<?=$key?>" v_section="firewall"
			sort-action="<?=$data[$key]['ACTION']?>" sort-protocol="<?=$data[$key]['PROTOCOL']?>" sort-port="<?=$data[$key]['PORT']?>"
			sort-ip="<?=str_replace('.', '', $data[$key]['IP'])?>" sort-comment="<?=$data[$key]['COMMENT']?>">
			<div class="l-unit__col l-unit__col--right">
				<div>
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check<?=$i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="rule[]" value="<?=$key?>">
					</div>
					<div class="clearfix l-unit__stat-col--left wide-1">
						<b>
							<a href="/edit/firewall/?rule=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Editing Firewall Rule") ?>">
							<?php if ($data[$key]['SUSPENDED'] == 'no') { ?>
								<?php if ($data[$key]['ACTION'] == 'DROP') { ?>
									<i class="fas fa-circle-minus status-icon red icon-pad-right"></i> <?=_($data[$key]['ACTION'])?>
								<?php } else {?>
									<i class="fas fa-circle-check status-icon green icon-pad-right"></i> <?=_($data[$key]['ACTION'])?>
								<?php } ?>
							<?php } else {?>
								<?php if ($data[$key]['ACTION'] == 'DROP') { ?>
									<i class="fas fa-circle-minus icon-pad-right" style=""></i> <?=_($data[$key]['ACTION'])?>
								<?php } else {?>
									<i class="fas fa-circle-check icon-pad-right"></i> <?=_($data[$key]['ACTION'])?>
								<?php } ?>
							<?php } ?>
							</a>
						</b>
					</div>
					<!-- START QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left compact-2 u-text-right">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix" style="padding-right: 10px;">
								<div class="actions-panel__col actions-panel__logs shortcut-enter" key-action="href"><a href="/edit/firewall/?rule=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Editing Firewall Rule") ?>"><i class="fas fa-pencil status-icon orange status-icon dim"></i></a></div>
								<div class="actions-panel__col actions-panel__suspend shortcut-s" key-action="js">
									<a id="<?=$spnd_action ?>_link_<?=$i?>" class="data-controls do_<?=$spnd_action?>" title="<?=_($spnd_action)?>">
										<i class="fas <?=$spnd_icon?> status-icon highlight status-icon dim do_<?=$spnd_action?>"></i>
										<input type="hidden" name="<?=$spnd_action?>_url" value="/<?=$spnd_action?>/firewall/?rule=<?=$key?>&token=<?=$_SESSION['token']?>">
										<div id="<?=$spnd_action?>_dialog_<?=$i?>" class="dialog js-confirm-dialog-suspend" title="<?= _("Confirmation") ?>">
											<p><?=sprintf($spnd_confirmation,$key)?></p>
										</div>
									</a>
								</div>
								<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
									<a id="delete_link_<?=$i?>" class="data-controls do_delete" title="<?= _("delete") ?>">
										<i class="fas fa-trash status-icon red status-icon dim do_delete"></i>
										<input type="hidden" name="delete_url" value="/delete/firewall/?rule=<?=$key?>&token=<?=$_SESSION['token']?>">
										<div id="delete_dialog_<?=$i?>" class="dialog js-confirm-dialog-delete" title="<?= _("Confirmation") ?>">
											<p><?=sprintf(_('DELETE_RULE_CONFIRMATION'),$key)?></p>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>
					<!-- END QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left wide-3"><b><?php if (!empty($data[$key]['COMMENT'])) echo '' . $data[$key]['COMMENT']; else echo "&nbsp;"; ?></b></div>
					<div class="clearfix l-unit__stat-col--left u-text-center"><?=_($data[$key]['PROTOCOL'])?></div>
					<div class="clearfix l-unit__stat-col--left wide-3 u-text-center"><b><?=$data[$key]['PORT']?></b></div>
					<div class="clearfix l-unit__stat-col--left u-text-center"><?=$data[$key]['IP']?></div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container">
		<div class="l-unit-ft">
			<div class="l-unit__col l-unit__col--right">
				<?php printf(ngettext('%d firewall rule', '%d firewall rules', $i),$i); ?>
			</div>
		</div>
	</div>
</footer>
