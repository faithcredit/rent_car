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

/**
 * Extends native application functions.
 *
 * @wponly 	the class extends VikApplication and uses different vars.
 * @since 	1.0
 * @see 	VikApplication
 */
class VrcApplication extends VikApplication
{
	/**
	 * Additional commands container for any methods.
	 *
	 * @var array
	 */
	private $commands;

	/**
	 * This method loads an additional CSS file (if available)
	 * for the current CMS, and CMS version.
	 *
	 * @return void
	 **/
	public function normalizeBackendStyles()
	{
		$document = JFactory::getDocument();

		if (file_exists(VRC_ADMIN_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'wp.css')) {
			$document->addStyleSheet(VRC_ADMIN_URI . 'helpers/' . 'wp.css');
		}
	}

	/**
	 * Includes a script URI.
	 *
	 * @param 	string 	$uri  The script URI.
	 *
	 * @return 	void
	 */
	public function addScript($uri)
	{
		JHtml::_('script', $uri);
	}

	/**
	* Sets additional commands for any methods. Like raise an error if the recipient email address is empty.
	* Returns this object for chainability.
	*/
	public function setCommand($key, $value)
	{
		if (!empty($key)) {
			$this->commands[$key] = $value;
		}
		return $this;
	}
	
	public function sendMail($from_address, $from_name, $to, $reply_address, $subject, $hmess, $is_html = true, $encoding = 'base64', $attachment = null)
	{
		if (strpos($to, ',') !== false) {
			$all_recipients = explode(',', $to);
			foreach ($all_recipients as $k => $v) {
				if (empty($v)) {
					unset($all_recipients[$k]);
				}
			}
			if (count($all_recipients) > 0) {
				$to = $all_recipients;
			}
		}

		if (empty($to)) {
			//Prevent Joomla Exceptions that would stop the script execution
			if (isset($this->commands['print_errors'])) {
				VikError::raiseWarning('', 'The recipient email address is empty. Email message could not be sent. Please check your configuration.');
			}
			return false;
		}
		
		if ($from_name == $from_address) {
			$mainframe = JFactory::getApplication();
			$attempt_fromn = $mainframe->get('fromname', '');
			if (!empty($attempt_fromn)) {
				$from_name = $attempt_fromn;
			}
		}

		/**
		 * We let the internal library process the email sending depending on the platform.
		 * This will allow us to perform the required manipulation of the content, if needed.
		 * 
		 * @deprecated  code below:
		 * return parent::sendMail($from_address, $from_name, $to, $reply_address, $subject, $hmess, $attachment, $is_html, $encoding);
		 * 
		 * @since   1.15.0 (J) - 1.3.0 (WP)
		 */
		$mail_data = new VRCMailWrapper([
			'sender'      => [$from_address, $from_name],
			'recipient'   => $to,
			'bcc'         => [],
			'reply'       => $reply_address,
			'subject'     => $subject,
			'content'     => $hmess,
			'attachments' => $attachment,
		]);

		return VRCFactory::getPlatform()->getMailer()->send($mail_data);
	}

	/**
	* @param $arr_values array
	* @param $current_key string
	* @param $empty_value string (J3.x only)
	* @param $default
	* @param $input_name string
	* @param $record_id = '' string
	*/
	public function getDropDown($arr_values, $current_key, $empty_value, $default, $input_name, $record_id = '')
	{
		$dropdown = '';
		$dropdown .= '<select name="'.$input_name.'" onchange="document.adminForm.submit();">'."\n";
		$dropdown .= '<option value="">'.$default.'</option>'."\n";
		$list = "\n";
		foreach ($arr_values as $k => $v) {
			$dropdown .= '<option value="'.$k.'"'.($k == $current_key ? ' selected="selected"' : '').'>'.$v.'</option>'."\n";
		}
		$dropdown .= '</select>'."\n";

		return $dropdown;
	}

	public function loadSelect2()
	{
		//load JS + CSS
		$document = JFactory::getDocument();
		$document->addStyleSheet(VRC_ADMIN_URI.'resources/select2.min.css');
		$this->addScript(VRC_ADMIN_URI.'resources/select2.min.js');
	}

	/**
	 * Returns the HTML code to render a regular dropdown
	 * menu styled through the jQuery plugin Select2.
	 *
	 * @param 	$arr_values 	array
	 * @param 	$current_key 	string
	 * @param 	$input_name 	string
	 * @param 	$placeholder 	string 		used when the select has no selected option (it's empty)
	 * @param 	$empty_name 	[string] 	the name of the option to set an empty value to the field (<option>$empty_name</option>)
	 * @param 	$empty_val 		[string]	the value of the option to set an empty value to the field (<option>$empty_val</option>)
	 * @param 	$onchange 		[string] 	javascript code for the onchange attribute
	 * @param 	$idattr 		[string] 	the identifier attribute of the select
	 *
	 * @return 	string
	 */
	public function getNiceSelect($arr_values, $current_key, $input_name, $placeholder, $empty_name = '', $empty_val = '', $onchange = 'document.adminForm.submit();', $idattr = '')
	{
		//load JS + CSS
		$this->loadSelect2();

		//attribute
		$idattr = empty($idattr) ? rand(1, 999) : $idattr;

		//select
		$dropdown = '<select id="'.$idattr.'" name="'.$input_name.'"'.(!empty($onchange) ? ' onchange="'.$onchange.'"' : '').'>'."\n";
		if (!empty($placeholder) && empty($current_key)) {
			//in order for the placeholder value to appear, there must be a blank <option> as the first option in the select
			$dropdown .= '<option></option>'."\n";
		} else {
			//unset the placeholder to not pass it to the select2 object, or the empty value will not be displayed
			$placeholder = '';
		}
		if (strlen($empty_name) || strlen($empty_val)) {
			$dropdown .= '<option value="'.$empty_val.'">'.$empty_name.'</option>'."\n";
		}
		foreach ($arr_values as $k => $v) {
			$dropdown .= '<option value="'.$k.'"'.($k == $current_key ? ' selected="selected"' : '').'>'.$v.'</option>'."\n";
		}
		$dropdown .= '</select>'."\n";

		//js code
		$dropdown .= '<script type="text/javascript">'."\n";
		$dropdown .= 'jQuery(document).ready(function() {'."\n";
		$dropdown .= '	jQuery("#'.$idattr.'").select2('.(!empty($placeholder) ? '{placeholder: "'.addslashes($placeholder).'"}' : '').');'."\n";
		$dropdown .= '});'."\n";
		$dropdown .= '</script>'."\n";

		return $dropdown;
	}

