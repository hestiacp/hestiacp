<!-- Begin toolbar -->
<div class="toolbar">
	<div class="toolbar-inner">
		<div class="toolbar-buttons">
			<a class="button button-secondary" id="btn-back" href="/list/cron/">
				<i class="fas fa-arrow-left status-icon blue"></i><?= _("Back") ?>
			</a>
		</div>
		<div class="toolbar-buttons">
			<button class="button" type="submit" form="vstobjects">
				<i class="fas fa-floppy-disk status-icon purple"></i><?= _("Save") ?>
			</button>
		</div>
	</div>
</div>
<!-- End toolbar -->

<div class="container animate__animated animate__fadeIn">

	<div class="cron-helper-container">
		<div id="tabs" class="cron-helper-tabs">
			<ul>
				<li><a href="#tabs-1"><?= _("Minutes") ?></a></li>
				<li><a href="#tabs-2"><?= _("Hourly") ?></a></li>
				<li><a href="#tabs-3"><?= _("Daily") ?></a></li>
				<li><a href="#tabs-4"><?= _("Weekly") ?></a></li>
				<li><a href="#tabs-5"><?= _("Monthly") ?></a></li>
			</ul>
			<div id="tabs-1">
				<form>
					<input type="hidden" name="h_hour" value="*">
					<input type="hidden" name="h_day" value="*">
					<input type="hidden" name="h_month" value="*">
					<input type="hidden" name="h_wday" value="*">
					<div class="u-mt10 u-mb20">
						<label for="h_min_1" class="form-label first"><?= _("Run Command") ?>:</label>
						<select class="form-select" name="h_min" id="h_min_1">
							<option value="*" selected="selected"><?= _("every minute") ?></option>
							<option value="*/2"><?= _("every two minutes") ?></option>
							<option value="*/5"><?= _("every") ?> 5</option>
							<option value="*/10"><?= _("every") ?> 10</option>
							<option value="*/15"><?= _("every") ?> 15</option>
							<option value="*/30"><?= _("every") ?> 30</option>
						</select>
					</div>
					<div class="u-pt10">
						<button type="submit" class="button button-secondary">
							<?= _("generate") ?>
						</button>
					</div>
				</form>
			</div>

			<div id="tabs-2">
				<form>
					<input type="hidden" name="h_day" value="*">
					<input type="hidden" name="h_month" value="*">
					<input type="hidden" name="h_wday" value="*">
					<div class="u-mt10 u-mb10">
						<label for="h_hour_2" class="form-label first"><?= _("Run Command") ?>:</label>
						<select class="form-select" name="h_hour" id="h_hour_2">
							<option value="*" selected="selected"><?= _("every hour") ?></option>
							<option value="*/2"><?= _("every two hours") ?></option>
							<option value="*/6"><?= _("every") ?> 6</option>
							<option value="*/12"><?= _("every") ?> 12</option>
						</select>
					</div>
					<div class="u-mb20">
						<label for="h_min_2" class="form-label first"><?= _("Minute") ?>:</label>
						<select class="form-select" name="h_min" id="h_min_2" style="width:70px;">
							<option value="0" selected="selected">00</option>
							<option value="15">15</option>
							<option value="30">30</option>
							<option value="45">45</option>
						</select>
					</div>
					<div class="u-pt10">
						<button type="submit" class="button button-secondary">
							<?= _("generate") ?>
						</button>
					</div>
				</form>
			</div>

			<div id="tabs-3">
				<form>
					<input type="hidden" name="h_month" value="*">
					<input type="hidden" name="h_wday" value="*">
					<div class="u-mt10 u-mb10">
						<label for="h_day_3" class="form-label first"><?= _("Run Command") ?>:</label>
						<select class="form-select" name="h_day" id="h_day_3">
							<option value="*" selected="selected"><?= _("every day") ?></option>
							<option value="1-31/2"><?= _("every odd day") ?></option>
							<option value="*/2"><?= _("every even day") ?></option>
							<option value="*/3"><?= _("every") ?> 3</option>
							<option value="*/5"><?= _("every") ?> 5</option>
							<option value="*/10"><?= _("every") ?> 10</option>
							<option value="*/15"><?= _("every") ?> 15</option>
						</select>
					</div>
					<div class="u-mb20">
						<label for="h_hour_3" class="form-label first"><?= _("Hour") ?>:</label>
						<select class="form-select" name="h_hour" id="h_hour_3" style="width:70px;">
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
						<select class="form-select" name="h_min" id="h_min_3" style="width:70px;">
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
						<button type="submit" class="button button-secondary">
							<?= _("generate") ?>
						</button>
					</div>
				</form>
			</div>

			<div id="tabs-4">
				<form>
					<input type="hidden" name="h_month" value="*">
					<input type="hidden" name="h_day" value="*">
					<div class="u-mt10 u-mb10">
						<label for="h_wday_4" class="form-label first"><?= _("Run Command") ?>:</label>
						<select class="form-select" name="h_wday" id="h_wday_4">
							<option value="*" selected="selected"><?= _("every day") ?></option>
							<option value="1,2,3,4,5"><?= _("weekdays (5 days)") ?></option>
							<option value="0,6"><?= _("weekend (2 days)") ?></option>
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
						<select class="form-select" name="h_hour" id="h_hour_4" style="width:70px;">
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
						<select class="form-select" name="h_min" id="h_min_4" style="width:70px;">
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
						<button type="submit" class="button button-secondary">
							<?= _("generate") ?>
						</button>
					</div>
				</form>
			</div>

			<div id="tabs-5">
				<form>
					<input type="hidden" name="h_wday" value="*">
					<div class="u-mt10 u-mb10">
						<label for="h_month_5" class="form-label first"><?= _("Run Command") ?>:</label>
						<select class="form-select" name="h_month" id="h_month_5">
							<option value="*" selected="selected"><?= _("every month") ?></option>
							<option value="1-11/2"><?= _("every odd month") ?></option>
							<option value="*/2"><?= _("every even month") ?></option>
							<option value="*/3"><?= _("every") ?> 3</option>
							<option value="*/6"><?= _("every") ?> 6</option>
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
						<label for="h_day_5" class="form-label first"><?= _("Date") ?>:</label>
						<select class="form-select" name="h_day" id="h_day_5" style="width:70px;">
							<option value="1" selected="selected">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
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
						<select class="form-select" name="h_hour" id="h_hour_5" style="width:70px;">
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
						<select class="form-select" name="h_min" id="h_min_5" style="width:70px;">
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
						<button type="submit" class="button button-secondary">
							<?= _("generate") ?>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<form id="vstobjects" name="v_add_cron" method="post">
		<input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
		<input type="hidden" name="ok" value="Add">

		<div class="form-container">
			<h1 class="form-title"><?= _("Adding Cron Job") ?></h1>
			<?php show_alert_message($_SESSION); ?>
			<div class="u-mb20">
				<label for="v_cmd" class="form-label"><?= _("Command") ?></label>
				<input type="text" class="form-control" name="v_cmd" id="v_cmd" value="<?= htmlentities(trim($v_cmd, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_min" class="form-label"><?= _("Minute") ?></label>
				<input type="text" class="form-control" name="v_min" id="v_min" style="width:220px;" value="<?= htmlentities(trim($v_min, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_hour" class="form-label"><?= _("Hour") ?></label>
				<input type="text" class="form-control" name="v_hour" id="v_hour" style="width:220px;" value="<?= htmlentities(trim($v_hour, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_day" class="form-label"><?= _("Day") ?></label>
				<input type="text" class="form-control" name="v_day" id="v_day" style="width:220px;" value="<?= htmlentities(trim($v_day, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_month" class="form-label"><?= _("Month") ?></label>
				<input type="text" class="form-control" name="v_month" id="v_month" style="width:220px;" value="<?= htmlentities(trim($v_month, "'")) ?>">
			</div>
			<div class="u-mb10">
				<label for="v_wday" class="form-label"><?= _("Day of week") ?></label>
				<input type="text" class="form-control" name="v_wday" id="v_wday" style="width:220px;" value="<?= htmlentities(trim($v_wday, "'")) ?>">
			</div>
		</div>

	</form>

</div>
