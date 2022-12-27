<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/edit/server/"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<a href="/add/ip/" class="button button-secondary" id="btn-create"><i class="fas fa-circle-plus icon-green"></i><?= _("Add IP") ?></a>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle" type="button" title="<?= _("Sort items") ?>">
					<?= _("sort by") ?>: <b><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></b>
				</button>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn u-hidden">
					<li entity="sort-date" sort_as_int="1"><span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-ip"><span class="name"><?= _("ip") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-netmask"><span class="name"><?= _("Netmask") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-interface"><span class="name"><?= _("Interface") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-domains" sort_as_int="1"><span class="name"><?= _("Domains") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-owner"><span class="name"><?= _("Owner") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
				</ul>
				<form x-bind="BulkEdit" action="/bulk/ip/" method="post">
					<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
					<select class="form-select" name="action">
						<option value=""><?= _("apply to selected") ?></option>
						<option value="reread IP"><?= _("reread IP") ?></option>
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

<div class="container units">
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div>
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input type="checkbox" class="js-toggle-all" title="<?= _("Select all") ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("IP Address") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact u-text-right"><b>&nbsp;</b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center compact-5"><b><?= _("Netmask") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Interface") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Status") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Domains") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Owner") ?></b></div>
			</div>
		</div>
	</div>

	<!-- Begin IP address list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;
		?>
		<div class="l-unit animate__animated animate__fadeIn" v_unit_id="<?=$key?>"
			v_section="ip" sort-ip="<?=str_replace('.', '', $key)?>" sort-date="<?=strtotime($data[$key]['DATE'] .' '. $data[$key]['TIME'] )?>"
			sort-netmask="<?=str_replace('.', '', $data[$key]['NETMASK'])?>" sort-interface="<?=$data[$key]['INTERFACE']?>" sort-domains="<?=$data[$key]['U_WEB_DOMAINS']?>"
			sort-owner="<?=$data[$key]['OWNER']?>">

			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?=$i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="ip[]" value="<?=$key?>">
				</div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><a href="/edit/ip/?ip=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Editing IP Address") ?>"><?=$key?> <?php if (!empty($data[$key]['NAT'])) echo ' → ' . $data[$key]['NAT'] . ''; ?></a></b>
				</div>
				<!-- START QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left compact u-text-right">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__logs shortcut-enter" key-action="href"><a href="/edit/ip/?ip=<?=$key?>&token=<?=$_SESSION['token']?>" title="<?= _("Editing IP Address") ?>"><i class="fas fa-pencil icon-orange icon-dim"></i></a></div>
							<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
								<a id="delete_link_<?=$i?>" class="data-controls do_delete" title="<?= _("delete") ?>">
									<i class="fas fa-trash icon-red icon-dim do_delete"></i>
									<input type="hidden" name="delete_url" value="/delete/ip/?ip=<?=$key?>&token=<?=$_SESSION['token']?>">
									<div id="delete_dialog_<?=$i?>" class="dialog js-confirm-dialog-delete" title="<?= _("Confirmation") ?>">
										<p><?=sprintf(_('DELETE_IP_CONFIRMATION'),$key)?></p>
									</div>
								</a>
							</div>
						</div>
					</div>
				</div>
				<!-- END QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left u-text-center compact-5"><?= $data[$key]["NETMASK"] ?></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><?= $data[$key]["INTERFACE"] ?></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _($data[$key]["STATUS"]) ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= $data[$key]["U_WEB_DOMAINS"] ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= $data[$key]["OWNER"] ?></b></div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d IP address", "%d IP addresses", $i), $i); ?>
		</p>
	</div>
</footer>
