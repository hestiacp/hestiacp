<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/firewall/"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<a href="/add/firewall/ipset/" class="button button-secondary" id="btn-create"><i class="fas fa-circle-plus icon-green"></i><?= _("Add IP list") ?></a>
		</div>
		<div class="toolbar-right">
			<form x-bind="BulkEdit" action="/bulk/firewall/ipset/" method="post">
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
<!-- End toolbar -->

<div class="container units">
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				<input id="toggle-all" type="checkbox" name="toggle-all" value="toggle-all" title="<?= _("Select all") ?>">
			</div>
			<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("Ip List Name") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-4"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-5"><b><?= _("Autoupdate") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-4"><b><?= _("Ip Version") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-4"><b><?= _("Date") ?></b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center compact-4"><b><?= _("Time") ?></b></div>
		</div>
	</div>

	<!-- Begin firewall IP address list item loop -->
	<?php foreach ($data as $key => $value) {
 	$listname = $key; ?>
		<div class="l-unit animate__animated animate__fadeIn">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?= $i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="setname[]" value="<?= $listname ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><?= $listname ?></b></div>
				<!-- START QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left compact-4">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
								<a id="delete_link_<?= $i ?>" class="data-controls do_delete">
									<i class="fas fa-trash icon-red icon-dim do_delete"></i>
									<input type="hidden" name="delete_url" value="/delete/firewall/ipset/?listname=<?= $listname ?>&token=<?= $_SESSION["token"] ?>">
									<div id="delete_dialog_<?= $i ?>" class="dialog js-confirm-dialog-delete" title="<?= _("Confirmation") ?>">
										<p><?= sprintf(_("DELETE_IPSET_CONFIRMATION"), $key) ?></p>
									</div>
								</a>
							</div>
						</div>
					</div>
				</div>
				<!-- END QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left u-text-center compact-5"><b>
						<?php if ($data[$key]["AUTOUPDATE"] == "no") { ?>
							<i class="fas fa-circle-xmark icon-red"></i>
						<?php } else { ?>
							<i class="fas fa-circle-check icon-green"></i>
						<?php } ?>
					</b>
				</div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact-4"><?= _($data[$key]["IP_VERSION"]) ?></div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact-4"><?= _($data[$key]["DATE"]) ?></div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact-4"><?= $data[$key]["TIME"] ?></div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php
				if ( $i == 0) {
					echo _('There are currently no IP lists defined.');
				} else {
					printf(ngettext('%d Ipset list', '%d Ipset lists', $i),$i);
				}
			?>
		</p>
	</div>
</footer>
