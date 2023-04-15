<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/backup/incremental/"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<a href="/schedule/restore/incremental/?token=<?= $_SESSION["token"] ?>&snapshot=<?= htmlentities($_GET["snapshot"]) ?>" class="button button-secondary"><i class="fas fa-arrow-rotate-left icon-green"></i><?= _("Restore All") ?></a>
		</div>
		<div class="toolbar-right">
			<form x-data x-bind="BulkEdit" action="/bulk/restore/" method="post">
				<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
				<input type="hidden" name="backup" value="<?= htmlentities($_GET["snapshot"]) ?>">
				<select class="form-select" name="action">
					<option value=""><?= _("apply to selected") ?></option>
					<option value="restore"><?= _("restore") ?></option>
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
				<div class="clearfix l-unit__stat-col--left compact-4"><b><?= _("Type") ?></b></div>
				<div class="clearfix l-unit__stat-col--left wide-7"><b><?= _("Details") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-right compact-4"><b><?= _("Restore") ?></b></div>
			</div>
		</div>
	</div>

	<div class="l-unit animate__animated animate__fadeIn">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				&nbsp;
			</div>
			<div class="clearfix l-unit__stat-col--left compact-4">
				<div class="l-unit__stat-col l-unit__stat-col--left">..</div>
			</div>
			<div class="clearfix l-unit__stat-col--left wide-2">
				<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><?=_('Directory');?></div>
			</div>
			<div class="clearfix l-unit__stat-col--left wide-7">
				<div class="l-unit__stat-col l-unit__stat-col--left wide-7"></div>
			</div>
			<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
				<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
					<div class="actions-panel clearfix">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="l-unit animate__animated animate__fadeIn">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact">
				&nbsp;
			</div>
			<div class="clearfix l-unit__stat-col--left compact-4">
				<div class="l-unit__stat-col l-unit__stat-col--left">.</div>
			</div>
			<div class="clearfix l-unit__stat-col--left wide-2">
				<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><?=_('Directory');?></div>
			</div>
			<div class="clearfix l-unit__stat-col--left wide-7">
				<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><?=convert_datetime($files[0]['ctime'], 'Y-m-d  H:i:s');?></div>
			</div>
			<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
				<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
					<div class="actions-panel clearfix">
					</div>
				</div>
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
					<input id="check<?= $i ?>" class="ch-toggle" type="checkbox" name="files[]" value="<?= $key ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4">
					<?php if($file['type'] == 'dir'){?>
						<div class="l-unit__stat-col l-unit__stat-col--left"><a href="/list/backup/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&browse=yes&folder=<?=htmlentities($file['path'])?>&token=<?=$_SESSION["token"]?>"><?=$file['name']?></a></div>
					<?php }else{?>
						<div class="l-unit__stat-col l-unit__stat-col--left"><?=$file['name']?></div>
					<?php
					}
					?>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-2">
					<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><?=$file['type']?></div>
				</div>
				<div class="clearfix l-unit__stat-col--left wide-7">
					<div class="l-unit__stat-col l-unit__stat-col--left wide-7"><?=convert_datetime($file['ctime'], 'Y-m-d  H:i:s');?></div>
				</div>
				<div class="clearfix l-unit__stat-col--left compact-4 u-text-right">
					<div class="l-unit-toolbar__col l-unit-toolbar__col--right u-noselect">
						<div class="actions-panel clearfix">
							<div class="actions-panel__col actions-panel__list shortcut-enter" key-action="href">
								<a href="/schedule/restore/incremental/?snapshot=<?= htmlentities($_GET["snapshot"]) ?>&type=web&object=<?= $key ?>&token=<?= $_SESSION["token"] ?>" title="<?= _("Restore") ?>">
									<i class="fas fa-arrow-rotate-left icon-green icon-dim u-mr5"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		}
		var_dump($file);
	?>

</div>

<footer class="app-footer">
	<div class="container app-footer-inner">
		<p>
			<?php printf(ngettext("%d item", "%d items", $i), $i); ?>
		</p>
	</div>
</footer>
