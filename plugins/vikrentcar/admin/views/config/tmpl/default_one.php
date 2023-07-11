<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

JHtml::_('jquery.framework', true, true);
JHtml::_('script', VRC_SITE_URI.'resources/jquery-ui.sortable.min.js');

$config = VRCFactory::getConfig();

$vrc_app  = VikRentCar::getVrcApplication();
$timeopst = VikRentCar::getTimeOpenStore(true);
$openat   = array(0, 0);
$closeat  = array(0, 0);
$alwopen  = true;
if (is_array($timeopst) && $timeopst[0] != $timeopst[1]) {
	$openat  = VikRentCar::getHoursMinutes($timeopst[0]);
	$closeat = VikRentCar::getHoursMinutes($timeopst[1]);
	$alwopen = false;
}
$calendartype = VikRentCar::calendarType(true);
$aehourschbasp = VikRentCar::applyExtraHoursChargesBasp();
$damageshowtype = VikRentCar::getDamageShowType();
$nowdf = VikRentCar::getDateFormat(true);
$nowtf = VikRentCar::getTimeFormat(true);

$maxdatefuture = VikRentCar::getMaxDateFuture(true);
$maxdate_val = intval(substr($maxdatefuture, 1, (strlen($maxdatefuture) - 1)));
$maxdate_interval = substr($maxdatefuture, -1, 1);

$vrcsef = file_exists(VRC_SITE_PATH.DS.'router.php');
?>

