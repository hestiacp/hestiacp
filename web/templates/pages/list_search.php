<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="javascript:window.history.back();" class="button button-secondary" id="btn-back"><i class="fas fa-arrow-left status-icon blue"></i><?= _("Back") ?></a>
			<a href="javascript:location.reload();" class="button button-secondary"><i class="fas fa-arrows-rotate status-icon green"></i> <?= _("Refresh") ?></a>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle" title="<?= _("Sort items") ?>">
					<?= _("sort by") ?>: <b><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></b>
				</button>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn u-hidden">
					<li entity="sort-date" sort_as_int="1"><span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-name"><span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
				</ul>
				<div class="toolbar-search">
					<form action="/search/" method="get">
						<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
						<input type="search" class="form-control js-search-input" name="q" value="<? echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>" title="<?= _("Search") ?>">
						<button type="submit" class="toolbar-input-submit" title="<?= _("Search") ?>">
							<i class="fas fa-magnifying-glass"></i>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container units">
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				&nbsp;
			</div>
			<div class="clearfix l-unit__stat-col--left text-center compact-2"><b><?= _("Status") ?></b></div>
			<div class="clearfix l-unit__stat-col--left wide-5"><b><?= _("Search Results") ?></b></div>
			<div class="clearfix l-unit__stat-col--left compact-3"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left text-center"><b><?= _("Date") ?></b></div>
			<div class="clearfix l-unit__stat-col--left text-center"><b><?= _("Owner") ?></b></div>
			<div class="clearfix l-unit__stat-col--left text-center"><b><?= _("Type") ?></b></div>
		</div>
	</div>

	<!-- Begin search result item loop -->
	<?php
		foreach ($data as $key => $value) {
			++$i;

			if ($value['SUSPENDED'] == 'yes') {
				$status = 'suspended';
				$spnd_action = 'unsuspend';
			} else {
				$status = 'active';
				$spnd_action = 'suspend';
			}

			if ($value['TYPE'] == 'db') {
				$object = 'database';
			} else {
				$object = strtolower($value['TYPE'] . ' ' . $value['KEY']);
			}

			$uniq_id = $value['TYPE'] . '-';
			if ($value['KEY'] == 'ACCOUNT'){
				$uniq_id .= 'acc-';
			}
			$uniq_id .= sha1($value['RESULT']);
		?>
		<div class="l-unit <?php if($status == 'suspended') echo 'l-unit--suspended'; if($_COOKIE[$uniq_id] == 1) echo ' l-unit--starred'; ?> animate__animated animate__fadeIn" id="web-unit-<?=$i?>" uniq-id="<?=$uniq_id?>" sort-date="<?=strtotime($value['DATE'].' '.$value['TIME'])?>" sort-name="<?=$value['RESULT']?>" sort-type="<?=_($object)?>" sort-owner="<?=$value['USER']?>" sort-status="<?=$status?>"
			style="<?php if (($_SESSION['POLICY_SYSTEM_HIDE_ADMIN'] === 'yes') && ($value['USER']) === 'admin') { echo 'display: none;';}?>">

			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact text-center">
					<?php
						if ($object === 'web domain') {
								$icon = 'fa-earth-americas';
						}
						if ($object === 'mail domain') {
								$icon = 'fa-envelopes-bulk';
						}
						if ($object === 'dns domain') {
								$icon = 'fa-book-atlas';
						}
						if ($object === 'dns record') {
								$icon = 'fa-book-atlas';
						}
						if ($object === 'database') {
								$icon = 'fa-database';
						}
						if ($object === 'cron job') {
								$icon = 'fa-clock';
						}
					?>
					<i class="fa <?=$icon;?> status-icon dim"></i>
				</div>
				<div class="clearfix l-unit__stat-col--left compact-2 text-center">
					<b>
						<?php if ($status === 'active') {?>
							<i class="fas fa-circle-check status-icon green"></i>
						<?php	} ?>
						<?php if ($status === 'suspended') {?>
							<i class="fas fa-triangle-exclamation status-icon orange"></i>
						<?php	} ?>
					</b>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-5 truncate">
					<?php
						if ($value['KEY'] == 'RECORD') {
							$edit_lnk = '/edit/'.$value['TYPE'].'/?domain='.$value['PARENT'].'&record_id='.$value['LINK'].'&user='.$value['USER'].'&token='.$_SESSION['token'].'';
						}
						if ($value['KEY'] == 'ACCOUNT') {
							$edit_lnk = '/edit/'.$value['TYPE'].'/?domain='.$value['PARENT'].'&account='.$value['LINK'].'&user='.$value['USER'].'&token='.$_SESSION['token'].'';
						}
						if ($value['KEY'] == 'JOB') {
							$edit_lnk = '/edit/'.$value['TYPE'].'/?job='.$value['LINK'].'&user='.$value['USER'].'&token='.$_SESSION['token'].'';
						}
						if ($value['KEY'] == 'DATABASE') {
							$edit_lnk = '/edit/'.$value['TYPE'].'/?database='.$value['RESULT'].'&user='.$value['USER'].'&token='.$_SESSION['token'].'';
						}
						if (($value['KEY'] != 'RECORD') && ($value['KEY'] != 'ACCOUNT') && ($value['KEY'] != 'JOB') && ($value['KEY'] != 'DATABASE') ) {
							$edit_lnk = '/edit/'.$value['TYPE'].'/?'.strtolower($value['KEY']).'='.$value['RESULT'].'&user='.$value['USER'].'&token='.$_SESSION['token'].'';
						}
					?>
					<b>
						<?php if (($_SESSION['userContext'] === 'admin') && ($_SESSION['user'] !== 'admin') && ($value['USER'] === 'admin') && ($_SESSION['POLICY_SYSTEM_PROTECTED_ADMIN'] === 'yes')) {?>
							<?=$value['RESULT']?>
						<?} else {?>
							<a href="<?=$edit_lnk; ?>"><?=$value['RESULT']?></a>
						<?php } ?>
					</b>
				</div>
				<div class="clearfix l-unit__stat-col--left text-right compact-3">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							&nbsp;
						</div>
					</div>
				</div>
				<div class="clearfix l-unit__stat-col--left text-center"><?=translate_date($value['DATE'])?></div>
				<div class="clearfix l-unit__stat-col--left text-center"><b>
						<a href="/search/?q=<?=htmlentities($_GET['q']); ?>&u=<?=$value['USER']; ?>&token=<?=$_SESSION['token']?>"><?=$value['USER']; ?></a>
						<?php if (!($_SESSION['POLICY_SYSTEM_HIDE_ADMIN'] === 'yes' && $value['USER'] !== 'admin')){
						if ($_SESSION['userContext'] === 'admin'){
						?>
							<a href="/login/?loginas=<?=$value['USER']?>&token=<?=$_SESSION['token']?>" title="<?= _("login as") ?> <?=$value['USER']?>"><i class="fas fa-right-to-bracket status-icon green status-icon dim icon-large"></i></a>
						<?php
						}
						}
						?>
						</b></div>
				<div class="clearfix l-unit__stat-col--left text-center"><?=_($object)?></b></div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container">
		<div class="l-unit-ft">
			<div class="l-unit__col l-unit__col--right">
				<?php printf(ngettext('%d object', '%d objects', $i),$i); ?>
			</div>
		</div>
	</div>
</footer>
