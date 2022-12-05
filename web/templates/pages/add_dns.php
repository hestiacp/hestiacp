<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/dns/">
				<i class="fas fa-arrow-left status-icon blue"></i><?=_('Back');?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<a href="#" class="button" data-action="submit" data-id="vstobjects">
				<i class="fas fa-floppy-disk status-icon purple"></i><?=_('Save');?>
			</a>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container animate__animated animate__fadeIn">

	<form id="vstobjects" name="v_add_dns" method="post" x-data="{ showAdvanced: <?= empty($v_adv) ? 'false' : 'true' ?> }">
		<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="form-title"><?=_('Adding DNS Domain');?></h1>
			<?php show_alert_message($_SESSION);?>
			<?php if (($user_plain == 'admin') && (($_GET['accept'] !== "true"))) {?>
				<div class="alert alert-danger alert-with-icon" role="alert">
					<i class="fas fa-exclamation"></i>
					<p><?=_('Avoid adding web domains on admin account');?></p>
				</div>
			<?php } ?>
			<?php if (($user_plain == 'admin') && (empty($_GET['accept']))) {?>
				<div class="u-side-by-side u-pt18">
					<a href="/add/user/" class="button u-width-full u-mr10"><?=_('Add User');?></a>
					<a href="/add/dns/?accept=true" class="button button-danger u-width-full u-ml10"><?=_('Continue');?></a>
				</div>
			<?php } ?>
			<?php if (($user_plain == 'admin') && (($_GET['accept'] === "true")) || ($user_plain !== "admin")) {?>
				<div class="u-mb10">
					<label for="v_domain" class="form-label"><?=_('Domain');?></label>
					<input type="text" class="form-control" name="v_domain" id="v_domain" value="<?=htmlentities(trim($v_domain, "'"))?>">
				</div>
				<div class="u-mb10">
					<label for="v_ip" class="form-label"><?=_('IP address');?></label>
					<div class="u-pos-relative">
						<select class="form-select" tabindex="-1" onchange="this.nextElementSibling.value=this.value">
							<option value="">clear</option>
							<?php
								foreach ($v_ips as $ip => $value) {
									$display_ip = empty($value['NAT']) ? $ip : "{$value['NAT']}";
									echo "<option value='{$display_ip}'>" . htmlentities($display_ip) . "</option>\n";
								}
							?>
						</select>
						<input type="text" class="form-control list-editor" name="v_ip" id="v_ip" value="<?=htmlentities(trim($v_ip, "'"))?>">
					</div>
				</div>
				<?php if (($_SESSION['userContext'] === 'admin') || ($_SESSION['userContext'] === 'user') && ($_SESSION['POLICY_USER_EDIT_DNS_TEMPLATES'] === 'yes')) { ?>
					<div class="u-mb10">
						<label for="v_template" class="form-label">
							<?=_('Template') . "<span class='optional'>" . strtoupper($_SESSION['DNS_SYSTEM']) . "</span>";?>
						</label>
						<select class="form-select" name="v_template" id="v_template">
							<?php
								foreach ($templates as $key => $value) {
									echo "\t\t\t\t<option value=\"".htmlentities($value)."\"";
									$svalue = "'".$value."'";
									if ((!empty($v_template)) && ( $value == $v_template ) || ($svalue == $v_template)) {
										echo ' selected' ;
									}
									echo ">".htmlentities($value)."</option>\n";
								}
							?>
						</select>
					</div>
				<?php } ?>
				<div class="u-mb20 u-mt20">
					<a x-on:click="showAdvanced = !showAdvanced" class="button button-secondary"><?=_('Advanced options');?></a>
				</div>
				<div id="advtable" x-show="showAdvanced">
					<div class="form-check u-mb10">
						<input class="form-check-input" type="checkbox" name="v_dnssec" id="v_dnssec" value="yes" <?php if($v_dnssec === 'yes'){ echo ' checked'; } ?>>
						<label for="v_dnssec">
							<?=_('Enable DNSSEC');?>
						</label>
					</div>
					<div class="u-mb10">
						<label for="v_exp" class="form-label">
							<?=_('Expiration Date');?> <span class="optional">(<?=_('YYYY-MM-DD');?>)</span>
						</label>
						<input type="text" class="form-control" name="v_exp" id="v_exp" value="<?=htmlentities(trim($v_exp, "'"))?>">
					</div>
					<div class="u-mb10">
						<label for="v_ttl" class="form-label"><?=_('TTL');?></label>
						<input type="text" class="form-control" name="v_ttl" id="v_ttl" value="<?=htmlentities(trim($v_ttl, "'"))?>">
					</div>
					<p class="form-label u-mb10"><?=_('Name servers');?></p>
					<div class="u-mb5">
						<input type="text" class="form-control" name="v_ns1" value="<?=htmlentities(trim($v_ns1, "'"))?>">
					</div>
					<div class="u-mb5">
						<input type="text" class="form-control" name="v_ns2" value="<?=htmlentities(trim($v_ns2, "'"))?>">
					</div>
					<?php
						if($v_ns3) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns3" value="'.htmlentities(trim($v_ns3, "'")).'">
								<span class="js-remove-ns additional-control delete u-ml10">'._('delete').'</span>
							</div>';
						}
						if($v_ns4) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns4" value="'.htmlentities(trim($v_ns4, "'")).'">
								<span class="js-remove-ns additional-control delete u-ml10">'._('delete').'</span>
							</div>';
						}
						if($v_ns5) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns5" value="'.htmlentities(trim($v_ns5, "'")).'">
								<span class="js-remove-ns additional-control delete u-ml10">'._('delete').'</span>
							</div>';
						}
						if($v_ns6) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns6" value="'.htmlentities(trim($v_ns6, "'")).'">
								<span class="js-remove-ns additional-control delete u-ml10">'._('delete').'</span>
							</div>';
						}
						if($v_ns7) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns7" value="'.htmlentities(trim($v_ns7, "'")).'">
								<span class="js-remove-ns additional-control delete u-ml10">'._('delete').'</span>
							</div>';
						}
						if($v_ns8) {
							echo '<div class="u-side-by-side u-mb5">
								<input type="text" class="form-control" name="v_ns8" value="'.htmlentities(trim($v_ns8, "'")).'">
								<span class="js-remove-ns additional-control delete u-ml10">'._('delete').'</span>
							</div>';
						}
					?>
					<div class="u-pt18 js-add-ns" <?php if ($v_ns8) echo 'style="display:none;"'; ?>>
						<span class="js-add-ns-button additional-control add"><?=_('Add one more Name Server');?></span>
					</div>
				</div>
			<?php } ?>
		</div>

	</form>

</div>
