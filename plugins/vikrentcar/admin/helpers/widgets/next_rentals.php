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
 * Class handler for admin widget "next rentals". This widget has settings.
 * 
 * @since 	1.2.0
 */
class VikRentCarAdminWidgetNextRentals extends VikRentCarAdminWidget
{
	/**
	 * The instance counter of this widget (used to give the form a unique name).
	 *
	 * @var 	int
	 */
	protected static $instance_counter = 0;

	/**
	 * Class constructor will define the widget name and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->widgetName = JText::_('VRCDASHUPCRES');
		$this->widgetDescr = JText::_('VRC_W_NEXTRENT_DESCR');
		$this->widgetId = basename(__FILE__);
	}

	public function render($data = null)
	{
		// increase widget's instance counter
		static::$instance_counter++;

		$vrc_auth_orders = JFactory::getUser()->authorise('core.vrc.orders', 'com_vikrentcar');
		$pidplace = VikRequest::getInt('idplace', 0, 'request');
		$today_end_ts = mktime(23, 59, 59, date("n"), date("j"), date("Y"));
		$next_rentals = array();

		$dbo = JFactory::getDbo();
		$q = "SELECT `id`,`custdata`,`status`,`idcar`,`ritiro`,`consegna`,`idplace`,`idreturnplace`,`country`,`nominative` FROM `#__vikrentcar_orders` WHERE `ritiro`>" . $today_end_ts . " " . ($pidplace > 0 ? "AND `idplace`='" . $pidplace . "' " : "") . "ORDER BY `#__vikrentcar_orders`.`ritiro` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$next_rentals = $dbo->loadAssocList();
		}

		$allplaces = array();
		$q = "SELECT `id`,`name` FROM `#__vikrentcar_places` ORDER BY `#__vikrentcar_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$allplaces = $dbo->loadAssocList();
		}

		$selplace = '';
		if (count($allplaces)) {
			$selplace = "<form action=\"index.php?option=com_vikrentcar\" method=\"post\" name=\"vrcdashform" . static::$instance_counter . "\" style=\"display: inline; margin: 0;\"><label for=\"vrc-filt-idplace\">".JText::_('VRCDASHPICKUPLOC')."</label> <select id=\"vrc-filt-idplace\" name=\"idplace\" onchange=\"javascript: document.vrcdashform" . static::$instance_counter . ".submit();\" style=\"margin: 0;\">\n<option value=\"0\">".JText::_('VRCDASHALLPLACES')."</option>\n";
			foreach ($allplaces as $place) {
				$selplace .= "<option value=\"".$place['id']."\"".($place['id'] == $pidplace ? " selected=\"selected\"" : "").">".$place['name']."</option>\n";
			}
			$selplace .= "</select></form>\n";
		}

		?>
		<div class="vrc-admin-widget-wrapper">
			<div class="vrc-admin-widget-head">
				<h4><i class="vrcicn-stopwatch"></i> <?php echo JText::_('VRCDASHUPCRES'); ?></h4>
			</div>
			<div class="vrc-dashboard-next-rentals table-responsive">
			<?php
			if (!empty($selplace)) {
				?>
				<div class="vrc-dash-location-filter"><?php echo $selplace; ?></div>
				<?php
			}
			?>
				<table class="table">
					<thead>
						<tr class="vrc-dashboard-next-rentals-firstrow">
							<th class="left"><?php echo JText::_('VRCDASHUPRESONE'); ?></th>
							<th class="left"><?php echo JText::_('VRCDASHUPRESTWO'); ?></th>
							<th class="left"><?php echo JText::_('VRPVIEWORDERSTWO'); ?></th>
							<th class="left"><?php echo JText::_('VRCDASHUPRESTHREE'); ?></th>
							<th class="left"><?php echo JText::_('VRCDASHUPRESFOUR'); ?></th>
							<th class="left"><?php echo JText::_('VRCDASHUPRESFIVE'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach ($next_rentals as $next) {
						$car = VikRentCar::getCarInfo($next['idcar']);
						$nominative = strlen($next['nominative']) > 1 ? $next['nominative'] : VikRentCar::getFirstCustDataField($next['custdata']);
						$country_flag = '';
						if (file_exists(VRC_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$next['country'].'.png')) {
							$country_flag = '<img src="'.VRC_ADMIN_URI.'resources/countries/'.$next['country'].'.png'.'" title="'.$next['country'].'" class="vrc-country-flag vrc-country-flag-left"/>';
						}
						$status_lbl = '';
						if ($next['status'] == 'confirmed') {
							$status_lbl = '<span class="label label-success vrc-status-label">'.JText::_('VRCONFIRMED').'</span>';
						} elseif ($next['status'] == 'standby') {
							$status_lbl = '<span class="label label-warning vrc-status-label">'.JText::_('VRSTANDBY').'</span>';
						} elseif ($next['status'] == 'cancelled') {
							$status_lbl = '<span class="label label-error vrc-status-label" style="background-color: #d9534f;">'.JText::_('VRCANCELLED').'</span>';
						}
						?>
						<tr class="vrc-dashboard-next-rentals-rows">
							<td align="left">
							<?php
							if ($vrc_auth_orders) {
								?>
								<a class="vrc-orderid" href="index.php?option=com_vikrentcar&amp;task=editorder&amp;cid[]=<?php echo $next['id']; ?>"><?php echo $next['id']; ?></a>
								<?php
							} else {
								?>
								<a class="vrc-orderid" href="javascript: void(0);"><?php echo $next['id']; ?></a>
								<?php
							}
							?>
							</td>
							<td align="left"><?php echo $car['name']; ?></td>
							<td align="left"><?php echo $country_flag.$nominative; ?></td>
							<td align="left"><?php echo (!empty($next['idplace']) && empty($pidplace) ? VikRentCar::getPlaceName($next['idplace'])." " : "").date($this->df.' '.$this->tf, $next['ritiro']); ?></td>
							<td align="left"><?php echo (!empty($next['idreturnplace']) ? VikRentCar::getPlaceName($next['idreturnplace'])." " : "").date($this->df.' '.$this->tf, $next['consegna']); ?></td>
							<td align="left"><?php echo $status_lbl; ?></td>
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
