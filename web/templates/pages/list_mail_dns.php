<?php
$v_webmail_alias = "webmail";
if (!empty($_SESSION["WEBMAIL_ALIAS"])) {
	$v_webmail_alias = $_SESSION["WEBMAIL_ALIAS"];
}
?>
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/mail/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
		</div>
		<div class="toolbar-right"></div>
	</div>
</div>

<div class="container">

	<h1 class="u-text-center u-hide-desktop u-mt20 u-pr30 u-mb20 u-pl30"><?= _("DNS Records") ?></h1>

	<div class="units-table js-units-container">
		<div class="units-table-header">
			<div class="units-table-cell"><?= _("Record") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Type") ?></div>
			<div class="units-table-cell u-text-center"><?= _("Priority") ?></div>
			<div class="units-table-cell u-text-center"><?= _("TTL") ?></div>
			<div class="units-table-cell"><?= _("IP or Value") ?></div>
		</div>

		<div class="units-table-row js-unit">
			<div class="units-table-cell">
				<label class="u-hide-desktop u-text-bold"><?= _("Record") ?>:</label>
				<input type="text" class="form-control" value="mail.<?= htmlspecialchars($_GET["domain"]) ?>">
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("Type") ?>:</span>
				A
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("Priority") ?>:</span>
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("TTL") ?>:</span>
				14400
			</div>
			<div class="units-table-cell u-text-center-desktop">
				<label class="u-hide-desktop u-text-bold"><?= _("IP or Value") ?>:</label>
				<input type="text" class="form-control" value="<?= empty($ips[array_key_first($ips)]["NAT"]) ? array_key_first($ips) : $ips[array_key_first($ips)]["NAT"] ?>">
			</div>
		</div>
		<?php if ($_SESSION["WEBMAIL_SYSTEM"]) { ?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell">
					<label class="u-hide-desktop u-text-bold"><?= _("Record") ?>:</label>
					<input type="text" class="form-control" value="<?= $v_webmail_alias ?>.<?= htmlspecialchars($_GET["domain"]) ?>">
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Type") ?>:</span>
					A
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Priority") ?>:</span>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("TTL") ?>:</span>
					14400
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<label class="u-hide-desktop u-text-bold"><?= _("IP or Value") ?>:</label>
					<input type="text" class="form-control" value="<?= empty($ips[array_key_first($ips)]["NAT"]) ? array_key_first($ips) : $ips[array_key_first($ips)]["NAT"] ?>">
				</div>
			</div>
		<?php } ?>
		<div class="units-table-row js-unit">
			<div class="units-table-cell">
				<label class="u-hide-desktop u-text-bold"><?= _("Record") ?>:</label>
				<input type="text" class="form-control" value="<?= htmlspecialchars($_GET["domain"]) ?>">
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("Type") ?>:</span>
				MX
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("Priority") ?>:</span>
				10
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("TTL") ?>:</span>
				14400
			</div>
			<div class="units-table-cell u-text-center-desktop">
				<label class="u-hide-desktop u-text-bold"><?= _("IP or Value") ?>:</label>
				<input type="text" class="form-control" value="mail.<?= htmlspecialchars($_GET["domain"]) ?>.">
			</div>
		</div>
		<div class="units-table-row js-unit">
			<div class="units-table-cell">
				<label class="u-hide-desktop u-text-bold"><?= _("Record") ?>:</label>
				<input type="text" class="form-control" value="<?= htmlspecialchars($_GET["domain"]) ?>">
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("Type") ?>:</span>
				TXT
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("Priority") ?>:</span>
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("TTL") ?>:</span>
				14400
			</div>
			<div class="units-table-cell u-text-center-desktop">
				<label class="u-hide-desktop u-text-bold"><?= _("IP or Value") ?>:</label>
				<?php $ip = empty($ips[array_key_first($ips)]["NAT"]) ? array_key_first($ips) : $ips[array_key_first($ips)]["NAT"]; ?>
				<input type="text" class="form-control" value="<?= htmlspecialchars("v=spf1 a mx ip4:" . $ip . " -all") ?>">
			</div>
		</div>
		<div class="units-table-row js-unit">
			<div class="units-table-cell">
				<label class="u-hide-desktop u-text-bold"><?= _("Record") ?>:</label>
				<input type="text" class="form-control" value="_dmarc">
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("Type") ?>:</span>
				TXT
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("Priority") ?>:</span>
			</div>
			<div class="units-table-cell u-text-bold u-text-center-desktop">
				<span class="u-hide-desktop"><?= _("TTL") ?>:</span>
				14400
			</div>
			<div class="units-table-cell u-text-center-desktop">
				<label class="u-hide-desktop u-text-bold"><?= _("IP or Value") ?>:</label>
				<input type="text" class="form-control" value="<?= htmlspecialchars("v=DMARC1; p=quarantine; pct=100") ?>">
			</div>
		</div>
		<?php foreach ($dkim as $key => $value) { ?>
			<div class="units-table-row js-unit">
				<div class="units-table-cell">
					<label class="u-hide-desktop u-text-bold"><?= _("Record") ?>:</label>
					<input type="text" class="form-control" value="<?= htmlspecialchars($key) ?>">
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Type") ?>:</span>
					TXT
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("Priority") ?>:</span>
				</div>
				<div class="units-table-cell u-text-bold u-text-center-desktop">
					<span class="u-hide-desktop"><?= _("TTL") ?>:</span>
					3600
				</div>
				<div class="units-table-cell u-text-center-desktop">
					<label class="u-hide-desktop u-text-bold"><?= _("IP or Value") ?>:</label>
					<input type="text" class="form-control" value="<?= htmlspecialchars(str_replace(['"', "'"], "", $dkim[$key]["TXT"])) ?>">
				</div>
			</div>
		<?php } ?>
	</div>

</div>

<footer class="app-footer">
</footer>
