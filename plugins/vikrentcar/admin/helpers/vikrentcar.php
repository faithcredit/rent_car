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
						<span><?php VikRentCarIcons::e('key'); ?> <a href="javascript: void(0);"><?php echo JText::_('VRMENUONE'); ?></a></span>
						<ul class="vrc-submenu-ul">
						<?php
						if ($vrc_auth_prices) {
							?>
							<li><span class="<?php echo ($highlight=="2" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=iva"><?php echo JText::_('VRMENUNINE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="1" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=prices"><?php echo JText::_('VRMENUFIVE'); ?></a></span></li>
							<?php
						}
						if ($vrc_auth_gsettings) {
							?>
							<li><span class="<?php echo ($highlight=="3" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=places"><?php echo JText::_('VRMENUTENTHREE'); ?></a></span></li>
							<?php
						}
						if ($vrc_auth_prices) {
							?>
							<li><span class="<?php echo ($highlight=="restrictions" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=restrictions"><?php echo JText::_('VRMENURESTRICTIONS'); ?></a></span></li>
							<?php
						}
						?>
						</ul>
					</li><?php
					}
					if ($vrc_auth_cars) {
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('car'); ?> <a href="javascript: void(0);"><?php echo JText::_('VRMENUTWO'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="4" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=categories"><?php echo JText::_('VRMENUSIX'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="6" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=optionals"><?php echo JText::_('VRMENUTENFIVE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="5" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=carat"><?php echo JText::_('VRMENUTENFOUR'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="7" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=cars"><?php echo JText::_('VRMENUTEN'); ?></a></span></li>
						</ul>
					</li><?php
					}
					if ($vrc_auth_prices) {
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('calculator'); ?> <a href="javascript: void(0);"><?php echo JText::_('VRCMENUFARES'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="fares" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=tariffs"><?php echo JText::_('VRCMENUPRICESTABLE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="13" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=seasons"><?php echo JText::_('VRMENUTENSEVEN'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="12" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=locfees"><?php echo JText::_('VRMENUTENSIX'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="20" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=oohfees"><?php echo JText::_('VRCMENUOOHFEES'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="ratesoverv" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=ratesoverv"><?php echo JText::_('VRCMENURATESOVERVIEW'); ?></a></span></li>
						</ul>
					</li><?php
					}
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('calendar-check'); ?> <a href="javascript: void(0);"><?php echo JText::_('VRMENUTHREE'); ?></a></span>
						<ul class="vrc-submenu-ul">
						<?php
						if ($vrc_auth_orders) {
						?>
							<li><span class="<?php echo ($highlight=="8" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=orders"><?php echo JText::_('VRMENUSEVEN'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="19" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=calendar"><?php echo JText::_('VRCMENUQUICKRES'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="15" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=overv"><?php echo JText::_('VRMENUTENNINE'); ?></a></span></li>
						<?php
						}
						?>
							<li><span class="<?php echo ($highlight=="18" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar"><?php echo JText::_('VRCMENUDASHBOARD'); ?></a></span></li>
						</ul>
					</li><?php
					if ($vrc_auth_management) {
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('chart-line'); ?> <a href="javascript: void(0);"><?php echo JText::_('VRCMENUMANAGEMENT'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="customers" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=customers"><?php echo JText::_('VRCMENUCUSTOMERS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="17" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=coupons"><?php echo JText::_('VRCMENUCOUPONS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="22" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=graphs"><?php echo JText::_('VRMENUGRAPHS'); ?></a></span></li>
						</ul>
					</li><?php
					}
					if ($vrc_auth_management) {
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('balance-scale'); ?> <a href="javascript: void(0);"><?php echo JText::_('VRCMENUADV'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo (in_array($highlight, ["crons", "managecron"]) ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;view=crons"><?php echo JText::_('VRCMENUCRONS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="pmsreports" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=pmsreports"><?php echo JText::_('VRCMENUPMSREPORTS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="trackings" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=trackings"><?php echo JText::_('VRCMENUTRACKINGS'); ?></a></span></li>
						</ul>
					</li><?php
					}
					if ($vrc_auth_gsettings) {
					?><li class="vrc-menu-parent-li">
						<span><?php VikRentCarIcons::e('cogs'); ?> <a href="javascript: void(0);"><?php echo JText::_('VRMENUFOUR'); ?></a></span>
						<ul class="vrc-submenu-ul">
							<li><span class="<?php echo ($highlight=="11" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=config"><?php echo JText::_('VRMENUTWELVE'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="21" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=translations"><?php echo JText::_('VRMENUTRANSLATIONS'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="14" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=payments"><?php echo JText::_('VRMENUTENEIGHT'); ?></a></span></li>
							<li><span class="<?php echo ($highlight=="16" ? "vmenulinkactive" : "vmenulink"); ?>"><a href="index.php?option=com_vikrentcar&amp;task=customf"><?php echo JText::_('VRMENUTENTEN'); ?></a></span></li>
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
						<span><?php echo JText::_('VRCGOTOPROBTN'); ?></span>
					</button>
				<?php
			} else {
				?>
					<button type="button" class="vrc-alreadypro" onclick="document.location.href='admin.php?option=com_vikrentcar&view=gotopro';">
						<?php VikRentCarIcons::e('trophy'); ?>
						<span><?php echo JText::_('VRCISPROBTN'); ?></span>
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

	public static function pUpdateProgram($version)
	{
		/**
		 * @wponly 	do nothing
		 */
	}

	/**
	 * Method to add parameters to the update extra query.
	 * 
	 * @joomlaonly 	this class is automatically loaded by Joomla
	 * 				to invoke this method when updating the component.
	 *
	 * @param   Update  &$update  An update definition
	 * @param   JTable  &$table   The update instance from the database
	 *
	 * @return  void
	 *
	 * @since 	1.15.1 (J) - 1.3.1 (WP)
	 */
	public static function prepareUpdate(&$update, &$table)
	{
		// get current domain
		$server = JFactory::getApplication()->input->server;

		// build query array
		$query = [
			'domain' => base64_encode($server->getString('HTTP_HOST')),
			'ip' 	 => $server->getString('REMOTE_ADDR'),
		];

		// always refresh the extra query before an update
		$update->set('extra_query', http_build_query($query, '', '&amp;'));
	}
}
