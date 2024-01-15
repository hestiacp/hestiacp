<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
			<?php if(str_starts_with($files[0]['path'],'/home/'.$user_plain) && $files[0]['path'] != '/home/'.$user_plain ){
			?>
			<a class="button button-secondary" id="btn-back" href="/list/backup/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&browse=yes&folder=<?=htmlentities($files[0]['path'])?>/../&token=<?=$_SESSION["token"]?>"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<?php }else{
			?>
			<a class="button button-secondary" id="btn-back" href="/list/backup/incremental/?token=<?=$_SESSION["token"]?>"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<?php
			}
			?>
			<?php } ?>
		</div>
		<div class="toolbar-right">
					<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/restore/" method="post">
						<input type="hidden" name="backup" value="<?=htmlentities($_GET["snapshot"]);?>"
						<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
						<select class="form-select" name="action">
							<option value=""><?= _("Apply to selected") ?></option>
							<option value="delete"><?= _("Restore Files") ?></option>
						</select>
						<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
							<i class="fas fa-arrow-right"></i>
						</button>
					</form>
				<?php } ?>
				<div class="toolbar-search">
					<form action="/search/" method="get">
						<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
						<input type="search" class="form-control js-search-input" name="q" value="<? echo isset($_POST['q']) ? htmlspecialchars($_POST['q']) : '' ?>" title="<?= _("Search") ?>">
						<button type="submit" class="toolbar-input-submit" title="<?= _("Search") ?>">
							<i class="fas fa-magnifying-glass"></i>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
<!-- End toolbar -->

<div class="container">
	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("Files") ?></h1>
	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell">
				<input type="checkbox" class="js-toggle-all-checkbox" title="<?= _("Select all") ?>" <?= $display_mode ?>>
			</div>
			<div class="units-table-cell"><?= _("Name") ?></div>
			<div class="units-table-cell"></div>
			<div class="units-table-cell u-text-center"><?= _("Type") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Date") ?></div>
		</div>
		<?php
		foreach($files as $file){
			if($file['path'] != '/home/'.$user_plain){
				if($file['path'] != '/home/'.$user_plain && $file['path'] == $files[0]['path']){
					?>
					<div class="units-table-row js-unit">
						<div class="units-table-cell">
						</div>
						<div class="units-table-cell units-table-heading-cell u-text-bold">
								<b><a href="/list/backup/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&browse=yes&folder=<?=htmlentities($files[0]['path'])?>/../&token=<?=$_SESSION["token"]?>"><i class="fas fa-folder icon-dim u-mr5"></i>..</a></b>
						</div>
						<div class="units-table-cell">
						</div>
						<div class="units-table-cell">
							<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
							<span class="u-text-bold">
								Directory
							</span>
						</div>
						<div class="units-table-cell">
						</div>
					</div>
				<?php
					}else{
				?>
					<div class="units-table-row js-unit">
						<div class="units-table-cell">
							<div>
								<input id="check<?= $i ?>" class="ch-toggle" type="checkbox" name="files[]" value="<?=htmlentities($file['path'])?>">
							</div>
						</div>
						<div class="units-table-cell">
							<div>
								<?php if($file['type'] == 'dir'){
									if(str_starts_with($file['path'], '/home/'.$user_plain.'/conf')){
								?>
								<b><i class="fas fa-folder icon-dim u-mr5"></i><?=$file['name']?></b>
								<?php
									}else{
									?>
										<b><a href="/list/backup/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&browse=yes&folder=<?=htmlentities($file['path'])?>&token=<?=$_SESSION["token"]?>"><i class="fas fa-folder icon-dim u-mr5"></i><?=$file['name']?></a></b>
									<?php
									}
								}else{
									?>
										<b><i class="fas fa-file icon-dim u-mr5"></i><?=$file['name']?></b>
									<?php
								}?>
							</div>
						</div>
						<div class="units-table-cell">
							<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=file&object=<?=htmlentities($file['path'])?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
								<i class="fas fa-arrow-rotate-left icon-green icon-dim u-mr5"></i>
							</a>
						</div>
						<div class="units-table-cell">
							<span class="u-hide-desktop u-text-bold"><?= _("Type") ?>:</span>
							<span class="u-text-bold">
								<?=getTransByType($file['type']);?>
							</span>
						</div>
						<div class="units-table-cell">
							<span class="u-hide-desktop u-text-bold"><?= _("Date / Time") ?>:</span>
							<span class="u-text-bold">
								<?=convert_datetime($file['ctime'], 'Y-m-d  H:i:s');?>
							</span>
						</div>
					</div>
				<?php
				}
			}
		} ?>
	</div>
</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d item", "%d items", $i), $i); ?>
		</p>
	</div>
</footer>
