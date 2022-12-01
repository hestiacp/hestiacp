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

	<form id="vstobjects" name="v_edit_dns" method="post" class="<?=$v_status?>">
		<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="form-title"><?=_('Editing DNS Domain');?></h1>
			<?php show_alert_message($_SESSION);?>
			<div class="u-mb10">
				<label for="v_domain" class="form-label"><?=_('Domain');?></label>
				<input type="text" class="form-control" name="v_domain" id="v_domain" value="<?=htmlentities(trim($v_domain, "'"))?>" disabled>
				<input type="hidden" name="v_domain" value="<?=htmlentities(trim($v_domain, "'"))?>">
			</div>
			<div class="u-mb10">
				<label for="v_ip" class="form-label"><?=_('IP address');?></label>
				<div class="u-pos-relative">
					<select class="form-select" tabindex="-1" onchange="this.nextElementSibling.value=this.value">
						<option value="">clear</option>
						<?php
							foreach ($v_ips as $ip => $value) {
								$display_ip = empty($value['NAT']) ? $ip : "{$value['NAT']}";
								$ip_selected = ((!empty($v_ip) && ($v_ip==$ip||$v_ip==$display_ip) ))? 'selected' : '';
								echo "<option value='{$display_ip}' {$ip_selected}>" . htmlentities($display_ip) . "</option>\n";
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
								if ((!empty($v_template)) && ( $value == $v_template ) || ($svalue == $v_template)){
									echo ' selected' ;
								}
								echo ">".htmlentities($value)."</option>\n";
							}
						?>
					</select>
				</div>
			<?php } ?>
			<div class="form-check u-mb10">
				<input class="form-check-input" type="checkbox" name="v_dnssec" id="v_dnssec" value="yes" <?php if($v_dnssec === 'yes'){ echo ' checked'; } ?>>
				<label for="v_dnssec">
					<?=_('Enable DNSSEC');?>
				</label>
			</div>
			<div class="u-mb10">
				<label for="v_exp" class="form-label">
					<?=_('Expiration Date');?><span class="optional">(<?=_('YYYY-MM-DD');?>)</span>
				</label>
				<input type="text" class="form-control" name="v_exp" id="v_exp" value="<?=htmlentities(trim($v_exp, "'"))?>">
			</div>
			<div class="u-mb10">
				<label for="v_soa" class="form-label"><?=_('SOA');?></label>
				<input type="text" class="form-control" name="v_soa" id="v_soa" value="<?=htmlentities(trim($v_soa, "'"))?>">
			</div>
			<div class="u-mb10">
				<label for="v_ttl" class="form-label"><?=_('TTL');?></label>
				<input type="text" class="form-control" name="v_ttl" id="v_ttl" value="<?=htmlentities(trim($v_ttl, "'"))?>">
			</div>
		</div>

	</form>

</div>
