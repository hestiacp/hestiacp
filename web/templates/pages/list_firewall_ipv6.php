<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/firewall/">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("IPv4 Firewall")) ?>
			</a>
			<a href="/add/firewall/ipv6/" class="button button-secondary js-button-create">
				<i class="fas fa-circle-plus icon-green"></i><?= tohtml( _("Add IPv6 Rule")) ?>
			</a>
			<a class="button button-secondary" href="/list/firewall/banlist/ipv6/">
				<i class="fas fa-eye icon-red"></i><?= tohtml( _("Banned IPv6 Addresses")) ?>
			</a>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= tohtml( _("Sort items")) ?>">
					<?= tohtml( _("Sort by")) ?>:
					<span class="u-text-bold">
						<?= tohtml( _("Action")) ?> <i class="fas fa-arrow-up-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-action">
						<span class="name"><?= tohtml( _("Action")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up active"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-protocol">
						<span class="name"><?= tohtml( _("Protocol")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-port">
						<span class="name"><?= tohtml( _("Port")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-ip" data-sort-as-int="0">
						<span class="name"><?= tohtml( _("IPv6 Address")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-comment">
						<span class="name"><?= tohtml( _("Comment")) ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<form x-data x-bind="BulkEdit" action="/bulk/firewall/ipv6/" method="post">
					<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
					<select class="form-select" name="action">
						<option value=""><?= tohtml( _("Apply to selected")) ?></option>
						<option value="delete"><?= tohtml( _("Delete")) ?></option>
						<option value="suspend"><?= tohtml( _("Suspend")) ?></option>
						<option value="unsuspend"><?= tohtml( _("Unsuspend")) ?></option>
					</select>
					<button type="submit" class="toolbar-input-submit" title="<?= tohtml( _("Apply to selected")) ?>">
						<i class="fas fa-arrow-right"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= tohtml( _("IPv6 Firewall Rules")) ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= tohtml( _("Select all")) ?>">
			</div>
			<div class="units-table-cell"><?= tohtml( _("Pos")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell"><?= tohtml( _("Action")) ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell"><?= tohtml( _("Comment")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Protocol")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("Port")) ?></div>
			<div class="units-table-cell u-text-center"><?= tohtml( _("IPv6 Address")) ?></div>
		</div>

		<?php
		$keys  = array_keys($data ?? []);
		$total = count($keys);
		foreach ($data as $key => $value):
			$pos = array_search($key, $keys);
			$move_up_enabled   = ($pos > 0);
			$move_down_enabled = ($pos < $total - 1);
			if ($value['SUSPENDED'] == 'yes') {
				$status            = 'suspended';
				$spnd_action       = 'unsuspend';
				$spnd_action_title = _('Unsuspend');
				$spnd_icon         = 'fa-play';
				$spnd_icon_class   = 'icon-green';
				$spnd_confirmation = _('Are you sure you want to unsuspend rule #%s?');
			} else {
				$status            = 'active';
				$spnd_action       = 'suspend';
				$spnd_action_title = _('Suspend');
				$spnd_icon         = 'fa-pause';
				$spnd_icon_class   = 'icon-highlight';
				$spnd_confirmation = _('Are you sure you want to suspend rule #%s?');
			}
			$action     = $value['ACTION'];
			$iconClass  = $action == "DROP" ? "fa-circle-minus" : "fa-circle-check";
			$colorClass = $action == "DROP" ? "icon-red" : "icon-green";
		?>
		<div class="units-table-row <?php if ($status == 'suspended') echo 'disabled'; ?> js-unit"
			data-sort-action="<?= tohtml($action) ?>"
			data-sort-protocol="<?= tohtml($value['PROTOCOL']) ?>"
			data-sort-port="<?= tohtml($value['PORT']) ?>"
			data-sort-ip="<?= tohtml($value['IP6']) ?>"
			data-sort-comment="<?= tohtml($value['COMMENT']) ?>">

			<div class="units-table-cell">
				<div>
					<input id="check<?= tohtml($i) ?>" class="js-unit-checkbox" type="checkbox" title="<?= tohtml( _("Select")) ?>" name="rule[]" value="<?= tohtml($key) ?>">
					<label for="check<?= tohtml($i) ?>" class="u-hide-desktop"><?= tohtml( _("Select")) ?></label>
				</div>
			</div>

			<div class="units-table-cell units-table-heading-cell u-text-bold">
				<span class="u-hide-desktop"><?= tohtml( _("Position")) ?>:</span>
				<a href="/edit/firewall/ipv6/?<?= tohtml(http_build_query(["rule" => $key, "token" => $_SESSION["token"]])) ?>"
					title="<?= tohtml( _("Edit IPv6 Rule")) ?>">
					<?= tohtml($key) ?>
				</a>
			</div>

			<div class="units-table-cell" style="padding-left:0;padding-right:0;">
				<ul class="units-table-row-actions">
					<li class="units-table-row-action shortcut-up" data-key-action="js">
						<a class="units-table-row-action-link data-controls js-confirm-action"
							style="<?= $move_up_enabled ? 'display:block!important' : 'display:none!important' ?>"
							href="/move/firewall/ipv6/?<?= tohtml(http_build_query(["rule" => $key, "direction" => "up", "token" => $_SESSION["token"]])) ?>"
							title="<?= tohtml( _("Move Rule Up")) ?>"
							data-confirm-title="<?= tohtml( _("Move Up")) ?>"
							data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to move rule #%s up?"), $key)) ?>">
							<i class="fas fa-arrow-up icon-blue"></i>
							<span class="u-hide-desktop"><?= tohtml( _("Move Rule Up")) ?></span>
						</a>
					</li>
					<li class="units-table-row-action shortcut-down" data-key-action="js">
						<a class="units-table-row-action-link data-controls js-confirm-action"
							style="<?= $move_down_enabled ? '' : 'display:none!important' ?>"
							href="/move/firewall/ipv6/?<?= tohtml(http_build_query(["rule" => $key, "direction" => "down", "token" => $_SESSION["token"]])) ?>"
							title="<?= tohtml( _("Move Rule Down")) ?>"
							data-confirm-title="<?= tohtml( _("Move Down")) ?>"
							data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to move rule #%s down?"), $key)) ?>">
							<i class="fas fa-arrow-down icon-blue"></i>
							<span class="u-hide-desktop"><?= tohtml( _("Move Rule Down")) ?></span>
						</a>
					</li>
				</ul>
			</div>

			<div class="units-table-cell units-table-heading-cell u-text-bold">
				<span class="u-hide-desktop"><?= tohtml( _("Action")) ?>:</span>
				<a href="/edit/firewall/ipv6/?<?= tohtml(http_build_query(["rule" => $key, "token" => $_SESSION["token"]])) ?>"
					title="<?= tohtml( _("Edit IPv6 Rule")) ?>">
					<?php $suspended = $value["SUSPENDED"] == "no"; ?>
					<i class="fas <?= tohtml($iconClass) ?> u-mr5 <?= tohtml($suspended ? $colorClass : "") ?>"></i>
					<?= tohtml($action) ?>
				</a>
			</div>

			<div class="units-table-cell">
				<ul class="units-table-row-actions">
					<li class="units-table-row-action shortcut-enter" data-key-action="href">
						<a class="units-table-row-action-link"
							href="/edit/firewall/ipv6/?<?= tohtml(http_build_query(["rule" => $key, "token" => $_SESSION["token"]])) ?>"
							title="<?= tohtml( _("Edit")) ?>">
							<i class="fas fa-pencil icon-orange"></i>
							<span class="u-hide-desktop"><?= tohtml( _("Edit")) ?></span>
						</a>
					</li>
					<li class="units-table-row-action shortcut-s" data-key-action="js">
						<a class="units-table-row-action-link data-controls js-confirm-action"
							href="/<?= tohtml($spnd_action) ?>/firewall/ipv6/?<?= tohtml(http_build_query(["rule" => $key, "token" => $_SESSION["token"]])) ?>"
							title="<?= tohtml($spnd_action_title) ?>"
							data-confirm-title="<?= tohtml($spnd_action_title) ?>"
							data-confirm-message="<?= tohtml(sprintf($spnd_confirmation, $key)) ?>">
							<i class="fas <?= tohtml($spnd_icon) ?> <?= tohtml($spnd_icon_class) ?>"></i>
							<span class="u-hide-desktop"><?= tohtml($spnd_action_title) ?></span>
						</a>
					</li>
					<li class="units-table-row-action shortcut-delete" data-key-action="js">
						<a class="units-table-row-action-link data-controls js-confirm-action"
							href="/delete/firewall/ipv6/?<?= tohtml(http_build_query(["rule" => $key, "token" => $_SESSION["token"]])) ?>"
							title="<?= tohtml( _("Delete")) ?>"
							data-confirm-title="<?= tohtml( _("Delete")) ?>"
							data-confirm-message="<?= tohtml(sprintf(_("Are you sure you want to delete rule #%s?"), $key)) ?>">
							<i class="fas fa-trash icon-red"></i>
							<span class="u-hide-desktop"><?= tohtml( _("Delete")) ?></span>
						</a>
					</li>
				</ul>
			</div>

			<div class="units-table-cell u-text-bold">
				<span class="u-hide-desktop"><?= tohtml( _("Comment")) ?>:</span>
				<?php if (!empty($value['COMMENT'])) echo tohtml($value['COMMENT']); ?>
			</div>

			<div class="units-table-cell u-text-center-desktop">
				<span class="u-hide-desktop u-text-bold"><?= tohtml( _("Protocol")) ?>:</span>
				<?= tohtml($value["PROTOCOL"]) ?>
			</div>

			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= tohtml( _("Port")) ?>:</span>
				<?= tohtml($value["PORT"]) ?>
			</div>

			<div class="units-table-cell u-text-center-desktop">
				<span class="u-hide-desktop u-text-bold"><?= tohtml( _("IPv6 Address")) ?>:</span>
				<?= tohtml($value["IP6"]) ?>
			</div>
		</div>
		<?php ++$i; endforeach; ?>
	</div>

	<div class="units-table-footer">
		<p><?php printf(ngettext("%d IPv6 firewall rule", "%d IPv6 firewall rules", $total), $total); ?></p>
	</div>

</div>
