<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/server/">
				<i class="fas fa-arrow-left status-icon blue"></i><?=_('Back');?>
			</a>
			<a href="/edit/server/php/" class="button button-secondary">
				<i class="fas fa-pencil status-icon orange"></i> <?=_('Configure');?> PHP
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

	<form id="vstobjects" name="v_configure_server" method="post">
		<input type="hidden" name="token" value="<?=$_SESSION['token']?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container">
			<h1 class="form-title"><?=_('Configuring Server');?>: <?=$v_service_name;?></h1>
			<?php show_alert_message($_SESSION);?>
			<div id="basic-options">
				<div class="u-mb10">
					<label for="v_worker_processes" class="form-label">worker_processes</label>
					<input type="text" class="form-control" regexp="worker_processes" prev_value="<?=htmlentities($v_worker_processes)?>" name="v_worker_processes" id="v_worker_processes" value="<?=htmlentities($v_worker_processes)?>">
				</div>
				<div class="u-mb10">
					<label for="v_worker_connections" class="form-label">worker_connections</label>
					<input type="text" class="form-control" regexp="worker_connections" prev_value="<?=htmlentities($v_worker_connections)?>" name="v_worker_connections" id="v_worker_connections" value="<?=htmlentities($v_worker_connections)?>">
				</div>
				<div class="u-mb10">
					<label for="v_client_max_body_size" class="form-label">client_max_body_size</label>
					<input type="text" class="form-control" regexp="client_max_body_size" prev_value="<?=htmlentities($v_client_max_body_size)?>" name="v_client_max_body_size" id="v_client_max_body_size" value="<?=htmlentities($v_client_max_body_size)?>">
				</div>
				<div class="u-mb10">
					<label for="v_send_timeout" class="form-label">send_timeout</label>
					<input type="text" class="form-control" regexp="send_timeout" prev_value="<?=htmlentities($v_send_timeout)?>" name="v_send_timeout" id="v_send_timeout" value="<?=htmlentities($v_send_timeout)?>">
				</div>
				<div class="u-mb10">
					<label for="v_proxy_connect_timeout" class="form-label">proxy_connect_timeout</label>
					<input type="text" class="form-control" regexp="proxy_connect_timeout" prev_value="<?=htmlentities($v_proxy_connect_timeout)?>" name="v_proxy_connect_timeout" id="v_proxy_connect_timeout" value="<?=htmlentities($v_proxy_connect_timeout)?>">
				</div>
				<div class="u-mb10">
					<label for="v_proxy_send_timeout" class="form-label">proxy_send_timeout</label>
					<input type="text" class="form-control" regexp="proxy_send_timeout" prev_value="<?=htmlentities($v_proxy_send_timeout)?>" name="v_proxy_send_timeout" id="v_proxy_send_timeout" value="<?=htmlentities($v_proxy_send_timeout)?>">
				</div>
				<div class="u-mb10">
					<label for="v_proxy_read_timeout" class="form-label">proxy_read_timeout</label>
					<input type="text" class="form-control" regexp="proxy_read_timeout" prev_value="<?=htmlentities($v_proxy_read_timeout)?>" name="v_proxy_read_timeout" id="v_proxy_read_timeout" value="<?=htmlentities($v_proxy_read_timeout)?>">
				</div>
				<div class="u-mb10">
					<label for="v_gzip" class="form-label">gzip</label>
					<input type="text" class="form-control" regexp="gzip" prev_value="<?=htmlentities($v_gzip)?>" name="v_gzip" id="v_gzip" value="<?=htmlentities($v_gzip)?>">
				</div>
				<div class="u-mb10">
					<label for="v_gzip_comp_level" class="form-label">gzip_comp_level</label>
					<input type="text" class="form-control" regexp="gzip_comp_level" prev_value="<?=htmlentities($v_gzip_comp_level)?>" name="v_gzip_comp_level" id="v_gzip_comp_level" value="<?=htmlentities($v_gzip_comp_level)?>">
				</div>
				<div class="u-mb20">
					<label for="v_charset" class="form-label">charset</label>
					<input type="text" class="form-control" regexp="charset" prev_value="<?=htmlentities($v_charset)?>" name="v_charset" id="v_charset" value="<?=htmlentities($v_charset)?>">
				</div>
				<div class="u-mb20">
					<a href="javascript:toggleOptions();" class="button button-secondary"><?=_('Advanced options');?></a>
				</div>
			</div>
			<div id="advanced-options" style="display:<?php if (empty($v_adv)) echo 'none';?> ;">
				<div class="u-mb20">
					<a href="javascript:toggleOptions();" class="button button-secondary"><?=_('Basic options');?></a>
				</div>
				<div class="u-mb20">
					<label for="v_config" class="form-label"><?=$v_config_path;?></label>
					<textarea class="form-control u-min-height600 u-allow-resize u-console" name="v_config" id="v_config"><?=$v_config;?></textarea>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" name="v_restart" id="v_restart" checked>
					<label for="v_restart">
						<?=_('restart');?>
					</label>
				</div>
			</div>
		</div>

	</form>

</div>
