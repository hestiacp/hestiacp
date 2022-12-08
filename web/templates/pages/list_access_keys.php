<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/edit/user/"><i class="fas fa-arrow-left status-icon blue"></i><?= _("Back") ?></a>
			<a href="/add/access-key/" class="button button-secondary" id="btn-create"><i class="fas fa-circle-plus status-icon green"></i><?= _("Add Access Key") ?></a>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle" title="<?= _("Sort items") ?>">
					<?= _("sort by") ?>: <b><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></b>
				</button>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn u-hidden">
					<li entity="sort-date" sort_as_int="1"><span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-key"><span class="name"><?= _("Access Key") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-comment"><span class="name"><?= _("Comment") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
				</ul>
				<form x-bind="BulkEdit" action="/bulk/access-key/" method="post">
					<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
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


<div class="container units">
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div>
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="toggle-all" type="checkbox" name="toggle-all" value="toggle-all" title="<?= _("Select all") ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left wide-6"><b><?= _("Access Key") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact text-right"><b>&nbsp;</b></div>
				<div class="clearfix l-unit__stat-col--left text-center wide-2"><b><?= _("Comment") ?></b></div>
				<div class="clearfix l-unit__stat-col--left text-center"><b><?= _("Date") ?></b></div>
				<div class="clearfix l-unit__stat-col--left text-center"><b><?= _("Time") ?></b></div>
			</div>
		</div>
	</div>

	<!-- Begin Access Keys list item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;
				$key_user = !empty($value['USER']) ? $value['USER'] : 'admin';
				$key_comment = !empty($value['COMMENT']) ? $value['COMMENT'] : '-';
				//$key_permissions = !empty($value['PERMISSIONS']) ? $value['PERMISSIONS'] : '-';
				//$key_permissions = implode(' ', $key_permissions);
				$key_date = !empty($value['DATE']) ? $value['DATE'] : '-';
				$key_time = !empty($value['TIME']) ? $value['TIME'] : '-';
		?>
		<div class="l-unit animate__animated animate__fadeIn" v_unit_id="<?=$key?>"
			v_section="key" sort-key="<?=strtolower($key)?>"
			sort-comment="<?=strtolower($key_comment)?>"
			sort-date="<?=strtotime($data[$key]['DATE'] .' '. $data[$key]['TIME'] )?>">

			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<input id="check<?=$i ?>" class="ch-toggle" type="checkbox" title="<?= _("Select") ?>" name="key[]" value="<?=$key?>">
				</div>
				<div class="clearfix l-unit__stat-col--left wide-6">
					<b><a href="/list/access-key/?key=<?=htmlentities($key);?>&token=<?=$_SESSION['token']?>" title="<?= _("Access Key") ?>: <?=$key;?>"><?=$key;?></a></b>
				</div>

				<!-- START QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left compact text-right">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__delete shortcut-delete" key-action="js">
								<a id="delete_link_<?= $i ?>" class="data-controls do_delete" title="<?= _("delete") ?>">
									<i class="fas fa-trash status-icon red status-icon dim do_delete"></i>
									<input type="hidden" name="delete_url" value="/delete/access-key/?key=<?= $key ?>&token=<?= $_SESSION["token"] ?>">
									<div id="delete_dialog_<?= $i ?>" class="dialog js-confirm-dialog-delete" title="<?= _("Confirmation") ?>">
										<p><?= sprintf(_("DELETE_ACCESS_KEY_CONFIRMATION"), $key) ?></p>
									</div>
								</a>
							</div>
						</div>
					</div>
				</div>
				<!-- END QUICK ACTION TOOLBAR AREA -->
				<div class="clearfix l-unit__stat-col--left text-center wide-2"><b><?= _($key_comment) ?></b></div>
				<div class="clearfix l-unit__stat-col--left text-center"><b><?= $key_date ?></b></div>
				<div class="clearfix l-unit__stat-col--left text-center"><b><?= $key_time ?></b></div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container">
		<div class="l-unit-ft">
			<div class="l-unit__col l-unit__col--right">
				<?php printf(ngettext("%d Access Key", "%d Access Keys", $i), $i); ?>
			</div>
		</div>
	</div>
</footer>