	/**
	 * Returns the Script tag to render the Bootstrap JModal window.
	 * The suffix can be passed to generate other JS functions.
	 * Optionally pass JavaScript code for the 'show' and 'hide' events.
	 * Only compatible with Joomla > 3.x. jQuery must be defined.
	 *
	 * @param 	$suffix 	string
	 * @param 	$hide_js 	string
	 * @param 	$show_js 	string
	 *
	 * @return 	string
	 */
	public function getJmodalScript($suffix = '', $hide_js = '', $show_js = '')
	{
		static $loaded = array();

		$doc = JFactory::getDocument();

		if (!isset($loaded[$suffix]))
		{
			$doc->addScriptDeclaration(
<<<JS
function vrcOpenJModal$suffix(id, modal_url, new_title) {

	var on_hide = null;

	if ("$hide_js") {
		on_hide = function() {
			$hide_js
		}
	}

	var on_show = null;

	if ("$show_js") {
		on_show = function() {
			$show_js
		}
	}
	
	wpOpenJModal(id, modal_url, on_show, on_hide);

	if (new_title) {
		jQuery('#jmodal-' + id + ' .modal-header h3').text(new_title);
	}

	return false;
}
JS
			);

			$loaded[$suffix] = 1;
		}

	}

	/**
	 * Returns a safe sub-string string with the requested length, by
	 * avoiding errors for those errors not supporting multi-byte strings.
	 * 
	 * @param   string  $text   the text to apply the substr onto.
	 * @param   string  $len    the length of the sub-string to take.
	 * 
	 * @return  string          the portion of the string.
	 * 
	 * @since   1.15.0 (J) - 1.3.0 (WP)
	 */
	public function safeSubstr($text, $len = 3)
	{
		$mb_supported = function_exists('mb_substr');

		if ($len < 1) {
			return $text;
		}
		
		return $mb_supported ? mb_substr($text, 0, $len, 'UTF-8') : substr($text, 0, $len);
	}

