<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - E4J srl
 * @copyright   Copyright (C) 2022 E4J srl. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Cron Job - Email Reminder
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarCronJobEmailReminder extends VRCCronJob
{
	// Track the processed elements within an array.
	// Alias the "isTracked" method to allow the reusability of the latter
	// inside the method overloaded by this class.
	use VRCCronTrackerArray { isTracked as traitIsTracked; }
	use VRCCronFixerReminder;

	/**
	 * This method should return all the form fields required to collect the information
	 * needed for the execution of the cron job.
	 * 
	 * @return  array  An associative array of form fields.
	 */
	public function getForm()
	{
		$checktype = [
			'pickup'  => JText::_('VRCCRONSMSREMPARAMCTYPEA'),
			'payment' => JText::_('VRCCRONSMSREMPARAMCTYPEB'),
			'dropoff' => JText::_('VRCCRONSMSREMPARAMCTYPEC'),
		];

		/**
		 * Build a list of all special tags for the visual editor.
		 */
		$special_tags_base = [
			'{customer_name}',
			'{customer_pin}',
			'{order_id}',
			'{pickup_date}',
			'{dropoff_date}',
			'{num_days}',
			'{car_booked}',
			'{pickup_location}',
			'{dropoff_location}',
			'{total}',
			'{total_paid}',
			'{remaining_balance}',
			'{order_link}',
		];

		/**
		 * Load all conditional text special tags.
		 */
		$condtext_tags = array_keys(VikRentCar::getConditionalRulesInstance()->getSpecialTags());

		// join special tags with conditional texts to construct a list of editor buttons,
		// displayed within the toolbar of Quill editor
		$editor_btns = array_merge($special_tags_base, $condtext_tags);

		// convert special tags into HTML buttons, displayed under the text editor
		$special_tags_base = array_map(function($tag)
		{
			return '<button type="button" class="btn" onclick="setCronTplTag(\'tpl_text\', \'' . $tag . '\');">' . $tag . '</button>';
		}, $special_tags_base);

		// convert conditional texts into HTML buttons, displayed under the text editor
		$condtext_tags = array_map(function($tag)
		{
			return '<button type="button" class="btn vrc-condtext-specialtag-btn" onclick="setCronTplTag(\'tpl_text\', \'' . $tag . '\');">' . $tag . '</button>';
		}, $condtext_tags);

		return [
			'cron_lbl' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<h4><i class="vrcicn-mail4"></i><i class="vrcicn-alarm"></i>&nbsp;' . $this->getTitle() . '</h4>',
			],
			'checktype' => [
				'type'    => 'select',
				'label'   => JText::_('VRCCRONSMSREMPARAMCTYPE'),
				'options' => $checktype,
			],
			'remindbefored' => [
				'type'       => 'number',
				'label'      => JText::_('VRCCRONSMSREMPARAMBEFD'),
				'help'       => JText::_('VRCCRONSMSREMPARAMCTYPECHELP'),
				'default'    => 2,
			],
			'less_days_advance' => [
				'type'    => 'select',
				'label'   => JText::_('VRC_CRON_DADV_LOWER'),
				'help'    => JText::_('VRC_CRON_DADV_LOWER_HELP'),
				'default' => 1,
				'options' => [
					1 => JText::_('VRYES'),
					0 => JText::_('VRNO'),
				],
			],
			'test' => [
				'type'    => 'select',
				'label'   => JText::_('VRCCRONSMSREMPARAMTEST'),
				'help'    => JText::_('VRCCRONEMAILREMPARAMTESTHELP'),
				'default' => 'OFF',
				'options' => [
					'ON'  => JText::_('VRYES'),
					'OFF' => JText::_('VRNO'),
				],
			],
			'subject' => [
				'type'    => 'text',
				'label'   => JText::_('VRCCRONEMAILREMPARAMSUBJECT'),
				'default' => JText::_('VRCCRONEMAILREMPARAMSUBJECT'),
			],
			'tpl_text' => [
				'type'    => 'visual_html',
				'label'   => JText::_('VRCCRONSMSREMPARAMTEXT'),
				'default' => $this->getDefaultTemplate(),
				'attributes' => [
					'id'    => 'tpl_text',
					'style' => 'width: 70%; height: 150px;',
				],
				'editor_opts' => [
					'modes' => [
						'text',
						'modal-visual',
					],
				],
				'editor_btns' => $editor_btns,
			],
			'buttons' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<div class="btn-toolbar vrc-smstpl-toolbar vrc-cronparam-cbar" style="margin-top: -10px;">
					<div class="btn-group pull-left vrc-smstpl-bgroup vik-contentbuilder-textmode-sptags">'
						. implode("\n", array_merge($special_tags_base, $condtext_tags))
					. '</div>
				</div>
				<script>
					function setCronTplTag(taid, tpltag) {
						var tplobj = document.getElementById(taid);
						if (tplobj != null) {
							var start = tplobj.selectionStart;
							var end = tplobj.selectionEnd;
							tplobj.value = tplobj.value.substring(0, start) + tpltag + tplobj.value.substring(end);
							tplobj.selectionStart = tplobj.selectionEnd = start + tpltag.length;
							tplobj.focus();
							jQuery("#" + taid).trigger("change");
						}
					}
				</script>',
			],
			'help' => [
				'type'  => 'custom',
				'label' => '',
				'html'  => '<p class="vrc-cronparam-suggestion"><i class="vrcicn-lifebuoy"></i>' . JText::_('VRCCRONSMSREMHELP') . '</p>',
			],
		];
	}

	/**
	 * Returns the title of the cron job.
	 * 
	 * @return  string
	 */
	public function getTitle()
	{
		return JText::_('VRC_CRON_EMAIL_REMINDER_TITLE');
	}

	/**
	 * Executes the cron job.
	 * 
	 * @return  boolean  True on success, false otherwise.
	 */
	protected function execute()
	{
		$checktype = $this->params->get('checktype', 'pickup');
		$remindbefored = (int) $this->params->get('remindbefored');

		$db = JFactory::getDbo();

		$start_ts = mktime( 0,  0,  0, date('n'), ($checktype == 'dropoff' ? ((int) date('j') - $remindbefored) : ((int) date('j') + $remindbefored)), date('Y'));
		$end_ts   = mktime(23, 59, 59, date('n'), ($checktype == 'dropoff' ? ((int) date('j') - $remindbefored) : ((int) date('j') + $remindbefored)), date('Y'));

		/**
		 * In case of check-in reminder with like 3 days in advance, we cannot allow to skip all
		 * bookings with a lower last-minute advance period, like a reservation made "today" for
		 * "tomorrow" would never be notified. For this reason, the flag for checking the bookings
		 * notified should be set to today, and so should the start date be set.
		 */
		if (($checktype == 'pickup' || $checktype == 'payment') && $remindbefored > 1 && $this->params->get('less_days_advance'))
		{
			$start_ts = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		}

		$this->output('<p>Reading orders with ' . ($checktype == 'dropoff' ? 'drop off' : 'pick up') . ' datetime between: ' . date('c', $start_ts) . ' - ' . date('c', $end_ts) . '</p>');

		$query = $db->getQuery(true);

		$query->select('o.*');
		$query->select($db->qn('co.idcustomer'));
		$query->select($db->qn('nat.country_name'));
		$query->select($db->qn('c.pin', 'customer_pin'));
		$query->select(sprintf('CONCAT_WS(\' \', %s, %s) AS %s',
			$db->qn('c.first_name'),
			$db->qn('c.last_name'),
			$db->qn('customer_name')
		));

		$query->from($db->qn('#__vikrentcar_orders', 'o'));
		$query->leftjoin($db->qn('#__vikrentcar_customers_orders', 'co') . ' ON ' . $db->qn('co.idorder') . ' = ' . $db->qn('o.id'));
		$query->leftjoin($db->qn('#__vikrentcar_customers', 'c') . ' ON ' . $db->qn('co.idcustomer') . ' = ' . $db->qn('c.id'));
		$query->leftjoin($db->qn('#__vikrentcar_countries', 'nat') . ' ON ' . $db->qn('nat.country_3_code') . ' = ' . $db->qn('o.country'));

		$query->where($db->qn('o.' . ($checktype == 'dropoff' ? 'consegna' : 'ritiro')) . ' >= ' . (int) $start_ts);
		$query->where($db->qn('o.' . ($checktype == 'dropoff' ? 'consegna' : 'ritiro')) . ' <= ' . (int) $end_ts);

		if (in_array($checktype, ['pickup', 'dropoff']))
		{
			$query->where($db->qn('o.status') . ' = ' . $db->q('confirmed'));
		}
		else
		{
			$query->where([
				$db->qn('o.status') . ' != ' . $db->q('cancelled'),
				$db->qn('o.order_total') . ' > 0', 
				$db->qn('o.totpaid') . ' > 0', 
				$db->qn('o.totpaid') . ' < ' . $db->qn('o.order_total'),
			]);
		}
		
		$db->setQuery($query);
		$bookings = $db->loadAssocList();

		if (!$bookings)
		{
			$this->output('<span>No orders to notify.</span>');

			return true;
		}

		$this->output('<p>Orders to be notified: '.count($bookings).'</p>');

		$def_subject = $this->params->get('subject');

		foreach ($bookings as $booking)
		{
			if ($this->isTracked($booking['id']))
			{
				// the element has been already processed, skip it
				$this->output('<span>Order ID ' . $booking['id'] . ' (' . $booking['customer_name'] . ') was already notified. Skipped.</span>');
				continue;
			}

			$message = $this->params->get('tpl_text');
			$this->params->set('subject', $def_subject);
			// language translation
			if (!empty($booking['lang'])) {
				$vrc_tn = VikRentCar::getTranslator();
				$vrc_tn::$force_tolang = null;
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $booking['lang']) {
					if (defined('ABSPATH')) {
						// wp
						$lang->load('com_vikrentcar', VIKRENTCAR_SITE_LANG, $booking['lang'], true);
						$lang->load('com_vikrentcar', VIKRENTCAR_ADMIN_LANG, $booking['lang'], true);
					} else {
						// J
						$lang->load('com_vikrentcar', JPATH_SITE, $booking['lang'], true);
						$lang->load('com_vikrentcar', JPATH_ADMINISTRATOR, $booking['lang'], true);
						$lang->load('joomla', JPATH_SITE, $booking['lang'], true);
						$lang->load('joomla', JPATH_ADMINISTRATOR, $booking['lang'], true);
					}
				}
				if ($vrc_tn->getDefaultLang() != $booking['lang']) {
					// force the translation to start because contents should be translated
					$vrc_tn::$force_tolang = $booking['lang'];
				}
				
				// convert cron data into an array and re-encode parameters to preserve
				// the compatibility with the translator
				$cron_tn = (array) $this->getData();
				$cron_tn['params'] = json_encode($this->params->getProperties());

				$vrc_tn->translateContents($cron_tn, '#__vikrentcar_cronjobs', array(), array(), $booking['lang']);
				$params_tn = json_decode($cron_tn['params'], true);
				if (is_array($params_tn) && array_key_exists('tpl_text', $params_tn)) {
					$message = $params_tn['tpl_text'];
				}
				if (is_array($params_tn) && array_key_exists('subject', $params_tn)) {
					$this->params->set('subject', $params_tn['subject']);
				}
			}
			//
			$send_res = $this->params->get('test', 'OFF') == 'ON' ? false : $this->sendEmailReminder($booking, $message);
			$this->output('<span>Result for sending eMail to '.$booking['custmail'].' - Order ID '.$booking['id'].' ('.$booking['customer_name'].(!empty($booking['lang']) ? ' '.$booking['lang'] : '').'): '.($send_res !== false ? '<i class="vrcicn-checkmark"></i>Success' : '<i class="vrcicn-cancel-circle"></i>Failure').($this->params->get('test', 'OFF') == 'ON' ? ' (Test Mode ON)' : '').'</span>');
			if ($send_res !== false) {
				$this->appendLog('eMail sent to '.$booking['custmail'].' - Order ID '.$booking['id'].' ('.$booking['customer_name'].(!empty($booking['lang']) ? ' '.$booking['lang'] : '').')');
				// store in execution flag that this order ID was notified
				$this->track($booking['id']);
			}
		}

		return true;
	}

	/**
	 * Checks whether the specified element has been already processed.
	 * 
	 * @param   mixed    $element  The element to check.
	 * 
	 * @return  boolean  True if already processed, false otherwise.
	 */
	protected function isTracked($element)
	{
		// launch backward compatibility process
		$this->normalizeDeprecatedFlag();
		// invoke trait method
		return $this->traitIsTracked($element);
	}

	private function sendEmailReminder($booking, $message)
	{
		if (!class_exists('VrcApplication')) {
			require_once(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'jv_helper.php');
		}

		$vrc_app = VikRentCar::getVrcApplication();
		$dbo = JFactory::getDbo();

		if (empty($booking['id']) || empty($booking['custmail'])) {
			return false;
		}

		$booking_car = [];
		$q = "SELECT `o`.`idcar`,`c`.`name` AS `car_name`,`c`.`params` AS `car_params` FROM `#__vikrentcar_orders` AS `o` LEFT JOIN `#__vikrentcar_cars` `c` ON `c`.`id`=`o`.`idcar` WHERE `o`.`id`=" . (int)$booking['id'] . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows()) {
			$booking_car = $dbo->loadAssoc();
		}

		$admin_sendermail = VikRentCar::getSenderMail();
		$vrc_tn = VikRentCar::getTranslator();
		$vrc_tn->translateContents($booking_car, '#__vikrentcar_cars', array('id' => 'idcar', 'name' => 'car_name'));

		if (!trim($message))
		{
			// no message, use default template
			$message = $this->getDefaultTemplate();
		}

		$message = $this->parseCustomerEmailTemplate($message, $booking, $booking_car, $vrc_tn);
		if (empty($message)) {
			return false;
		}

		$is_html = (strpos($message, '<') !== false || strpos($message, '</') !== false);
		if ($is_html && !preg_match("/(<\/?br\/?>)+/", $message)) {
			// when no br tags found, apply nl2br
			$message = nl2br($message);
		}

		$vrc_app->sendMail($admin_sendermail, $admin_sendermail, $booking['custmail'], $admin_sendermail, $this->params->get('subject'), $message, $is_html);

		return true;
	}

	private function parseCustomerEmailTemplate($message, $booking, $booking_car, $vrc_tn = null)
	{
		$tpl = $message;

		/**
		 * Parse all conditional text rules.
		 */
		VikRentCar::getConditionalRulesInstance()
			->set(['booking', 'car'], [$booking, $booking_car])
			->parseTokens($tpl);
		//

		$vrc_df = VikRentCar::getDateFormat();
		$df = $vrc_df == "%d/%m/%Y" ? 'd/m/Y' : ($vrc_df == "%m/%d/%Y" ? 'm/d/Y' : 'Y-m-d');
		$tpl = str_replace('{customer_name}', $booking['customer_name'], $tpl);
		$tpl = str_replace('{order_id}', $booking['id'], $tpl);
		$tpl = str_replace('{pickup_date}', date($df, $booking['ritiro']), $tpl);
		$tpl = str_replace('{dropoff_date}', date($df, $booking['consegna']), $tpl);
		$tpl = str_replace('{num_days}', $booking['days'], $tpl);
		$tpl = str_replace('{car_booked}', $booking_car['car_name'], $tpl);
		$ritplace = !empty($booking['idplace']) ? VikRentCar::getPlaceName($booking['idplace'], $vrc_tn) : "";
		$consegnaplace = !empty($booking['idreturnplace']) ? VikRentCar::getPlaceName($booking['idreturnplace'], $vrc_tn) : "";
		$tpl = str_replace('{pickup_location}', $ritplace, $tpl);
		$tpl = str_replace('{dropoff_location}', $consegnaplace, $tpl);
		$tpl = str_replace('{total}', VikRentCar::numberFormat($booking['order_total']), $tpl);
		$tpl = str_replace('{total_paid}', VikRentCar::numberFormat($booking['totpaid']), $tpl);
		$remaining_bal = $booking['order_total'] - $booking['totpaid'];
		$tpl = str_replace('{remaining_balance}', VikRentCar::numberFormat($remaining_bal), $tpl);
		$tpl = str_replace('{customer_pin}', $booking['customer_pin'], $tpl);
		
		//
		$bestitemid = VikRentCar::findProperItemIdType(['order']);
		$book_link = VikRentCar::externalroute("index.php?option=com_vikrentcar&view=order&sid=" . $booking['sid'] . "&ts=" . $booking['ts'], false, (!empty($bestitemid) ? $bestitemid : null));
		//
		$tpl = str_replace('{order_link}', $book_link, $tpl);

		/**
		 * Cars Distinctive Features parsing
		 */
		preg_match_all('/\{carfeature ([a-zA-Z0-9 ]+)\}/U', $tpl, $matches);
		if (isset($matches[1]) && is_array($matches[1]) && @count($matches[1]) > 0) {
			foreach ($matches[1] as $reqf) {
				$cars_features = array();
				$distinctive_features = array();
				$cparams = json_decode($booking_car['car_params'], true);
				if (array_key_exists('features', $cparams) && count($cparams['features']) && !empty($booking['carindex']) && array_key_exists($booking['carindex'], $cparams['features'])) {
					$distinctive_features = $cparams['features'][$booking['carindex']];
				}
				if (count($distinctive_features)) {
					$feature_found = false;
					foreach ($distinctive_features as $dfk => $dfv) {
						if (stripos($dfk, $reqf) !== false) {
							$feature_found = $dfk;
							if (strlen(trim($dfk)) == strlen(trim($reqf))) {
								break;
							}
						}
					}
					if ($feature_found !== false && strlen($distinctive_features[$feature_found])) {
						$cars_features[] = $distinctive_features[$feature_found];
					}
				}
				if (count($cars_features)) {
					$rpval = implode(', ', $cars_features);
				} else {
					$rpval = '';
				}
				$tpl = str_replace("{carfeature ".$reqf."}", $rpval, $tpl);
			}
		}
		//

		return $tpl;
	}

	/**
	 * Returns the default e-mail template.
	 * 
	 * @return  string
	 */
	private function getDefaultTemplate()
	{	
		static $tmpl = '';

		if (!$tmpl)
		{
			$sitelogo 	  = VRCFactory::getConfig()->get('sitelogo');
			$company_name = VikRentCar::getFrontTitle();
			
			if ($sitelogo && is_file(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'resources'. DIRECTORY_SEPARATOR . $sitelogo))
			{
				$tmpl .= '<p style="text-align: center;">'
					. '<img src="' . VRC_ADMIN_URI . 'resources/' . $sitelogo . '" alt="' . htmlspecialchars($company_name) . '" /></p>'
					. "\n";
			}

			$tmpl .= 
<<<HTML
<h1 style="text-align: center;">
	<span style="font-family: verdana;">$company_name</span>
</h1>
<hr class="vrc-editor-hl-mailwrapper">
<h4>Dear {customer_name},</h4>
<p><br></p>
<p>This is an automated message to remind you the pick-up time for your car: {pickup_date}.</p>
<p>You can always get in touch with us should you have any questions.</p>
<p><br></p>
<p><br></p>
<p>Thank you.</p>
<p>$company_name</p>
<hr class="vrc-editor-hl-mailwrapper">
<p><br></p>
HTML
			;
		}

		return $tmpl;
	}
}
