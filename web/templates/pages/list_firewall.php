<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/server/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<a href="/add/firewall/" class="button button-secondary js-button-create">
				<i class="fas fa-circle-plus icon-green"></i><?= _("Add Rule") ?>
			</a>
			<?php if (!empty($_SESSION["FIREWALL_EXTENSION"])): ?>
				<a class="button button-secondary" href="/list/firewall/banlist/">
					<i class="fas fa-eye icon-red"></i><?= _("Fail2ban Banlists") ?>
				</a>
				<a class="button button-secondary" href="/list/firewall/ipset/">
					<i class="fas fa-list icon-blue"></i><?= _("IPset IP Lists") ?>
				</a>
			<?php endif; ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>: <b><?= _("Action") ?> <i class="fas fa-arrow-up-a-z"></i></b>
				</button>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn js-sorting-menu u-hidden">
					<li data-entity="sort-action">
						<span class="name"><?= _("Action") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up active"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-protocol">
						<span class="name"><?= _("Protocol") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-port">
						<span class="name"><?= _("Port") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-ip" data-sort-as-int="1">
						<span class="name"><?= _("IP Address") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-comment">
						<span class="name"><?= _("Comment") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<form x-data x-bind="BulkEdit" action="/bulk/firewall/" method="post">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<select class="form-select" name="action">
						<option value=""><?= _("Apply to selected") ?></option>
						<option value="delete"><?= _("Delete") ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container units compact">
	<div class="header units-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				<input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>">
			</div>
			<div class="clearfix l-unit__stat-col--left wide-1"><b><?= _("Action") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-2 u-text-right"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("Comment") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Protocol") ?></b></div>
			<div class="clearfix l-unit__stat-col--left wide-3 u-text-center"><b><?= _("Port") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("IP Address") ?></b></div>
		</div>
	</div>

	<!-- Begin firewall chain/action list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;
			if ($data[$key]['SUSPENDED'] == 'yes') {
				$status = 'suspended';
				$spnd_action = 'unsuspend';
				$spnd_action_title = _('Unsuspend');
				$spnd_icon = 'fa-play';
				$spnd_confirmation = _('Are you sure you want to unsuspend rule #%s?') ;
			} else {
				$status = 'active';
				$spnd_action = 'suspend';
				$spnd_action_title = _('Suspend');
				$spnd_icon = 'fa-pause';
				$spnd_confirmation = _('Are you sure you want to suspend rule #%s?') ;
			}
		?>
		<div class="l-unit <?php if ($status == 'suspended') echo 'l-unit--suspended'; ?> animate__animated animate__fadeIn js-sortable-unit"
			data-sort-action="<?=$data[$key]['ACTION']?>"
			data-sort-protocol="<?=$data[$key]['PROTOCOL']?>"
			data-sort-port="<?=$data[$key]['PORT']?>"
			data-sort-ip="<?=str_replace('.', '', $data[$key]['IP'])?>"
			data-sort-comment="<?=$data[$key]['COMMENT']?>">
			<div class="l-unit__col l-unit__col--right">
				<div>
					<div class="clearfix l-unit__stat-col--left super-compact">
						<input id="check<?= $i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="rule[]" value="<?= $key ?>">
					</div>
					<div class="clearfix l-unit__stat-col--left wide-1">
						<b>
							<a href="/edit/firewall/?rule=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Edit Firewall Rule") ?>">
								<?php
									$suspended = $data[$key]["SUSPENDED"] == "no";
									$action = $data[$key]["ACTION"];
									$iconClass = $action == "DROP" ? "fa-circle-minus" : "fa-circle-check";
									$colorClass = $action == "DROP" ? "icon-red" : "icon-green";
								?>
								<i class="fas <?= $iconClass ?> u-mr5 <?= $suspended ? $colorClass : "" ?>"></i> <?= $action ?>
							</a>
						</b>
					</div>
					<!-- START QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left compact-2 u-text-right">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix" style="padding-right: 10px;">
								<div class="actions-panel__col actions-panel__logs shortcut-enter" data-key-action="href"><a href="/edit/firewall/?rule=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Edit Firewall Rule") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a></div>
								<div class="actions-panel__col actions-panel__suspend shortcut-s" data-key-action="js">
									<a
										class="data-controls js-confirm-action"
										href="/<?=$spnd_action?>/firewall/?rule=<?=$key?>&token=<?=$_SESSION['token']?>"
										data-confirm-title="<?= $spnd_action_title ?>"
										data-confirm-message="<?= sprintf($spnd_confirmation, $key) ?>"
									>
										<i class="fas <?= $spnd_icon ?> icon-highlight icon-dim"></i>
									</a>
								</div>
								<div class="actions-panel__col actions-panel__delete shortcut-delete" data-key-action="js">
									<a
										class="data-controls js-confirm-action"
										href="/delete/firewall/?rule=<?=$key?>&token=<?=$_SESSION['token']?>"
										data-confirm-title="<?= _("Delete") ?>"
										data-confirm-message="<?= sprintf(_('Are you sure you want to delete rule %s'), $key) ?>"
									>
										<i class="fas fa-trash icon-red icon-dim"></i>
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
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d firewall rule", "%d firewall rules", $i), $i); ?>
		</p>
	</div>
</footer>