	/**
	 * Loads the necessary JS and CSS assets to render the jQuery UI Datepicker calendar.
	 * 
	 * @since   1.1.0
	 * @since   1.15.0 (J) - 1.3.0 (WP) the lang definitions work for both front and back -ends.
	 */
	public function loadDatePicker()
	{
		static $datepicker_loaded = null;

		if ($datepicker_loaded) {
			// loaded flag
			return;
		}

		$document = JFactory::getDocument();
		$document->addStyleSheet(VRC_SITE_URI.'resources/jquery-ui.min.css');
		
		JHtml::_('jquery.framework', true, true);
		$this->addScript(VRC_SITE_URI.'resources/jquery-ui.min.js');

		$vrc_df = VikRentCar::getDateFormat();
		$juidf = $vrc_df == "%d/%m/%Y" ? 'dd/mm/yy' : ($vrc_df == "%m/%d/%Y" ? 'mm/dd/yy' : 'yy/mm/dd');

		$is_rtl_str = 'false';
		$now_lang = JFactory::getLanguage();
		if (method_exists($now_lang, 'isRtl')) {
			$is_rtl_str = $now_lang->isRtl() ? 'true' : $is_rtl_str;
		}

		$ldecl = '
jQuery(function($){'."\n".'
	$.datepicker.regional["vikrentcar"] = {'."\n".'
		closeText: "'.JText::_('VRCJQCALDONE').'",'."\n".'
		prevText: "'.JText::_('VRCJQCALPREV').'",'."\n".'
		nextText: "'.JText::_('VRCJQCALNEXT').'",'."\n".'
		currentText: "'.JText::_('VRCJQCALTODAY').'",'."\n".'
		monthNames: ["'.JText::_('VRMONTHONE').'","'.JText::_('VRMONTHTWO').'","'.JText::_('VRMONTHTHREE').'","'.JText::_('VRMONTHFOUR').'","'.JText::_('VRMONTHFIVE').'","'.JText::_('VRMONTHSIX').'","'.JText::_('VRMONTHSEVEN').'","'.JText::_('VRMONTHEIGHT').'","'.JText::_('VRMONTHNINE').'","'.JText::_('VRMONTHTEN').'","'.JText::_('VRMONTHELEVEN').'","'.JText::_('VRMONTHTWELVE').'"],'."\n".'
		monthNamesShort: ["'.$this->safeSubstr(JText::_('VRMONTHONE')).'","'.$this->safeSubstr(JText::_('VRMONTHTWO')).'","'.$this->safeSubstr(JText::_('VRMONTHTHREE')).'","'.$this->safeSubstr(JText::_('VRMONTHFOUR')).'","'.$this->safeSubstr(JText::_('VRMONTHFIVE')).'","'.$this->safeSubstr(JText::_('VRMONTHSIX')).'","'.$this->safeSubstr(JText::_('VRMONTHSEVEN')).'","'.$this->safeSubstr(JText::_('VRMONTHEIGHT')).'","'.$this->safeSubstr(JText::_('VRMONTHNINE')).'","'.$this->safeSubstr(JText::_('VRMONTHTEN')).'","'.$this->safeSubstr(JText::_('VRMONTHELEVEN')).'","'.$this->safeSubstr(JText::_('VRMONTHTWELVE')).'"],'."\n".'
		dayNames: ["'.JText::_('VRWEEKDAYZERO').'", "'.JText::_('VRWEEKDAYONE').'", "'.JText::_('VRWEEKDAYTWO').'", "'.JText::_('VRWEEKDAYTHREE').'", "'.JText::_('VRWEEKDAYFOUR').'", "'.JText::_('VRWEEKDAYFIVE').'", "'.JText::_('VRWEEKDAYSIX').'"],'."\n".'
		dayNamesShort: ["'.$this->safeSubstr(JText::_('VRWEEKDAYZERO')).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYONE')).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYTWO')).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYTHREE')).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYFOUR')).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYFIVE')).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYSIX')).'"],'."\n".'
		dayNamesMin: ["'.$this->safeSubstr(JText::_('VRWEEKDAYZERO'), 2).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYONE'), 2).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYTWO'), 2).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYTHREE'), 2).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYFOUR'), 2).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYFIVE'), 2).'", "'.$this->safeSubstr(JText::_('VRWEEKDAYSIX'), 2).'"],'."\n".'
		weekHeader: "'.JText::_('VRCJQCALWKHEADER').'",'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.VikRentCar::getFirstWeekDay().','."\n".'
		isRTL: ' . $is_rtl_str . ','."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikrentcar"]);'."\n".'
});';
		$document->addScriptDeclaration($ldecl);

		// cache loaded flag
		$datepicker_loaded = 1;
	}

	/**
	 * Loads the CMS's native datepicker calendar.
	 *
	 * @since 	1.10
	 */
	public function getCalendar($val, $name, $id = null, $df = null, array $attributes = array())
	{
		if ($df === null)
		{
			$df = VikRentCar::getDateFormat();
		}

		return parent::calendar($val, $name, $id, $df, $attributes);
	}

	/**
	 * Returns a masked e-mail address. The e-mail are masked using 
	 * a technique to encode the bytes in hexadecimal representation.
	 * The chunk of the masked e-mail will be also encoded to be HTML readable.
	 *
	 * @param 	string 	 $email 	The e-mail to mask.
	 * @param 	boolean  $reverse 	True to reverse the e-mail address.
	 * 								Only if the e-mail is not contained into an attribute.
	 *
	 * @return 	string 	 The masked e-mail address.
	 */
	public function maskMail($email, $reverse = false)
	{
		if ($reverse)
		{
			// reverse the e-mail address
			$email = strrev($email);
		}

		// converts the e-mail address from bin to hex
		$email = bin2hex($email);
		// append ;&#x sequence after every chunk of the masked e-mail
		$email = chunk_split($email, 2, ";&#x");
		// prepend &#x sequence before the address and trim the ending sequence
		$email = "&#x" . substr($email, 0, -3);

		return $email;
	}

	/**
	 * Returns a safemail tag to avoid the bots spoof a plain address.
	 *
	 * @param 	string 	 $email 	The e-mail address to mask.
	 * @param 	boolean  $mail_to 	True if the address should be wrapped
	 * 								within a "mailto" link.
	 *
	 * @return 	string 	 The HTML tag containing the masked address.
	 *
	 * @uses 	maskMail()
	 */
	public function safeMailTag($email, $mail_to = false)
	{
		// include the CSS declaration to reverse the text contained in the <safemail> tags
		JFactory::getDocument()->addStyleDeclaration('safemail {direction: rtl;unicode-bidi: bidi-override;}');

		// mask the reversed e-mail address
		$masked = $this->maskMail($email, true);

		// include the address into a custom <safemail> tag
		$tag = "<safemail>$masked</safemail>";

		if ($mail_to)
		{
			// mask the address for mailto command (do not use reverse)
			$mailto = $this->maskMail($email);

			// wrap the safemail tag within a mailto link
			$tag = "<a href=\"mailto:$mailto\" class=\"mailto\">$tag</a>";
		}

		return $tag;
	}

	/**
	 * Loads and echoes the script necessary to render the Fancybox
	 * plugin for jQuery to open images or iframes within a modal box.
	 * This resolves conflicts with some Bootstrap or Joomla (4) versions
	 * that do not support the old-native CSS class .modal with "behavior.modal".
	 * Mainly made to open pictures in a modal box, so the default "type" is set to "image".
	 * By passing a custom $opts string, the "type" property could be set to "iframe", but
	 * in this case it's better to use the other method of this class (Jmodal).
	 * The base jQuery library should be already loaded when using this method.
	 *
	 * @param 	string 	 	$selector 	The jQuery selector to trigger Fancybox.
	 * @param 	string  	$opts 		The options object for the Fancybox setup.
	 * @param 	boolean  	$reloadfunc If true, an additional function is included in the script
	 *									to apply again Fancybox to newly added images to the DOM (via Ajax).
	 *
	 * @return 	void
	 *
	 * @uses 	addScript()
	 */
	public function prepareModalBox($selector = '.vrcmodal', $opts = '', $reloadfunc = false)
	{
		if (empty($opts)) {
			$opts = '';
		}
		$document = JFactory::getDocument();
		$document->addStyleSheet(VRC_SITE_URI.'resources/jquery.fancybox.css');
		$this->addScript(VRC_SITE_URI.'resources/jquery.fancybox.js');

		$reloadjs = '
		function reloadFancybox() {
			jQuery("'.$selector.'").fancybox('.$opts.');
		}
		';
		$js = '
		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("'.$selector.'").fancybox' . (!empty($opts) ? '('.$opts.')' : '()') . ';
		});'.($reloadfunc ? $reloadjs : '').'
		</script>';

