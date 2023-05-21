<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/firewall/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<a href="/add/firewall/ipset/" class="button button-secondary js-button-create">
				<i class="fas fa-circle-plus icon-green"></i><?= _("Add IP list") ?>
			</a>
		</div>
		<div class="toolbar-right">
			<form x-data x-bind="BulkEdit" action="/bulk/firewall/ipset/" method="post">
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
<!-- End toolbar -->

<div class="container">
	<div class="units js-units-container">
		<div class="header units-header">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("IP List Name") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact-4"><b>&nbsp;</b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact-5"><b><?= _("Auto Update") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact-4"><b><?= _("IP Version") ?></b></div>
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
						<input id="check<?= $i ?>" class="js-unit-checkbox" type="checkbox" title="<?= _("Select") ?>" name="setname[]" value="<?= $listname ?>">
					</div>
					<div class="clearfix l-unit__stat-col--left wide-3"><b><?= $listname ?></b></div>
					<!-- START QUICK ACTION TOOLBAR AREA -->
					<div class="clearfix l-unit__stat-col--left compact-4">
						<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
							<div class="actions-panel clearfix">
								<div class="actions-panel__col actions-panel__delete shortcut-delete" data-key-action="js">
									<a
										class="data-controls js-confirm-action"
										href="/delete/firewall/ipset/?listname=<?= $listname ?>&token=<?= $_SESSION["token"] ?>"
										data-confirm-title="<?= _("Delete") ?>"
										data-confirm-message="<?= sprintf(_("Are you sure you want to delete IP list %s?"), $key) ?>"
									>
										<i class="fas fa-trash icon-red icon-dim"></i>
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
		<?php
		$i++;
	} ?>
	</div>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php
				if ( $i == 0) {
					echo _('There are currently no IP lists defined.');
				} else {
					printf(ngettext('%d IP list', '%d IP lists', $i),$i);
				}
			?>
		</p>
	</div>
</footer>
