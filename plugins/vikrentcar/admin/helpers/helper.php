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

class VikRentCarHelper
{
	public static function printHeader($highlight = "")
	{
		$cookie = JFactory::getApplication()->input->cookie;
		$tmpl = VikRequest::getVar('tmpl');
		if ($tmpl == 'component') {
			return;
		}
		$view = VikRequest::getVar('view');
		/**
		 * @wponly Hide menu for Pro-update views
		 */
		$skipmenu = array('getpro');
		if (in_array($view, $skipmenu)) {
			return;
		}
		//
		$backlogo = VikRentCar::getBackendLogo();
		$vrc_auth_cars = JFactory::getUser()->authorise('core.vrc.cars', 'com_vikrentcar');
		$vrc_auth_prices = JFactory::getUser()->authorise('core.vrc.prices', 'com_vikrentcar');
		$vrc_auth_orders = JFactory::getUser()->authorise('core.vrc.orders', 'com_vikrentcar');
		$vrc_auth_gsettings = JFactory::getUser()->authorise('core.vrc.gsettings', 'com_vikrentcar');
		$vrc_auth_management = JFactory::getUser()->authorise('core.vrc.management', 'com_vikrentcar');
		?>
		<div class="vrc-menu-container<?php echo $view == 'dashboard' ? ' vrc-menu-container-closer' : ''; ?>">
			<div class="vrc-menu-left">
				<a href="index.php?option=com_vikrentcar"><img src="<?php echo VRC_ADMIN_URI . (!empty($backlogo) ? 'resources/'.$backlogo : 'vikrentcar.png'); ?>" alt="VikRentCar Logo" /></a>
			</div>
			<div class="vrc-menu-right">
				<ul class="vrc-menu-ul"><?php
					if ($vrc_auth_prices || $vrc_auth_gsettings) {
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('key'); ?> <a href="javascript: void(0);"><?php echo JText::translate('VRMENUONE'); ?></a></span>
						<ul class="vrc-submenu-ul">
						<?php
						if ($vrc_auth_prices) {
							?>
							<li><span class="<?php echo ($highlight=="2" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=iva"><?php echo JText::translate('VRMENUNINE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="1" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=prices"><?php echo JText::translate('VRMENUFIVE'); ?></a></span></li>
							<?php
						}
						if ($vrc_auth_gsettings) {
							?>
							<li><span class="<?php echo ($highlight=="3" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=places"><?php echo JText::translate('VRMENUTENTHREE'); ?></a></span></li>
							<?php
						}
						if ($vrc_auth_prices) {
							?>
							<li><span class="<?php echo ($highlight=="restrictions" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=restrictions"><?php echo JText::translate('VRMENURESTRICTIONS'); ?></a></span></li>
							<?php
						}
						?>
						</ul>
					</li><?php
					}
					if ($vrc_auth_cars) {
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('car'); ?> <a href="javascript: void(0);"><?php echo JText::translate('VRMENUTWO'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="4" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=categories"><?php echo JText::translate('VRMENUSIX'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="6" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=optionals"><?php echo JText::translate('VRMENUTENFIVE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="5" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=carat"><?php echo JText::translate('VRMENUTENFOUR'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="7" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=cars"><?php echo JText::translate('VRMENUTEN'); ?></a></span></li>
						</ul>
					</li><?php
					}
					if ($vrc_auth_prices) {
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('calculator'); ?> <a href="javascript: void(0);"><?php echo JText::translate('VRCMENUFARES'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="fares" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=tariffs"><?php echo JText::translate('VRCMENUPRICESTABLE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="13" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=seasons"><?php echo JText::translate('VRMENUTENSEVEN'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="12" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=locfees"><?php echo JText::translate('VRMENUTENSIX'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="20" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=oohfees"><?php echo JText::translate('VRCMENUOOHFEES'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="ratesoverv" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=ratesoverv"><?php echo JText::translate('VRCMENURATESOVERVIEW'); ?></a></span></li>
						</ul>
					</li><?php
					}
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('calendar-check'); ?> <a href="javascript: void(0);"><?php echo JText::translate('VRMENUTHREE'); ?></a></span>
						<ul class="vrc-submenu-ul">
						<?php
						if ($vrc_auth_orders) {
						?>
							<li><span class="<?php echo ($highlight=="8" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=orders"><?php echo JText::translate('VRMENUSEVEN'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="19" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=calendar"><?php echo JText::translate('VRCMENUQUICKRES'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="15" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=overv"><?php echo JText::translate('VRMENUTENNINE'); ?></a></span></li>
						<?php
						}
						?>
							<li><span class="<?php echo ($highlight=="18" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar"><?php echo JText::translate('VRCMENUDASHBOARD'); ?></a></span></li>
						</ul>
					</li><?php
					if ($vrc_auth_management) {
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('chart-line'); ?> <a href="javascript: void(0);"><?php echo JText::translate('VRCMENUMANAGEMENT'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="customers" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=customers"><?php echo JText::translate('VRCMENUCUSTOMERS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="17" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=coupons"><?php echo JText::translate('VRCMENUCOUPONS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="22" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=graphs"><?php echo JText::translate('VRMENUGRAPHS'); ?></a></span></li>
						</ul>
					</li><?php
					}
					if ($vrc_auth_management) {
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('balance-scale'); ?> <a href="javascript: void(0);"><?php echo JText::translate('VRCMENUADV'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo (in_array($highlight, ["crons", "managecron"]) ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;view=crons"><?php echo JText::translate('VRCMENUCRONS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="pmsreports" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=pmsreports"><?php echo JText::translate('VRCMENUPMSREPORTS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="trackings" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=trackings"><?php echo JText::translate('VRCMENUTRACKINGS'); ?></a></span></li>
						</ul>
					</li><?php
					}
					if ($vrc_auth_gsettings) {
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('cogs'); ?> <a href="javascript: void(0);"><?php echo JText::translate('VRMENUFOUR'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="11" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=config"><?php echo JText::translate('VRMENUTWELVE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="21" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=translations"><?php echo JText::translate('VRMENUTRANSLATIONS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="14" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=payments"><?php echo JText::translate('VRMENUTENEIGHT'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="16" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=customf"><?php echo JText::translate('VRMENUTENTEN'); ?></a></span></li>
						</ul>
					</li><?php
					}
					?></ul>
				<div class="vrc-menu-updates">
			<?php
			/**
			 * @wponly PRO Version
			 */
			VikRentCarLoader::import('update.license');
			if (!VikRentCarLicense::isPro()) {
				?>
					<button type="button" class="vrc-gotopro" onclick="document.location.href='admin.php?option=com_vikrentcar&view=gotopro';">
						<?php VikRentCarIcons::e('rocket'); ?>
						<span><?php echo JText::translate('VRCGOTOPROBTN'); ?></span>
					</button>
				<?php
			} else {
				?>
					<button type="button" class="vrc-alreadypro" onclick="document.location.href='admin.php?option=com_vikrentcar&view=gotopro';">
						<?php VikRentCarIcons::e('trophy'); ?>
						<span><?php echo JText::translate('VRCISPROBTN'); ?></span>
					</button>
				<?php
			}
			?>
				</div>
			</div>
		</div>
		<script type="text/javascript">
		var vrc_menu_type = <?php echo (int)$cookie->get('vrcMenuType', '0', 'string') ?>;
		var vrc_menu_on = ((vrc_menu_type % 2) == 0);
		//
		function vrcDetectMenuChange(e) {
			e = e || window.event;
			if ((e.which == 77 || e.keyCode == 77) && e.altKey) {
				//ALT+M
				vrc_menu_type++;
				vrc_menu_on = ((vrc_menu_type % 2) == 0);
				console.log(vrc_menu_type, vrc_menu_on);
				//Set Cookie for next page refresh
				var nd = new Date();
				nd.setTime(nd.getTime() + (365*24*60*60*1000));
				document.cookie = "vrcMenuType="+vrc_menu_type+"; expires=" + nd.toUTCString() + "; path=/; SameSite=Lax";
			}
		}
		document.onkeydown = vrcDetectMenuChange;
		//
		jQuery(document).ready(function(){
			jQuery('.vrc-menu-parent-li').click(function() {
				if (jQuery(this).find('ul.vrc-submenu-ul').is(':visible')) {
					vrc_menu_on = false;
					return;
				}
				jQuery('ul.vrc-submenu-ul').hide();
				jQuery(this).find('ul.vrc-submenu-ul').show();
				vrc_menu_on = true;
			});
			jQuery('.vrc-menu-parent-li').hover(
				function() {
					if (vrc_menu_on === true) {
						jQuery(this).addClass('vrc-menu-parent-li-opened');
						jQuery(this).find('ul.vrc-submenu-ul').show();
					}
				},function() {
					if (vrc_menu_on === true) {
						jQuery(this).removeClass('vrc-menu-parent-li-opened');
						jQuery(this).find('ul.vrc-submenu-ul').hide();
					}
				}
			);
			var targetY = jQuery('.vrc-menu-right').offset().top + jQuery('.vrc-menu-right').outerHeight() + 150;
			jQuery(document).click(function(event) { 
				if (!jQuery(event.target).closest('.vrc-menu-right').length && parseInt(event.which) == 1 && event.pageY < targetY) {
					jQuery('ul.vrc-submenu-ul').hide();
					vrc_menu_on = true;
				}
			});

			if (jQuery('.vmenulinkactive').length) {
				jQuery('.vmenulinkactive').parent('li').parent('ul').parent('li').addClass('vrc-menu-parent-li-active');
				if ((vrc_menu_type % 2) != 0) {
					jQuery('.vmenulinkactive').parent('li').parent('ul').show();
				}
			}
		});
		</script>
		<?php
	}

	public static function printFooter()
	{
		$tmpl = VikRequest::getVar('tmpl');
		if ($tmpl == 'component') {
			return;
		}
		/**
		 * @wponly "Powered by" is VikWP.com
		 */
		echo '<br clear="all" />' . '<div id="hmfooter">' . JText::sprintf('VRCVERSION', VIKRENTCAR_SOFTWARE_VERSION) . ' <a href="https://vikwp.com/" target="_blank">VikWP - vikwp.com</a></div>';
	}

	//VikUpdater plugin methods - Start
	public static function pUpdateProgram($version)
	{
		?>
		<form name="adminForm" action="index.php" method="post" enctype="multipart/form-data" id="adminForm">
	
			<div class="span12">
				<fieldset class="form-horizontal">
					<legend><?php $version->shortTitle ?></legend>
					<div class="control"><strong><?php echo $version->title; ?></strong></div>

					<div class="control" style="margin-top: 10px;">
						<button type="button" class="btn btn-primary" onclick="downloadSoftware(this);">
							<?php echo JText::translate($version->compare == 1 ? 'VRDOWNLOADUPDATEBTN1' : 'VRDOWNLOADUPDATEBTN0'); ?>
						</button>
					</div>

					<div class="control vik-box-error" id="update-error" style="display: none;margin-top: 10px;"></div>

					<?php if ( isset($version->changelog) && count($version->changelog) ) { ?>

						<div class="control vik-update-changelog" style="margin-top: 10px;">

							<?php echo self::digChangelog($version->changelog); ?>

						</div>

					<?php } ?>
				</fieldset>
			</div>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="option" value="com_vikrentcar"/>
		</form>

		<div id="vikupdater-loading" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999999 !important; background-color: rgba(0,0,0,0.5);">
			<div id="vikupdater-loading-content" style="position: fixed; left: 33.3%; top: 30%; width: 33.3%; height: auto; z-index: 101; padding: 10px; border-radius: 5px; background-color: #fff; box-shadow: 5px 5px 5px 0 #000; overflow: auto; text-align: center;">
				<span id="vikupdater-loading-message" style="display: block; text-align: center;"></span>
				<span id="vikupdater-loading-dots" style="display: block; font-weight: bold; font-size: 25px; text-align: center; color: green;">.</span>
			</div>
		</div>
		
		<script type="text/javascript">
		var isRunning = false;
		var loadingInterval;

		function vikLoadingAnimation() {
			var dotslength = jQuery('#vikupdater-loading-dots').text().length + 1;
			if (dotslength > 10) {
				dotslength = 1;
			}
			var dotscont = '';
			for (var i = 1; i <= dotslength; i++) {
				dotscont += '.';
			}
			jQuery('#vikupdater-loading-dots').text(dotscont);
		}

		function openLoadingOverlay(message) {
			jQuery('#vikupdater-loading-message').html(message);
			jQuery('#vikupdater-loading').fadeIn();
			loadingInterval = setInterval(vikLoadingAnimation, 1000);
		}

		function closeLoadingOverlay() {
			jQuery('#vikupdater-loading').fadeOut();
			clearInterval(loadingInterval);
		}

		function downloadSoftware(btn) {

			if ( isRunning ) {
				return;
			}

			switchRunStatus(btn);
			setError(null);

			var jqxhr = jQuery.ajax({
				url: "index.php?option=com_vikrentcar&task=updateprogramlaunch&tmpl=component",
				type: "POST",
				data: {}
			}).done(function(resp) {

				try {
					var obj = JSON.parse(resp);
				} catch (e) {
					console.log(resp);
					return;
				}
				
				if ( obj === null ) {

					// connection failed. Something gone wrong while decoding JSON
					alert('<?php echo addslashes('Connection Error'); ?>');

				} else if ( obj.status ) {

					document.location.href = 'index.php?option=com_vikrentcar';
					return;

				} else {

					console.log("### ERROR ###");
					console.log(obj);

					if ( obj.hasOwnProperty('error') ) {
						setError(obj.error);
					} else {
						setError('Your website does not own a valid support license!<br />Please visit <a href="https://extensionsforjoomla.com" target="_blank">extensionsforjoomla.com</a> to purchase a license or to receive assistance.');
					}

				}

				switchRunStatus(btn);

			}).fail(function(resp) {
				console.log('### FAILURE ###');
				console.log(resp);
				alert('<?php echo addslashes('Connection Error'); ?>');

				switchRunStatus(btn);
			}); 
		}

		function switchRunStatus(btn) {
			isRunning = !isRunning;

			jQuery(btn).prop('disabled', isRunning);

			if ( isRunning ) {
				// start loading
				openLoadingOverlay('The process may take a few minutes to complete.<br />Please wait without leaving the page or closing the browser.');
			} else {
				// stop loading
				closeLoadingOverlay();
			}
		}

		function setError(err) {

			if ( err !== null && err !== undefined && err.length ) {
				jQuery('#update-error').show();
			} else {
				jQuery('#update-error').hide();
			}

			jQuery('#update-error').html(err);

		}

	</script>
		<?php
	}

	/**
	 * Scan changelog structure.
	 *
	 * @param 	array 	$arr 	The list containing changelog elements.
	 * @param 	mixed 	$html 	The html built. 
	 * 							Specify false to echo the structure immediately.
	 *
	 * @return 	string|void 	The HTML structure or nothing.
	 */
	private static function digChangelog(array $arr, $html = '')
	{
		foreach ( $arr as $elem ):

			if ( isset($elem->tag) ):

				// build attributes

				$attributes = "";
				if ( isset($elem->attributes) ) {

					foreach ( $elem->attributes as $k => $v ) {
						$attributes .= " $k=\"$v\"";
					}

				}

				// build tag opening

				$str = "<{$elem->tag}$attributes>";

				if ( $html ) {
					$html .= $str;
				} else {
					echo $str;
				}

				// display contents

				if ( isset($elem->content) ) {

					if ( $html ) {
						$html .= $elem->content;
					} else {
						echo $elem->content;
					}

				}

				// recursive iteration for elem children

				if ( isset($elem->children) ) {
					self::digChangelog($elem->children, $html);
				}

				// build tag closure

				$str = "</{$elem->tag}>";

				if ( $html ) {
					$html .= $str;
				} else {
					echo $str;
				}

			endif;

		endforeach;

		return $html;
	}
	//VikUpdater plugin methods - End
}
