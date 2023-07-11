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
 * Class handler for admin widget "report".
 * 
 * @since 	1.2.0
 */
class VikRentCarAdminWidgetReport extends VikRentCarAdminWidget
{
	/**
	 * The instance counter of this widget. Since we do not load individual parameters
	 * for each widget's instance, we use a static counter to determine its settings.
	 *
	 * @var 	int
	 */
	protected static $instance_counter = -1;

	/**
	 * Class constructor will define the widget name and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->widgetName = JText::_('VRCDASHSTATS');
		$this->widgetDescr = JText::_('VRC_W_REPORT_DESCR');
		$this->widgetId = basename(__FILE__);
	}

	/**
	 * Custom method for this widget only to count the report data.
	 * The method is called by the admin controller through an AJAX request.
	 * The visibility should be public, it should not exit the process, and
	 * any content sent to output will be returned to the AJAX response.
	 */
	public function countReportData()
	{
		// load report data
		$data = $this->loadReportData();

		$data_strings = array();
		foreach ($data as $what => $count) {
			$data_strings[] = $what . ':' . $count;
		}

		echo implode(';', $data_strings);
	}

	public function render($data = null)
	{
		// increase widget's instance counter
		static::$instance_counter++;

		// check whether the widget is being rendered via AJAX when adding it through the customizer
		$is_ajax = $this->isAjaxRendering();

		// generate a unique ID for the sticky notes wrapper instance
		$wrapper_instance = !$is_ajax ? static::$instance_counter : rand();
		$wrapper_id = 'vrc-widget-report-' . $wrapper_instance;

		$vrc_auth_orders = JFactory::getUser()->authorise('core.vrc.orders', 'com_vikrentcar');
		if (!$vrc_auth_orders) {
			// permissions for managing the rental orders are necessary
			return;
		}

		?>
		<div class="vrc-admin-widget-wrapper">
			<div class="vrc-admin-widget-head">
				<h4><?php VikRentCarIcons::e('tasks'); ?> <?php echo JText::_('VRCDASHSTATS'); ?></h4>
			</div>
			<div id="<?php echo $wrapper_id; ?>" class="vrc-widget-boxnumber-outer vrc-dashboard-widget-report">
				<div class="vrc-widget-boxnumber-wrap vrc-widget-boxnumber-green">
					<span class="vrc-widget-boxnumber-count" data-period="tot_next_conf">0</span>
					<div class="vrc-widget-boxnumber-lbl"><?php echo JText::_('VRCDASHTOTRESCONF'); ?></div>
				</div>
				<div class="vrc-widget-boxnumber-wrap vrc-widget-boxnumber-red">
					<span class="vrc-widget-boxnumber-count" data-period="tot_next_pend">0</span>
					<div class="vrc-widget-boxnumber-lbl"><?php echo JText::_('VRCDASHTOTRESPEND'); ?></div>
				</div>
				<div class="vrc-widget-boxnumber-wrap vrc-widget-boxnumber-red">
					<span class="vrc-widget-boxnumber-count" data-period="tot_last_month">0</span>
					<div class="vrc-widget-boxnumber-lbl"><?php echo JText::_('VRC_RENTALS_LAST_MONTH'); ?></div>
				</div>
				<div class="vrc-widget-boxnumber-wrap vrc-widget-boxnumber-green">
					<span class="vrc-widget-boxnumber-count" data-period="tot_this_month">0</span>
					<div class="vrc-widget-boxnumber-lbl"><?php echo JText::_('VRC_RENTALS_THIS_MONTH'); ?></div>
				</div>
			</div>
		</div>
		<?php

		if (static::$instance_counter === 0 || $is_ajax) {
			/**
			 * Print the JS code only once for all instances of this widget.
			 * The real rendering is made through AJAX, not when the page loads.
			 */
			?>
		<script type="text/javascript">

			/**
			 * Calculates the proper duration of the animation given the steps.
			 * 
			 * @param 	int 	steps 	the number of steps to animate (target number).
			 * 
			 * @return 	int 	 		the suggested duration for the animation in ms.
			 */
			function vrcWidgetRptCounterDuration(steps) {
				var min_duration = 500,
					max_duration = 10000,
					tms_per_step = 250;

				var duration = tms_per_step * steps;

				if (duration < min_duration) {
					return min_duration;
				}

				if (duration > max_duration) {
					return max_duration;
				}

				return duration;
			}
			
			/**
			 * Updates the counter(s) by making an AJAX request and starts their animation.
			 */
			function vrcWidgetRptCountData() {
				// the widget method to call
				var call_method = 'countReportData';

				// make a silent request to count the report data
				vrcDoAjax(
					'index.php',
					{
						option: "com_vikrentcar",
						task: "exec_admin_widget",
						widget_id: "<?php echo $this->getIdentifier(); ?>",
						call: call_method,
						tmpl: "component"
					},
					function(response) {
						try {
							var obj_res = JSON.parse(response);
							if (!obj_res.hasOwnProperty(call_method)) {
								console.error('Unexpected JSON response', obj_res);
								return;
							}

							// response must contain values separated by ;
							var data_split = obj_res[call_method].split(';');
							if (!data_split.length) {
								return;
							}

							// compose object
							var data_numbers = {};
							for (var i = 0; i < data_split.length; i++) {
								var data_value = data_split[i].split(':');
								data_numbers[data_value[0]] = parseFloat(data_value[1]);
							}

							// update all counter values (in case of multiple instances)
							jQuery('#<?php echo $wrapper_id; ?>').find('.vrc-widget-boxnumber-count').each(function() {
								var counter_type = jQuery(this).attr('data-period');
								if (!counter_type || !data_numbers.hasOwnProperty(counter_type)) {
									// continue as this property is not available
									return;
								}

								var current_counter = parseInt(jQuery(this).text());
								if (current_counter >= data_numbers[counter_type]) {
									// do nothing if we do not have a higher counter value
									return;
								}

								// make sure the duration is valid for these steps
								var counter_duration;
								if (current_counter > 0) {
									counter_duration = vrcWidgetRptCounterDuration(data_numbers[counter_type] - current_counter);
								} else {
									counter_duration = vrcWidgetRptCounterDuration(data_numbers[counter_type]);
								}

								// set new counter value and property, then start counter animation
								jQuery(this).text(data_numbers[counter_type]).prop('Counter', current_counter).animate({
									Counter: jQuery(this).text()
								}, {
									duration: counter_duration,
									easing: 'swing',
									step: function (cur) {
										jQuery(this).text(Math.ceil(cur));
									}
								});
							});
						} catch(err) {
							console.error('could not parse JSON response', err, response);
						}
					},
					function(error) {
						console.error(error);
						// make counter value empty
						jQuery('.vrc-widget-boxnumber-count').text('');
					}
				);
			}

			jQuery(document).ready(function() {
				// run the AJAX request when the page loads
				vrcWidgetRptCountData();

				// set an interval of 10 minutes for updating the counter value
				setInterval(vrcWidgetRptCountData, (1000 * 60 * 10));
			});
		</script>
			<?php
		}
	}

