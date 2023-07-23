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
					<i class="fas fa-eye icon-red"></i><?= _("Banned IP Addresses") ?>
				</a>
			<?php endif; ?>
			<a class="button button-secondary" href="/list/firewall/ipset/">
				<i class="fas fa-list icon-blue"></i><?= _("IPset IP Lists") ?>
			</a>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<span class="u-text-bold">
						<?= _("Action") ?> <i class="fas fa-arrow-up-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
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

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Firewall Rules") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>">
			</div>
			<div class="units-table-cell"><?= _("Action") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell"><?= _("Comment") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Protocol") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Port") ?></div>
			<div class="units-table-cell u-text-center"><?= _("IP Address") ?></div>
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
					$spnd_icon_class = 'icon-green';
					$spnd_confirmation = _('Are you sure you want to unsuspend rule #%s?') ;
				} else {
					$status = 'active';
					$spnd_action = 'suspend';
					$spnd_action_title = _('Suspend');
					$spnd_icon = 'fa-pause';
					$spnd_icon_class = 'icon-highlight';
					$spnd_confirmation = _('Are you sure you want to suspend rule #%s?') ;
				}
			?>
			<div class="units-table-row <?php if ($status == 'suspended') echo 'disabled'; ?> js-unit"
				data-sort-action="<?= $data[$key]['ACTION'] ?>"
				data-sort-protocol="<?= $data[$key]['PROTOCOL'] ?>"
				data-sort-port="<?= $data[$key]['PORT'] ?>"
				data-sort-ip="<?= str_replace('.', '', $data[$key]['IP']) ?>"
				data-sort-comment="<?= $data[$key]['COMMENT'] ?>">
				<div class="units-table-cell">
					<div>
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="rule[]" value="<?= $key ?>">
						<label for="check<?= $i ?>" class="u-hide-desktop"><?= _("Select") ?></label>
					</div>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Action") ?>:</span>
					<a href="/edit/firewall/?rule=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Edit Firewall Rule") ?>">
						<?php
							$suspended = $data[$key]["SUSPENDED"] == "no";
							$action = $data[$key]["ACTION"];
							$iconClass = $action == "DROP" ? "fa-circle-minus" : "fa-circle-check";
							$colorClass = $action == "DROP" ? "icon-red" : "icon-green";
						?>
						<i class="fas <?= $iconClass ?> u-mr5 <?= $suspended ? $colorClass : "" ?>"></i> <?= $action ?>
					</a>
				</div>
				<div class="units-table-cell">
					<ul class="units-table-row-actions">
						<li class="units-table-row-action shortcut-enter" data-key-action="href">
							<a
								class="units-table-row-action-link"
								href="/edit/firewall/?rule=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Edit Firewall Rule") ?>"
							>
								<i class="fas fa-pencil icon-orange"></i>
								<span class="u-hide-desktop"><?= _("Edit Firewall Rule") ?></span>
							</a>
						</li>
						<li class="units-table-row-action shortcut-s" data-key-action="js">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/<?= $spnd_action ?>/firewall/?rule=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= $spnd_action_title ?>"
								data-confirm-title="<?= $spnd_action_title ?>"
								data-confirm-message="<?= sprintf($spnd_confirmation, $key) ?>"
							>
								<i class="fas <?= $spnd_icon ?> <?= $spnd_icon_class ?>"></i>
								<span class="u-hide-desktop"><?= $spnd_action_title ?></span>
							</a>
						</li>
						<li class="units-table-row-action shortcut-delete" data-key-action="js">
							<a
								class="units-table-row-action-link data-controls js-confirm-action"
								href="/delete/firewall/?rule=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
								title="<?= _("Delete") ?>"
								data-confirm-title="<?= _("Delete") ?>"
								data-confirm-message="<?= sprintf(_("Are you sure you want to delete rule %s"), $key) ?>"
							>
								<i class="fas fa-trash icon-red"></i>
								<span class="u-hide-desktop"><?= _("Delete") ?></span>
							</a>
						</li>
					</ul>
				</div>
				<div class="units-table-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Comment") ?>:</span>
					<?php if (!empty($data[$key]['COMMENT'])) { echo $data[$key]['COMMENT']; } ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Protocol") ?>:</span>
					<?= _($data[$key]["PROTOCOL"]) ?>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Port") ?>:</span>
					<?= $data[$key]["PORT"] ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("IP Address") ?>:</span>
					<?= $data[$key]["IP"] ?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d firewall rule", "%d firewall rules", $i), $i); ?>
		</p>
	</div>
</footer>
