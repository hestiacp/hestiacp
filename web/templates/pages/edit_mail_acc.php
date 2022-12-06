<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/mail/?domain=<?=htmlentities(trim($v_domain, "'"))?>&token=<?=$_SESSION['token']?>">
				<i class="fas fa-arrow-left status-icon blue"></i><?=_('Back');?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<button class="button" type="submit" form="vstobjects">
				<i class="fas fa-floppy-disk status-icon purple"></i><?=_('Save');?>
			</button>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container animate__animated animate__fadeIn">

	<form
		x-data="{
			hasAutoReply: <?= $v_autoreply == 'yes' ? 'true' : 'false' ?>
		}"
		id="vstobjects"
		name="v_edit_mail_acc"
		method="post"
		class="<?=$v_status?>"
	>
		<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container form-container-wide">
			<h1 class="form-title"><?=_('Editing Mail Account');?></h1>
			<?php show_alert_message($_SESSION);?>
			<div class="sidebar-container">
				<div>
					<div class="u-mb10">
						<label for="v_email" class="form-label"><?=_('Account');?></label>
						<input type="text" class="form-control" name="v_email" id="v_email" value="<?=htmlentities($_GET['account'])."@".htmlentities($_GET['domain'])?>" disabled>
						<input type="hidden" name="v_domain" value="<?=htmlentities(trim($v_domain, "'"))?>">
						<input type="hidden" name="v_account" value="<?=htmlentities(trim($v_account, "'"))?>">
					</div>
					<div class="u-mb10">
						<label for="v_password" class="form-label">
							<?=_('Password');?>
							<a href="javascript:applyRandomString();" title="<?=_('generate');?>" class="u-ml5"><i class="fas fa-arrows-rotate status-icon green icon-large"></i></a>
						</label>
						<div class="u-pos-relative u-mb10">
							<input type="text" class="form-control js-password-input" name="v_password" id="v_password" value="<?=htmlentities(trim($v_password, "'"))?>">
							<meter max="4" class="password-meter"></meter>
						</div>
					</div>
					<p class="u-mb10"><?=_('Your password must have at least');?>:</p>
					<ul class="u-list-bulleted">
						<li><?=_('8 characters long');?></li>
						<li><?=_('1 uppercase & 1 lowercase character');?></li>
						<li><?=_('1 number');?></li>
					</ul>
					<div class="u-pt18 u-mb10">
						<label for="v_send_email" class="form-label">
							<?=_('Send login credentials to email address') ?>
						</label>
						<input type="email" class="form-control" name="v_send_email" id="v_send_email" value="<?=htmlentities(trim($v_send_email, "'"))?>">
						<input type="hidden" name="v_credentials" class="js-hidden-credentials">
					</div>
					<div class="u-mb10">
						<label for="v_quota" class="form-label">
							<?=_('Quota');?> <span class="optional">(<?=_('in megabytes');?>)</span>
						</label>
						<div class="u-pos-relative">
							<input type="text" class="form-control" name="v_quota" id="v_quota" value="<?php if (!empty($v_quota)) {echo htmlentities(trim($v_quota, "'"));} else { echo "0"; } ?>">
							<i class="unlim-trigger fas fa-infinity" title="<?=_('Unlimited');?>"></i>
						</div>
					</div>
					<div class="u-mb10">
						<label for="v_aliases" class="form-label">
							<?=_('Aliases');?> <span class="optional">(<?=_('use local-part');?>)</span>
						</label>
						<textarea class="form-control" name="v_aliases" id="v_aliases"><?=htmlentities(trim($v_aliases, "'"))?></textarea>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" name="v_blackhole" id="v_blackhole" <?php if ($v_blackhole == 'yes') echo 'checked' ?>>
						<label for="v_blackhole">
							<?=_('Discard all mail');?>
						</label>
					</div>
					<div id="id_fwd_for" style="display:<?php if ($v_blackhole == 'yes') {echo 'none';} else {echo 'block';}?> ;">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="v_fwd_only" id="v_fwd_for" <?php if ($v_fwd_only == 'yes') echo 'checked' ?>>
							<label for="v_fwd_for">
								<?=_('Do not store forwarded mail');?>
							</label>
						</div>
					</div>
					<div class="form-check u-mb10">
						<input x-model="hasAutoReply" class="form-check-input" type="checkbox" name="v_autoreply" id="v_autoreply">
						<label for="v_autoreply">
							<?=_('Autoreply');?>
						</label>
					</div>
					<div x-cloak x-show="hasAutoReply" id="autoreplytable">
						<div class="u-mb10">
							<label for="v_autoreply_message" class="form-label"><?=_('Message');?></label>
							<textarea class="form-control" name="v_autoreply_message" id="v_autoreply_message"><?=htmlentities(trim($v_autoreply_message, "'"))?></textarea>
						</div>
					</div>
					<div id="v-fwd-opt">
						<div class="u-mb10">
							<label for="v_fwd" class="form-label">
								<?=_('Forward to');?> <span class="optional">(<?=_('one or more email addresses');?>)</span>
							</label>
							<textarea class="form-control" name="v_fwd" id="v_fwd" <?php if($v_blackhole == 'yes') echo "disabled";?>><?=htmlentities(trim($v_fwd, "'"))?></textarea>
						</div>
					</div>
					<div class="u-mb20">
						<label for="v_rate" class="form-label">
							<?=_('Rate limit');?> <span class="optional">(<?=_('Email / hour');?>)</span>
						</label>
						<input type="text" class="form-control" name="v_rate" id="v_rate" value="<?=htmlentities(trim($v_rate, "'"))?>" <?php if($_SESSION['userContext'] != "admin"){ echo "disabled";}?>>
					</div>
				</div>
				<div>
					<div class="panel js-mail-info">
						<h2 class="u-text-H3 u-mb10"><?=_('Common account settings');?></h2>
						<ul class="values-list u-mb20">
							<li class="values-list-item">
								<span class="values-list-label"><?=_('Username');?></span>
								<span class="values-list-value"><span class="js-account-output"></span>@<?=htmlentities(trim($v_domain, "'"))?></span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?=_('Password');?></span>
								<span class="values-list-value"><span class="js-password-output"></span></span>
							</li>
							<?php if ($_SESSION['WEBMAIL_SYSTEM']) {?>
								<li class="values-list-item">
									<span class="values-list-label"><?=_('Webmail');?></span>
									<span class="values-list-value"><a href="http://<?=htmlentities($v_webmail_alias)?>" target="_blank">http://<?=htmlentities($v_webmail_alias)?></a></span>
								</li>
							<?php } ?>
							<li class="values-list-item">
								<span class="values-list-label"><?=_('Hostname');?></span>
								<span class="values-list-value">mail.<?=htmlentities($v_domain)?></span>
							</li>
						</ul>
						<h2 class="u-text-H3 u-mb10"><?=_('IMAP settings');?></h2>
						<ul class="values-list u-mb20">
							<li class="values-list-item">
								<span class="values-list-label"><?=_('Authentication');?></span>
								<span class="values-list-value"><?=_('Normal password');?></span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?=_('SSL/TLS');?></span>
								<span class="values-list-value"><?=_('Port');?> 993</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?=_('STARTTLS');?></span>
								<span class="values-list-value"><?=_('Port');?> 143</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?=_('No encryption');?></span>
								<span class="values-list-value"><?=_('Port');?> 143</span>
							</li>
						</ul>
						<h2 class="u-text-H3 u-mb10"><?=_('POP3 settings');?></h2>
						<ul class="values-list u-mb20">
							<li class="values-list-item">
								<span class="values-list-label"><?=_('Authentication');?></span>
								<span class="values-list-value"><?=_('Normal password');?></span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?=_('SSL/TLS');?></span>
								<span class="values-list-value"><?=_('Port');?> 995</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?=_('STARTTLS');?></span>
								<span class="values-list-value"><?=_('Port');?> 110</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?=_('No encryption');?></span>
								<span class="values-list-value"><?=_('Port');?> 110</span>
							</li>
						</ul>
						<h2 class="u-text-H3 u-mb10"><?=_('SMTP settings');?></h2>
						<ul class="values-list">
							<li class="values-list-item">
								<span class="values-list-label"><?=_('Authentication');?></span>
								<span class="values-list-value"><?=_('Normal password');?></span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?=_('SSL/TLS');?></span>
								<span class="values-list-value"><?=_('Port');?> 465</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?=_('STARTTLS');?></span>
								<span class="values-list-value"><?=_('Port');?> 587</span>
							</li>
							<li class="values-list-item">
								<span class="values-list-label"><?=_('No encryption');?></span>
								<span class="values-list-value"><?=_('Port');?> 25</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>

	</form>

</div>
