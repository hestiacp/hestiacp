<?php
$v_webmail_alias = "webmail";
if (!empty($_SESSION["WEBMAIL_ALIAS"])) {
	$v_webmail_alias = $_SESSION["WEBMAIL_ALIAS"];
}
?>
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/mail/"><i class="fas fa-arrow-left status-icon blue"></i><?= _("Back") ?></a>
		</div>
		<div class="toolbar-right">
		</div>
	</div>
</div>

<div class="container units">
	<div class="header table-header">
		<div class="l-unit__col l-unit__col--right">
			<div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("Record") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Type") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("Priority") ?></b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center"><b><?= _("TTL") ?></b></div>
				<div class="clearfix l-unit__stat-col--left wide-3"><b><?= _("IP or Value") ?></b></div>
			</div>
		</div>
	</div>

	<div class="l-unit animate__animated animate__fadeIn">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left wide-3">
				<input type="text" class="form-control" style="width:260px;" value="mail.<?= htmlspecialchars($_GET["domain"]) ?>">
			</div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>A</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>14400</b></div>
			<div class="clearfix l-unit__stat-col--left wide-3">
				<input type="text" class="form-control" style="width:400px;" value="<?= empty($ips[array_key_first($ips)]["NAT"]) ? array_key_first($ips) : $ips[array_key_first($ips)]["NAT"] ?>">
			</div>
		</div>
	</div>
	<?php if ($_SESSION["WEBMAIL_SYSTEM"]) { ?>
		<div class="l-unit animate__animated animate__fadeIn">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left wide-3">
					<input type="text" class="form-control" style="width:260px;" value="<?= $v_webmail_alias ?>.<?= htmlspecialchars($_GET["domain"]) ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>A</b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>&nbsp;</b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>14400</b></div>
				<div class="clearfix l-unit__stat-col--left wide-3">
					<input type="text" class="form-control" style="width:400px;" value="<?= empty($ips[array_key_first($ips)]["NAT"]) ? array_key_first($ips) : $ips[array_key_first($ips)]["NAT"] ?>">
				</div>
			</div>
		</div>
	<?php } ?>
	<div class="l-unit animate__animated animate__fadeIn">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left wide-3">
				<input type="text" class="form-control" style="width:260px;" value="<?= htmlspecialchars($_GET["domain"]) ?>">
			</div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>MX</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>10</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>14400</b></div>
			<div class="clearfix l-unit__stat-col--left wide-3">
				<input type="text" class="form-control" style="width:400px;" value="mail.<?= htmlspecialchars($_GET["domain"]) ?>.">
			</div>
		</div>
	</div>
	<div class="l-unit animate__animated animate__fadeIn">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left wide-3">
				<input type="text" class="form-control" style="width:260px;" value="<?= htmlspecialchars($_GET["domain"]) ?>">
			</div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>TXT</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>14400</b></div>
			<?php $ip = empty($ips[array_key_first($ips)]["NAT"]) ? array_key_first($ips) : $ips[array_key_first($ips)]["NAT"]; ?>
			<div class="clearfix l-unit__stat-col--left wide-3">
				<input type="text" class="form-control" style="width:400px;" value="<?= htmlspecialchars("v=spf1 a mx ip4:" . $ip . " -all") ?>">
			</div>
		</div>
	</div>
	<div class="l-unit animate__animated animate__fadeIn">
		<div class="l-unit__col l-unit__col--right">
			<div class="clearfix l-unit__stat-col--left wide-3">
				<input type="text" class="form-control" style="width:260px;" value="_dmarc">
			</div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>TXT</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>&nbsp;</b></div>
			<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>14400</b></div>
			<div class="clearfix l-unit__stat-col--left wide-3">
				<input type="text" class="form-control" style="width:400px;" value="<?= htmlspecialchars("v=DMARC1; p=quarantine; pct=100") ?>">
			</div>
		</div>
	</div>
	<?php foreach ($dkim as $key => $value) { ?>
		<div class="l-unit animate__animated animate__fadeIn">
			<div class="l-unit__col l-unit__col--right">
				<div class="clearfix l-unit__stat-col--left wide-3">
					<input type="text" class="form-control" style="width:260px;" value="<?= htmlspecialchars($key) ?>">
				</div>
				<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>TXT</b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>&nbsp;</b></div>
				<div class="clearfix l-unit__stat-col--left u-text-center u-pt10"><b>3600</b></div>
				<div class="clearfix l-unit__stat-col--left wide-3">
					<input type="text" class="form-control" style="width:400px;" value="<?= htmlspecialchars(str_replace(['"', "'"], "", $dkim[$key]["TXT"])) ?>">
				</div>
			</div>
		</div>
	<?php } ?>
</div>

<footer class="app-footer">
	<div class="container">
		<div class="l-unit-ft">
			<div class="l-unit__col l-unit__col--right">
			</div>
			<div class="l-unit__col l-unit__col--right back clearfix">
			</div>
		</div>
	</div>
</footer>
