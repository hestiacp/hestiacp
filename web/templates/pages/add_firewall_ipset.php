<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/firewall/ipset/">
				<i class="fas fa-arrow-left icon-blue"></i><?= _("Back") ?>
			</a>
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

	<form id="main-form" name="v_add_ipset" method="post">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="ok" value="Add">

		<?php
		$country = [
			"br" => "Brazil",
			"ca" => "Canada",
			"cn" => "China",
			"fr" => "French",
			"de" => "Germany",
			"in" => "India",
			"id" => "Indonesia",
			"nl" => "Netherlands",
			"ro" => "Romania",
			"ru" => "Russia",
			"es" => "Spain",
			"ch" => "Switzerland",
			"tr" => "Turkey",
			"ua" => "Ukraine",
			"gb" => "United Kingdom",
			"us" => "United States",
		];

		function generate_iplist($country, $type) {
			$iplist = [];
			$lowercaseType = strtolower($type);
			foreach ($country as $iso => $name) {
				$iplist[] = [
					"name" => "[$type] " . _("Country") . " - $name",
					"source" => "https://raw.githubusercontent.com/ipverse/rir-ip/master/country/$iso/$lowercaseType-aggregated.txt",
				];
			}
			return $iplist;
		}

		$country_iplists = generate_iplist($country, "IPv4");
		// Uncomment below for IPv6
		// $country_ipv6lists = generate_iplist($country, 'IPv6');

		$blacklist_iplists = [
			["name" => "[IPv4] " . _("Block Malicious IPs"), "source" => "script:/usr/local/hestia/install/common/firewall/ipset/blacklist.sh"],
			// Uncomment below for IPv6
			// array('name' => "[IPv6] " . _("Block Malicious IPs"), 'source' => "script:/usr/local/hestia/install/common/firewall/ipset/blacklist.ipv6.sh"),
		];
		?>

		<div class="form-container">
			<h1 class="u-mb20"><?= _("Add IPset IP List for Firewall") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_ipname" class="form-label"><?= _("IP List Name") ?></label>
				<input type="text" class="form-control" name="v_ipname" id="v_ipname" maxlength="255" value="<?= htmlentities(trim($v_ipname, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_datasource" class="form-label">
					<?= _("Data Source") ?> <span class="optional">(<?= _("url, script or file") ?>)</span>
				</label>
				<div class="u-pos-relative">
					<select
						class="form-select js-datasource-select"
						tabindex="-1"
						onchange="this.nextElementSibling.value=this.value"
						data-country-iplists="<?= htmlspecialchars(json_encode($country_iplists), ENT_QUOTES, "UTF-8") ?>"
						data-blacklist-iplists="<?= htmlspecialchars(json_encode($blacklist_iplists), ENT_QUOTES, "UTF-8") ?>"
					>
						<option value=""><?= _("Clear") ?></option>
					</select>
					<input type="text" class="form-control list-editor" name="v_datasource" id="v_datasource" maxlength="255" value="<?= htmlentities(trim($v_datasource, "'")) ?>">
				</div>
			</div>
			<div class="u-mb10">
				<label for="v_ipver" class="form-label"><?= _("IP Version") ?></label>
				<select class="form-select" name="v_ipver" id="v_ipver">
					<option value="v4" <?php if ((!empty($v_ipver)) && ( $v_ipver == "'v4'" )) echo 'selected'?>>IPv4</option>
					<option value="v6" <?php if ((!empty($v_ipver)) && ( $v_ipver == "'v6'" )) echo 'selected'?>>IPv6</option>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_autoupdate" class="form-label"><?= _("Auto Update") ?></label>
				<select class="form-select" name="v_autoupdate" id="v_autoupdate">
					<option value="yes" <?php if ((!empty($v_autoupdate)) && ( $v_autoupdate == "'yes'" )) echo 'selected'?>><?= _("Yes") ?></option>
					<option value="no" <?php if ((!empty($v_autoupdate)) && ( $v_autoupdate == "'no'" )) echo 'selected'?>><?= _("No") ?></option>
				</select>
			</div>
		</div>

	</form>

</div>