<div class="vrc-config-maintab-left">
	<fieldset class="adminform">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VRCCONFIGBOOKINGPART'); ?></legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONEFIVE'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('allowrent', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::allowRent(), 1, 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONESIX'); ?></div>
					<div class="vrc-param-setting"><textarea name="disabledrentmsg" rows="5" cols="50"><?php echo JHtml::_('esc_textarea', VikRentCar::getDisabledRentMsg()); ?></textarea></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONETENSIX'); ?></div>
					<div class="vrc-param-setting"><input type="text" name="adminemail" value="<?php echo JHtml::_('esc_attr', VikRentCar::getAdminMail()); ?>" size="30"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGSENDERMAIL'); ?></div>
					<div class="vrc-param-setting"><input type="text" name="senderemail" value="<?php echo JHtml::_('esc_attr', VikRentCar::getSenderMail()); ?>" size="30"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONESEVEN'); ?></div>
					<div class="vrc-param-setting">&nbsp;</div>
				</div>
				<div class="vrc-param-container vrc-param-nested">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONEONE'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('timeopenstorealw', JText::_('VRYES'), JText::_('VRNO'), ($alwopen ? 'yes' : 0), 'yes', 0, 'toggleOpeningTime(this.checked);'); ?></div>
				</div>
				<div class="vrc-param-container vrc-param-nested" id="vrc-opening-time" style="<?php echo $alwopen ? 'display: none;' : ''; ?>">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONETWO'); ?></div>
					<div class="vrc-param-setting">
						<div style="display: block; margin-bottom: 3px;">
							<span class="vrcrestrdrangesp"><?php echo JText::_('VRCONFIGONETHREE'); ?></span>
							<select name="timeopenstorefh">
							<?php
							for ($i = 0; $i <= 23; $i++) {
								$in = $i < 10 ? ("0" . $i) : $i;
								?>
								<option value="<?php echo $i; ?>"<?php echo $openat[0] == $i ? ' selected="selected"' : ''; ?>><?php echo $in; ?></option>
								<?php
							}
							?>
							</select>
							&nbsp;
							<select name="timeopenstorefm">
							<?php
							for ($i = 0; $i <= 59; $i++) {
								$in = $i < 10 ? ("0" . $i) : $i;
								?>
								<option value="<?php echo $i; ?>"<?php echo $openat[1] == $i ? ' selected="selected"' : ''; ?>><?php echo $in; ?></option>
								<?php
							}
							?>
							</select>
						</div>
						<div style="display: block; margin-bottom: 3px;">
							<span class="vrcrestrdrangesp"><?php echo JText::_('VRCONFIGONEFOUR'); ?></span>
							<select name="timeopenstoreth">
							<?php
							for ($i = 0; $i <= 23; $i++) {
								$in = $i < 10 ? ("0" . $i) : $i;
								?>
								<option value="<?php echo $i; ?>"<?php echo $closeat[0] == $i ? ' selected="selected"' : ''; ?>><?php echo $in; ?></option>
								<?php
							}
							?>
							</select>
							&nbsp;
							<select name="timeopenstoretm">
							<?php
							for ($i = 0; $i <= 59; $i++) {
								$in = $i < 10 ? ("0" . $i) : $i;
								?>
								<option value="<?php echo $i; ?>"<?php echo $closeat[1] == $i ? ' selected="selected"' : ''; ?>><?php echo $in; ?></option>
								<?php
							}
							?>
							</select>
						</div>
					</div>
				</div>
				<?php
				$forced_pickup  = $config->get('forced_pickup', '');
				$forced_dropoff = $config->get('forced_dropoff', '');
				$is_forced_time = (strlen($forced_pickup) || strlen($forced_dropoff));
				?>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRC_FORCE_PICKDROP_TIMES'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('forcedtimes', JText::_('VRYES'), JText::_('VRNO'), (int)$is_forced_time, 1, 0, 'toggleForcedTimes(this.checked);'); ?></div>
				</div>
				<div class="vrc-param-container vrc-param-nested vrc-forcedtimes" style="<?php echo !$is_forced_time ? 'display: none;' : ''; ?>">
					<div class="vrc-param-label"><?php echo JText::_('VRC_FORCE_PICK_TIME'); ?></div>
					<div class="vrc-param-setting">
						<select name="forced_pickup">
							<option value=""></option>
					<?php
					for ($h = 0; $h < 24; $h++) {
						for ($m = 0; $m < 60; $m += 15) {
							$say_value = (string)(($h * 3600) + ($m * 60));
							$say_time  = ($h < 10 ? '0' : '') . $h . ':' . ($m < 10 ? '0' : '') . $m;
							?>
							<option value="<?php echo $say_value; ?>"<?php echo $say_value == $forced_pickup ? ' selected="selected"' : ''; ?>><?php echo $say_time; ?></option>
							<?php
						}
					}
					?>
						</select>
					</div>
				</div>
				<div class="vrc-param-container vrc-param-nested vrc-forcedtimes" style="<?php echo !$is_forced_time ? 'display: none;' : ''; ?>">
					<div class="vrc-param-label"><?php echo JText::_('VRC_FORCE_DROP_TIME'); ?></div>
					<div class="vrc-param-setting">
						<select name="forced_dropoff">
							<option value=""></option>
					<?php
					for ($h = 0; $h < 24; $h++) {
						for ($m = 0; $m < 60; $m += 15) {
							$say_value = (string)(($h * 3600) + ($m * 60));
							$say_time  = ($h < 10 ? '0' : '') . $h . ':' . ($m < 10 ? '0' : '') . $m;
							?>
							<option value="<?php echo $say_value; ?>"<?php echo $say_value == $forced_dropoff ? ' selected="selected"' : ''; ?>><?php echo $say_time; ?></option>
							<?php
						}
					}
					?>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONEELEVEN'); ?></div>
					<div class="vrc-param-setting">
						<select name="dateformat">
							<option value="%d/%m/%Y"<?php echo ($nowdf == "%d/%m/%Y" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGONETWELVE'); ?></option>
							<option value="%Y/%m/%d"<?php echo ($nowdf=="%Y/%m/%d" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGONETENTHREE'); ?></option>
							<option value="%m/%d/%Y"<?php echo ($nowdf == "%m/%d/%Y" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGUSDATEFORMAT'); ?></option>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGTIMEFORMAT'); ?></div>
					<div class="vrc-param-setting">
						<select name="timeformat">
							<option value="H:i"<?php echo ($nowtf=="H:i" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGTIMEFORMATLAT'); ?></option>
							<option value="h:i A"<?php echo ($nowtf=="h:i A" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGTIMEFORMATENG'); ?></option>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONEEIGHT'); ?></div>
					<div class="vrc-param-setting"><input type="number" name="hoursmorerentback" value="<?php echo VikRentCar::getHoursMoreRb(); ?>" min="0"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGEHOURSBASP'); ?></div>
					<div class="vrc-param-setting">
						<select name="ehourschbasp">
							<option value="1"<?php echo ($aehourschbasp == true ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGEHOURSBEFORESP'); ?></option>
							<option value="0"<?php echo ($aehourschbasp == false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCONFIGEHOURSAFTERSP'); ?></option>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCCONFIGDAMAGESHOWTYPE'); ?></div>
					<div class="vrc-param-setting">
						<select name="damageshowtype">
							<option value="1"<?php echo ($damageshowtype == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCCONFIGDAMAGETYPEONE'); ?></option>
							<option value="2"<?php echo ($damageshowtype == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCCONFIGDAMAGETYPETWO'); ?></option>
							<option value="3"<?php echo ($damageshowtype == 3 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRCCONFIGDAMAGETYPETHREE'); ?></option>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONENINE'); ?></div>
					<div class="vrc-param-setting"><input type="number" name="hoursmorecaravail" value="<?php echo VikRentCar::getHoursCarAvail(); ?>" min="0"/> <?php echo JText::_('VRCONFIGONETENEIGHT'); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCPICKONDROP'); ?> <?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPICKONDROP'), 'content' => JText::_('VRCPICKONDROPHELP'))); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('pickondrop', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::allowPickOnDrop(true), 1, 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCTODAYBOOKINGS'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('todaybookings', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::todayBookings(), 1, 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRC_AUTO_ASSIGN_CUNIT'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('autocarunit', JText::_('VRYES'), JText::_('VRNO'), (int)$config->get('autocarunit', 1), 1, 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONECOUPONS'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('enablecoupons', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::couponsEnabled(), 1, 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGENABLECUSTOMERPIN'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('enablepin', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::customersPinEnabled(), 1, 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONETENFIVE'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('tokenform', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::tokenForm() ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGREQUIRELOGIN'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('requirelogin', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::requireLogin(), 1, 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCICALKEY'); ?></div>
					<div class="vrc-param-setting"><input type="text" name="icalkey" value="<?php echo JHtml::_('esc_attr', VikRentCar::getIcalSecretKey()); ?>" size="10"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONETENSEVEN'); ?></div>
					<div class="vrc-param-setting"><input type="number" name="minuteslock" value="<?php echo VikRentCar::getMinutesLock(); ?>" min="0"/></div>
				</div>
			</div>
		</div>
	</fieldset>
</div>

<div class="vrc-config-maintab-right">

	<fieldset class="adminform">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VRCCONFIGSEARCHPART'); ?></legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONEDROPDPLUS'); ?></div>
					<div class="vrc-param-setting"><input type="number" name="setdropdplus" value="<?php echo VikRentCar::setDropDatePlus(true); ?>" min="0"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGMINDAYSADVANCE'); ?></div>
					<div class="vrc-param-setting"><input type="number" name="mindaysadvance" value="<?php echo VikRentCar::getMinDaysAdvance(true); ?>" min="0"/></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGMAXDATEFUTURE'); ?></div>
					<div class="vrc-param-setting">
						<input type="number" name="maxdate" value="<?php echo JHtml::_('esc_attr', $maxdate_val); ?>" min="0"/> 
						<select name="maxdateinterval">
							<option value="d"<?php echo $maxdate_interval == 'd' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEDAYS'); ?></option>
							<option value="w"<?php echo $maxdate_interval == 'w' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEWEEKS'); ?></option>
							<option value="m"<?php echo $maxdate_interval == 'm' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEMONTHS'); ?></option>
							<option value="y"<?php echo $maxdate_interval == 'y' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEYEARS'); ?></option>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONETEN'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('placesfront', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::showPlacesFront(true) ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONETENFOUR'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('showcategories', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::showCategoriesFront(true) ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCCONFIGSEARCHFILTCHARACTS'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('charatsfilter', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::useCharatsFilter(true) ? 'yes' : 0), 'yes', 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRC_SEARCH_SUGGESTIONS'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('searchsuggestions', JText::_('VRYES'), JText::_('VRNO'), (VikRentCar::showSearchSuggestions() ? 1 : 0), 1, 0); ?></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label">
						<?php echo JText::_('VRCPREFCOUNTRIESORD'); ?> 
						<?php echo $vrc_app->createPopover(array('title' => JText::_('VRCPREFCOUNTRIESORD'), 'content' => JText::_('VRCPREFCOUNTRIESORDHELP'))); ?>
						<div class="vrc-preferred-countries-edit-wrap">
							<span onclick="vrcDisplayCustomPrefCountries();"><?php VikRentCarIcons::e('edit'); ?></span>
						</div>
					</div>
					<div class="vrc-param-setting">
						<ul class="vrc-preferred-countries-sortlist">
						<?php
						$preferred_countries = VikRentCar::preferredCountriesOrdering(true);
						foreach ($preferred_countries as $ccode => $langname) {
							?>
							<li class="vrc-preferred-countries-elem">
								<span><?php VikRentCarIcons::e('ellipsis-v'); ?> <?php echo $langname; ?></span>
								<input type="hidden" name="pref_countries[]" value="<?php echo JHtml::_('esc_attr', $ccode); ?>" />
							</li>
							<?php
						}
						?>
						</ul>
						<script type="text/javascript">
						function vrcDisplayCustomPrefCountries() {
							var all_countries = new Array;
							jQuery('input[name="pref_countries[]"]').each(function() {
								all_countries.push(jQuery(this).val());
							});
							var current_countries = all_countries.join(', ');
							var custom_countries = prompt("<?php echo addslashes(JText::_('VRCPREFCOUNTRIESORD')); ?>", current_countries);
							if (custom_countries != null && custom_countries != current_countries) {
								jQuery('.vrc-preferred-countries-edit-wrap').append('<input type="hidden" name="cust_pref_countries" value="' + custom_countries + '"/>');
								jQuery('#adminForm').find('input[name="task"]').val('saveconfig');
								jQuery('#adminForm').submit();
							}
						}
						jQuery(document).ready(function() {
							jQuery('.vrc-preferred-countries-sortlist').sortable();
							jQuery('.vrc-preferred-countries-sortlist').disableSelection();
						});
						</script>
					</div>
				</div>
			</div>
		</div>
	</fieldset>

	<fieldset class="adminform">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VRCCONFIGSYSTEMPART'); ?></legend>
			<div class="vrc-params-container">
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCCONFIGCRONKEY'); ?></div>
					<div class="vrc-param-setting"><input type="text" name="cronkey" value="<?php echo JHtml::_('esc_attr', VikRentCar::getCronKey()); ?>" size="6" /></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCCONFENMULTILANG'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('multilang', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::allowMultiLanguage(), 1, 0); ?></div>
				</div>
				<!-- @wponly  we cannot display the setting for the SEF Router -->
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCLOADFA'); ?></div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('usefa', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::isFontAwesomeEnabled(true), 1, 0); ?></div>
				</div>
				<!-- @wponly  the configuration setting to toggle the loading of Bootstrap is only here -->
				<div class="vrc-param-container">
					<div class="vrc-param-label">Bootstrap CSS/JS</div>
					<div class="vrc-param-setting"><?php echo $vrc_app->printYesNoButtons('bootstrap', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentCar::loadBootstrap(), 1, 0); ?></div>
				</div>
				<!-- @wponly  jQuery main library should not be loaded as it's already included by WP -->
				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRCONFIGONECALENDAR'); ?></div>
					<div class="vrc-param-setting">
						<select name="calendar">
							<option value="jqueryui"<?php echo ($calendartype == "jqueryui" ? " selected=\"selected\"" : ""); ?>>jQuery UI</option>
						</select>
					</div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label">Google Maps API Key</div>
					<div class="vrc-param-setting"><input type="text" name="gmapskey" value="<?php echo JHtml::_('esc_attr', VikRentCar::getGoogleMapsKey()); ?>" size="30" /></div>
				</div>
				<div class="vrc-param-container">
					<div class="vrc-param-label">Ipinfo.io API Token</div>
					<div class="vrc-param-setting"><input type="text" name="ipinfo_token" value="<?php echo JHtml::_('esc_attr', VikRentCar::getIPInfoAPIToken()); ?>" size="30" /></div>
				</div>
			</div>
		</div>
	</fieldset>

	<!-- BACKUP -->

	<fieldset class="adminform">
		<div class="vrc-params-wrap">
			<legend class="adminlegend"><?php echo JText::_('VRC_CONFIG_BACKUP'); ?></legend>

			<div class="vrc-params-container">

				<!-- TYPE -->

				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRC_CONFIG_BACKUP_TYPE'); ?></div>
					<div class="vrc-param-setting">
						<?php
						$options = [];

						foreach ($this->backupExportTypes as $type => $handler)
						{
							$options[] = JHtml::_('select.option', $type, $handler->getName());	
						}

						$backup_export_type = $config->get('backuptype', 'full');
						?>
						<select name="backuptype">
							<?php echo JHtml::_('select.options', $options, 'value', 'text', $backup_export_type); ?>
						</select>
						<?php
						// display a description for the export types
						foreach ($this->backupExportTypes as $type => $handler)
						{
							?>
							<div class="vrc-param-setting-comment" id="backup_export_type_<?php echo $type; ?>" style="<?php echo $type === $backup_export_type ? '' : 'display: none;'; ?>">
								<?php echo $handler->getDescription(); ?>
							</div>
							<?php
						}
						?>
					</div>
				</div>

				<!-- FOLDER -->

				<div class="vrc-param-container">
					<div class="vrc-param-label"><?php echo JText::_('VRC_CONFIG_BACKUP_FOLDER'); ?></div>
					<div class="vrc-param-setting">
						<?php
						// get saved path
						$path = rtrim($config->get('backupfolder', ''), DIRECTORY_SEPARATOR);

						// get system temporary path
						$tmp_path = rtrim(JFactory::getApplication()->get('tmp_path', ''), DIRECTORY_SEPARATOR);

						if (!$path)
						{
							$path = $tmp_path;
						}
						?>
						<input type="text" name="backupfolder" value="<?php echo $this->escape($path); ?>" size="64" />
						<div class="vrc-param-setting-comment">
							<?php echo JText::sprintf('VRC_CONFIG_BACKUP_FOLDER_HELP', (defined('ABSPATH') ? ABSPATH : JPATH_SITE)); ?>
						</div>
					</div>
				</div>

			</div>

			<!-- BACK-UP MANAGEMENT - Button -->

			<div class="vrc-param-container">
				<div class="vrc-param-label">&nbsp;</div>
				<div class="vrc-param-setting">
					<a href="index.php?option=com_vikrentcar&amp;view=backups" class="btn vrc-config-btn" id="backup-btn" target="_blank">
						<?php echo JText::_('VRC_CONFIG_BACKUP_MANAGE_BTN'); ?>
					</a>
				</div>
			</div>

		</div>

		<script>
			(function($) {
				'use strict';

				$(function() {
					$('select[name="backuptype"]').on('change', function() {
						const type = $(this).val();

						$('#adminForm *[id^="backup_export_type_"]').hide();
						$('#backup_export_type_' + type).show();
					});
				});
			})(jQuery);

			function toggleOpeningTime(enabled) {
				if (enabled) {
					jQuery('#vrc-opening-time').hide();
				} else {
					jQuery('#vrc-opening-time').show();
				}
			}

			function toggleForcedTimes(enabled) {
				if (enabled) {
					jQuery('.vrc-forcedtimes').show();
				} else {
					jQuery('.vrc-forcedtimes').hide();
				}
			}
		</script>

	</fieldset>

<?php
if (defined('ABSPATH')) {
	/**
	 * @wponly 	trigger event onDisplayViewConfigGlobal to display additional parameters
	 */
	$extra_forms = JFactory::getApplication()->triggerEvent('onDisplayViewConfigGlobal', array($this));
	foreach ($extra_forms as $extra_form) {
		foreach ($extra_form as $form_name => $form_html) {
			?>
		<fieldset class="adminform">
			<div class="vrc-params-wrap">
				<legend class="adminlegend"><?php echo JText::_($form_name); ?></legend>
				<?php echo $form_html; ?>
			</div>
		</fieldset>
			<?php
		}
	}
}
?>

</div>
