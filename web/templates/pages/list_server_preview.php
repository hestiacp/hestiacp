<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a href="/edit/server/" class="button button-secondary" id="btn-back"><i class="fas fa-arrow-left status-icon blue"></i><?= _("Back") ?></a>
		</div>
		<div class="toolbar-buttons">
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container units">

	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact text-center">&nbsp;</div>
			<div class="clearfix l-unit__stat-col--left"><b><?= _("Category") ?></b></div>
			<div class="clearfix l-unit__stat-col--left wide-6"><b><?= _("Name") ?></b></div>
			<div class="clearfix l-unit__stat-col--left wide-2"><b><?= _("Status") ?></b></div>

		</div>
	</div>
	<!-- Start of item element-->
	<div class="l-unit header animate__animated animate__fadeIn">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left super-compact text-center">
				<i class="fas fa-gear status-icon blue"></i>
			</div>
			<div class="clearfix l-unit__stat-col--left"><b><?= _("System") ?></b></div>
			<div class="clearfix l-unit__stat-col--left wide-6"><b><?= _("Policy") ?>: <?= _("Allow suspended users to log in with read-only access") ?></b></div>
			<div class="clearfix l-unit__stat-col--left wide-2">Partially implemented.</div>
		</div>
	</div>
	<!-- End of item element-->
</div>

<footer class="app-footer">
	<div class="container">
		<div class="l-unit-ft">
			<div class="l-unit__col l-unit__col--right"></div>
		</div>
	</div>
</footer>
