<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/web/">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<a href="/delete/web/cache/?<?= tohtml(http_build_query(["domain" => $v_domain, "token" => $_SESSION['token']])) ?>" class="button button-secondary js-clear-cache-button <?php if (!($v_nginx_cache == 'yes' || (($v_proxy_template == 'caching' || is_int(strpos($v_proxy_template, 'caching-'))) && $_SESSION['PROXY_SYSTEM'] == 'nginx'))) { echo "u-hidden"; } ?>">
				<i class="fas fa-trash icon-red"></i><?= tohtml( _("Purge NGINX Cache")) ?>
			</a>
			<?php if ($_SESSION["PLUGIN_APP_INSTALLER"] !== "false") { ?>
				<a href="/add/webapp/?<?= tohtml(http_build_query(["domain" => $v_domain])) ?>" class="button button-secondary">
					<i class="fas fa-magic icon-blue"></i><?= tohtml( _("Quick Install App")) ?>
				</a>
			<?php } ?>
			<a href="/add/node/?<?= tohtml(http_build_query(["domain" => $v_domain, "user" => !empty($_GET["user"]) ? $_GET["user"] : (isset($v_username) ? $v_username : $_SESSION["user"])])) ?>" class="button button-secondary">
				<svg style="width: 14px; height: 14px; vertical-align: -2px; margin-right: 6px; display: inline-block;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128"><path fill="#83CD29" d="M112.771 30.334L68.674 4.729c-2.781-1.584-6.402-1.584-9.205 0L14.901 30.334C12.031 31.985 10 35.088 10 38.407v51.142c0 3.319 2.084 6.423 4.954 8.083l11.775 6.688c5.628 2.772 7.617 2.772 10.178 2.772 8.333 0 13.093-5.039 13.093-13.828v-50.49c0-.713-.371-1.774-1.071-1.774h-5.623C42.594 41 41 42.061 41 42.773v50.49c0 3.896-3.524 7.773-10.11 4.48L18.723 90.73c-.424-.23-.723-.693-.723-1.181V38.407c0-.482.555-.966.982-1.213l44.424-25.561c.415-.235 1.025-.235 1.439 0l43.882 25.555c.42.253.272.722.272 1.219v51.142c0 .488.183.963-.232 1.198l-44.086 25.576c-.378.227-.847.227-1.261 0l-11.307-6.749c-.341-.198-.746-.269-1.073-.086-3.146 1.783-3.726 2.02-6.677 3.043-.726.253-1.797.692.41 1.929l14.798 8.754a9.294 9.294 0 004.647 1.246c1.642 0 3.25-.426 4.667-1.246l43.885-25.582c2.87-1.672 4.23-4.764 4.23-8.083V38.407c0-3.319-1.36-6.414-4.229-8.073zM77.91 81.445c-11.726 0-14.309-3.235-15.17-9.066-.1-.628-.633-1.379-1.272-1.379h-5.731c-.709 0-1.279.86-1.279 1.566 0 7.466 4.059 16.512 23.453 16.512 14.039 0 22.088-5.455 22.088-15.109 0-9.572-6.467-12.084-20.082-13.886-13.762-1.819-15.16-2.738-15.16-5.962 0-2.658 1.184-6.203 11.374-6.203 9.105 0 12.461 1.954 13.842 8.091.118.577.645.991 1.24.991h5.754c.354 0 .692-.143.94-.396.24-.272.367-.613.335-.979-.891-10.568-7.912-15.493-22.112-15.493-12.631 0-20.166 5.334-20.166 14.275 0 9.698 7.497 12.378 19.622 13.577 14.505 1.422 15.633 3.542 15.633 6.395 0 4.955-3.978 7.066-13.309 7.066z"/></svg><?= tohtml( _("Node.js App")) ?>
			</a>
			<button type="submit" class="button" form="main-form">
				<i class="fas fa-floppy-disk icon-purple"></i><?= tohtml( _("Save")) ?>
			</button>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container">
	<?php
		$web_x_data = [
			"statsAuthEnabled" => !empty($v_stats_user),
			"redirectEnabled" => !empty($v_redirect),
			"sslEnabled" => $v_ssl == "yes",
			"letsEncryptEnabled" => $v_letsencrypt == "yes" || $v_letsencrypt == "on",
			"showCertificates" => !($v_letsencrypt == "yes" || $v_letsencrypt == "on"),
			"showAdvanced" => false,
			"nginxCacheEnabled" => $v_nginx_cache == "yes",
			"proxySupportEnabled" => !empty($v_proxy),
			"customDocumentRootEnabled" => !empty($v_custom_doc_root),
		];
	?>

	<form
		x-data="<?= tohtml(json_encode($web_x_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_THROW_ON_ERROR)) ?>"
		id="main-form"
		name="v_edit_web"
		method="post"
		class="<?= tohtml($v_status) ?> js-enable-inputs-on-submit"
	>
		<input type="hidden" name="token" value="<?= tohtml($_SESSION["token"]) ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="u-mb20"><?= tohtml( _("Edit Web Domain")) ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb10">
				<label for="v_domain" class="form-label"><?= tohtml( _("Domain")) ?></label>
				<input type="text" class="form-control" name="v_domain" id="v_domain" value="<?= tohtml(trim($v_domain, "'")) ?>" disabled required>
				<input type="hidden" name="v_domain" value="<?= tohtml(trim($v_domain, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_aliases" class="form-label"><?= tohtml( _("Aliases")) ?></label>
				<textarea class="form-control" name="v_aliases" id="v_aliases"><?= tohtml(trim($v_aliases, "'")) ?></textarea>
			</div>
			<?php if ($v_letsencrypt == "yes" || $v_letsencrypt == "on") { ?>
				<div class="alert alert-info u-mb10" role="alert">
					<i class="fas fa-exclamation"></i>
					<p><?= tohtml( _("If the aliases changes, Let's Encrypt will obtain a new SSL certificate.")) ?></p>
				</div>
			<?php } ?>
			<div class="u-mb20">
				<label for="v_ip" class="form-label"><?= tohtml( _("IP Address")) ?></label>
				<select class="form-select" name="v_ip" id="v_ip">
					<?php
						foreach ($ips as $ip => $value) {
							$display_ip = htmlentities(empty($value['NAT']) ? $ip : "{$value['NAT']}");
							$ip_selected = ((!empty($v_ip) && $ip == $v_ip) || $v_ip == "'{$ip}'")	? 'selected' : '';
							echo "\n\t\t\t\t\t\t\t\t\t\t\t\t<option value=\"{$ip}\" {$ip_selected}>{$display_ip}</option>\n";
						}
					?>
				</select>
			</div>
			<div class="u-mb10">
				<label for="v_stats" class="form-label"><?= tohtml( _("Web Statistics")) ?></label>
				<select class="form-select js-stats-select" name="v_stats" id="v_stats">
					<?php
						foreach ($stats as $key => $value) {
							$svalue = "'".$value."'";
							echo "\t\t\t\t<option value=\"".htmlentities($value)."\"";
							if (empty($v_stats)) $v_stats = 'none';
							if (( $value == $v_stats ) || ($svalue == $v_stats )){
								echo ' selected' ;
							}
						echo ">". htmlentities(_($value)) ."</option>\n";
						}
					?>
				</select>
			</div>
			<div class="u-mb10 js-stats-auth" style="<?php if ($v_stats == "none") { ?>display:none<?php } ?>">
				<div class="form-check">
					<input x-model="statsAuthEnabled" class="form-check-input" type="checkbox" name="v_stats_auth" id="v_stats_auth">
					<label for="v_stats_auth">
						<?= tohtml( _("Statistics Authorization")) ?>
					</label>
				</div>
			</div>
			<div class="u-pl30 js-stats-auth">
				<div x-cloak x-show="statsAuthEnabled" name="v-add-web-domain-stats-user">
					<div class="u-mb10">
						<label for="v_stats_user" class="form-label"><?= tohtml( _("Username")) ?></label>
						<input type="text" class="form-control" name="v_stats_user" id="v_stats_user" value="<?= tohtml(trim($v_stats_user, "'")) ?>">
					</div>
					<div class="u-mb20">
						<label for="v_password" class="form-label">
							<?= tohtml( _("Password")) ?>
							<button type="button" title="<?= tohtml( _("Generate")) ?>" class="u-unstyled-button u-ml5 js-generate-password">
								<i class="fas fa-arrows-rotate icon-green"></i>
							</button>
						</label>
						<div class="u-pos-relative">
							<input type="text" class="form-control js-password-input" name="v_stats_password" id="v_password" value="<?= tohtml(trim($v_stats_password, "'")) ?>">
						</div>
					</div>
				</div>
			</div>
			<div class="form-check u-mb10">
				<input x-model="redirectEnabled" class="form-check-input" type="checkbox" name="v-redirect-checkbox" id="v-redirect-checkbox">
				<label for="v-redirect-checkbox">
					<?= tohtml( _("Enable domain redirection")) ?>
				</label>
			</div>
			<div x-cloak x-show="redirectEnabled" id="v_redirect" class="u-pl30 u-mb10">
				<div class="form-check">
					<input class="form-check-input js-redirect-custom-value" type="radio" name="v-redirect" id="v-redirect-radio-1" value="<?= tohtml('www.'.$v_domain) ?>" <?php if ($v_redirect == "www.".$v_domain) echo 'checked'; ?>>
					<label for="v-redirect-radio-1">
						<?= tohtml(sprintf(_("Redirect visitors to %s"), "www." . $v_domain)) ?>
					</label>
				</div>
				<div class="form-check">
					<input class="form-check-input js-redirect-custom-value" type="radio" name="v-redirect" id="v-redirect-radio-2" value="<?= tohtml($v_domain) ?>" <?php if ( $v_redirect == $v_domain) echo 'checked'; ?>>
					<label for="v-redirect-radio-2">
						<?= tohtml(sprintf(_("Redirect visitors to %s"), $v_domain)) ?>
					</label>
				</div>
				<div class="form-check">
					<input class="form-check-input js-redirect-custom-value" type="radio" name="v-redirect" id="v-redirect-radio-3" value="custom" <?php if ( !empty($v_redirect_custom)) echo 'checked'; ?>>
					<label for="v-redirect-radio-3">
						<?= tohtml( _("Redirect visitors to a custom domain or web address")) ?>
					</label>
				</div>
				<div class="u-pl30 js-custom-redirect-fields <?php if (empty($v_redirect_custom)) { echo 'u-hidden'; } ?>">
					<div class="u-mt15 u-mb10">
						<label for="v-redirect-custom" class="form-label"><?= tohtml( _("Target domain or URL")) ?></label>
						<input type="text" class="form-control" name="v-redirect-custom" id="v-redirect-custom" value="<?= tohtml($v_redirect_custom) ?>">
					</div>
					<div class="u-mb20">
						<label for="v-redirect-code" class="form-label"><?= tohtml( _("Status code")) ?>:</label>
						<select class="form-select" name="v-redirect-code" id="v-redirect-code">
							<?php foreach ($redirect_code_options as $status_code): ?>
								<option value="<?= tohtml($status_code) ?>" <?php if ((int) $v_redirect_code === (int) $status_code) echo 'selected="selected"'; ?>>
								<?= tohtml($status_code) ?>
							</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
			<div class="form-check u-mb10">
				<input x-model="sslEnabled" class="form-check-input" type="checkbox" name="v_ssl" id="v_ssl">
				<label for="v_ssl">
					<?= tohtml( _("Enable SSL for this domain")) ?>
				</label>
			</div>
			<div x-cloak x-show="sslEnabled" class="u-pl30">
				<div class="form-check u-mb10">
					<input x-model="letsEncryptEnabled" class="form-check-input js-toggle-lets-encrypt" type="checkbox" name="v_letsencrypt" id="v_letsencrypt">
					<label for="v_letsencrypt">
						<?= tohtml( _("Use Let's Encrypt to obtain SSL certificate")) ?>
					</label>
				</div>
				<div class="form-check u-mb10">
					<input class="form-check-input" type="checkbox" name="v_ssl_forcessl" id="v_ssl_forcessl" <?php if ($v_ssl_forcessl == 'yes') echo 'checked' ?>>
					<label for="v_ssl_forcessl">
						<?= tohtml( _("Enable automatic HTTPS redirection")) ?>
					</label>
				</div>
				<div class="form-check u-mb20">
					<input class="form-check-input" type="checkbox" name="v_ssl_hsts" id="ssl_hsts" <?php if ($v_ssl_hsts == 'yes') echo 'checked' ?>>
					<label for="ssl_hsts">
						<?= tohtml( _("Enable HTTP Strict Transport Security (HSTS)")) ?>
						<a href="https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security" target="_blank">
							<i class="fas fa-question-circle"></i>
						</a>
					</label>
				</div>
				<div x-cloak x-show="showCertificates" class="js-ssl-details">
					<div class="u-mb10">
						<label for="ssl_crt" class="form-label">
							<?= tohtml( _("SSL Certificate")) ?>
							<span id="generate-csr"> / <a class="form-link" target="_blank" href="/generate/ssl/?<?= tohtml(http_build_query(["domain" => $v_domain])) ?>"><?= tohtml( _("Generate Self-Signed SSL Certificate")) ?></a></span>
						</label>
						<textarea class="form-control u-min-height100 u-console" name="v_ssl_crt" id="ssl_crt"><?= tohtml(trim($v_ssl_crt, "'")) ?></textarea>
					</div>
					<div class="u-mb10">
						<label for="v_ssl_key" class="form-label"><?= tohtml( _("SSL Private Key")) ?></label>
						<textarea class="form-control u-min-height100 u-console" name="v_ssl_key" id="v_ssl_key"><?= tohtml(trim($v_ssl_key, "'")) ?></textarea>
					</div>
					<div class="u-mb20">
						<label for="v_ssl_ca" class="form-label">
							<?= tohtml( _("SSL Certificate Authority / Intermediate")) ?> <span class="optional">(<?= tohtml( _("Optional")) ?>)</span>
						</label>
						<textarea class="form-control u-min-height100 u-console" name="v_ssl_ca" id="v_ssl_ca"><?= tohtml(trim($v_ssl_ca, "'")) ?></textarea>
					</div>
				</div>
				<?php if ($v_ssl != "no") { ?>
					<ul class="values-list">
						<li class="values-list-item">
							<span class="values-list-label"><?= tohtml( _("Issued To")) ?></span>
							<span class="values-list-value"><?= tohtml($v_ssl_subject) ?></span>
						</li>
						<?php if ($v_ssl_aliases) {
							$v_ssl_aliases = str_replace(",", ", ", $v_ssl_aliases); ?>
							<li class="values-list-item">
								<span class="values-list-label"><?= tohtml( _("Alternate")) ?></span>
								<span class="values-list-value"><?= tohtml($v_ssl_aliases) ?></span>
							</li>
						<?php } ?>
						<li class="values-list-item">
							<span class="values-list-label"><?= tohtml( _("Not Before")) ?></span>
							<span class="values-list-value"><?= tohtml($v_ssl_not_before) ?></span>
						</li>
						<li class="values-list-item">
							<span class="values-list-label"><?= tohtml( _("Not After")) ?></span>
							<span class="values-list-value"><?= tohtml($v_ssl_not_after) ?></span>
						</li>
						<li class="values-list-item">
							<span class="values-list-label"><?= tohtml( _("Signature")) ?></span>
							<span class="values-list-value"><?= tohtml($v_ssl_signature) ?></span>
						</li>
						<li class="values-list-item">
							<span class="values-list-label"><?= tohtml( _("Key Size")) ?></span>
							<span class="values-list-value"><?= tohtml($v_ssl_pub_key) ?></span>
						</li>
						<li class="values-list-item">
							<span class="values-list-label"><?= tohtml( _("Issued By")) ?></span>
							<span class="values-list-value"><?= tohtml($v_ssl_issuer) ?></span>
						</li>
						<p x-cloak x-show="letsEncryptEnabled" id="letsinfo">
							<button
								type="button"
								class="form-link"
								x-on:click="showCertificates = !showCertificates"
								x-text='showCertificates ? <?= json_encode(_("Hide Certificate"), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?> : <?= json_encode(_("Show Certificate"), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>'>
								<?= tohtml( _("Show Certificate")) ?>
							</button>
						</p>
					</ul>
				<?php } ?>
			</div>
			<div class="u-mt15 u-mb20">
				<button x-on:click="showAdvanced = !showAdvanced" type="button" class="button button-secondary">
					<?= tohtml( _("Advanced Options")) ?>
				</button>
			</div>
			<div x-cloak x-show="showAdvanced">
				<?php if ($_SESSION["userContext"] === "admin" || ($_SESSION["userContext"] === "user" && $_SESSION["POLICY_USER_EDIT_WEB_TEMPLATES"] === "yes")) { ?>
					<div class="u-mb10">
						<label for="v_template" class="form-label">
							<?= tohtml( _("Web Template")) ?> <span class="optional"><?= tohtml(strtoupper($_SESSION["WEB_SYSTEM"])) ?></span>
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
					<?php if ($_SESSION["WEB_SYSTEM"] == "nginx") { ?>
						<div class="form-check u-mb10">
							<input x-model="nginxCacheEnabled" class="form-check-input" type="checkbox" name="v_nginx_cache_check" id="v_nginx_cache_check">
							<label for="v_nginx_cache_check">
								<?= tohtml( _("Enable FastCGI cache")) ?>
								<a href="https://hestiacp.com/docs/server-administration/web-templates.html#nginx-fastcgi-cache" target="_blank" class="u-ml5">
									<i class="fas fa-circle-question"></i>
								</a>
							</label>
						</div>
						<div x-cloak x-show="nginxCacheEnabled" id="v_nginx_duration" class="u-pl30">
							<div class="u-mb10">
								<label for="v_nginx_cache_duration" class="form-label">
									<?= tohtml( _("Cache Duration")) ?> <span class="optional">(<?= tohtml( _("For example")) ?>: 30s, 10m or 1d)</span>
								</label>
								<input type="text" class="form-control" name="v_nginx_cache_duration" id="v_nginx_cache_duration" value="<?= tohtml(trim($v_nginx_cache_duration, "'")) ?>">
							</div>
						</div>
					<?php } ?>
					<?php if (!empty($_SESSION["WEB_BACKEND"])) { ?>
						<div class="u-mb10">
								<label for="v_backend_template" class="form-label">
									<?= tohtml( _("Backend Template")) ?> <span class="optional"><?= tohtml(strtoupper($_SESSION["WEB_BACKEND"])) ?></span>
								</label>
							<select class="form-select" name="v_backend_template" id="v_backend_template">
								<?php
									foreach ($backend_templates as $key => $value) {
										echo "\t\t\t\t<option value=\"".tohtml($value)."\"";
										$svalue = "'".$value."'";
										if ((!empty($v_backend_template)) && (($value == $v_backend_template) || ($svalue == $v_backend_template))) {
											echo ' selected' ;
										}
										if ((empty($v_backend_template)) && ($value == 'default')){
											echo ' selected' ;
										}
										echo ">".tohtml($value)."</option>\n";
									}
								?>
							</select>
						</div>
					<?php } ?>
					<?php if (!empty($_SESSION["PROXY_SYSTEM"])) { ?>
						<div style="display: none;">
							<div class="form-check u-mb10">
								<input x-model="proxySupportEnabled" class="form-check-input" type="checkbox" name="v_proxy" id="v_proxy">
									<label for="v_proxy">
										<?= tohtml( _("Proxy Support")) ?> <span class="optional"><?= tohtml(strtoupper($_SESSION["PROXY_SYSTEM"])) ?></span>
									</label>
							</div>
						</div>
						<div x-cloak x-show="proxySupportEnabled" id="proxytable">
							<div class="u-mb10">
								<label for="v_proxy_template" class="form-label"><?= tohtml( _("Proxy Template")) ?></label>
								<select class="form-select js-proxy-template-select" name="v_proxy_template" id="v_proxy_template">
									<?php
										foreach ($proxy_templates as $key => $value) {
											echo "\t\t\t\t<option value=\"".tohtml($value)."\"";
											$svalue = "'".$value."'";
											if ((!empty($v_proxy_template)) && (($value == $v_proxy_template) || ($svalue == $v_proxy_template))) {
												echo ' selected' ;
											}
											if ((empty($v_proxy_template)) && ($value == 'default')){
												echo ' selected' ;
											}
											echo ">".tohtml($value)."</option>\n";
										}
									?>
								</select>
							</div>
							<div class="u-mb10">
								<label for="v_proxy_ext" class="form-label"><?= tohtml( _("Proxy Extensions")) ?></label>
								<textarea class="form-control u-min-height100" name="v_proxy_ext" id="v_proxy_ext"><?php if (!empty($v_proxy_ext)) { echo tohtml(trim($v_proxy_ext, "'"));} else { echo 'jpg, jpeg, gif, png, ico, svg, css, zip, tgz, gz, rar, bz2, exe, pdf, doc, xls, ppt, txt, odt, ods, odp, odf, tar, bmp, rtf, js, mp3, avi, mpeg, flv, html, htm'; } ?></textarea>
							</div>
						</div>
					<?php } ?>
				<?php } ?>
				<div class="form-check u-mb10">
					<input x-model="customDocumentRootEnabled" class="form-check-input" type="checkbox" name="v_custom_doc_root_check" id="v_custom_doc_root_check">
					<label for="v_custom_doc_root_check">
						<?= tohtml( _("Custom document root")) ?>
					</label>
				</div>
				<div x-cloak x-show="customDocumentRootEnabled" id="v_custom_doc_root" class="u-pl30">
					<div class="u-mb10">
						<label for="v-custom-doc-domain" class="form-label"><?= tohtml( _("Point to")) ?></label>
						<input type="hidden" class="js-custom-docroot-prepath" name="v-custom-doc-root_prepath" value="<?= tohtml($v_custom_doc_root_prepath) ?>">
						<select class="form-select js-custom-docroot-domain" name="v-custom-doc-domain" id="v-custom-doc-domain">
							<?php foreach ($user_domains as $domain): ?>
							<option value="<?= tohtml($domain) ?>"
								<?php if ($v_custom_doc_domain === $domain || (empty($v_custom_doc_domain) && $domain === $v_domain)) echo 'selected="selected"'; ?>>
								<?= tohtml($domain) ?>
							</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="u-mb10">
						<label for="v-custom-doc-folder" class="form-label">
							<?= tohtml( _("Directory")) ?> <span class="optional">(<?= tohtml( _("Optional")) ?>)</span>
						</label>
						<input type="text" class="form-control js-custom-docroot-dir" name="v-custom-doc-folder" id="v-custom-doc-folder" value="<?= tohtml(trim($v_custom_doc_folder, "'")) ?>">
						<small class="js-custom-docroot-hint"></small>
					</div>
				</div>
				<?php if (in_array($_SESSION["FTP_SYSTEM"], ["vsftpd", "proftpd"])) { ?>
					<div class="form-check u-mb10">
						<input class="form-check-input js-toggle-ftp-accounts" type="checkbox" name="v_ftp" id="v_ftp" <?php if (!empty($v_ftp_user)) echo 'checked' ?>>
						<label for="v_ftp">
							<?= tohtml( _("Additional FTP account(s)")) ?>
						</label>
					</div>
					<div class="js-active-ftp-accounts">
						<?php foreach ($v_ftp_users as $i => $ftp_user): ?>
						<?php
							$v_ftp_user     = $ftp_user['v_ftp_user'];
							$v_ftp_password = $ftp_user['v_ftp_password'];
							$v_ftp_path     = $ftp_user['v_ftp_path'];
							$v_ftp_email    = $ftp_user['v_ftp_email'];
							$v_ftp_pre_path = $ftp_user['v_ftp_pre_path'];
						?>
						<div class="js-ftp-account js-ftp-account-nrm" name="v_add_domain_ftp" style="<?php if (empty($v_ftp_user)) { echo 'display: none;'; } ?>">
							<div class="u-mb10">
								<?= tohtml( _("FTP")) ?> #<span class="js-ftp-user-number"><?= tohtml($i + 1) ?></span>
								<button type="button" class="form-link form-link-danger u-ml5 js-delete-ftp-account"><?= tohtml( _("Delete")) ?></button>
								<input type="hidden" class="js-ftp-user-deleted" name="v_ftp_user[<?= tohtml($i) ?>][delete]" value="0">
								<input type="hidden" class="js-ftp-user-is-new" name="v_ftp_user[<?= tohtml($i) ?>][is_new]" value="<?= tohtml($ftp_user['is_new']) ?>">
							</div>
							<div class="u-pl30 u-mb10">
								<label for="v_ftp_user[<?= tohtml($i) ?>][v_ftp_user]" class="form-label">
									<?= tohtml( _("Username")) ?><br>
									<span style="color:#777;"><?= tohtml(sprintf(_('Prefix %s will be added to username automatically'),$user_plain."_")) ?></span>
								</label>
								<input type="text" class="form-control js-ftp-user"<?= $ftp_user['is_new'] != 1 ? ' disabled="disabled"' : '' ?>
								name="v_ftp_user[<?= tohtml($i) ?>][v_ftp_user]" id="v_ftp_user[<?= tohtml($i) ?>][v_ftp_user]" value="<?= tohtml(trim($v_ftp_user, "'")) ?>">
								<small class="hint js-ftp-user-hint"></small>
							</div>
							<div class="u-pl30 u-mb10">
								<label for="v_ftp_user[<?= tohtml($i) ?>][v_ftp_password]" class="form-label">
									<?= tohtml( _("Password")) ?>
									<button type="button" title="<?= tohtml( _("Generate")) ?>" class="u-unstyled-button u-ml5 js-ftp-password-generate">
										<i class="fas fa-arrows-rotate icon-green"></i>
									</button>
								</label>
								<input type="text" class="form-control js-ftp-user-psw" name="v_ftp_user[<?= tohtml($i) ?>][v_ftp_password]" id="v_ftp_user[<?= tohtml($i) ?>][v_ftp_password]" value="<?= tohtml(trim($v_ftp_password, "'")) ?>">
							</div>
							<div class="u-pl30 u-mb10">
								<label for="v_ftp_user[<?= tohtml($i) ?>][v_ftp_path]" class="form-label"><?= tohtml( _("Path")) ?></label>
								<input type="hidden" name="v_ftp_pre_path" value="<?= tohtml(!empty($v_ftp_pre_path) ? trim($v_ftp_pre_path, "'") : '/') ?>">
								<input type="hidden" name="v_ftp_user[<?= tohtml($i) ?>][v_ftp_path_prev]" value="<?php if (!empty($v_ftp_path)) echo tohtml(($v_ftp_path[0] != '/' ? '/' : '') . trim($v_ftp_path, "'")); ?>">
								<input type="text" class="form-control js-ftp-path" name="v_ftp_user[<?= tohtml($i) ?>][v_ftp_path]" id="v_ftp_user[<?= tohtml($i) ?>][v_ftp_path]" value="<?php if (!empty($v_ftp_path)) echo tohtml(($v_ftp_path[0] != '/' ? '/' : '') . trim($v_ftp_path, "'")); ?>">
								<span class="hint-prefix"><?= tohtml(trim($v_ftp_pre_path, "'")) ?></span><span class="hint js-ftp-path-hint"></span>
							</div>
							<?php if ($ftp_user['is_new'] == 1): ?>
								<div class="u-pl30 u-mb10">
									<label for="v_ftp_user[<?= tohtml($i) ?>][v_ftp_email]" class="form-label"><?= tohtml( _("Send FTP credentials to email")) ?></label>
									<input type="email" class="form-control js-email-alert-on-psw" name="v_ftp_user[<?= tohtml($i) ?>][v_ftp_email]" id="v_ftp_user[<?= tohtml($i) ?>][v_ftp_email]" value="<?= tohtml(trim($v_ftp_email, "'")) ?>">
								</div>
							<?php endif; ?>
						</div>
						<?php endforeach; ?>
					</div>

					<button type="button" class="form-link u-mt20 js-add-ftp-account" style="<?php if (empty($v_ftp_user)) echo 'display: none;' ?>">
						<?= tohtml( _("Add FTP account")) ?>
					</button>
				<?php } ?>
			</div>
		</div>

	</form>

