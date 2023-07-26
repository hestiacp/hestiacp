<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="javascript:window.history.back();" class="button button-secondary button-back js-button-back">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<a href="javascript:location.reload();" class="button button-secondary">
				<i class="fas fa-arrows-rotate icon-green"></i><?= _("Refresh") ?>
			</a>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<span class="u-text-bold">
						<?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i>
					</span>
				</button>
				<ul class="toolbar-sorting-menu js-sorting-menu u-hidden">
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<div class="toolbar-search">
					<form action="/search/" method="get">
						<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
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

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Search Results") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("Status") ?></div>
			<div class="units-table-cell"><?= _("Search Results") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Date") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Owner") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Type") ?></div>
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
			<div class="units-table-row <?php if ($status == 'suspended') echo 'disabled'; ?> js-unit"
				data-uniq-id="<?= $uniq_id?>"
				data-sort-date="<?= strtotime($value['DATE'].' '.$value['TIME']) ?>"
				data-sort-name="<?= $value['RESULT'] ?>"
				data-sort-type="<?= _($object) ?>"
				data-sort-owner="<?= $value["USER"] ?>"
				data-sort-status="<?= $status ?>"
				style="<?php if (($_SESSION['POLICY_SYSTEM_HIDE_ADMIN'] === 'yes') && ($value['USER']) === 'admin') { echo 'display: none;'; } ?>">
				<div class="units-table-cell u-text-center-desktop">
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
					<i class="fa <?= $icon ?> icon-dim"></i>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Status") ?>:</span>
					<?php if ($status === "active") { ?>
						<i class="fas fa-circle-check icon-green"></i>
					<?php } ?>
					<?php if ($status === "suspended") { ?>
						<i class="fas fa-triangle-exclamation icon-orange"></i>
					<?php } ?>
				</div>
				<div class="units-table-cell units-table-heading-cell u-text-bold">
					<span class="u-hide-desktop"><?= _("Search Results") ?>:</span>
					<?php
						if ($value['KEY'] == 'RECORD') {
							$edit_lnk = '/edit/'.$value['TYPE'].'/?domain='.$value['PARENT'].'&record_id='.$value['LINK'].'&user='.$value['USER'];
						}
						if ($value['KEY'] == 'ACCOUNT') {
							$edit_lnk = '/edit/'.$value['TYPE'].'/?domain='.$value['PARENT'].'&account='.$value['LINK'].'&user='.$value['USER'];
						}
						if ($value['KEY'] == 'JOB') {
							$edit_lnk = '/edit/'.$value['TYPE'].'/?job='.$value['LINK'].'&user='.$value['USER'];
						}
						if ($value['KEY'] == 'DATABASE') {
							$edit_lnk = '/edit/'.$value['TYPE'].'/?database='.$value['RESULT'].'&user='.$value['USER'];
						}
						if (($value['KEY'] != 'RECORD') && ($value['KEY'] != 'ACCOUNT') && ($value['KEY'] != 'JOB') && ($value['KEY'] != 'DATABASE') ) {
							$edit_lnk = '/edit/'.$value['TYPE'].'/?'.strtolower($value['KEY']).'='.$value['RESULT'].'&user='.$value['USER'];
						}
					?>
					<?php
						if (($_SESSION['userContext'] === 'admin') && ($_SESSION['user'] !== 'admin') && ($value['USER'] === 'admin') && ($_SESSION['POLICY_SYSTEM_PROTECTED_ADMIN'] === 'yes')) {
							echo $value['RESULT'];
						} else {
							if ($value['USER'] == $_SESSION['user']) {
								$href = $edit_lnk.'&token='.$_SESSION['token'];
							} else {
								$href = '/login/?loginas='.$value['USER'].'&token='.$_SESSION['token'].'&edit_link='.urlencode($edit_lnk);
							}
							echo '<a href="' . $href . '">' . $value['RESULT'] . '</a>';
						}
					?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Date") ?>:</span>
					<time datetime="<?= htmlspecialchars($value["DATE"]) ?>">
						<?= translate_date($value["DATE"]) ?>
					</time>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Owner") ?>:</span>
					<a href="/search/?q=<?= htmlentities($_GET["q"]) ?>&u=<?= $value["USER"] ?>&token=<?= $_SESSION["token"] ?>">
						<?= $value["USER"] ?>
					</a>
					<?php if (!($_SESSION["POLICY_SYSTEM_HIDE_ADMIN"] === "yes" && $value["USER"] !== "admin") && $_SESSION["userContext"] === "admin") { ?>
						<a href="/login/?loginas=<?= $value["USER"] ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Log in as") ?> <?= $value["USER"] ?>" class="u-ml5">
							<i class="fas fa-right-to-bracket icon-green icon-dim"></i>
							<span class="u-hidden-visually"><?= _("Log in as") ?> <?= $value["USER"] ?></span>
						</a>
					<?php } ?>
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
					<?= _($object) ?>
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d object", "%d objects", $i), $i); ?>
		</p>
	</div>
</footer>
