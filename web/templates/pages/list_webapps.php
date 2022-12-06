<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/edit/web/?domain=<?=htmlentities($v_domain)?>">
				<i class="fas fa-arrow-left status-icon blue"></i><?=_('Back');?>
			</a>
		</div>
		<div class="toolbar-buttons">
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container animate__animated animate__fadeIn">

	<div class="form-container form-container-wide">
		<h1 class="form-title"><?=_('Quick Install App');?></h1>
		<?php
			if (!empty($_SESSION['error_msg'])) {
				$msg_icon = 'fa-circle-exclamation status-icon red';
				$msg_text = htmlentities($_SESSION['error_msg']);
				$msg_class = 'inline-danger';
			} else {
				if (!empty($_SESSION['ok_msg'])) {
					$msg_icon = 'fa-circle-check status-icon green';
					$msg_text = $_SESSION['ok_msg'];
					$msg_class = 'inline-success';
				}
			}
			if(!empty($msg_class)){
		?>
		<p class="<?=$msg_class;?> u-mb20"><i class="fas <?=$msg_icon;?>"></i> <?=$msg_text;?></p>
		<?php }; ?>
		<div class="cards">
			<!-- List available web apps -->
			<?php foreach($v_web_apps as $webapp):?>
				<div class="card <?=($webapp['enabled'] ? '' : 'disabled');?>">
					<div class="card-thumb">
						<img src="/src/app/WebApp/Installers/<?=$webapp['name'];?>/<?=$webapp['thumbnail'];?>" alt="<?=$webapp['name'];?>">
					</div>
					<div class="card-content">
						<p class="card-title"><?=$webapp['name'];?></p>
						<p class="u-mb10"><?=_('version');?>: <?=$webapp['version'];?></p>
						<a href="/add/webapp/?app=<?=$webapp['name'];?>&domain=<?=htmlentities($v_domain)?>" class="button"><?=_('Setup');?></a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

</div>
