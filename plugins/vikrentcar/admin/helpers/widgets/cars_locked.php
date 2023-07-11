<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class handler for admin widget "cars locked". This widget has settings.
 * 
 * @since 	1.2.0
 */
class VikRentCarAdminWidgetCarsLocked extends VikRentCarAdminWidget
{
	/**
	 * Class constructor will define the widget name and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->widgetName = JText::_('VRC_W_CARSLOCKED_TITLE');
		$this->widgetDescr = JText::_('VRC_W_CARSLOCKED_DESCR');
		$this->widgetId = basename(__FILE__);
	}

	public function render($data = null)
	{
		$vrc_auth_orders = JFactory::getUser()->authorise('core.vrc.orders', 'com_vikrentcar');
		$cars_locked = array();

		$dbo = JFactory::getDbo();
		
		$q = "DELETE FROM `#__vikrentcar_tmplock` WHERE `until`<" . time() . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$q = "SELECT `lock`.*,`c`.`name` AS `car_name`,`o`.`custdata`,`o`.`idcar`,`o`.`country`,`o`.`nominative` FROM `#__vikrentcar_tmplock` AS `lock` LEFT JOIN `#__vikrentcar_orders` `o` ON `lock`.`idorder`=`o`.`id` LEFT JOIN `#__vikrentcar_cars` `c` ON `lock`.`idcar`=`c`.`id` WHERE `lock`.`until`>".time()." ORDER BY `lock`.`id` DESC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cars_locked = $dbo->loadAssocList();
		}

		?>
		<div class="vrc-admin-widget-wrapper">
			<div class="vrc-admin-widget-head">
				<h4><?php VikRentCarIcons::e('lock'); ?> <?php echo JText::_('VRCDASHCARSLOCKED'); ?> <span>(<?php echo count($cars_locked); ?>)</span></h4>
			</div>
			<div class="vrc-dashboard-cars-locked table-responsive">
			<?php
			if (!empty($selplace)) {
				?>
				<div class="vrc-dash-location-filter"><?php echo $selplace; ?></div>
				<?php
			}
			?>
				<table class="table">
					<thead>
						<tr class="vrc-dashboard-cars-locked-firstrow">
							<th class="left"><?php echo JText::_('VRCDASHUPRESTWO'); ?></th>
							<th class="left"><?php echo JText::_('VRPVIEWORDERSTWO'); ?></th>
							<th class="left"><?php echo JText::_('VRCDASHLOCKUNTIL'); ?></th>
							<th class="left"><?php echo JText::_('VRCDASHUPRESONE'); ?></th>
							<th class="center">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach ($cars_locked as $lock) {
						$country_flag = '';
						if (file_exists(VRC_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$lock['country'].'.png')) {
							$country_flag = '<img src="'.VRC_ADMIN_URI.'resources/countries/'.$lock['country'].'.png'.'" title="'.$lock['country'].'" class="vrc-country-flag vrc-country-flag-left"/>';
						}
						?>
						<tr class="vrc-dashboard-cars-locked-rows">
							<td align="left"><?php echo $lock['car_name']; ?></td>
							<td align="left"><?php echo $country_flag.$lock['nominative']; ?></td>
							<td align="left"><?php echo date($this->df.' '.$this->tf, $lock['until']); ?></td>
							<td align="left">
							<?php
							if ($vrc_auth_orders) {
								?>
								<a class="vrc-orderid" href="index.php?option=com_vikrentcar&amp;task=editorder&amp;cid[]=<?php echo $lock['idorder']; ?>" target="_blank"><?php echo $lock['idorder']; ?></a>
								<?php
							} else {
								?>
								<a class="vrc-orderid" href="javascript: void(0);"><?php echo $lock['idorder']; ?></a>
								<?php
							}
							?>
							</td>
							<td align="center">
								<button type="button" class="btn btn-danger" onclick="if (confirm('<?php echo addslashes(JText::_('VRCDELCONFIRM')); ?>')) location.href='index.php?option=com_vikrentcar&amp;task=unlockrecords&amp;cid[]=<?php echo $lock['id']; ?>';"><?php echo JText::_('VRCDASHUNLOCK'); ?></button>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
}