	/**
	 * Helper method to get the report data.
	 * 
	 * @return 	array 	an associative list of report values.
	 */
	protected function loadReportData()
	{
		$now 			  = time();
		$now_info 		  = getdate();
		$current_mon_from = mktime(0, 0, 0, $now_info['mon'], 1, $now_info['year']);
		$current_mon_to   = mktime(23, 59, 59, $now_info['mon'], date('t', $current_mon_from), $now_info['year']);
		$past_mon_from 	  = mktime(0, 0, 0, ($now_info['mon'] - 1), 1, $now_info['year']);
		$past_mon_to 	  = mktime(23, 59, 59, ($now_info['mon'] - 1), date('t', $past_mon_from), $now_info['year']);

		$dbo = JFactory::getDbo();

		$q = "SELECT COUNT(*) FROM `#__vikrentcar_orders` WHERE `ritiro`>" . $now . " AND `status`='confirmed';";
		$dbo->setQuery($q);
		$dbo->execute();
		$totnextrentconf = $dbo->loadResult();

		$q = "SELECT COUNT(*) FROM `#__vikrentcar_orders` WHERE `ritiro`>" . $now . " AND `status`='standby';";
		$dbo->setQuery($q);
		$dbo->execute();
		$totnextrentpend = $dbo->loadResult();

		// load all rentals for this month
		$q = "SELECT COUNT(*) FROM `#__vikrentcar_orders` WHERE `ritiro`>=" . $current_mon_from . " AND `ritiro`<=" . $current_mon_to . " AND `status`='confirmed';";
		$dbo->setQuery($q);
		$dbo->execute();
		$tot_this_month = $dbo->loadResult();

		// load all rentals for last month
		$q = "SELECT COUNT(*) FROM `#__vikrentcar_orders` WHERE `ritiro`>=" . $past_mon_from . " AND `ritiro`<=" . $past_mon_to . " AND `status`='confirmed';";
		$dbo->setQuery($q);
		$dbo->execute();
		$tot_last_month = $dbo->loadResult();

		return array(
			'tot_next_conf'  => $totnextrentconf,
			'tot_next_pend'  => $totnextrentpend,
			'tot_this_month' => $tot_this_month,
			'tot_last_month' => $tot_last_month,
		);
	}
}
