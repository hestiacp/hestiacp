<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<?php if ($_SESSION["userContext"] === "admin" && isset($_GET["user"]) && $_GET["user"] !== "admin") { ?>
				<a class="button button-secondary button-back js-button-back" href="/list/key/?user=<?= htmlentities($_GET["user"]) ?>">
					<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
				</a>
			<?php } else { ?>
				<a class="button button-secondary button-back js-button-back" href="/list/key/">
					<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
				</a>
			<?php } ?>
		</div>
		<div class="toolbar-buttons">
			<button type="submit" class="button" form="main-form">
				<i class="fas fa-floppy-disk icon-purple"></i><?= _("Save") ?>
			</button>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">

	<form id="main-form" name="v_add_key" method="post">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Add SSH Key") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div>
				<label for="v_key" class="form-label"><?= _("SSH Key") ?></label>
				<textarea class="form-control u-min-height300" name="v_key" id="v_key" required><?= htmlentities(trim($v_key, "'")) ?></textarea>
			</div>
		</div>

	</form>

</div>
