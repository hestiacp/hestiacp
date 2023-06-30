<?php
if( !defined("HESTIA_DIR_BIN") ){
	die("Direct access disabled");
}
?>

<div x-data="{ open: false }" class="debug-panel">
	<button
		type="button"
		class="debug-panel-toggle"
		x-on:click="open = !open"
		x-text="open ? '<?= _("Close debug panel") ?>' : '<?= _("Open debug panel") ?>'">
		<?= _("Open debug panel") ?>
	</button>
	<div x-cloak x-show="open" class="debug-panel-content animate__animated animate__fadeIn">
		<?php
			echo "<h3 class=\"u-mb10\">Server Variables</h3>";
			foreach ($_SERVER as $key => $val) {
				echo "<span class=\"u-text-bold\">" . htmlentities($key) . "= </span> " . htmlentities($val) . " ";
			}
		?>
		<?php
			echo "<h3 class=\"u-mb10 u-mt10\">Session Variables</h3>";
			foreach ($_SESSION as $key => $val) {
				echo "<span class=\"u-text-bold\">" . htmlentities($key) . "= </span> " . htmlentities($val) . " ";
			}
		?>
		<?php
			echo "<h3 class=\"u-mb10 u-mt10\">POST Variables</h3>";
			foreach ($_POST as $key => $val) {
				echo "<span class=\"u-text-bold\">" . htmlentities($key) . "= </span> " . htmlentities($val) . " ";
			}
		?>
		<?php
			echo "<h3 class=\"u-mb10 u-mt10\">GET Variables</h3>";
			foreach ($_GET as $key => $val) {
				echo "<span class=\"u-text-bold\">" . htmlentities($key) . "= </span> " . htmlentities($val) . " ";
			}
		?>
	</div>
</div>