</div>

<div class="u-hidden js-ftp-account-template">
	<div class="js-ftp-account js-ftp-account-nrm" name="v_add_domain_ftp">
		<div class="u-mb10">
			<?= tohtml( _("FTP")) ?> #<span class="js-ftp-user-number"></span>
			<button type="button" class="form-link form-link-danger u-ml5 js-delete-ftp-account"><?= tohtml( _("Delete")) ?></button>
			<input type="hidden" class="js-ftp-user-deleted" name="v_ftp_user[%INDEX%][delete]" value="0">
			<input type="hidden" class="js-ftp-user-is-new" name="v_ftp_user[%INDEX%][is_new]" value="1">
		</div>
		<div class="u-pl30 u-mb10">
			<label for="v_ftp_user[%INDEX%][v_ftp_user]" class="form-label">
				<?= tohtml( _("Username")) ?><br>
				<span style="color:#777;"><?= tohtml(sprintf(_("Prefix %s will be added to username automatically"), $user_plain . "_")) ?></span>
			</label>
			<input type="text" class="form-control js-ftp-user" name="v_ftp_user[%INDEX%][v_ftp_user]" id="v_ftp_user[%INDEX%][v_ftp_user]" value="">
			<small class="hint js-ftp-user-hint"></small>
		</div>
		<div class="u-pl30 u-mb10">
			<label for="v_ftp_user[%INDEX%][v_ftp_password]" class="form-label">
				<?= tohtml( _("Password")) ?>
				<button type="button" title="<?= tohtml( _("Generate")) ?>" class="u-unstyled-button u-ml5 js-ftp-password-generate">
					<i class="fas fa-arrows-rotate icon-green"></i>
				</button>
			</label>
			<input type="text" class="form-control js-ftp-user-psw" name="v_ftp_user[%INDEX%][v_ftp_password]" id="v_ftp_user[%INDEX%][v_ftp_password]">
		</div>
		<div class="u-pl30 u-mb10">
			<label for="v_ftp_user[%INDEX%][v_ftp_path]" class="form-label"><?= tohtml( _("Path")) ?></label>
			<input type="hidden" name="v_ftp_pre_path" value="">
			<input type="text" class="form-control js-ftp-path" name="v_ftp_user[%INDEX%][v_ftp_path]" id="v_ftp_user[%INDEX%][v_ftp_path]" value="">
			<span class="hint-prefix"><?= tohtml(trim($v_ftp_pre_path_new_user, "'")) ?></span><span class="hint js-ftp-path-hint"></span>
		</div>
		<div class="u-pl30 u-mb10">
			<label for="v_ftp_user[%INDEX%][v_ftp_email]" class="form-label"><?= tohtml( _("Send FTP credentials to email")) ?></label>
			<input type="email" class="form-control js-email-alert-on-psw" name="v_ftp_user[%INDEX%][v_ftp_email]" id="v_ftp_user[%INDEX%][v_ftp_email]" value="">
		</div>
	</div>
</div>
