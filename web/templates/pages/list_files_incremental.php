<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if(str_starts_with($files[0]['path'],'/home/'.$user_plain) && $files[0]['path'] != '/home/'.$user_plain ){
			?>
			<a class="button button-secondary" id="btn-back" href="/list/backup/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&browse=yes&folder=<?=htmlentities($files[0]['path'])?>/../&token=<?=$_SESSION["token"]?>"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<?php }else{
			?>
			<a class="button button-secondary" id="btn-back" href="/list/backup/incremental/?token=<?=$_SESSION["token"]?>"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<?php
			}
			?>
		</div>
		<div class="toolbar-right">
			<form x-data x-bind="BulkEdit" action="/bulk/restore/" method="post">
				<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
				<input type="hidden" name="backup" value="<?= htmlentities($_GET["snapshot"]) ?>">
				<select class="form-select" name="action">
					<option value=""><?= _("apply to selected") ?></option>
					<option value="restore"><?= _("Restore") ?></option>
				</select>
				<button type="submit" class="toolbar-input-submit">
					<i class="fas fa-arrow-right"></i>
				</button>
			</form>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
					<input type="search" class="form-control js-search-input" name="q" value="<? echo isset($_POST['q']) ? htmlspecialchars($_POST['q']) : '' ?>">
					<button type="submit" class="toolbar-input-submit" value=""><i class="fas fa-magnifying-glass"></i></button>
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
				<div class="clearfix l-unit__stat-col--left wide-7"><b><?= _("Nme") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-right compact-4">&nbsp;</div>
				<div class="clearfix l-unit__stat-col--left compact-4"><b><?= _("Type") ?></b></div>
				<div class="clearfix l-unit__stat-col--left compact-4"><b><?= _("Date") ?></b></div>

			</div>
		</div>
	</div>
	<?php if($files[0]['path'] != '/home/'.$user_plain){
	?>

	<div class="l-unit animate__animated animate__fadeIn">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				&nbsp;
			</div>
			<div class="clearfix l-unit__stat-col--left wide-7">
				<div class="l-unit__stat-col l-unit__stat-col--left"><b><a href="/list/backup/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&browse=yes&folder=<?=htmlentities($files[0]['path'])?>/../&token=<?=$_SESSION["token"]?>"><i class="fas fa-folder icon-dim u-mr5"></i>..</a></b></div>
			</div>
			<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
				<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
					<div class="actions-panel clearfix">
						&nbsp;
					</div>
				</div>
			</div>
			<div class="clearfix l-unit__stat-col--left compact-4">
				<div class="l-unit__stat-col l-unit__stat-col--left compact-4"><?=_('Directory');?></div>
			</div>
			<div class="clearfix l-unit__stat-col--left wide-">
				<div class="l-unit__stat-col l-unit__stat-col--left wide-7"></div>
			</div>
		</div>
	</div>
	<?php
	}
	?>
	<div class="l-unit animate__animated animate__fadeIn">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				&nbsp;
			</div>
			<div class="clearfix l-unit__stat-col--left wide-7">
				<div class="l-unit__stat-col l-unit__stat-col--left"><b><a href="/list/backup/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&browse=yes&folder=<?=htmlentities($files[0]['path'])?>&token=<?=$_SESSION["token"]?>"><i class="fas fa-folder icon-dim u-mr5"></i>.</a></b></div>
			</div>
			<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
				<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
					<div class="actions-panel clearfix">
						<div class="actions-panel__col actions-panel__list shortcut-enter" key-action="href">
							&nbsp;
						</div>
					</div>
				</div>
			</div>
			<div class="clearfix l-unit__stat-col--left compact-4">
				<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><?=_('Directory');?></div>
			</div>
			<div class="clearfix l-unit__stat-col--left compact-4">
				<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><?=convert_datetime($files[0]['ctime'], 'Y-m-d  H:i:s');?></div>
			</div>
		</div>
	</div>
	<!-- List web domains -->
	<?php
		unset($files[0]);
		foreach($files as $file){
		?>
		<div class="l-unit animate__animated animate__fadeIn">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left super-compact">
					<?php if($file['type'] == 'dir' && str_starts_with($file['path'], '/home/'.$user_plain.'/conf')){
					?>
					&nbsp;
					<?php
					}else{
					?><input id="check<?= $i ?>" class="ch-toggle" type="checkbox" name="files[]" value="<?=htmlentities($file['path'])?>">
					<?php
					}
					?>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-7">
					<?php if($file['type'] == 'dir'){
						if(str_starts_with($file['path'], '/home/'.$user_plain.'/conf')){
						?>
						<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><i class="fas fa-folder icon-dim u-mr5"></i><?=$file['name']?></b></div>
						<?php
						}else{
						?>
						<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><a href="/list/backup/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&browse=yes&folder=<?=htmlentities($file['path'])?>&token=<?=$_SESSION["token"]?>"><i class="fas fa-folder icon-dim u-mr5"></i><?=$file['name']?></a></b></div>

						<?php
						}
						?>
					<?php }else{?>
						<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><b><i class="fas fa-file icon-dim u-mr5"></i><?=$file['name']?></b></div>
					<?php
					}
					?>
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__list shortcut-enter" key-action="href">
								<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=file&object=<?=htmlentities($file['path'])?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
									<i class="fas fa-arrow-rotate-left icon-green icon-dim u-mr5"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4">
					<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><?=getTransByType($file['type']);?></div>
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4">
					<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><?=convert_datetime($file['ctime'], 'Y-m-d  H:i:s');?></div>
				</div>
			</div>
		</div>
		<?php
		}
	?>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d item", "%d items", $i), $i); ?>
		</p>
	</div>
</footer>
