<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/server/"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<a href="/list/server/?cpu" class="button button-secondary"><i class="fas fa-chart-pie icon-green"></i><?= _("show: CPU / MEM / NET / DISK") ?></a>
		</div>
		<div class="toolbar-right">
			<a class="toolbar-link<?php if ((empty($period)) || ($period == 'daily')) echo " selected" ?>" href="?period=daily"><?= _("Daily") ?></a>
			<a class="toolbar-link<?php if ((!empty($period)) && ($period == 'weekly')) echo " selected" ?>" href="?period=weekly"><?= _("Weekly") ?></a>
			<a class="toolbar-link<?php if ((!empty($period)) && ($period == 'monthly')) echo " selected" ?>" href="?period=monthly"><?= _("Monthly") ?></a>
			<a class="toolbar-link<?php if ((!empty($period)) && ($period == 'yearly')) echo " selected" ?>" href="?period=yearly"><?= _("Yearly") ?></a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<script defer src="/js/vendor/chart.min.js?<?= JS_LATEST_UPDATE ?>"></script>

<div class="container animate__animated animate__fadeIn">
	<div class="form-container form-container-narrow">
		<!-- Begin graph list item loop -->
		<?php foreach ($data as $key => $value) { ?>
			<div class="u-mb40">
				<h2 class="u-mb20"><?= htmlspecialchars($data[$key]["TITLE"]) ?></h2>
				<canvas
					class="js-rrd-chart"
					data-service="<?= $data[$key]["TYPE"] !== 'net' ? htmlspecialchars($data[$key]["RRD"]) : 'net_' . htmlspecialchars($data[$key]["RRD"]); ?>"
					data-period="<?= htmlspecialchars($period) ?>"
				></canvas>
			</div>
		<?php } ?>
	</div>
</div>

<footer class="app-footer">
</footer>
