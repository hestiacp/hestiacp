<div x-data="{ open: false }" class="debug-panel">
	<button x-on:click="open = !open" type="button" class="debug-panel-toggle">
		<?= _("Toggle Debug Panel") ?>
	</button>
	<div x-cloak x-show="open" class="debug-panel-content animate__animated animate__fadeIn">
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
