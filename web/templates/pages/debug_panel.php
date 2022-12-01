<div class="debug-panel">
	<a class="debug-panel-toggle" href="javascript:elementHideShow('debug-panel-content')">
		<?= _("Toggle Debug Panel") ?>
	</a>
	<div id="debug-panel-content" class="debug-panel-content animate__animated animate__fadeIn" style="display:none;">
		<?php
			echo "<h3 class=\"u-mb10\">Server Variables</h3>";
			foreach ($_SERVER as $key => $val) {
				echo "<b>" . $key . "= </b> " . $val . " ";
			}
  	?>
		<?php
			echo "<h3 class=\"u-mb10 u-mt10\">Session Variables</h3>";
			foreach ($_SESSION as $key => $val) {
				echo "<b>" . $key . "= </b> " . $val . " ";
			}
  	?>
		<?php
			echo "<h3 class=\"u-mb10 u-mt10\">POST Variables</h3>";
			foreach ($_POST as $key => $val) {
				echo "<b>" . $key . "= </b> " . $val . " ";
			}
  	?>
		<?php
			echo "<h3 class=\"u-mb10 u-mt10\">GET Variables</h3>";
			foreach ($_GET as $key => $val) {
				echo "<b>" . $key . "= </b> " . $val . " ";
			}
  	?>
	</div>
</div>
