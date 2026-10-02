<?php
$theme = !empty($_SESSION["userTheme"]) ? $_SESSION["userTheme"] : (!empty($_SESSION["THEME"]) ? $_SESSION["THEME"] : "default");
$is_dark_theme = ($theme === "dark" || strpos($theme, "dark") !== false || $theme === "twilight" || $theme === "nord");
?>
<!-- Scoped Theme-Adaptive Styles for Node.js & PM2 Dashboard -->
<style>
	.node-dashboard {
		<?php if ($is_dark_theme) { ?>
		--n-bg-card: #282828;
		--n-bg-header: #202020;
		--n-bg-inner: #1f1f1f;
		--n-border: #3d3d3d;
		--n-border-subtle: #333333;
		--n-text-head: #ececec;
		--n-text-body: #cdcdcd;
		--n-text-muted: #888888;
		--n-btn-bg: #333333;
		--n-btn-hover: #404040;
		--n-btn-color: #eeeeee;
		--n-tag-bg: rgba(255, 255, 255, 0.06);
		--n-table-stripe: #252525;
		<?php } else { ?>
		--n-bg-card: #ffffff;
		--n-bg-header: #f8fafc;
		--n-bg-inner: #f8fafc;
		--n-border: #e2e8f0;
		--n-border-subtle: #edf2f7;
		--n-text-head: #1e293b;
		--n-text-body: #334155;
		--n-text-muted: #64748b;
		--n-btn-bg: #f1f5f9;
		--n-btn-hover: #e2e8f0;
		--n-btn-color: #1e293b;
		--n-tag-bg: #f1f5f9;
		--n-table-stripe: #fafbfc;
		<?php } ?>
	}

	.node-header-wrap {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 22px;
		flex-wrap: wrap;
		gap: 16px;
	}

	.node-header-title {
		font-size: 22px;
		font-weight: 700;
		margin: 0;
		color: var(--n-text-head);
	}

	/* Sleek Live Indicator */
	.node-live-status {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		font-size: 13px;
		font-weight: 600;
		color: var(--n-text-muted);
		background: var(--n-tag-bg);
		padding: 6px 12px;
		border-radius: 6px;
		border: 1px solid var(--n-border);
	}

	.node-live-pulse {
		width: 8px;
		height: 8px;
		border-radius: 50%;
		background-color: #2ecc71;
		box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7);
		animation: nodePulse 2s infinite cubic-bezier(0.66, 0, 0, 1);
		display: inline-block;
	}

	@keyframes nodePulse {
		to {
			box-shadow: 0 0 0 8px rgba(46, 204, 113, 0);
		}
	}

	/* Stats Grid */
	.node-stats-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
		gap: 16px;
		margin-bottom: 24px;
	}

	.node-stat-card {
		background: var(--n-bg-card);
		border: 1px solid var(--n-border);
		border-radius: 8px;
		padding: 16px 18px;
		display: flex;
		align-items: center;
		gap: 14px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
		transition: transform 0.15s ease, box-shadow 0.15s ease;
	}

	.node-stat-card:hover {
		transform: translateY(-1px);
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
	}

	.node-stat-icon-wrapper {
		width: 44px;
		height: 44px;
		border-radius: 8px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	.node-stat-label {
		font-size: 11px;
		color: var(--n-text-muted);
		text-transform: uppercase;
		font-weight: 700;
		letter-spacing: 0.5px;
		margin-bottom: 3px;
	}

	.node-stat-value {
		font-size: 18px;
		font-weight: 700;
		color: var(--n-text-head);
	}

	/* Containers / Cards */
	.node-section-card {
		background: var(--n-bg-card);
		border: 1px solid var(--n-border);
		border-radius: 8px;
		margin-bottom: 24px;
		overflow: hidden;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
	}

	.node-section-header {
		padding: 14px 20px;
		border-bottom: 1px solid var(--n-border);
		display: flex;
		justify-content: space-between;
		align-items: center;
		background: var(--n-bg-header);
	}

	.node-section-title {
		font-size: 14px;
		font-weight: 700;
		display: flex;
		align-items: center;
		gap: 8px;
		margin: 0;
		color: var(--n-text-head);
	}

	/* Table */
	.node-table {
		width: 100%;
		border-collapse: collapse;
		text-align: left;
	}

	.node-table th {
		padding: 12px 18px;
		font-size: 11.5px;
		text-transform: uppercase;
		font-weight: 700;
		color: var(--n-text-muted);
		border-bottom: 1px solid var(--n-border);
		background: var(--n-bg-header);
		letter-spacing: 0.5px;
	}

	.node-table td {
		padding: 14px 18px;
		font-size: 13.5px;
		border-bottom: 1px solid var(--n-border-subtle);
		vertical-align: middle;
		color: var(--n-text-body);
	}

	.node-table tr:hover td {
		background: var(--n-table-stripe);
	}

	.node-table tr:last-child td {
		border-bottom: none;
	}

	.node-status-pill {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 4px 10px;
		border-radius: 20px;
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.node-status-pill.online {
		background: rgba(46, 204, 113, 0.15);
		color: #27ae60;
		border: 1px solid rgba(46, 204, 113, 0.3);
	}

	.node-status-pill.stopped {
		background: rgba(243, 156, 18, 0.15);
		color: #d68910;
		border: 1px solid rgba(243, 156, 18, 0.3);
	}

	.node-status-pill.errored {
		background: rgba(231, 76, 60, 0.15);
		color: #c0392b;
		border: 1px solid rgba(231, 76, 60, 0.3);
	}

	.node-metric-tag {
		background: var(--n-tag-bg);
		border: 1px solid var(--n-border);
		color: var(--n-text-body);
		padding: 3px 8px;
		border-radius: 4px;
		font-size: 12px;
		font-family: monospace;
		font-weight: 600;
	}

	.node-btn-group {
		display: flex;
		gap: 6px;
		justify-content: flex-end;
	}

	.node-btn-action {
		width: 32px;
		height: 32px;
		border-radius: 6px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border: 1px solid var(--n-border);
		background: var(--n-btn-bg);
		color: var(--n-btn-color);
		cursor: pointer;
		transition: all 0.15s ease;
	}

	.node-btn-action:hover {
		background: var(--n-btn-hover);
		transform: scale(1.05);
	}

	.node-btn-action.btn-logs:hover { color: #8e2fca; border-color: rgba(142, 47, 202, 0.4); }
	.node-btn-action.btn-restart:hover { color: #326b9b; border-color: rgba(50, 107, 155, 0.4); }
	.node-btn-action.btn-stop:hover { color: #e67e22; border-color: rgba(230, 126, 34, 0.4); }
	.node-btn-action.btn-delete:hover { color: #e74c3c; border-color: rgba(231, 76, 60, 0.4); }

	/* Form Layout */
	.node-form-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
		gap: 20px;
		padding: 22px;
	}

	.node-form-card {
		background: var(--n-bg-inner);
		border: 1px solid var(--n-border);
		border-radius: 6px;
		padding: 18px;
	}

	.node-form-card-title {
		font-size: 13.5px;
		font-weight: 700;
		color: var(--n-text-head);
		margin-bottom: 16px;
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.node-empty-state {
		padding: 40px 20px;
		text-align: center;
		color: var(--n-text-muted);
	}

	.node-empty-icon {
		font-size: 36px;
		opacity: 0.4;
		margin-bottom: 12px;
	}
</style>

<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/web/">
				<i class="fas fa-arrow-left icon-blue"></i><?= tohtml( _("Back")) ?>
			</a>
			<a class="button button-secondary" href="/add/node/<?= !empty($v_domain) ? '?domain=' . tohtml(urlencode($v_domain)) : '' ?>">
				<i class="fas fa-arrows-rotate icon-green"></i><?= tohtml( _("Refresh")) ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<?php if ($node_installed && $read_only !== true) { ?>
				<button type="submit" name="btn_add" value="1" class="button" form="main-form">
					<i class="fas fa-play icon-purple"></i><?= tohtml( _("Start Application")) ?>
				</button>
			<?php } ?>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container node-dashboard">
	<!-- Page Header -->
	<div class="node-header-wrap">
		<div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
			<h1 class="node-header-title">
				<?= tohtml( _("Node.js & PM2 Application Manager")) ?>
			</h1>
			<span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; padding: 3px 8px; border-radius: 4px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.25);">
				<?= tohtml( _("Beta / Experimental")) ?>
			</span>
		</div>

		<div class="node-live-status">
			<span class="node-live-pulse"></span>
			<span><?= tohtml( _("Live Monitoring")) ?></span>
		</div>
	</div>

	<?php show_alert_message($_SESSION); ?>
	<div id="ajax-alert-container"></div>

	<?php if (!$node_installed) { ?>
		<div class="alert alert-warning u-mb20" role="alert">
			<i class="fas fa-triangle-exclamation"></i>
			<div>
				<p class="u-mb10"><strong><?= tohtml( _("Node.js / PM2 Not Installed")) ?></strong></p>
				<p><?= tohtml( _("Node.js or PM2 process manager is not installed on this server.")) ?></p>
				<p><?= tohtml( _("You can install it on your server using:")) ?> <code>apt-get install nodejs && npm install -g pm2</code></p>
			</div>
		</div>
	<?php } else { ?>
		<!-- Overview Metric Cards Grid -->
		<div class="node-stats-grid">
			<!-- Node.js Version Card -->
			<div class="node-stat-card">
				<div class="node-stat-icon-wrapper" style="background: rgba(131, 205, 41, 0.15); border: 1px solid rgba(131, 205, 41, 0.3);">
					<svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128">
						<path fill="#83CD29" d="M112.771 30.334L68.674 4.729c-2.781-1.584-6.402-1.584-9.205 0L14.901 30.334C12.031 31.985 10 35.088 10 38.407v51.142c0 3.319 2.084 6.423 4.954 8.083l11.775 6.688c5.628 2.772 7.617 2.772 10.178 2.772 8.333 0 13.093-5.039 13.093-13.828v-50.49c0-.713-.371-1.774-1.071-1.774h-5.623C42.594 41 41 42.061 41 42.773v50.49c0 3.896-3.524 7.773-10.11 4.48L18.723 90.73c-.424-.23-.723-.693-.723-1.181V38.407c0-.482.555-.966.982-1.213l44.424-25.561c.415-.235 1.025-.235 1.439 0l43.882 25.555c.42.253.272.722.272 1.219v51.142c0 .488.183.963-.232 1.198l-44.086 25.576c-.378.227-.847.227-1.261 0l-11.307-6.749c-.341-.198-.746-.269-1.073-.086-3.146 1.783-3.726 2.02-6.677 3.043-.726.253-1.797.692.41 1.929l14.798 8.754a9.294 9.294 0 004.647 1.246c1.642 0 3.25-.426 4.667-1.246l43.885-25.582c2.87-1.672 4.23-4.764 4.23-8.083V38.407c0-3.319-1.36-6.414-4.229-8.073zM77.91 81.445c-11.726 0-14.309-3.235-15.17-9.066-.1-.628-.633-1.379-1.272-1.379h-5.731c-.709 0-1.279.86-1.279 1.566 0 7.466 4.059 16.512 23.453 16.512 14.039 0 22.088-5.455 22.088-15.109 0-9.572-6.467-12.084-20.082-13.886-13.762-1.819-15.16-2.738-15.16-5.962 0-2.658 1.184-6.203 11.374-6.203 9.105 0 12.461 1.954 13.842 8.091.118.577.645.991 1.24.991h5.754c.354 0 .692-.143.94-.396.24-.272.367-.613.335-.979-.891-10.568-7.912-15.493-22.112-15.493-12.631 0-20.166 5.334-20.166 14.275 0 9.698 7.497 12.378 19.622 13.577 14.505 1.422 15.633 3.542 15.633 6.395 0 4.955-3.978 7.066-13.309 7.066z"/>
					</svg>
				</div>
				<div>
					<div class="node-stat-label"><?= tohtml( _("Node.js Runtime")) ?></div>
					<div class="node-stat-value"><?= tohtml($node_v) ?></div>
				</div>
			</div>

			<!-- PM2 Version Card -->
			<div class="node-stat-card">
				<div class="node-stat-icon-wrapper" style="background: rgba(82, 11, 245, 0.12); border: 1px solid rgba(82, 11, 245, 0.25);">
					<svg style="width: 22px; height: 22px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 96 96">
						<path fill="url(#card-pm2-a)" d="m65.3613 9.47461-3.4351 5.88909c-.3921.6723-1.0988 1.047-1.8255 1.047-.3609 0-.7272-.0923-1.0618-.2876-1.0069-.5874-1.3468-1.8803-.7594-2.8873l2.1938-3.76119h-6.1682c.0005.35992-.0818.72409-.275 1.05759l-9.4898 16.3906c-2.3272 4.036-7.3208 6.3756-11.2034 6.3756l-3.0383 0c-1.1657 0-2.1109-.9451-2.1109-2.1109s.9452-2.111 2.1109-2.111h3.0383c2.7519 0 5.9054-1.9558 7.4717-4.129 1.0442-1.4488 4.0291-6.6065 8.9547-15.47289h-5.6067C35.299 28.5488 30.0216 24.8546 14.1893 24.8546c-1.0481 1.5874-2.102 3.1807-3.1326 4.7497h11.0488c1.1657 0 2.1109.9451 2.1109 2.1109s-.9452 2.111-2.1109 2.111H8.30449c-.94201 1.4618-1.83759 2.8725-2.65823 4.2013l34.68264-.2438-1.5521.0011c2.1004.0427 5.498-1.1869 7.143-3.7729l8.1773-12.9249c.6233-.9853 1.9268-1.2787 2.9126-.6549.9848.6233 1.2782 1.9268.655 2.9126l-8.18 12.9297c-2.4661 3.8747-7.134 5.7935-10.7696 5.7318l-35.57561.2607c-1.64919 2.9664-2.13911 4.9044-2.11836 5.8083H48.1796l24.0613-36.9419c-1.5188-.8755-3.6942-1.58322-6.478-1.65869h-.4016Z"/>
						<path fill="url(#card-pm2-b)" d="M48.1794 48.0752H1.02044c-.151466 2.2366.54602 4.5597 1.85851 6.601l1.17389 0H34.4526c4.9127-.5396 9.2716 1.5035 13.0765 6.1292l7.9178 12.8003c.6217.9864.3251 2.2899-.6612 2.9111-.3494.22-.7383.3251-1.1225.3251-.7019 0-1.3875-.3494-1.7885-.9869-4.8819-7.9131-7.8546-12.3891-8.9181-13.4281-1.5954-1.5584-3.6388-3.5166-8.504-3.5166H5.53051l2.81709 4.4736h11.2741c1.1658 0 2.111.9452 2.111 2.111 0 1.1658-.9452 2.111-2.111 2.111h-8.6159l2.6556 4.2177c18.9987 0 21.6374 2.1109 28.1603 14.7014h5.5344c-4.7264-8.4403-7.7129-13.4191-8.9596-14.9365-1.8701-2.276-4.3902-3.9826-7.6529-3.9826h-2.9287c-1.1658 0-2.1109-.9452-2.1109-2.111 0-1.1658.9451-2.111 2.1109-2.111h2.9287c4.4763 0 9.4991 2.8757 11.3124 6.3757l10.0176 16.3906c.0702.1213.1256.2469.1694.3748h7.5473l-2.4113-4.1339c-.5879-1.007-.2475-2.2994.7594-2.8873.3346-.1948.7009-.2876 1.0618-.2876.7262 0 1.4334.3747 1.8255 1.0475l3.6525 6.2613h1.8569c1.4708.0046 2.5484-.1519 4.6501-1.5079L48.1794 48.0752Z"/>
						<path fill="url(#card-pm2-c)" d="m72.241 11.1333-2.9322 4.4621 3.633 5.4494c.6465.97.3847 2.2809-.5858 2.9274-.3594.2401-.7663.3546-1.1684.3546-.6824 0-1.3521-.3298-1.7585-.9399l-2.6392-3.9586-2.9142 4.4341 10.4108 17.0392c2.4339 3.9824 2.4223 8.9231-.0296 12.8948-.0316.0502-.0649.0998-.1002.1478l-1.2751 1.721c-.4142.5583-1.0523.8539-1.6977.8539-.437 0-.8777-.1351-1.2555-.4148-.9368-.6945-1.1331-2.0165-.4391-2.9533l1.2191-1.6444c1.5679-2.5981 1.5605-5.8099-.0243-8.4038L61.312 27.764 47.9648 48.0752 58.4611 32.253l5.1835 8.0079c2.6598 4.1096 2.6492 9.4007-.0269 13.5002l-6.1535 9.4265-9.4994-15.1124L60.1763 67.503l5.6262-7.5014c.6998-.932 2.0223-1.1209 2.9554-.4216.9325.6992 1.1214 2.0228.4221 2.9553l-6.5972 8.7964 3.2086 5.1043 15.5531-24.5568c1.7737-2.8002 1.7943-6.4322.0522-9.2519l-7.5219-12.1776c-.6121-.9916-.305-2.2925.6872-2.9052.9926-.6127 2.2925-.3051 2.9052.6866l7.5213 12.1776c2.5849 4.185 2.5543 9.5738-.077 13.7297l-16.4286 25.939c-.066.1045-.142.1979-.2222.2866l2.9232 4.6531c1.2737-1.0062 2.1804-1.8512 2.7202-2.5349 6.1947-7.8462 12.6421-17.273 19.342-28.2804 2.2942-3.7693 2.3543-8.4397.0845-12.1977-8.635-14.2955-18.2282-29.4527-21.0896-30.8708Z"/>
						<defs>
							<linearGradient id="card-pm2-a" x1="4782.04" x2="4063.43" y1="2506.44" y2="1310.92" gradientUnits="userSpaceOnUse">
								<stop stop-color="#520bf5"/>
								<stop offset="1" stop-color="#9d1fe0"/>
							</linearGradient>
							<linearGradient id="card-pm2-b" x1="4735.88" x2="3861.29" y1="1393.59" y2="2789.62" gradientUnits="userSpaceOnUse">
								<stop stop-color="#9e1fa7"/>
								<stop offset="1" stop-color="#e540ae"/>
							</linearGradient>
							<linearGradient id="card-pm2-c" x1="4751.51" x2="1457.78" y1="3705.32" y2="3705.32" gradientUnits="userSpaceOnUse">
								<stop stop-color="#4b6af2"/>
								<stop offset="1" stop-color="#4ca7eb"/>
							</linearGradient>
						</defs>
					</svg>
				</div>
				<div>
					<div class="node-stat-label"><?= tohtml( _("PM2 Manager")) ?></div>
					<div class="node-stat-value"><?= tohtml($pm2_v) ?></div>
				</div>
			</div>

			<!-- NPM Version Card -->
			<div class="node-stat-card">
				<div class="node-stat-icon-wrapper" style="background: rgba(193, 33, 39, 0.12); border: 1px solid rgba(193, 33, 39, 0.25);">
					<svg style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<path fill="#c12127" d="M7.480775 15.766h3.012825v-1.5064h3.0128V8.23395H7.480775V15.766Zm3.012825 -6.02565H12v3.012825h-1.5064V9.74035Zm4.21795 -1.5064v6.02565h3.0128v-4.51925h1.506425v4.51925h1.5064v-4.51925h1.506425v4.51925H23.75V8.23395H14.71155ZM0.25 14.2596h3.012825v-4.51925h1.5064v4.51925h1.506425V8.23395H0.25v6.02565Z" stroke-width="0.25"/>
					</svg>
				</div>
				<div>
					<div class="node-stat-label"><?= tohtml( _("NPM Tools")) ?></div>
					<div class="node-stat-value"><?= tohtml($npm_v) ?></div>
				</div>
			</div>

			<!-- Active Apps Counter Card -->
			<div class="node-stat-card">
				<div class="node-stat-icon-wrapper" style="background: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.25);">
					<i class="fas fa-server icon-blue" style="font-size: 19px;"></i>
				</div>
				<div>
					<div class="node-stat-label"><?= tohtml( _("Active Apps")) ?></div>
					<div class="node-stat-value" id="metric-active-count"><?= count($pm2_apps) ?></div>
				</div>
			</div>
		</div>

		<!-- Running Applications Card -->
		<div class="node-section-card">
			<div class="node-section-header">
				<div class="node-section-title">
					<i class="fas fa-layer-group icon-blue"></i>
					<span><?= tohtml( _("Running Applications")) ?></span>
				</div>
				<small style="color: var(--n-text-muted); font-size: 12px;">
					<i class="fas fa-clock-rotate-left"></i> <?= tohtml( _("Real-time auto-refresh")) ?>
				</small>
			</div>

			<div style="overflow-x: auto;">
				<table class="node-table" id="apps-table">
					<thead>
						<tr>
							<th><?= tohtml( _("Application (Domain)")) ?></th>
							<th><?= tohtml( _("Status")) ?></th>
							<th><?= tohtml( _("CPU")) ?></th>
							<th><?= tohtml( _("Memory")) ?></th>
							<th><?= tohtml( _("Uptime")) ?></th>
							<th style="text-align: right;"><?= tohtml( _("Actions")) ?></th>
						</tr>
					</thead>
					<tbody id="apps-tbody">
						<?php if (empty($pm2_apps)) { ?>
							<tr id="no-apps-row">
								<td colspan="6">
									<div class="node-empty-state">
										<i class="fas fa-cubes node-empty-icon"></i>
										<div style="font-weight: 600; font-size: 14px; margin-bottom: 4px; color: var(--n-text-head);"><?= tohtml( _("No active applications deployed yet")) ?></div>
										<div style="font-size: 12.5px; opacity: 0.8;"><?= tohtml( _("Use the deployment form below to launch your first Node.js app.")) ?></div>
									</div>
								</td>
							</tr>
						<?php } else { ?>
							<?php foreach ($pm2_apps as $app) {
								$app_name = $app["name"] ?? "";
								$app_status = $app["pm2_env"]["status"] ?? "unknown";
								$app_cpu = ($app["monit"]["cpu"] ?? 0) . "%";
								$app_mem = round(($app["monit"]["memory"] ?? 0) / 1024 / 1024, 1) . " MB";
								$app_uptime = isset($app["pm2_env"]["pm_uptime"]) ? round((time() - ($app["pm2_env"]["pm_uptime"] / 1000)) / 60) . " min" : "-";
								$status_class = ($app_status === "online") ? "online" : (($app_status === "stopped") ? "stopped" : "errored");
							?>
								<tr id="app-row-<?= tohtml($app_name) ?>">
									<td style="font-weight: 600;">
										<div style="display: flex; align-items: center; gap: 8px;">
											<svg style="width: 16px; height: 16px; flex-shrink: 0;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128">
												<path fill="#83CD29" d="M112.771 30.334L68.674 4.729c-2.781-1.584-6.402-1.584-9.205 0L14.901 30.334C12.031 31.985 10 35.088 10 38.407v51.142c0 3.319 2.084 6.423 4.954 8.083l11.775 6.688c5.628 2.772 7.617 2.772 10.178 2.772 8.333 0 13.093-5.039 13.093-13.828v-50.49c0-.713-.371-1.774-1.071-1.774h-5.623C42.594 41 41 42.061 41 42.773v50.49c0 3.896-3.524 7.773-10.11 4.48L18.723 90.73c-.424-.23-.723-.693-.723-1.181V38.407c0-.482.555-.966.982-1.213l44.424-25.561c.415-.235 1.025-.235 1.439 0l43.882 25.555c.42.253.272.722.272 1.219v51.142c0 .488.183.963-.232 1.198l-44.086 25.576c-.378.227-.847.227-1.261 0l-11.307-6.749c-.341-.198-.746-.269-1.073-.086-3.146 1.783-3.726 2.02-6.677 3.043-.726.253-1.797.692.41 1.929l14.798 8.754a9.294 9.294 0 004.647 1.246c1.642 0 3.25-.426 4.667-1.246l43.885-25.582c2.87-1.672 4.23-4.764 4.23-8.083V38.407c0-3.319-1.36-6.414-4.229-8.073zM77.91 81.445c-11.726 0-14.309-3.235-15.17-9.066-.1-.628-.633-1.379-1.272-1.379h-5.731c-.709 0-1.279.86-1.279 1.566 0 7.466 4.059 16.512 23.453 16.512 14.039 0 22.088-5.455 22.088-15.109 0-9.572-6.467-12.084-20.082-13.886-13.762-1.819-15.16-2.738-15.16-5.962 0-2.658 1.184-6.203 11.374-6.203 9.105 0 12.461 1.954 13.842 8.091.118.577.645.991 1.24.991h5.754c.354 0 .692-.143.94-.396.24-.272.367-.613.335-.979-.891-10.568-7.912-15.493-22.112-15.493-12.631 0-20.166 5.334-20.166 14.275 0 9.698 7.497 12.378 19.622 13.577 14.505 1.422 15.633 3.542 15.633 6.395 0 4.955-3.978 7.066-13.309 7.066z"/>
											</svg>
											<a href="http://<?= tohtml($app_name) ?>" target="_blank" style="text-decoration: none; color: inherit;">
												<?= tohtml($app_name) ?>
												<i class="fas fa-arrow-up-right-from-square" style="font-size: 10px; opacity: 0.5; margin-left: 4px;"></i>
											</a>
										</div>
									</td>
									<td>
										<span class="node-status-pill <?= $status_class ?> app-status-badge">
											<?= tohtml($app_status) ?>
										</span>
									</td>
									<td><span class="node-metric-tag app-cpu-val"><?= tohtml($app_cpu) ?></span></td>
									<td><span class="node-metric-tag app-mem-val"><?= tohtml($app_mem) ?></span></td>
									<td class="app-uptime-val"><?= tohtml($app_uptime) ?></td>
									<td style="text-align: right;">
										<div class="node-btn-group">
											<button type="button" class="node-btn-action btn-logs" onclick="openLogsModal('<?= tohtml($app_name) ?>')" title="<?= tohtml( _("View Console Logs")) ?>">
												<i class="fas fa-terminal"></i>
											</button>
											<button type="button" class="node-btn-action btn-restart" onclick="runAjaxAction('restart', '<?= tohtml($app_name) ?>', this)" title="<?= tohtml( _("Restart")) ?>">
												<i class="fas fa-arrows-rotate"></i>
											</button>
											<button type="button" class="node-btn-action btn-stop" onclick="runAjaxAction('stop', '<?= tohtml($app_name) ?>', this)" title="<?= tohtml( _("Stop")) ?>">
												<i class="fas fa-pause"></i>
											</button>
											<button type="button" class="node-btn-action btn-delete" onclick="confirmDeleteApp('<?= tohtml($app_name) ?>', this)" title="<?= tohtml( _("Delete")) ?>">
												<i class="fas fa-trash"></i>
											</button>
										</div>
									</td>
								</tr>
							<?php } ?>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Deploy New Application Section -->
		<div class="node-section-card">
			<div class="node-section-header">
				<div class="node-section-title">
					<i class="fas fa-rocket icon-purple"></i>
					<span><?= tohtml( _("Deploy Node.js Application")) ?></span>
				</div>
			</div>

			<form id="main-form" method="POST" name="v_add_node" action="/add/node/">
				<input type="hidden" name="token" id="csrf_token" value="<?= tohtml($_SESSION["token"]) ?>">
				<input type="hidden" name="v_user" value="<?= tohtml($user_plain) ?>">
				<input type="hidden" name="btn_add" value="1">

				<div class="node-form-grid">
					<!-- Domain & Network Settings -->
					<div class="node-form-card">
						<div class="node-form-card-title">
							<i class="fas fa-globe icon-blue"></i>
							<span><?= tohtml( _("Domain & Network")) ?></span>
						</div>

						<div class="u-mb20">
							<label for="v_domain" class="form-label"><?= tohtml( _("Domain")) ?></label>
							<select class="form-select" name="v_domain" id="v_domain" required onchange="onDomainChanged()">
								<option value=""><?= tohtml( _("-- Select Domain --")) ?></option>
								<?php if (!empty($user_domains)) { ?>
									<?php foreach ($user_domains as $domain_name => $dom_data) { ?>
										<option value="<?= tohtml($domain_name) ?>" <?= ($v_domain === $domain_name) ? 'selected' : '' ?>>
											<?= tohtml($domain_name) ?>
										</option>
									<?php } ?>
								<?php } elseif (!empty($v_domain)) { ?>
									<option value="<?= tohtml($v_domain) ?>" selected><?= tohtml($v_domain) ?></option>
								<?php } ?>
							</select>
						</div>

						<div>
							<label for="v_port" class="form-label"><?= tohtml( _("Internal Port (Reverse Proxy)")) ?></label>
							<input type="number" class="form-control" id="v_port" name="v_port" value="<?= tohtml($suggested_port) ?>" min="1024" max="65535" required>
							<small class="form-hint"><?= tohtml( _("Port where your app listens. NGINX will automatically forward traffic to it.")) ?></small>
						</div>
					</div>

					<!-- App Source & Entry Script -->
					<div class="node-form-card">
						<div class="node-form-card-title">
							<i class="fas fa-code icon-green"></i>
							<span><?= tohtml( _("Application Source")) ?></span>
						</div>

						<div class="u-mb20">
							<label for="v_path" class="form-label"><?= tohtml( _("Application Directory")) ?></label>
							<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
								<input type="text" class="form-control" id="v_path" name="v_path" value="/home/<?= tohtml($user_plain) ?>/web/<?= tohtml($v_domain ?: 'domain') ?>/public_html/app" style="flex: 1; min-width: 180px;" required onchange="checkPackageJson()" oninput="checkPackageJson()">
								<button type="button" class="button button-secondary" onclick="openFM()" title="<?= tohtml( _("File Manager")) ?>" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px;">
									<i class="fas fa-folder-open icon-green"></i> <span><?= tohtml( _("Files")) ?></span>
								</button>
								<button type="button" class="button button-secondary" id="btn-create-dir" onclick="createAppDir()" title="<?= tohtml( _("Create Folder")) ?>" style="display: <?= !empty($app_dir_exists) ? 'none' : 'inline-flex' ?>; align-items: center; gap: 4px; padding: 6px 12px;">
									<i class="fas fa-folder-plus icon-blue"></i> <span><?= tohtml( _("Create")) ?></span>
								</button>
								<button type="button" class="button button-secondary" onclick="runNpmInstall()" id="btn-npm-install" title="npm install" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px;">
									<svg style="width: 14px; height: 14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
										<path fill="#c12127" d="M7.480775 15.766h3.012825v-1.5064h3.0128V8.23395H7.480775V15.766Zm3.012825 -6.02565H12v3.012825h-1.5064V9.74035Zm4.21795 -1.5064v6.02565h3.0128v-4.51925h1.506425v4.51925h1.5064v-4.51925h1.506425v4.51925H23.75V8.23395H14.71155ZM0.25 14.2596h3.012825v-4.51925h1.5064v4.51925h1.506425V8.23395H0.25v6.02565Z" stroke-width="0.25"/>
									</svg>
									<span>npm install</span>
								</button>
							</div>
							<small class="form-hint"><?= tohtml( _("Full directory path to your Node.js application.")) ?></small>
						</div>

						<div>
							<label for="v_entry" class="form-label"><?= tohtml( _("Entry Script")) ?></label>
							<input type="text" class="form-control" id="v_entry" name="v_entry" value="app.js" placeholder="app.js, server.js, index.js" required>
							<small class="form-hint"><?= tohtml( _("Main entry script or ecosystem.config.js for cluster deployment.")) ?></small>
						</div>
					</div>
				</div>

				<!-- Submit Button Bar -->
				<div style="padding: 0 22px 22px 22px; display: flex; justify-content: flex-end;">
					<button type="submit" name="btn_add" value="1" class="button" style="padding: 10px 24px; font-size: 14px;">
						<i class="fas fa-play icon-purple"></i> <?= tohtml( _("Start Application")) ?>
					</button>
				</div>
			</form>
		</div>
	<?php } ?>
</div>

<!-- Modern Logs Terminal Modal -->
<div id="logs-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
	<div style="background: #181b20; color: #eee; width: 92%; max-width: 980px; height: 82vh; border-radius: 8px; box-shadow: 0 20px 60px rgba(0,0,0,0.6); display: flex; flex-direction: column; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
		<!-- Modal Window Header -->
		<div style="padding: 12px 20px; background: #22262c; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
			<div style="display: flex; align-items: center; gap: 12px;">
				<div style="display: flex; gap: 6px;">
					<span style="width: 11px; height: 11px; border-radius: 50%; background: #ff5f56; display: inline-block;"></span>
					<span style="width: 11px; height: 11px; border-radius: 50%; background: #ffbd2e; display: inline-block;"></span>
					<span style="width: 11px; height: 11px; border-radius: 50%; background: #27c93f; display: inline-block;"></span>
				</div>
				<span style="font-weight: 700; font-size: 14px; letter-spacing: 0.3px; color: #fff;" id="logs-modal-title"><?= tohtml( _("Application Logs")) ?></span>
			</div>
			<!-- Controls -->
			<div style="display: flex; gap: 8px; align-items: center;">
				<select id="logs-type-select" onchange="fetchLogs()" style="background: #2d3239; color: #fff; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; padding: 4px 10px; font-size: 12px;">
					<option value="all"><?= tohtml( _("All Logs")) ?></option>
					<option value="out"><?= tohtml( _("Stdout (Output)")) ?></option>
					<option value="err"><?= tohtml( _("Stderr (Errors)")) ?></option>
				</select>
				<select id="logs-lines-select" onchange="fetchLogs()" style="background: #2d3239; color: #fff; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; padding: 4px 10px; font-size: 12px;">
					<option value="50">50 lines</option>
					<option value="100" selected>100 lines</option>
					<option value="250">250 lines</option>
				</select>
				<button type="button" onclick="fetchLogs()" style="background: #2d3239; color: #fff; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; padding: 5px 10px; cursor: pointer;" title="<?= tohtml( _("Refresh")) ?>">
					<i class="fas fa-arrows-rotate"></i>
				</button>
				<button type="button" onclick="copyLogsToClipboard()" style="background: #2d3239; color: #fff; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; padding: 5px 10px; cursor: pointer;" title="<?= tohtml( _("Copy")) ?>">
					<i class="fas fa-copy"></i>
				</button>
				<button type="button" onclick="closeLogsModal()" style="background: transparent; color: #aaa; border: none; font-size: 18px; cursor: pointer; margin-left: 6px;">
					<i class="fas fa-xmark"></i>
				</button>
			</div>
		</div>
		<!-- Terminal Body -->
		<pre id="logs-terminal-content" style="flex: 1; margin: 0; padding: 20px; background: #0e1116; color: #38ef7d; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; font-size: 12.5px; line-height: 1.5; overflow-y: auto; white-space: pre-wrap; word-break: break-all;"></pre>
	</div>
</div>

<!-- Embedded File Manager Modal -->
<div id="fm-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
	<div style="background: #1e2227; color: #eee; width: 95%; max-width: 1320px; height: 88vh; border-radius: 8px; box-shadow: 0 20px 60px rgba(0,0,0,0.6); display: flex; flex-direction: column; overflow: hidden; border: 1px solid rgba(255,255,255,0.15);">
		<!-- FM Modal Header -->
		<div style="padding: 12px 20px; background: #262b32; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center;">
			<div style="display: flex; align-items: center; gap: 10px;">
				<i class="fas fa-folder-open icon-green" style="font-size: 16px;"></i>
				<span style="font-weight: 700; font-size: 14px; color: #fff;" id="fm-modal-title"><?= tohtml( _("File Manager")) ?></span>
			</div>
			<!-- Controls -->
			<div style="display: flex; gap: 8px; align-items: center;">
				<a href="/fm/" target="_blank" id="fm-external-link" class="button button-secondary" style="padding: 4px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;" title="<?= tohtml( _("Open in New Window")) ?>">
					<i class="fas fa-arrow-up-right-from-square"></i> <span><?= tohtml( _("New Tab")) ?></span>
				</a>
				<button type="button" onclick="refreshFmIframe()" class="button button-secondary" style="padding: 4px 10px; font-size: 12px;" title="<?= tohtml( _("Refresh")) ?>">
					<i class="fas fa-arrows-rotate"></i>
				</button>
				<button type="button" onclick="closeFmModal()" style="background: transparent; color: #aaa; border: none; font-size: 20px; cursor: pointer; margin-left: 6px;" title="<?= tohtml( _("Close")) ?>">
					<i class="fas fa-xmark"></i>
				</button>
			</div>
		</div>
		<!-- FM Iframe Container -->
		<div style="flex: 1; position: relative; background: #fff;">
			<iframe id="fm-iframe" src="about:blank" style="width: 100%; height: 100%; border: none;"></iframe>
		</div>
	</div>
</div>

<script>
let currentLogDomain = '';
let activePollingInterval = null;

function getDomain() {
	return document.getElementById('v_domain') ? document.getElementById('v_domain').value : '';
}

function onDomainChanged() {
	const dom = getDomain();
	const user = "<?= tohtml($user_plain) ?>";
	if (dom) {
		document.getElementById('v_path').value = `/home/${user}/web/${dom}/public_html/app`;
		checkPackageJson();
	}
}

function checkPackageJson() {
	const dom = getDomain();
	const path = document.getElementById('v_path') ? document.getElementById('v_path').value : '';
	if (!dom || !path) {
		const btnCreate = document.getElementById('btn-create-dir');
		if (btnCreate) btnCreate.style.display = 'inline-flex';
		return;
	}

	fetch(`/add/node/?ajax=inspect_app&domain=${encodeURIComponent(dom)}&path=${encodeURIComponent(path)}`)
		.then(r => r.json())
		.then(data => {
			const btnCreate = document.getElementById('btn-create-dir');
			if (btnCreate) {
				// Hide Create Folder button if folder already exists
				btnCreate.style.display = (data && data.dir_exists === true) ? 'none' : 'inline-flex';
			}

			if (data && data.main) {
				document.getElementById('v_entry').value = data.main;
			} else if (data && data.pkg && data.pkg.main) {
				document.getElementById('v_entry').value = data.pkg.main;
			}
		}).catch(() => {});
}

function createAppDir() {
	const dom = getDomain();
	if (!dom) {
		alert("<?= tohtml( _("Please select a domain first.")) ?>");
		return;
	}
	fetch(`/add/node/?ajax=create_app&domain=${encodeURIComponent(dom)}`)
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				document.getElementById('v_path').value = data.path;
				const btnCreate = document.getElementById('btn-create-dir');
				if (btnCreate) btnCreate.style.display = 'none';
				showAjaxAlert('success', "<?= tohtml( _("Directory created successfully!")) ?>");
				checkPackageJson();
			} else {
				showAjaxAlert('danger', "<?= tohtml( _("Failed to create directory.")) ?>");
			}
		})
		.catch(() => {
			showAjaxAlert('danger', "<?= tohtml( _("Request failed.")) ?>");
		});
}

function runNpmInstall() {
	const dom = getDomain();
	const path = document.getElementById('v_path') ? document.getElementById('v_path').value : '';
	if (!dom) {
		alert("<?= tohtml( _("Please select a domain first.")) ?>");
		return;
	}
	const btn = document.getElementById('btn-npm-install');
	const origHtml = btn.innerHTML;
	btn.disabled = true;
	btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

	fetch(`/add/node/?ajax=npm_install&domain=${encodeURIComponent(dom)}&path=${encodeURIComponent(path)}`)
		.then(r => r.json())
		.then(data => {
			btn.disabled = false;
			btn.innerHTML = origHtml;
			if (data.success) {
				showAjaxAlert('success', 'npm install completed successfully.');
			} else {
				showAjaxAlert('danger', 'npm install failed: ' + data.output);
			}
		})
		.catch(() => {
			btn.disabled = false;
			btn.innerHTML = origHtml;
			showAjaxAlert('danger', 'Request failed.');
		});
}

function openFM() {
	const dom = getDomain();
	const pathInput = document.getElementById('v_path');
	let targetPath = `/web/${dom}/public_html/app`;

	if (pathInput && pathInput.value) {
		const val = pathInput.value.trim();
		const webIdx = val.indexOf('/web/');
		if (webIdx !== -1) {
			targetPath = val.substring(webIdx);
		}
	}

	const url = dom ? `/fm/#/?cd=${encodeURIComponent(targetPath)}` : `/fm/`;

	// Open inside Modal
	document.getElementById('fm-modal-title').innerText = `File Manager: ${targetPath}`;
	document.getElementById('fm-external-link').href = url;
	const iframe = document.getElementById('fm-iframe');
	iframe.src = url;
	document.getElementById('fm-modal').style.display = 'flex';
}

function closeFmModal() {
	document.getElementById('fm-modal').style.display = 'none';
	document.getElementById('fm-iframe').src = 'about:blank';
	checkPackageJson();
}

function refreshFmIframe() {
	const iframe = document.getElementById('fm-iframe');
	if (iframe) {
		iframe.src = iframe.src;
	}
}

// Global ESC key listener to close active modals
document.addEventListener('keydown', (e) => {
	if (e.key === 'Escape') {
		closeLogsModal();
		closeFmModal();
	}
});

function showAjaxAlert(type, message) {
	const container = document.getElementById('ajax-alert-container');
	container.innerHTML = `
		<div class="alert alert-${type} u-mb20" role="alert">
			<i class="fas fa-${type === 'success' ? 'check' : 'triangle-exclamation'}"></i>
			<div><p>${message}</p></div>
		</div>
	`;
	setTimeout(() => { container.innerHTML = ''; }, 6000);
}

// Action executor with Instant Feedback
function runAjaxAction(action, domain, btnEl) {
	const iconEl = btnEl.querySelector('i');
	const origClass = iconEl ? iconEl.className : '';
	if (iconEl) iconEl.className = 'fas fa-spinner fa-spin';
	btnEl.disabled = true;

	const token = document.getElementById('csrf_token') ? document.getElementById('csrf_token').value : '';

	fetch(`/add/node/?ajax=ajax_action&action=${encodeURIComponent(action)}&domain=${encodeURIComponent(domain)}&token=${encodeURIComponent(token)}`)
		.then(r => r.json())
		.then(data => {
			btnEl.disabled = false;
			if (iconEl) iconEl.className = origClass;
			if (data.success) {
				showAjaxAlert('success', data.message);
				fetchRealtimeStats();
			} else {
				showAjaxAlert('danger', data.message);
			}
		})
		.catch(() => {
			btnEl.disabled = false;
			if (iconEl) iconEl.className = origClass;
			showAjaxAlert('danger', 'Action request failed.');
		});
}

function confirmDeleteApp(domain, btnEl) {
	if (confirm("<?= tohtml( _("Are you sure you want to delete this application?")) ?>")) {
		runAjaxAction('delete', domain, btnEl);
	}
}

// Modal Log Viewer
function openLogsModal(domain) {
	currentLogDomain = domain;
	document.getElementById('logs-modal-title').innerText = `Logs: ${domain}`;
	document.getElementById('logs-terminal-content').innerText = 'Loading logs...';
	document.getElementById('logs-modal').style.display = 'flex';
	fetchLogs();
}

function closeLogsModal() {
	document.getElementById('logs-modal').style.display = 'none';
	currentLogDomain = '';
}

function fetchLogs() {
	if (!currentLogDomain) return;
	const lines = document.getElementById('logs-lines-select').value;
	const type = document.getElementById('logs-type-select').value;
	const terminal = document.getElementById('logs-terminal-content');

	fetch(`/add/node/?ajax=get_logs&domain=${encodeURIComponent(currentLogDomain)}&lines=${encodeURIComponent(lines)}&type=${encodeURIComponent(type)}`)
		.then(r => r.json())
		.then(data => {
			terminal.innerText = data.logs || 'No logs available.';
			terminal.scrollTop = terminal.scrollHeight;
		})
		.catch(() => {
			terminal.innerText = 'Failed to fetch logs.';
		});
}

function copyLogsToClipboard() {
	const text = document.getElementById('logs-terminal-content').innerText;
	navigator.clipboard.writeText(text).then(() => {
		alert('Logs copied to clipboard!');
	});
}

// Real-time Poller Loop (every 4 seconds)
function fetchRealtimeStats() {
	fetch('/add/node/?ajax=get_stats')
		.then(r => r.json())
		.then(data => {
			if (!data.success || !Array.isArray(data.apps)) return;

			const countEl = document.getElementById('metric-active-count');
			if (countEl) countEl.innerText = data.apps.length;

			data.apps.forEach(app => {
				const row = document.getElementById(`app-row-${app.name}`);
				if (row) {
					const badge = row.querySelector('.app-status-badge');
					if (badge) {
						badge.innerText = app.status;
						badge.className = `node-status-pill app-status-badge ${app.status === 'online' ? 'online' : (app.status === 'stopped' ? 'stopped' : 'errored')}`;
					}
					const cpu = row.querySelector('.app-cpu-val');
					if (cpu) cpu.innerText = app.cpu;
					const mem = row.querySelector('.app-mem-val');
					if (mem) mem.innerText = app.memory;
					const uptime = row.querySelector('.app-uptime-val');
					if (uptime) uptime.innerText = app.uptime;
				}
			});
		})
		.catch(() => {});
}

// Start polling and initial inspection
document.addEventListener('DOMContentLoaded', () => {
	activePollingInterval = setInterval(fetchRealtimeStats, 4000);
	checkPackageJson();
});
</script>
