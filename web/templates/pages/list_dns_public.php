<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($read_only !== "true") { ?>
				<a href="/add/dns/" class="button button-secondary js-button-create">
					<i class="fas fa-circle-plus icon-green"></i><?= _("Add DNS Domain") ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-right">
			<div class="toolbar-sorting">
				<button class="toolbar-sorting-toggle js-toggle-sorting-menu" type="button" title="<?= _("Sort items") ?>">
					<?= _("Sort by") ?>:
					<b>
						<?php if ($_SESSION['userSortOrder'] === 'name') { $label = _('Name'); } else { $label = _('Date'); } ?>
						<?= $label ?> <i class="fas fa-arrow-down-a-z"></i>
					</b>
				</button>
				<ul class="toolbar-sorting-menu animate__animated animate__fadeIn js-sorting-menu u-hidden">
					<li data-entity="sort-date" data-sort-as-int="1">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'date') { echo 'active'; } ?>"><?= _("Date") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-expire" data-sort-as-int="1">
						<span class="name"><?= _("Expire") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-ip">
						<span class="name"><?= _("IP Address") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-name">
						<span class="name <?php if ($_SESSION['userSortOrder'] === 'name') { echo 'active'; } ?>"><?= _("Name") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
					<li data-entity="sort-records">
						<span class="name"><?= _("Records") ?> <i class="fas fa-arrow-down-a-z"></i></span><span class="up"><i class="fas fa-arrow-up-a-z"></i></span>
					</li>
				</ul>
				<?php if ($read_only !== "true") { ?>
					<form x-data x-bind="BulkEdit" action="/bulk/dns/" method="post">
						<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>" />
						<select class="form-select" name="action">
							<option value=""><?= _("Apply to selected") ?></option>
							<?php if ($_SESSION["userContext"] === "admin") { ?>
								<option value="rebuild"><?= _("Rebuild") ?></option>
							<?php } ?>
							<option value="suspend"><?= _("Suspend") ?></option>
							<option value="unsuspend"><?= _("Unsuspend") ?></option>
							<option value="delete"><?= _("Delete") ?></option>
						</select>
						<button type="submit" class="toolbar-input-submit" title="<?= _("Apply to selected") ?>">
							<i class="fas fa-arrow-right"></i>
						</button>
					</form>
				<?php } ?>
			</div>
			<div class="toolbar-search">
				<form action="/search/" method="get">
					<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>" />
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

	<div class="units js-units-container">
		<div class="l-unit animate__animated animate__fadeIn js-unit">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b><?= _("DNSKEY Record") ?></b></div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><input type="text" class="form-control" value="<?php echo $data[$domain]["RECORD"]; ?>"></b></div>
			</div>
		</div>
		<div class="l-unit animate__animated animate__fadeIn js-unit">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b><?= _("DS Record") ?></b></div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><input type="text" class="form-control" value="<?php echo $data[$domain]["DS"]; ?>"></b></div>
			</div>
		</div>
		<div class="l-unit animate__animated animate__fadeIn js-unit">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b><?= _("Public Key") ?></b></div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><input type="text" class="form-control" value="<?php echo $data[$domain]["KEY"]; ?>"></b></div>
			</div>
		</div>
		<div class="l-unit animate__animated animate__fadeIn js-unit">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b><?= _("Key Tag / Flag") ?></b></div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><input type="text" class="form-control" value="<?php echo $flag; ?>"></b></div>
			</div>
		</div>
		<div class="l-unit animate__animated animate__fadeIn js-unit">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b><?= _("Algorithm") ?></b></div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><input type="text" class="form-control" value="<?php echo $algorithm; ?>"></b></div>
			</div>
		</div>
	</div>

</div>
