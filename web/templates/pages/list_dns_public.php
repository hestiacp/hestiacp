<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== 'true') {?>
				<a href="/add/dns/" class="button button-secondary" id="btn-create">
					<i class="fas fa-circle-plus status-icon green"></i><?=_('Add DNS Domain');?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<ul class="context-menu sort-order animate__animated animate__fadeIn" style="display: none;">
					<li entity="sort-date" sort_as_int="1"><span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?=_('Date');?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-expire" sort_as_int="1"><span class="name"><?=_('Expire');?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-ip"><span class="name"><?=_('IP address');?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-name"><span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?=_('Name');?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
					<li entity="sort-records"><span class="name"><?=_('Records');?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span></li>
				</ul>
				<div class="sort-by" title="<?=_('Sort items');?>">
					<?=_('sort by');?>:
					<b>
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?=$label;?> <i class="fas fa-arrow-down-a-z"></i>
					</b>
				</div>
				<?php if ($read_only !== 'true') {?>
					<form action="/bulk/dns/" method="post" id="objects">
						<input type="hidden" name="token" value="<?=$_SESSION['token']?>" />
						<select class="form-select" name="action">
							<option value=""><?=_('apply to selected');?></option>
							<?php if ($_SESSION['userContext'] === 'admin') {?>
								<option value="rebuild"><?=_('rebuild');?></option>
							<?php } ?>
							<option value="suspend"><?=_('suspend');?></option>
							<option value="unsuspend"><?=_('unsuspend');?></option>
							<option value="delete"><?=_('delete');?></option>
						</select>
						<button type="submit" class="toolbar-submit" value="" title="<?=_('apply to selected');?>">
							<i class="fas fa-arrow-right"></i>
						</button>
					</form>
				<?php } ?>
			</div>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?=$_SESSION['token']?>" />
					<input type="search" class="form-control js-search-input" name="q" value="<? echo isset($_POST['q']) ? htmlspecialchars($_POST['q']) : '' ?>" title="<?=_('Search');?>">
					<button type="submit" class="toolbar-submit" onclick="return doSearch('/search/')" value="" title="<?=_('Search');?>">
						<i class="fas fa-magnifying-glass"></i>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container units">

<div class="l-unit animate__animated animate__fadeIn">
	<div class="l-unit__col l-unit__col--right">
		<div class="clearfix l-unit__stat-col--left text-center u-pt10"><b><?=_('DNSKEY record');?></b></div>
		<div class="clearfix l-unit__stat-col--left wide-3"><b><input type="text" class="form-control" value="<?php echo $data[$domain]['RECORD'];?>"></b></div>
	</div>
</div>
<div class="l-unit animate__animated animate__fadeIn">
	<div class="l-unit__col l-unit__col--right">
		<div class="clearfix l-unit__stat-col--left text-center u-pt10"><b><?=_('DS record');?></b></div>
		<div class="clearfix l-unit__stat-col--left wide-3"><b><input type="text" class="form-control" value="<?php echo $data[$domain]['DS'];?>"></b></div>
	</div>
</div>
<div class="l-unit animate__animated animate__fadeIn">
	<div class="l-unit__col l-unit__col--right">
		<div class="clearfix l-unit__stat-col--left text-center u-pt10"><b><?=_('Public key');?></b></div>
		<div class="clearfix l-unit__stat-col--left wide-3"><b><input type="text" class="form-control" value="<?php echo $data[$domain]['KEY'];?>"></b></div>
	</div>
</div>
<div class="l-unit animate__animated animate__fadeIn">
	<div class="l-unit__col l-unit__col--right">
		<div class="clearfix l-unit__stat-col--left text-center u-pt10"><b><?=_('Key Tag / Flag');?></b></div>
		<div class="clearfix l-unit__stat-col--left wide-3"><b><input type="text" class="form-control" value="<?php echo $flag;?>"></b></div>
	</div>
</div>
<div class="l-unit animate__animated animate__fadeIn">
	<div class="l-unit__col l-unit__col--right">
		<div class="clearfix l-unit__stat-col--left text-center u-pt10"><b><?=_('Algorithm');?></b></div>
		<div class="clearfix l-unit__stat-col--left wide-3"><b><input type="text" class="form-control" value="<?php echo $algorithm;?>"></b></div>
	</div>
</div>
