<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/server/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
			<a href="/list/server/?cpu" class="button button-secondary">
				<i class="fas fa-chart-pie icon-green"></i><?= _("Advanced Details") ?>
			</a>
		</div>
		<div class="toolbar-right">
			<a class="toolbar-link<?php if ((empty($period)) || ($period == 'daily')) echo " selected" ?>" href="?period=daily"><?= _("Daily") ?></a>
			<a class="toolbar-link<?php if ((!empty($period)) && ($period == 'weekly')) echo " selected" ?>" href="?period=weekly"><?= _("Weekly") ?></a>
			<a class="toolbar-link<?php if ((!empty($period)) && ($period == 'monthly')) echo " selected" ?>" href="?period=monthly"><?= _("Monthly") ?></a>
			<a class="toolbar-link<?php if ((!empty($period)) && ($period == 'yearly')) echo " selected" ?>" href="?period=yearly"><?= _("Yearly") ?></a>
                        <a class="toolbar-link<?php if ((!empty($period)) && ($period == 'biennially')) echo " selected" ?>" href="?period=biennially"><?= _("Biennially") ?></a>
                        <a class="toolbar-link<?php if ((!empty($period)) && ($period == 'triennially')) echo " selected" ?>" href="?period=triennially"><?= _("Triennially") ?></a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">
	<div class="form-container form-container-wide">
		<!-- Begin graph list item loop -->
		<?php foreach ($data as $key => $value) { ?>
			<div class="u-mb40">
				<h2 class="u-mb20"><?= htmlspecialchars($data[$key]["TITLE"]) ?></h2>
				<canvas
					class="u-max-height300 js-rrd-chart"
					data-service="<?= $data[$key]["TYPE"] !== "net" ? htmlspecialchars($data[$key]["RRD"]) : "net_" . htmlspecialchars($data[$key]["RRD"]) ?>"
					data-period="<?= htmlspecialchars($period) ?>"
				></canvas>
			</div>
		<?php } ?>
	</div>
</div>

<footer class="app-footer">
</footer>