		echo $js;
	}

	/**
	 * Method used to handle the reCAPTCHA events.
	 *
	 * @param 	string 	$event 		The reCAPTCHA event to trigger.
	 * 								Here's the list of the accepted events:
	 * 								- display 	Returns the HTML used to 
	 *											display the reCAPTCHA input.
	 *								- check 	Validates the POST data to make sure
	 * 											the reCAPTCHA input was checked.
	 * @param 	array  	$options 	A configuration array.
	 *
	 * @return 	mixed 	The event response.
	 *
	 * @since 	1.2.3
	 * @wponly 	the Joomla integration differs
	 */
	public function reCaptcha($event = 'display', array $options = array())
	{
		$response = null;
		// an optional configuration array (just leave empty)
		$options = array();
		// trigger reCAPTCHA display event to fill $response var
		do_action_ref_array('vik_recaptcha_' . $event, array(&$response, $options));
		// display reCAPTCHA by echoing it (empty in case reCAPTCHA is not available)
		return $response;
	}

	/**
	 * Checks if the com_user captcha is configured.
	 * In case the parameter is set to global, the default one
	 * will be retrieved.
	 * 
	 * @param 	string 	 $plugin  The plugin name to check ('recaptcha' by default).
	 *
	 * @return 	boolean  True if configured, otherwise false.
	 *
	 * @since 	1.2.3
	 * @wponly 	the Joomla integration differs
	 */
	public function isCaptcha($plugin = 'recaptcha')
	{
		return apply_filters('vik_' . $plugin . '_on', false);
	}

	/**
	 * Checks if the global captcha is configured.
	 * 
	 * @param 	string 	 $plugin  The plugin name to check ('recaptcha' by default).
	 *
	 * @return 	boolean  True if configured, otherwise false.
	 *
	 * @since 	1.2.3
	 */
	public function isGlobalCaptcha($plugin = 'recaptcha')
	{
		return $this->isCaptcha($plugin);
	}

	/**
	 * Method used to obtain a WordPress media form field.
	 *
	 * @return 	string 	The media in HTML.
	 *
	 * @since 	1.1.0
	 */
	public function getMediaField($name, $value = null, array $data = array())
	{
		// check if WordPress is installed
		if (defined('ABSPATH'))
		{
			add_action('admin_enqueue_scripts', function() {
				wp_enqueue_media();
			});

			// import form field class
			JLoader::import('adapter.form.field');

			// create XML field manifest
			$xml = "<field name=\"$name\" type=\"media\" modowner=\"vikrentcar\" />";

			// instantiate field
			$field = JFormField::getInstance(simplexml_load_string($xml));

			// overwrite name and value within data
			$data['name']  = $name;
			$data['value'] = $value;

			// inject display data within field instance
			foreach ($data as $k => $v)
			{
				$field->bind($v, $k);
			}

			// render field
			return $field->render();
		}

		// fallback to Joomla

		// init media field
		$field = new JFormFieldMedia(null, $value);
		// setup an empty form as placeholder
		$field->setForm(new JForm('vikrentcar.media'));

		// force field attributes
		$data['name']  = $name;
		$data['value'] = $value;

		if (empty($data['previewWidth']))
		{
			// there is no preview width, set a defualt value
			// to make the image visible within the popover
			$data['previewWidth'] = 480;	
		}

		// render the field	
		return $field->render('joomla.form.field.media', $data);
	}

	/**
	 * Displays a multi-state toggle switch element with unlimited buttons.
	 * Custom values, contents, labels, attributes and JS events can be attached
	 * to each button. VCM will use this same method.
	 * 
	 * @param 	string 	$name 	the input name equal for all radio buttons.
	 * @param 	string 	$value 	the current input field value to be pre-selected.
	 * @param 	array 	$values list of radio buttons with each value.
	 * @param 	array 	$labels list of contents for each button trigger.
	 * @param 	array 	$attrs 	list of associative array attributes for each button.
	 * @param 	array 	$wrap 	list of associative array attributes for the wrapper.
	 * 
	 * @return 	string 	the necessary HTML to render the multi-state toggle switch.
	 * 
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function multiStateToggleSwitchField($name, $value, $values = array(), $labels = array(), $attrs = array(), $wrap = array())
	{
		static $tooltip_js_declared = null;

		// whether tooltip for titles is needed
		$needs_tooltip = false;

		// HTML container
		$multi_state_switch = '';

		if (!is_array($values) || !count($values)) {
			// values must be set or we don't know what buttons to display
			return $multi_state_switch;
		}

		// build default classes for the tri-state toggle switch (with 3 buttons)
		$def_tristate_cls = array(
			'vik-multiswitch-radiobtn-on',
			'vik-multiswitch-radiobtn-def',
			'vik-multiswitch-radiobtn-off',
		);

		// start wrapper
		$multi_state_switch .= "\n" . '<div class="vik-multiswitch-wrap' . (isset($wrap['class']) ? (' ' . $wrap['class']) : '') . '">' . "\n";

		foreach ($values as $btn_k => $btn_val) {
			// build default classes for button label
			$btn_classes = array('vik-multiswitch-radiobtn');
			if (isset($def_tristate_cls[$btn_k])) {
				// push default class for a 3-state toggle switch
				array_push($btn_classes, $def_tristate_cls[$btn_k]);
			}
			// check if additional custom classes have been defined for this button
			if (isset($attrs[$btn_k]) && isset($attrs[$btn_k]['label_class']) && !empty($attrs[$btn_k]['label_class'])) {
				if (is_array($attrs[$btn_k]['label_class'])) {
					// list of additional classes for this button
					$btn_classes = array_merge($btn_classes, $attrs[$btn_k]['label_class']);
				} elseif (is_string($attrs[$btn_k]['label_class'])) {
					// multiple classes should be space-separated
					array_push($btn_classes, $attrs[$btn_k]['label_class']);
				}
			}

			// check title as first thing, even though this is passed along with the labels
			$label_title = '';
			if (isset($labels[$btn_k]) && !is_scalar($labels[$btn_k]) && isset($labels[$btn_k]['title'])) {
				$needs_tooltip = true;
				$label_title = ' title="' . addslashes(htmlentities($labels[$btn_k]['title'])) . '"';
			}

			// start button label
			$multi_state_switch .= "\t" . '<label class="' . implode(' ', $btn_classes) . '"' . $label_title . '>' . "\n";

			// check button input radio
			$radio_attributes = array();
			if (($value !== null && $value == $btn_val) || ($value === null && $btn_k === 0)) {
				// this radio button must be checked (pre-selected)
				$radio_attributes['checked'] = true;
			}
			// check if custom attributes were specified for this input
			if (isset($attrs[$btn_k]) && isset($attrs[$btn_k]['input'])) {
				// must be an associative array with key = attribute name, value = attribute value
				foreach ($attrs[$btn_k]['input'] as $attr_name => $attr_val) {
					// javascript events could be attached like 'onchange'=>'myCallback(this.value)'
					$radio_attributes[$attr_name] = $attr_val;
				}
			}
			$radio_attr_string = '';
			foreach ($radio_attributes as $attr_name => $attr_val) {
				if ($attr_val === true) {
					// short-attribute name, like "checked"
					$radio_attr_string .= $attr_name . ' ';
					continue;
				}
				$radio_attr_string .= $attr_name . '="' . $attr_val . '" ';
			}
			$multi_state_switch .= "\t\t" . '<input type="radio" name="' . $name . '" value="' . $btn_val . '" ' . $radio_attr_string . '/>' . "\n";

			// add button trigger
			$multi_state_switch .= "\t\t" . '<span class="vik-multiswitch-trigger"></span>' . "\n";

			// check button label text
			if (isset($labels[$btn_k])) {
				/**
				 * By default, the buttons of the toggle switch use an animation,
				 * which requires an absolute positioning of the "label-text".
				 * For this reason, there cannot be a minimum width for these texts
				 * and so the content should fit the default width. Usually, using
				 * a font-awesome icon is the best content. For using literal texts,
				 * like "Dark", "Light" etc.. the class "vik-multiswitch-noanimation"
				 * should be passed to the button label text.
				 */
				$label_txt = '';
				$label_class = '';
				if (!is_scalar($labels[$btn_k])) {
					// with an associative array we accept value, title and custom classes
					if (isset($labels[$btn_k]['value'])) {
						$label_txt = $labels[$btn_k]['value'];
					}
					if (isset($labels[$btn_k]['class'])) {
						$label_class = ' ' . ltrim($labels[$btn_k]['class']);
					}
				} else {
					// just a string, maybe with text or HTML mixed content
					$label_txt = $labels[$btn_k];
				}
				if (strlen($label_txt)) {
					// append button label text only if some text has been defined
					$multi_state_switch .= "\t\t" . '<span class="vik-multiswitch-txt' . $label_class . '">' . $label_txt . '</span>' . "\n";
				}
			}

			// end button label
			$multi_state_switch .= "\t" . '</label>' . "\n";
		}

		// end wrapper
		$multi_state_switch .= '</div>' . "\n";

		// check tooltip JS rendering
		if (!$tooltip_js_declared && $needs_tooltip) {
			// turn static flag on
			$tooltip_js_declared = 1;

			// add script declaration for JS rendering of tooltips
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration(
<<<JS
jQuery(function() {
	if (jQuery.isFunction(jQuery.fn.tooltip)) {
		jQuery('.vik-multiswitch-wrap label').tooltip();
	}
});
JS
			);
		}

		return $multi_state_switch;
	}

	/**
	 * Returns a list of supported fonts for the third-party visual editor (Quill).
	 * 
	 * @param 	bool 	$short_names 	whether to return short font names.
	 * 
	 * @return 	array 	list of font family names or short names.
	 * 
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function getVisualEditorFonts($short_names = false)
	{
		// supported fonts
		$font_families = ['Sans Serif', 'Arial', 'Courier', 'Garamond', 'Tahoma', 'Times New Roman', 'Verdana', 'Inconsolata', 'Sailec Light', 'Monospace'];

		if (!$short_names) {
			// return the regular names to be displayed
			return $font_families;
		}

		// return the "short" names of the supported fonts
		return array_map(function($font) {
			return str_replace(' ', '-', strtolower($font));
		}, $font_families);
	}

	/**
	 * Loads the necessary assets for the third-party visual editor (Quill).
	 * 
	 * @return 	void
	 * 
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function loadVisualEditorAssets()
	{
		// access the document
		$doc = JFactory::getDocument();

		// build the list of font families
		$font_families = $this->getVisualEditorFonts();
		$font_shortfam = $this->getVisualEditorFonts(true);
		$js_font_names = json_encode($font_shortfam);
		$css_font_decl = '';
		foreach ($font_families as $k => $font_name) {
			$font_val = $font_shortfam[$k];
			$css_font_decl .= '.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="' . $font_val . '"]::before,';
			$css_font_decl .= '.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="' . $font_val . '"]::before {' . "\n";
			$css_font_decl .= 'content: "' . $font_name . '";' . "\n";
			$css_font_decl .= 'font-family: "' . $font_name . '";' . "\n";
			$css_font_decl .= '}' . "\n";
		}

		// append inline CSS styles to document
		$doc->addStyleDeclaration('
			.ql-picker.ql-specialtags .ql-picker-label {
				padding-right: 18px;
			}
			.ql-picker.ql-specialtags .ql-picker-label:before {
				content: "' . htmlspecialchars(JText::_('VRC_CONDTEXT_TKN')) . '";
			}
		' . $css_font_decl);

		// append theme CSS to document
		$doc->addStyleSheet(VRC_ADMIN_URI . 'resources/quill/quill.snow.css');

		// append JS assets to document
		$this->addScript(VRC_ADMIN_URI . 'resources/quill/quill.js');
		$this->addScript(VRC_ADMIN_URI . 'resources/quill/quill-image-resize.min.js');
		$this->addScript(VRC_ADMIN_URI . 'resources/quill/vik-content-builder.js');

		// icon for mail wrapper
		$mail_wrapper_icn = '<i class="' . VikRentCarIcons::i('minus-square') . '" title="' . htmlspecialchars(JText::_('VRC_INSERT_CONT_WRAPPER')) . '"></i>';

		// icon for mail preview
		$mail_preview_icn = '<i class="' . VikRentCarIcons::i('eye') . '" title="' . htmlspecialchars(JText::_('VRCPREVIEW')) . '"></i>';

		// icon for property logo (home icon)
		$mail_homelogo_icn = '<i class="' . VikRentCarIcons::i('car') . '" title="' . htmlspecialchars(JText::_('VRCONFIGFOURLOGO')) . '"></i>';

		// append JS script declaration to document
		$doc->addScriptDeclaration(
<<<JS
// Quill pre-configuration
(function() {
	// configure Quill to use inline styles rather than classes
	
	var AlignClass = Quill.import('attributors/class/align');
	Quill.register(AlignClass, true);

	var BackgroundClass = Quill.import('attributors/class/background');
	Quill.register(BackgroundClass, true);

	var ColorClass = Quill.import('attributors/class/color');
	Quill.register(ColorClass, true);

	var FontClass = Quill.import('attributors/class/font');
	Quill.register(FontClass, true);

	var SizeClass = Quill.import('attributors/class/size');
	Quill.register(SizeClass, true);

	var AlignStyle = Quill.import('attributors/style/align');
	Quill.register(AlignStyle, true);

	var BackgroundStyle = Quill.import('attributors/style/background');
	Quill.register(BackgroundStyle, true);

	var ColorStyle = Quill.import('attributors/style/color');
	Quill.register(ColorStyle, true);

	var SizeStyle = Quill.import('attributors/style/size');
	Quill.register(SizeStyle, true);

	var FontStyle = Quill.import('attributors/style/font');
	Quill.register(FontStyle, true);

	// set additional fonts
	var Font = Quill.import('formats/font');
	Font.whitelist = $js_font_names;
	Quill.register(Font, true);

	// register custom Blot for special tags
	var Inline = Quill.import('blots/inline');
	class Specialtag extends Inline {
		static create(value) {
			let node = super.create(value);
			if (value) {
				node.setAttribute('class', value);
			}
			return node;
		}

		static formats(domNode) {
			return domNode.getAttribute("class");
		}

		format(name, value) {
			if (name !== this.statics.blotName || !value) {
				return super.format(name, value);
			}
			if (value) {
				this.domNode.setAttribute('class', value);
			}
		}
	}
	Specialtag.blotName = 'specialtag';
	Specialtag.tagName = 'strong';
	Quill.register(Specialtag);

	// register custom Blot for mail-wrapper
	var BlockEmbed = Quill.import('blots/block/embed');
	class MailWrapper extends BlockEmbed { }
	MailWrapper.blotName = 'mailwrapper';
	MailWrapper.className = 'vrc-editor-hl-mailwrapper';
	MailWrapper.tagName = 'hr';
	Quill.register(MailWrapper);

	// register custom Blot for preview
	class Preview extends Inline { }
	Preview.blotName = 'preview';
	Preview.tagName = 'span';
	Quill.register(Preview);

	// register custom icons for mail-wrapper and preview
	var icons = Quill.import('ui/icons');
	icons['mailwrapper'] = '$mail_wrapper_icn';
	icons['preview'] = '$mail_preview_icn';
	icons['homelogo'] = '$mail_homelogo_icn';
})();
JS
		);
	}

	/**
	 * Renders a third-party visual editor (Quill).
	 * 
	 * @param 	string 	$name 	the input name of the textarea field.
	 * @param 	string 	$value 	the current value of the textarea field/editor.
	 * @param 	array 	$attrs 	list of associative array attributes for the textarea.
	 * @param 	array 	$opts 	associative array of options for the editor.
	 * @param 	array 	$btns 	associative array of custom buttons for the editor (special tags).
	 * 
	 * @return 	string 	the necessary HTML to render the visual editor.
	 * 
	 * @since 	1.15.0 (J) - 1.3.0 (WP)
	 */
	public function renderVisualEditor($name, $value, $attrs = array(), $opts = array(), $btns = array())
	{
		if (empty($name)) {
			return null;
		}

		// build the AJAX endpoint for uploading files and to preview the message
		$upload_endpoint   = VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=upload_media_file');
		$ajax_preview_mess = VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=mail.preview_visual_editor');
		$ajax_logo_url 	   = VikRentCar::ajaxUrl('index.php?option=com_vikrentcar&task=mail.get_default_logo');

		// the HTML to build
		$editor = "\n";

		// static editor counter
		static $editor_counter = 0;

		// increase counter for this instance
		$editor_counter++;

		// build id attributes for text and visual editors
		$editor_id = 'vik-contentbuilder-editor-' . $editor_counter;
		if (!isset($attrs['id'])) {
			$attrs['id'] = 'vik-contentbuilder-tarea-' . $editor_counter;
		}

		// textarea attributes
		$ta_attributes = array();
		foreach ($attrs as $aname => $aval) {
			if ($aname == 'name') {
				// skip reserved attribute name
				continue;
			}
			$ta_attributes[] = $aname . '="' . JHtml::_('esc_attr', $aval) . '"';
		}

		// labels for modes and editor
		$text_html_lbl 	 = JText::_('VRC_MODE_TEXTHTML');
		$visual_mode_lbl = JText::_('VRC_MODE_VISUAL');
		JText::script('VRC_CONT_WRAPPER');
		JText::script('VRC_CONT_WRAPPER_HELP');

		// allowed modes to display the visual editor
		$allowed_modes = array(
			'text' 		   => $text_html_lbl,
			'visual' 	   => $visual_mode_lbl,
			'modal-visual' => $visual_mode_lbl,
		);

		// define the default buttons to display for mode switching
		$modes = array(
			'text' 	 	   => $text_html_lbl,
			'modal-visual' => $visual_mode_lbl,
		);

		// overwrite modes to display
		if (isset($opts['modes']) && is_array($opts['modes']) && count($opts['modes'])) {
			// check if the given array is associative, hence inclusive of texts
			if (array_keys($opts['modes']) != range(0, (count($opts['modes']) - 1))) {
				// replace modes
				$modes = $opts['modes'];
			} else {
				// only the allowed keys must have been passed
				$new_modes = array();
				foreach ($opts['modes'] as $mode_type) {
					if (!isset($allowed_modes[$mode_type])) {
						continue;
					}
					$new_modes[$mode_type] = $allowed_modes[$mode_type];
				}
				$modes = count($new_modes) ? $new_modes : $modes;
			}
		}

		// overwrite default mode
		if (isset($opts['def_mode']) && isset($modes[$opts['def_mode']]) && count($modes) > 1) {
			$def_mode_val = $modes[$opts['def_mode']];
			unset($modes[$opts['def_mode']]);
			// sort modes accordingly
			$modes = array_merge(array($opts['def_mode'] => $def_mode_val), $modes);
			// reset the array pointer
			reset($modes);
		}

		// set default mode
		$default_mode = key($modes);

		// build visual editor JS options
		$js_editor_opts = [
			'snippetsyntax' => true,
			'modules' => [
				'toolbar' => [
					'container' => [
						[
							[
								'font' => $this->getVisualEditorFonts(true)
							],
						],
						[
							[
								'header' => [1, 2, 3, 4, 5, 6, false]
							]
						],
						[
							'bold',
							'italic',
							'underline',
							'strike',
							'blockquote',
						],
						[
							['align'  => []],
							['indent' => '-1'],
							['indent' => '+1'],
						],
						[
							[
								'color' => []
							],
							[
								'background' => []
							],
						],
						[
							['list' => 'ordered'],
							['list' => 'bullet'],
						],
						[
							'link',
							'image',
							'homelogo',
						],
						[
							'mailwrapper',
							'preview',
						],
					],
				],
				'imageResize' => [
					'displaySize' => true,
				],
			],
			'theme'   => 'snow',
		];

		// build the list of special tags to be added to the editor
		$special_tags_btns = array();
		foreach ($btns as $tag_val) {
			$special_tags_btns[] = $tag_val;
		}

		if (count($special_tags_btns)) {
			// add custom buttons to the editor to manage special tags
			$js_editor_opts['modules']['toolbar']['container'][] = array(
				array('specialtags' => $special_tags_btns)
			);
			// append CSS inline styles
			$editor .= '<style type="text/css">' . "\n";
			foreach ($special_tags_btns as $tag_val) {
				$editor .= '.ql-picker.ql-specialtags .ql-picker-item[data-value="' . $tag_val . '"]:before {
					content: "' . $tag_val . '";
				}' . "\n";
			}
			$editor .= '</style>' . "\n";
		}

		// attept to pretty print a JSON encoded string for the editor options
		$editor_opts_str = defined('JSON_PRETTY_PRINT') ? json_encode($js_editor_opts, JSON_PRETTY_PRINT) : json_encode($js_editor_opts);

		// safe default value for editor (HTML tags should not be converted to entities)
		$safe_value = preg_replace("/(<\/ ?textarea>)+/i", '', $value);

		// the HTML to render the visual editor
		$html_visual_editor = '<div class="vik-contentbuilder-editor-container" id="' . $editor_id . '"></div>';

		// build the actual HTML content
		$editor .= '<div class="vik-contentbuilder-wrapper">' . "\n";
		if (count($modes) > 1) {
			// display buttons to switch mode only if more than one mode available
			$editor .= "\t" . '<div class="vik-contentbuilder-switcher">' . "\n";
			foreach ($modes as $key => $val) {
				if (!isset($allowed_modes[$key])) {
					continue;
				}
				$editor .= "\t\t" . '<button type="button" class="btn btn-small vik-contentbuilder-switcher-btn' . ($default_mode == $key ? ' vik-contentbuilder-switcher-btn-active' : '') . '" data-switch="' . $key . '" onclick="VikContentBuilder.switchMode(this);">' . $val . '</button>' . "\n";
			}
			$editor .= "\t" . '</div>' . "\n";
		}
		$editor .= "\t" . '<div class="vik-contentbuilder-inner">' . "\n";
		$editor .= "\t\t" . '<textarea name="' . $name . '" data-switch="text" ' . implode(' ', $ta_attributes) . ' style="' . ($default_mode != 'text' ? 'display: none;' : '') . '">' . $safe_value . '</textarea>' . "\n";
		if (isset($modes['visual'])) {
			$editor .= "\t\t" . '<div class="vik-contentbuilder-container" data-switch="visual" style="' . ($default_mode != 'visual' ? 'display: none;' : '') . '">' . "\n";
			$editor .= "\t\t\t" . $html_visual_editor . "\n";
			$editor .= "\t\t" . '</div>' . "\n";
		}
		if (isset($modes['modal-visual'])) {
			$editor .= "\t\t" . '<div class="vrc-modal-overlay-block vik-contentbuilder-modal" data-switch="modal-visual" data-appendto=".vik-contentbuilder-modal-content" style="' . ($default_mode != 'modal-visual' ? 'display: none;' : '') . '">' . "\n";
			$editor .= "\t\t\t" . '<a class="vrc-modal-overlay-close" href="javascript: void(0);" onclick="VikContentBuilder.switchMode(this, \'text\');"></a>' . "\n";
			$editor .= "\t\t\t" . '<div class="vrc-modal-overlay-content vik-contentbuilder-modal-content">' . "\n";
			$editor .= "\t\t\t\t" . '<div class="vrc-modal-overlay-content-head vik-contentbuilder-modal-head"><span class="vrc-modal-overlay-close-times" onclick="VikContentBuilder.switchMode(this, \'text\');">&times;</span></div>' . "\n";
			$editor .= "\t\t\t\t" . '<div class="vrc-modal-overlay-content-body vik-contentbuilder-modal-body">' . "\n";
			$editor .= "\t\t\t\t\t" . (!isset($modes['visual']) ? $html_visual_editor : '') . "\n";
			$editor .= "\t\t\t\t" . '</div>' . "\n";
			$editor .= "\t\t\t" . '</div>' . "\n";
			$editor .= "\t\t" . '</div>' . "\n";
		}
		$editor .= "\t" . '</div>' . "\n";
		$editor .= '</div>' . "\n";
		$editor .= "\n";
		
		// add JS script to HTML content
		$editor .= '<script>' . "\n";
		$editor .= 'jQuery(function() {
			var vrc_toast_mailwrapper = null;
			var visual_editor_handlers = {
				"specialtags": function(tag) {
					if (tag) {
						var cursorPosition = this.quill.getSelection().index;
						this.quill.insertText(cursorPosition, tag, "specialtag", "vrc-editor-hl-specialtag");
						cursorPosition += tag.length + 1;
						this.quill.setSelection(cursorPosition, "silent");
						this.quill.insertText(cursorPosition, " ");
						this.quill.setSelection(cursorPosition + 1, "silent");
						this.quill.deleteText(cursorPosition - 1, 1);
					}
				},
				"image": function(clicked) {
					var img_handler = new VikContentBuilderImageHandler(this.quill);
					img_handler.setEndpoint("' . $upload_endpoint . '").present();
				},
				"mailwrapper": function(clicked) {
					var range = this.quill.getSelection(true);
					this.quill.insertText(range.index, "\n", "user");
					this.quill.insertEmbed(range.index + 1, "mailwrapper", true, "user");
					this.quill.setSelection(range.index + 2, "silent");
					if (!vrc_toast_mailwrapper) {
						vrc_toast_mailwrapper = 1;
						VRCToast.enqueue(new VRCToastMessage({
							title: 	Joomla.JText._("VRC_CONT_WRAPPER"),
							body:  	Joomla.JText._("VRC_CONT_WRAPPER_HELP"),
							icon:  	"' . VikRentCarIcons::i('minus-square') . '",
							delay: 	{
								min: 6000,
								max: 20000,
								tollerance: 4000,
							},
							action: () => {
								VRCToast.dispose(true);
							}
						}));
					}
				},
				"preview": function(clicked) {
					VRCCore.doAjax("' . $ajax_preview_mess . '", {
						content: this.quill.root.innerHTML,
						bid: (typeof window["vrc_current_bid"] !== "undefined" ? window["vrc_current_bid"] : null)
					}, (resp) => {
						var pop_win = window.open("", "", "width=800, height=600, scrollbars=yes");
						pop_win.document.body.innerHTML = resp[0];
					}, (err) => {
						console.log(err);
						alert(err.responseText);
					});
				},
				"homelogo": function(clicked) {
					VRCCore.doAjax("' . $ajax_logo_url . '", {}, (resp) => {
						try {
							this.quill.insertEmbed(this.quill.getSelection().index, "image", resp.url);
						} catch(e) {
							alert("Generic logo image error");
						}
					}, (err) => {
						console.log(err);
						alert(err.responseText);
					});
				}
			};
			var visual_editor_ext_opts = ' . $editor_opts_str . ';
			visual_editor_ext_opts["modules"]["toolbar"]["handlers"] = visual_editor_handlers;
			var visual_editor = new Quill("#' . $editor_id . '", visual_editor_ext_opts);
			var editor_content = jQuery("textarea#' . $attrs['id'] . '").val();
			if (editor_content && editor_content.length) {
				if (editor_content.indexOf("<") >= 0) {
					// replace special tags
					editor_content = editor_content.replace(/([^"\']|^)({(?:condition: ?)?[a-z0-9_]{5,64}})([^"\']|$)/g, function(match, before, tag, after) {
						return before + "<strong class=\"vrc-editor-hl-specialtag\">" + tag + "</strong>" + after;
					});
					var editor_delta = visual_editor.clipboard.convert(editor_content);
					// set editor HTML content
					visual_editor.setContents(editor_delta, "silent");
				} else {
					// set text content
					visual_editor.setText(editor_content, "silent");
				}
			}
			visual_editor.on("text-change", function(delta, source) {
				jQuery("textarea#' . $attrs['id'] . '").val(visual_editor.root.innerHTML);
			});
			jQuery("textarea#' . $attrs['id'] . '").on("change", function() {
				var editor_content = jQuery(this).val();
				var editor_delta = visual_editor.clipboard.convert(editor_content);
				visual_editor.setContents(editor_delta, "silent");
			});
			try {
				// push editor instance to the pool
				VikContentBuilder.pushEditor(visual_editor);
			} catch(e) {
				console.error("Could not push new visual editor instance", e);
			}
		});' . "\n";
		$editor .= '</script>' . "\n";

		// return the necessary HTML string to be displayed
		return $editor;
	}
}
