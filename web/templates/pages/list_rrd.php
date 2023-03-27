<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/server/"><i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?></a>
			<a href="/list/server/?cpu" class="button button-secondary"><i class="fas fa-chart-pie icon-green"></i><?= _("show: CPU / MEM / NET / DISK") ?></a>
		</div>
		<div class="toolbar-right">
			<a class="toolbar-link<?php if ((empty($period)) || ($period == 'day')) echo " selected" ?>" href="?period=daily"><?= _("Daily") ?></a>
			<a class="toolbar-link<?php if ((!empty($period)) && ($period == 'week')) echo " selected" ?>" href="?period=weekly"><?= _("Weekly") ?></a>
			<a class="toolbar-link<?php if ((!empty($period)) && ($period == 'month')) echo " selected" ?>" href="?period=monthly"><?= _("Monthly") ?></a>
			<a class="toolbar-link<?php if ((!empty($period)) && ($period == 'year')) echo " selected" ?>" href="?period=yearly"><?= _("Yearly") ?></a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container animate__animated animate__fadeIn">
	<div class="form-container form-container-narrow">
		<!-- Begin graph list item loop -->
		<?php foreach ($data as $key => $value) { ?>
			<div class="u-mb40">
				<h2 class="u-mb20">
					<?= _($data[$key]["TITLE"]) ?>
				</h2>
				<a href="/list/rrd/image.php?/rrd/<?=$data[$key]['TYPE']."/".$period."-".$data[$key]['RRD'].".png"?>" class="u-block" target="_blank">
					<img class="u-image-fluid u-rounded" src="/list/rrd/image.php?/rrd/<?=$data[$key]['TYPE']."/".$period."-".$data[$key]['RRD'].".png"?>" alt="">
				</a>
			</div>
		<?php } ?>
	</div>
</div>

<footer class="app-footer">
</footer>
