<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/web/">
				<i class="fas fa-arrow-left status-icon blue"></i><?= _("Back") ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<?php if (($user_plain == "admin" && $_GET["accept"] === "true") || $user_plain !== "admin") { ?>
				<button class="button" type="submit" form="vstobjects">
					<i class="fas fa-floppy-disk status-icon purple"></i><?= _("Save") ?>
				</button>
			<?php } ?>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container animate__animated animate__fadeIn">

	<form id="vstobjects" name="v_add_web" method="post">
		<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="form-title"><?= _("Add Web Domain") ?></h1>
			<?php show_alert_message($_SESSION);?>
			<?php if (($user_plain == 'admin') && (($_GET['accept'] !== "true"))) {?>
				<div class="alert alert-danger alert-with-icon" role="alert">
					<i class="fas fa-exclamation"></i>
					<p><?= _("Avoid adding web domains on admin account") ?></p>
				</div>
			<?php } ?>
			<?php if (($user_plain == 'admin') && (empty($_GET['accept']))) {?>
				<div class="u-side-by-side u-pt18">
					<a href="/add/user/" class="button u-width-full u-mr10"><?= _("Add User") ?></a>
					<a href="/add/web/?accept=true" class="button button-danger u-width-full u-ml10"><?= _("Continue") ?></a>
				</div>
			<?php } ?>
			<?php if (($user_plain == 'admin') && (($_GET['accept'] === "true")) || ($user_plain !== "admin")) {?>
				<div class="u-mb10">
					<label for="v_domain" class="form-label"><?= _("Domain") ?></label>
					<input type="text" class="form-control" name="v_domain" id="v_domain" value="<?=htmlentities(trim($v_domain, "'"))?>">
				</div>
				<div class="u-mb20">
					<label for="v_ip" class="form-label"><?= _("IP Address") ?></label>
					<select class="form-select" name="v_ip" id="v_ip">
						<?php
							foreach ($ips as $ip => $value) {
								$display_ip = htmlentities(empty($value['NAT']) ? $ip : "{$value['NAT']}");
								$ip_selected = (!empty($v_ip) && $ip == $_POST['v_ip']) ? 'selected' : '';
								echo "\t\t\t\t<option value=\"{$ip}\" {$ip_selected}>{$display_ip}</option>\n";
							}
						?>
					</select>
				</div>
				<?php if ((isset($_SESSION['DNS_SYSTEM'])) && (!empty($_SESSION['DNS_SYSTEM']))) {?>
					<?php if($panel[$user_plain]['DNS_DOMAINS'] != "0") { ?>
						<div class="form-check u-mb10">
							<input class="form-check-input" type="checkbox" name="v_dns" id="v_dns" <?php if (empty($v_dns)&&$panel[$user_plain]['DNS_DOMAINS'] != "0") ?>>
							<label for="v_dns">
								<?= _("DNS Support") ?>
							</label>
						</div>
					<?php } ?>
				<?php } ?>
				<?php if ((isset($_SESSION['IMAP_SYSTEM'])) && (!empty($_SESSION['IMAP_SYSTEM']))) {?>
					<?php if($panel[$user_plain]['MAIL_DOMAINS'] != "0") { ?>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="v_mail" id="v_mail" <?php if (empty($v_mail)&&$panel[$user_plain]['MAIL_DOMAINS'] != "0") ?>>
							<label for="v_mail">
								<?= _("Mail Support") ?>
							</label>
						</div>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</div>

	</form>

</div>
