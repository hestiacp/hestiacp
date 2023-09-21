<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary button-back js-button-back" href="/list/cron/">
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

	<form id="main-form" name="v_edit_cron" method="post" class="<?= $v_status ?>">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="save" value="save">

		<div class="form-container form-container-wide">
			<h1 class="u-mb20"><?= _("Edit Cron Job") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb20">
				<label for="v_cmd" class="form-label"><?= _("Command") ?></label>
				<input type="text" class="form-control" name="v_cmd" id="v_cmd" value="<?= htmlentities(trim($v_cmd, "'")) ?>">
			</div>
			<div class="sidebar-left-grid">
				<div class="sidebar-left-grid-sidebar">
					<div class="u-mb10">
						<label for="v_min" class="form-label"><?= _("Minute") ?></label>
						<input type="text" class="form-control" name="v_min" id="v_min" value="<?= htmlentities(trim($v_min, "'")) ?>">
					</div>
					<div class="u-mb10">
						<label for="v_hour" class="form-label"><?= _("Hour") ?></label>
						<input type="text" class="form-control" name="v_hour" id="v_hour" value="<?= htmlentities(trim($v_hour, "'")) ?>">
					</div>
					<div class="u-mb10">
						<label for="v_day" class="form-label"><?= _("Day") ?></label>
						<input type="text" class="form-control" name="v_day" id="v_day" value="<?= htmlentities(trim($v_day, "'")) ?>">
					</div>
					<div class="u-mb10">
						<label for="v_month" class="form-label"><?= _("Month") ?></label>
						<input type="text" class="form-control" name="v_month" id="v_month" value="<?= htmlentities(trim($v_month, "'")) ?>">
					</div>
					<div class="u-mb10">
						<label for="v_wday" class="form-label"><?= _("Day of Week") ?></label>
						<input type="text" class="form-control" name="v_wday" id="v_wday" value="<?= htmlentities(trim($v_wday, "'")) ?>">
					</div>
				</div>
				<div class="sidebar-left-grid-content">
					<div class="tabs cron-tabs js-tabs">
						<div class="tabs-items" role="tablist">
							<button type="button" class="tabs-item" id="tab-one" role="tab" tabindex="0" aria-selected="true"><?= _("Minutes") ?></button>
							<button type="button" class="tabs-item" id="tab-two" role="tab" tabindex="-1"><?= _("Hourly") ?></button>
							<button type="button" class="tabs-item" id="tab-three" role="tab" tabindex="-1"><?= _("Daily") ?></button>
							<button type="button" class="tabs-item" id="tab-four" role="tab" tabindex="-1"><?= _("Weekly") ?></button>
							<button type="button" class="tabs-item" id="tab-five" role="tab" tabindex="-1"><?= _("Monthly") ?></button>
						</div>
						<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-one" tabindex="0">
							<fieldset>
								<input type="hidden" name="h_hour" value="*" form="">
								<input type="hidden" name="h_day" value="*" form="">
								<input type="hidden" name="h_month" value="*" form="">
								<input type="hidden" name="h_wday" value="*" form="">
								<div class="u-mt10 u-mb20">
									<label for="h_min_1" class="form-label first"><?= _("Run Command") ?>:</label>
									<select class="form-select" name="h_min" id="h_min_1" form="">
										<option value="*" selected="selected"><?= _("Every minute") ?></option>
										<option value="*/2"><?= sprintf(_("Every %s minutes"), 2) ?></option>
										<option value="*/5"><?= sprintf(_("Every %s minutes"), 5) ?></option>
										<option value="*/10"><?= sprintf(_("Every %s minutes"), 10) ?></option>
										<option value="*/15"><?= sprintf(_("Every %s minutes"), 15) ?></option>
										<option value="*/30"><?= sprintf(_("Every %s minutes"), 30) ?></option>
									</select>
								</div>
								<div class="u-pt10">
									<button type="button" class="button button-secondary js-generate-cron">
										<?= _("Generate") ?>
									</button>
								</div>
							</fieldset>
						</div>
						<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-two" tabindex="0" hidden>
							<fieldset>
								<input type="hidden" name="h_day" value="*" form="">
								<input type="hidden" name="h_month" value="*" form="">
								<input type="hidden" name="h_wday" value="*" form="">
								<div class="u-mt10 u-mb10">
									<label for="h_hour_2" class="form-label first"><?= _("Run Command") ?>:</label>
									<select class="form-select" name="h_hour" id="h_hour_2" form="">
										<option value="*" selected="selected"><?= _("Every hour") ?></option>
										<option value="*/2"><?= sprintf(_("Every %s hours"), 2) ?></option>
										<option value="*/6"><?= sprintf(_("Every %s hours"), 6) ?></option>
										<option value="*/12"><?= sprintf(_("Every %s hours"), 12) ?></option>
									</select>
								</div>
								<div class="u-mb20">
									<label for="h_min_2" class="form-label first"><?= _("Minute") ?>:</label>
									<select class="form-select" name="h_min" id="h_min_2" style="width:70px;" form="">
										<option value="0" selected="selected">00</option>
										<option value="15">15</option>
										<option value="30">30</option>
										<option value="45">45</option>
									</select>
								</div>
								<div class="u-pt10">
									<button type="button" class="button button-secondary js-generate-cron">
										<?= _("Generate") ?>
									</button>
								</div>
							</fieldset>
						</div>
						<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-three" tabindex="0" hidden>
							<fieldset>
								<input type="hidden" name="h_month" value="*" form="">
								<input type="hidden" name="h_wday" value="*" form="">
								<div class="u-mt10 u-mb10">
									<label for="h_day_3" class="form-label first"><?= _("Run Command") ?>:</label>
									<select class="form-select" name="h_day" id="h_day_3" form="">
										<option value="*" selected="selected"><?= _("Every day") ?></option>
										<option value="1-31/2"><?= _("Every odd day") ?></option>
										<option value="*/2"><?= _("Every even day") ?></option>
										<option value="*/3"><?= sprintf(_("Every %s days"), 2) ?></option>
										<option value="*/5"><?= sprintf(_("Every %s days"), 5) ?></option>
										<option value="*/10"><?= sprintf(_("Every %s days"), 10) ?></option>
										<option value="*/15"><?= sprintf(_("Every %s days"), 15) ?></option>
									</select>
								</div>
								<div class="u-mb20">
									<label for="h_hour_3" class="form-label first"><?= _("Hour") ?>:</label>
									<select class="form-select" name="h_hour" id="h_hour_3" style="width:70px;" form="">
										<option value="0">00</option>
										<option value="1">01</option>
										<option value="2">02</option>
										<option value="3">03</option>
										<option value="4">04</option>
										<option value="5">05</option>
										<option value="6">06</option>
										<option value="7">07</option>
										<option value="8">08</option>
										<option value="9">09</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12" selected="selected">12</option>
										<option value="13">13</option>
										<option value="14">14</option>
										<option value="15">15</option>
										<option value="16">16</option>
										<option value="17">17</option>
										<option value="18">18</option>
										<option value="19">19</option>
										<option value="20">20</option>
										<option value="21">21</option>
										<option value="22">22</option>
										<option value="23">23</option>
									</select>
									<label for="h_min_3" class="form-label"><?= _("Minute") ?>:</label>
									<select class="form-select" name="h_min" id="h_min_3" style="width:70px;" form="">
										<option value="0" selected="selected">00</option>
										<option value="1">01</option>
										<option value="2">02</option>
										<option value="5">05</option>
										<option value="10">10</option>
										<option value="15">15</option>
										<option value="20">20</option>
										<option value="25">25</option>
										<option value="30">30</option>
										<option value="35">35</option>
										<option value="40">40</option>
										<option value="45">45</option>
										<option value="50">50</option>
										<option value="55">55</option>
									</select>
								</div>
								<div class="u-pt10">
									<button type="button" class="button button-secondary js-generate-cron">
										<?= _("Generate") ?>
									</button>
								</div>
							</fieldset>
						</div>
						<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-four" tabindex="0" hidden>
							<fieldset>
								<input type="hidden" name="h_month" value="*" form="">
								<input type="hidden" name="h_day" value="*" form="">
								<div class="u-mt10 u-mb10">
									<label for="h_wday_4" class="form-label first"><?= _("Run Command") ?>:</label>
									<select class="form-select" name="h_wday" id="h_wday_4" form="">
										<option value="*" selected="selected"><?= _("Every day") ?></option>
										<option value="1,2,3,4,5"><?= _("Weekdays (5 days)") ?></option>
										<option value="0,6"><?= _("Weekend (2 days)") ?></option>
										<option value="1"><?= _("Monday") ?></option>
										<option value="2"><?= _("Tuesday") ?></option>
										<option value="3"><?= _("Wednesday") ?></option>
										<option value="4"><?= _("Thursday") ?></option>
										<option value="5"><?= _("Friday") ?></option>
										<option value="6"><?= _("Saturday") ?></option>
										<option value="0"><?= _("Sunday") ?></option>
									</select>
								</div>
								<div class="u-mb20">
									<label for="h_hour_4" class="form-label first"><?= _("Hour") ?>:</label>
									<select class="form-select" name="h_hour" id="h_hour_4" style="width:70px;" form="">
										<option value="0">00</option>
										<option value="1">01</option>
										<option value="2">02</option>
										<option value="3">03</option>
										<option value="4">04</option>
										<option value="5">05</option>
										<option value="6">06</option>
										<option value="7">07</option>
										<option value="8">08</option>
										<option value="9">09</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12" selected="selected">12</option>
										<option value="13">13</option>
										<option value="14">14</option>
										<option value="15">15</option>
										<option value="16">16</option>
										<option value="17">17</option>
										<option value="18">18</option>
										<option value="19">19</option>
										<option value="20">20</option>
										<option value="21">21</option>
										<option value="22">22</option>
										<option value="23">23</option>
									</select>
									<label for="h_min_4" class="form-label"><?= _("Minute") ?>:</label>
									<select class="form-select" name="h_min" id="h_min_4" style="width:70px;" form="">
										<option value="0" selected="selected">00</option>
										<option value="1">01</option>
										<option value="2">02</option>
										<option value="5">05</option>
										<option value="10">10</option>
										<option value="15">15</option>
										<option value="20">20</option>
										<option value="25">25</option>
										<option value="30">30</option>
										<option value="35">35</option>
										<option value="40">40</option>
										<option value="45">45</option>
										<option value="50">50</option>
										<option value="55">55</option>
									</select>
								</div>
								<div class="u-pt10">
									<button type="button" class="button button-secondary js-generate-cron">
										<?= _("Generate") ?>
									</button>
								</div>
							</fieldset>
						</div>
						<div class="tabs-panel" role="tabpanel" aria-labelledby="tab-five" tabindex="0" hidden>
							<fieldset>
								<input type="hidden" name="h_wday" value="*" form="">
								<div class="u-mt10 u-mb10">
									<label for="h_month_5" class="form-label first"><?= _("Run Command") ?>:</label>
									<select class="form-select" name="h_month" id="h_month_5" form="">
										<option value="*" selected="selected"><?= _("Every month") ?></option>
										<option value="1-11/2"><?= _("Every odd month") ?></option>
										<option value="*/2"><?= _("Every even month") ?></option>
										<option value="*/3"><?= sprintf(_("Every %s months"), 3) ?></option>
										<option value="*/6"><?= sprintf(_("Every %s months"), 6) ?></option>
										<option value="1"><?= _("Jan") ?></option>
										<option value="2"><?= _("Feb") ?></option>
										<option value="3"><?= _("Mar") ?></option>
										<option value="4"><?= _("Apr") ?></option>
										<option value="5"><?= _("May") ?></option>
										<option value="6"><?= _("Jun") ?></option>
										<option value="7"><?= _("Jul") ?></option>
										<option value="8"><?= _("Aug") ?></option>
										<option value="9"><?= _("Sep") ?></option>
										<option value="10"><?= _("Oct") ?></option>
										<option value="11"><?= _("Nov") ?></option>
										<option value="12"><?= _("Dec") ?></option>
									</select>
								</div>
								<div class="u-mb20">
									<label for="h_day_5" class="form-label first"><?= _("Day") ?>:</label>
									<select class="form-select" name="h_day" id="h_day_5" style="width:70px;" form="">
										<option value="1" selected="selected">01</option>
										<option value="2">02</option>
										<option value="3">03</option>
										<option value="4">04</option>
										<option value="5">05</option>
										<option value="6">06</option>
										<option value="7">07</option>
										<option value="8">08</option>
										<option value="9">09</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12">12</option>
										<option value="13">13</option>
										<option value="14">14</option>
										<option value="15">15</option>
										<option value="16">16</option>
										<option value="17">17</option>
										<option value="18">18</option>
										<option value="19">19</option>
										<option value="20">20</option>
										<option value="21">21</option>
										<option value="22">22</option>
										<option value="23">23</option>
										<option value="24">24</option>
										<option value="25">25</option>
										<option value="26">26</option>
										<option value="27">27</option>
										<option value="28">28</option>
										<option value="29">29</option>
										<option value="30">30</option>
										<option value="31">31</option>
									</select>
									<label for="h_hour_5" class="form-label"><?= _("Hour") ?>:</label>
									<select class="form-select" name="h_hour" id="h_hour_5" style="width:70px;" form="">
										<option value="0">00</option>
										<option value="1">01</option>
										<option value="2">02</option>
										<option value="3">03</option>
										<option value="4">04</option>
										<option value="5">05</option>
										<option value="6">06</option>
										<option value="7">07</option>
										<option value="8">08</option>
										<option value="9">09</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12" selected="selected">12</option>
										<option value="13">13</option>
										<option value="14">14</option>
										<option value="15">15</option>
										<option value="16">16</option>
										<option value="17">17</option>
										<option value="18">18</option>
										<option value="19">19</option>
										<option value="20">20</option>
										<option value="21">21</option>
										<option value="22">22</option>
										<option value="23">23</option>
									</select>
									<label for="h_min_5" class="form-label"><?= _("Minute") ?>:</label>
									<select class="form-select" name="h_min" id="h_min_5" style="width:70px;" form="">
										<option value="0" selected="selected">00</option>
										<option value="1">01</option>
										<option value="2">02</option>
										<option value="5">05</option>
										<option value="10">10</option>
										<option value="15">15</option>
										<option value="20">20</option>
										<option value="25">25</option>
										<option value="30">30</option>
										<option value="35">35</option>
										<option value="40">40</option>
										<option value="45">45</option>
										<option value="50">50</option>
										<option value="55">55</option>
									</select>
								</div>
								<div class="u-pt10">
									<button type="button" class="button button-secondary js-generate-cron">
										<?= _("Generate") ?>
									</button>
								</div>
							</fieldset>
						</div>
					</div>
				</div>
			</div>
		</div>

	</form>

</div>
