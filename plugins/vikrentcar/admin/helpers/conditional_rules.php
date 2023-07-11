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
 * Helper class for the conditional rules.
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarHelperConditionalRules
{
	/**
	 * The singleton instance of the class.
	 *
	 * @var VikRentCarHelperConditionalRules
	 */
	protected static $instance = null;

	/**
	 * A flag that indicates the rules debug mode.
	 *
	 * @var bool
	 */
	public static $debugRules = false;

	/**
	 * An array to store some cached/static values.
	 *
	 * @var array
	 */
	protected static $helper = null;

	/**
	 * Logs the execution of all the complex template
	 * editing methods that manipulate the HTML/PHP DOM.
	 *
	 * @var array
	 */
	protected static $editingLog = null;

	/**
	 * The database handler instance.
	 *
	 * @var object
	 */
	protected $dbo;

	/**
	 * The list of rules instances loaded.
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * The VikRentCar translation object.
	 *
	 * @var object
	 */
	protected $vrc_tn;

	/**
	 * Class constructor is protected.
	 *
	 * @see 	getInstance()
	 */
	protected function __construct()
	{
		static::$helper = array();
		$this->dbo = JFactory::getDbo();
		$this->rules = array();
		$this->vrc_tn = VikRentCar::getTranslator();
		$this->load();
	}

	/**
	 * Returns the global object, either
	 * a new instance or the existing instance
	 * if the class was already instantiated.
	 *
	 * @return 	self 	A new instance of the class.
	 */
	public static function getInstance()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Loads a list of all available conditional rules.
	 *
	 * @return 	self
	 */
	protected function load()
	{
		// require main/parent conditional-rule class
		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'conditional_rule.php');

		$rules_base  = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'conditionalrules' . DIRECTORY_SEPARATOR;
		$rules_files = glob($rules_base . '*.php');

		/**
		 * Trigger event to let other plugins register additional rules.
		 *
		 * @return 	array 	A list of supported rules.
		 */
		$list = JFactory::getApplication()->triggerEvent('onLoadConditionalRules');
		foreach ($list as $chunk) {
			// merge default rule files with the returned ones
			$rules_files = array_merge($rules_files, (array)$chunk);
		}

		foreach ($rules_files as $rf) {
			try {
				// require rule class file
				if (is_file($rf)) {
					require_once($rf);
				}

				// instantiate rule object
				$classname  = 'VikRentCarConditionalRule' . str_replace(' ', '', ucwords(str_replace('_', ' ', basename($rf, '.php'))));
				if (class_exists($classname)) {
					$rule = new $classname();
					// push rule object
					array_push($this->rules, $rule);
				}
			} catch (Exception $e) {
				// do nothing
			}
		}

		return $this;
	}

	/**
	 * Gets the list of conditional rules instantiated.
	 *
	 * @return 	array 	list of conditional rules objects.
	 */
	public function getRules()
	{
		return $this->rules;
	}

	/**
	 * Gets a single conditional rule instantiated.
	 * 
	 * @param 	string 	$id 	the rule identifier.
	 *
	 * @return 	mixed 	the conditional rule object, false otherwise.
	 */
	public function getRule($id)
	{
		foreach ($this->rules as $rule) {
			if ($rule->getIdentifier() != $id) {
				continue;
			}
			return $rule;
		}

		return false;
	}

	/**
	 * Gets a list of sorted rule names, ids and descriptions.
	 *
	 * @return 	array 	associative and sorted rules list.
	 */
	public function getRuleNames()
	{
		$names = array();
		$pool  = array();

		foreach ($this->rules as $rule) {
			$id 	= $rule->getIdentifier();
			$name 	= $rule->getName();
			$descr 	= $rule->getDescription();
			$rdata = new stdClass;
			$rdata->id 	= $id;
			$rdata->name 	= $name;
			$rdata->descr 	= $descr;
			$names[$name] 	= $rdata;
		}

		// apply sorting by name
		ksort($names);

		// push sorted rules to pool
		foreach ($names as $rdata) {
			array_push($pool, $rdata);
		}

		return $pool;
	}

	/**
	 * Tells whether the rule object overrides the method from its parent
	 * class. Useful to distinguish action-rules from filter-rules.
	 * 
	 * @param 	object 	$rule 		child class object of VikRentCarConditionalRule.
	 * @param 	string 	$method 	the name of the method to check if it was overridden.
	 * 
	 * @return 	bool 				true if overridden, false otherwise.
	 */
	public function supportsAction($rule, $method = 'callbackAction')
	{
		if (!class_exists('ReflectionMethod')) {
			return false;
		}

		$reflect = new ReflectionMethod($rule, $method);

		return ($reflect->getDeclaringClass()->getName() == get_class($rule));
	}

	/**
	 * Helper method for the controller to compose the rules
	 * of the conditional text by parsing all input values in the same order requested.
	 * 
	 * @return 	array 	list of stdClass object with the various rules params.
	 */
	public function composeRulesParamsFromRequest()
	{
		$rules_list = array();
		$raw_vals = JFactory::getApplication()->input->getArray();

		foreach ($raw_vals as $raw_inp_key => $rule_inp_vals) {
			foreach ($this->rules as $rule) {
				$rule_id = $rule->getIdentifier();
				$rule_inp_key = basename($rule_id, '.php');
				if ($rule_inp_key != $raw_inp_key) {
					continue;
				}
				// rule found, make sure the settings are not empty
				$has_vals = false;
				foreach ($rule_inp_vals as $rule_inp_val) {
					if (is_array($rule_inp_val) && count($rule_inp_val)) {
						$has_vals = true;
						break;
					}
					if (is_string($rule_inp_val) && strlen($rule_inp_val)) {
						$has_vals = true;
						break;
					}
				}
				if (!$has_vals) {
					// do not store empty rule params
					continue 2;
				}
				// compose rule object
				$rule_data = new stdClass;
				$rule_data->id = $rule_id;
				$rule_data->params = $rule_inp_vals;
				// push rule to list
				array_push($rules_list, $rule_data);
			}
		}

		return $rules_list;
	}

	/**
	 * Helper method to load all special tags and related records.
	 * 
	 * @param 	string 	$orby_col 	the column to order by.
	 * @param 	string 	$orby_dir 	the order by direction.
	 * 
	 * @return 	array 	associative list of special-tags (key) records (value).
	 */
	public function getSpecialTags($orby_col = 'name', $orby_dir = 'ASC')
	{
		$special_tags = array();

		$q = "SELECT `ct`.* FROM `#__vikrentcar_condtexts` AS `ct` ORDER BY `ct`.`{$orby_col}` {$orby_dir};";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows()) {
			$records = $this->dbo->loadAssocList();
			$this->vrc_tn->translateContents($records, '#__vikrentcar_condtexts');
			foreach ($records as $record) {
				// decode rules
				$record['rules'] = !empty($record['rules']) ? json_decode($record['rules']) : array();
				$record['rules'] = !is_array($record['rules']) ? array() : $record['rules'];
				// push record
				$special_tags[$record['token']] = $record;
			}
		}

		return $special_tags;
	}

	/**
	 * Helper method to load one precise conditional text from the given special tag.
	 * 
	 * @param 	string 	$token 	the special tag (token) to look for.
	 * 
	 * @return 	array 	the record of the conditional text found or an empty array.
	 */
	public function getBySpecialTag($token)
	{
		$cond_text = array();

		$q = "SELECT * FROM `#__vikrentcar_condtexts` WHERE `token`=" . $this->dbo->quote($token) . ";";
		$this->dbo->setQuery($q);
		$this->dbo->execute();
		if ($this->dbo->getNumRows()) {
			$cond_text = $this->dbo->loadAssoc();
			$this->vrc_tn->translateContents($cond_text, '#__vikrentcar_condtexts');
		}

		return $cond_text;
	}

	/**
	 * Helper method to store information in the helper array.
	 * 
	 * @param 	mixed 	$key 	the key or array of keys to set.
	 * @param 	mixed 	$val 	the value or array of values to set.
	 * 
	 * @return 	self
	 */
	public function set($key, $val = null)
	{
		if (!is_string($key) && !is_array($key)) {
			return $this;
		}

		if (is_string($key)) {
			$key = array($key);
		}
		if (!is_array($val)) {
			$val = array($val);
		}

		foreach ($key as $i => $prop) {
			if (!isset($val[$i])) {
				continue;
			}
			static::$helper[$prop] = $val[$i];
		}

		return $this;
	}

	/**
	 * Helper method to get information from the helper array.
	 * 
	 * @param 	string 	$key 	the key to get.
	 * @param 	string 	$def 	the default value to get.
	 * 
	 * @return 	mixed 			the requested key value.
	 */
	public function get($key, $def = null)
	{
		return isset(static::$helper[$key]) ? static::$helper[$key] : $def;
	}

	/**
	 * Parses all tokens in the given template string and applies the
	 * conditional texts found if all rules are compliant.
	 * 
	 * @param 	string 	$tmpl 	the template string to parse, passed by reference.
	 * 
	 * @return 	mixed 			false if no tokens found, integer for how many tokens were applied.
	 */
	public function parseTokens(&$tmpl)
	{
		preg_match_all('/\{condition: ?([a-zA-Z0-9_]+)\}/U', $tmpl, $matches);

		$tot_tokens = count($matches[0]);

		if (!$tot_tokens) {
			// no tokens to parse
			return false;
		}

		// default empty replacement
		$null_replace = '';

		// load all helper property keys and values
		$prop_keys = array_keys(static::$helper);
		$prop_vals = array_values(static::$helper);

		// load all conditional text records
		$cond_texts = $this->getSpecialTags();

		// iterate through all tokens found
		foreach ($matches[0] as $token) {
			if (!isset($cond_texts[$token])) {
				if (static::$debugRules) {
					// debuggining at this step cannot be enabled as the record was not found
					$null_replace = "{$token} was not found";
				}
				// remove token from template file
				$tmpl = str_replace($token, $null_replace, $tmpl);
				// decrease total tokens applied
				$tot_tokens--;
				// iterate to the next token, if any
				continue;
			}

			// set debug mode according to record
			static::$debugRules = (bool)$cond_texts[$token]['debug'];

			// set flag to know whether the token was compliant
			$compliant = false;
			
			// parse all rules for this conditional text
			foreach ($cond_texts[$token]['rules'] as $rule_data) {
				if (empty($rule_data->id)) {
					continue;
				}
				$rule = $this->getRule($rule_data->id);
				if ($rule === false) {
					continue;
				}
				// inject params and booking to rule, then check if compliant
				$compliant = $rule->setParams($rule_data->params)->setProperties($prop_keys, $prop_vals)->isCompliant();
				if (!$compliant) {
					if (static::$debugRules) {
						$null_replace = JText::sprintf('VRC_DEBUG_RULE_CONDTEXT', $rule->getName(), $token);
					}
					// all rules must be compliant with the booking
					break;
				}
			}

			if (!$compliant) {
				// remove token from template file
				$tmpl = str_replace($token, $null_replace, $tmpl);
				// decrease total tokens applied
				$tot_tokens--;
				// iterate to the next token, if any
				continue;
			}

			// all rules were compliant, trigger callback and manipulation actions
			foreach ($cond_texts[$token]['rules'] as $rule_data) {
				if (empty($rule_data->id)) {
					continue;
				}
				$rule = $this->getRule($rule_data->id);
				if ($rule === false) {
					continue;
				}
				// inject params and booking to rule
				$rule->setParams($rule_data->params)->setProperties($prop_keys, $prop_vals);
				// trigger callback action
				$rule->callbackAction();
				// allow rule to manipulate the actual message
				$cond_texts[$token]['msg'] = $rule->manipulateMessage($cond_texts[$token]['msg']);
			}

			/**
			 * The message of the conditional text rules is usually written through a WYSIWYG editor
			 * which may contain HTML tags. However, the context where these texts are being used is
			 * unknown to VRC, and so we can detect from the content whether plain text messages are
			 * necessary, maybe for sending an SMS message. We allow the use of special strings to
			 * detect if no HTML should ever be included in the message, like [sms] or [plain text].
			 */
			$requires_plain_text = false;
			if (preg_match_all("/(\[sms\]|\[plain_? ?text\])+/i", $cond_texts[$token]['msg'], $plt_matches)) {
				$requires_plain_text = true;
				foreach ($plt_matches[1] as $plt_match) {
					$cond_texts[$token]['msg'] = str_replace($plt_match, '', $cond_texts[$token]['msg']);
				}
				$cond_texts[$token]['msg'] = strip_tags($cond_texts[$token]['msg']);
			}

			/**
			 * @wponly 	we need to let WordPress parse the paragraphs in the message.
			 */
			if (defined('ABSPATH') && !empty($cond_texts[$token]['msg']) && !$requires_plain_text) {
				$cond_texts[$token]['msg'] = wpautop($cond_texts[$token]['msg']);
			}

			/**
			 * Make sure any src/href attributes does not contain relative URLs.
			 */
			$cond_texts[$token]['msg'] = preg_replace_callback("/\s*(src|href)=([\"'])(.*?)[\"']/i", function($match) {
				// check if the URL starts with the base domain
				if (stripos($match[3], JUri::root()) !== 0 && !preg_match("/^(https?:\/\/|www\.)/i", $match[3])) {
					// prepend base domain to URL
					$match[0] = ' ' . $match[1] . '=' . $match[2] . JUri::root() . $match[3] . $match[2];
				}
				return $match[0];
			}, $cond_texts[$token]['msg']);

			// finally, apply the message to the template
			$tmpl = str_replace($token, $cond_texts[$token]['msg'], $tmpl);
		}

		return $tot_tokens;
	}

	/**
	 * Toggles the rules debugging mode.
	 * 
	 * @return 	self
	 */
	public function toggleDebugging()
	{
		static::$debugRules = !static::$debugRules;

		return $this;
	}

	/**
	 * Returns the list of the template files paths supporting the conditional text tags.
	 * 
	 * @return 	array
	 */
	public static function getTemplateFilesPaths()
	{
		return [
			'email_tmpl.php' 		=> VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'email_tmpl.php',
			'pdf_tmpl.php' 			=> VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'pdf_tmpl.php',
			'checkin_pdf_tmpl.php' 	=> VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'checkin_pdf_tmpl.php',
			'invoice_tmpl.php' 		=> VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'invoices' . DIRECTORY_SEPARATOR . 'invoice_tmpl.php',
		];
	}

	/**
	 * Returns the list of the template files names supporting the conditional text tags.
	 * 
	 * @return 	array
	 */
	public static function getTemplateFilesNames()
	{
		return [
			'email_tmpl.php' 		=> JText::_('VRCONFIGEMAILTEMPLATE'),
			'pdf_tmpl.php' 			=> JText::_('VRCONFIGPDFTEMPLATE'),
			'checkin_pdf_tmpl.php' 	=> JText::_('VRCONFIGPDFCHECKINTEMPLATE'),
			'invoice_tmpl.php' 	   	=> JText::_('VRCONFIGPDFINVOICETEMPLATE'),
		];
	}

	/**
	 * Returns the list of the template files contents supporting the conditional text tags.
	 * 
	 * @param 	string 	$file 	the basename of the template file to read.
	 * 
	 * @return 	array
	 */
	public static function getTemplateFilesContents($file = null)
	{
		$templates = self::getTemplateFilesPaths();

		if (!empty($file) && isset($templates[$file])) {
			$templates = [
				$file => $templates[$file]
			];
		}

		$contents = [];
		foreach ($templates as $file => $path) {
			if (!is_file($path)) {
				continue;
			}
			switch ($file) {
				case 'email_tmpl.php':
					$contents[$file] = VikRentCar::loadEmailTemplate();
					break;
				case 'pdf_tmpl.php':
					$data = VikRentCar::loadPdfTemplate();
					$contents[$file] = $data[0];
					break;
				case 'checkin_pdf_tmpl.php':
					$contents[$file] = VikRentCar::loadCheckinPdfTemplate();
					break;
				case 'invoice_tmpl.php':
					$data = VikRentCar::loadInvoiceTmpl();
					$contents[$file] = $data[0];
					break;
				default:
					break;
			}
		}

		return $contents;
	}

	/**
	 * Returns the list of the template files code supporting the conditional text tags.
	 * 
	 * @param 	string 	$file 	the basename of the template file to read.
	 * 
	 * @return 	mixed 			array of files code, or just the code of the requested file.
	 */
	public static function getTemplateFileCode($file = null)
	{
		$templates = self::getTemplateFilesPaths();

		if (!empty($file) && isset($templates[$file])) {
			$templates = [
				$file => $templates[$file]
			];
		}

		$contents = [];
		foreach ($templates as $f => $path) {
			if (!is_file($path)) {
				continue;
			}
			$fp = fopen($path, 'r');
			if (!$fp) {
				continue;
			}
			$fcode = '';
			while (!feof($fp)) {
				$fcode .= fread($fp, 8192);
			}
			fclose($fp);
			if (empty($fcode)) {
				continue;
			}
			$contents[$f] = $fcode;
		}

		return !empty($file) && isset($contents[$file]) ? $contents[$file] : $contents;
	}

	/**
	 * Updates the source code of the given template file name.
	 * 
	 * @param 	string 	$file 	the basename of the template file to write.
	 * @param 	string 	$code 	the new code of the template file to write.
	 * 
	 * @return 	bool 			true on success, false otherwise.
	 */
	public static function writeTemplateFileCode($file, $code)
	{
		$templates = self::getTemplateFilesPaths();

		if (!isset($templates[$file]) || empty($code)) {
			return false;
		}

		$fp = fopen($templates[$file], 'w+');
		if (!$fp) {
			return false;
		}
		$bytes = fwrite($fp, $code);
		fclose($fp);

		return ($bytes !== false);
	}

	/**
	 * Tells whether the given special tag is used in the passed content.
	 * 
	 * @param 	string 	$tag 		the special tag to look for.
	 * @param 	string 	$content 	the content of the template file.
	 * 
	 * @return 	bool
	 */
	public static function isTagInContent($tag, $content)
	{
		if (empty($tag) || empty($content)) {
			return false;
		}

		if (strpos($tag, '{condition:') === false) {
			// invalid conditional text special tag
			return false;
		}

		return (strpos($content, $tag) !== false);
	}

	/**
	 * Makes sure the path obtained to query the raw source code will produce
	 * results in the raw php code. Some Libxml constants may skip html or
	 * style tags, while the html source code may contain more tbody tags
	 * than the php source code as it is parsed by the browser, where table
	 * tags without a nested tbody tag will add it automatically.
	 * 
	 * @param 	string 		$tag_path 	the node-path to the tag obtained
	 * 									from the html source code.
	 * @param 	DOMXpath 	$php_path 	DOMXpath object for the php code.
	 * 
	 * @return  string 					a valid query path to be used.
	 *
	 * @see 	addTagByComparingSources() and addStylesByComparingSources()
	 */
	public static function adjustDOMXpathQuery($tag_path, $php_xpath)
	{
		// Xpath query expressions require two leading slashes
		if (substr($tag_path, 0, 2) !== '//' && substr($tag_path, 0, 1) == '/') {
			$tag_path = '/' . $tag_path;
		}

		// take care of any count mismatch of tbody tags
		$tbody_in_html = substr_count($tag_path, 'tbody');
		$tbody_in_php = $php_xpath->evaluate("count(//tbody)");
		if ($tbody_in_html > 0 && (int)$tbody_in_php < $tbody_in_html) {
			// raw php code has got less tbody nodes than html code
			$tbody_in_php = (int)$tbody_in_php;
			$parts = explode('/tbody', $tag_path);
			$new_tag_path = '';
			foreach ($parts as $k => $path_part) {
				// use only the amount of tbody found in php code
				$new_tag_path .= $path_part . ($k < $tbody_in_php ? '/tbody' : '');
			}
			// set new path to tag
			$tag_path = $new_tag_path;
		}

		// foresee the result of the Xpath query
		$testcase = $php_xpath->query($tag_path);
		if (!$testcase || !$testcase->length) {
			// this Xpath query is about to fail, try to do something
			
			if (strpos($tag_path, '/style') !== false) {
				// style tags found in the HTML source code may not be available in the PHP source code
				$tag_path = str_replace('/style', '', $tag_path);
			}
			
			/**
			 * BC with old invoice template file structure where no table is ever inside another.
			 */
			if (strpos($tag_path, '//table/table[2]') !== false) {
				$tag_path = str_replace('//table/table[2]', '//table[3]', $tag_path);
			} elseif (strpos($tag_path, '//table/table[1]') !== false) {
				$tag_path = str_replace('//table/table[1]', '//table[2]', $tag_path);
			}
		}
		

		return $tag_path;
	}

	/**
	 * Earlier versions of PHP and Libxml may not support to load code strings
	 * without adding the DOCTYPE, the html+body tags, and any missing/malformed tag.
	 * This will break the entire PHP source code of the file by getting HTML entities
	 * like ?&gt; for the PHP closing tag, or =&gt; for the array key-val operator.
	 * 
	 * @param 	string 	$php_code 	the raw source code generated by DOMDocument.
	 * @param 	object 	$php_dom 	the DOMDocument object of the php code.
	 * 
	 * @return 	string 				the clean PHP source code to write onto the file.
	 */
	public static function cleanPHPSourceCode($php_code, $php_dom)
	{
		/**
		 * The following constants will produce a different nodePath, and they are available
		 * starting from PHP 5.4 and Libxml >= 2.7.8.
		 */
		$libxml_updated = defined('LIBXML_HTML_NOIMPLIED') && defined('LIBXML_HTML_NODEFDTD');
		//

		// immediately restore the PHP tags that could have been converted to HTML entities
		$php_code = str_replace(array('&lt;?php', '?&gt;'), array('<?php', '?>'), $php_code);

		// remove doctype
		$php_code = preg_replace("/(^<!DOCTYPE.*\R)/i", '', $php_code);

		/**
		 * Grab anything between PHP tags to make sure there are no syntax errors due to HTML entities.
		 * 
		 * @see 	In the callback we should NEVER user strip_tags as PHP can contain HTML.
		 * 			We can at most look for some HTML tags mixed to PHP code to remove only them.
		 */
		$php_code = preg_replace_callback("/<\?php(.*?)\?>/si", function($match) {
			// use just what's inside the PHP tags
			$pure_code = $match[1];
			/**
			 * PHP comments in the check-in document template file may get for array declarations
			 * one opening "<p>" tag next to the HTML entity for "=>". Therefore, we remove it.
			 */
			if (strpos($pure_code, '=&gt;') !== false && strpos($pure_code, 'array') !== false && preg_match_all("/(<[a-zA-Z]+>)/", $pure_code, $extra_tags)) {
				foreach ($extra_tags[0] as $extra_tag) {
					// strip the tag as well as its closing version
					$pure_code = str_replace(array($extra_tag, str_replace('<', '</', $extra_tag)), '', $pure_code);
				}
			}
			// return the decoded HTML entities needed by PHP
			return '<?php' . html_entity_decode($pure_code) . '?>';
		}, $php_code);

		// get rid of html and body tags
		$php_code = str_replace(array('<html>', '<body>', '</html>', '</body>'), '', $php_code);

		// check if we have an HTML closing tag after the PHP closing tag due to previous manipulation of PHP code
		$php_code = preg_replace_callback("/\?>\R+(<\/?[a-zA-Z]+.*?>)/s", function($match) {
			if ($match[1] && strpos($match[1], '/') !== false) {
				return str_replace($match[1], '', $match[0]);
			}
			return $match[0];
		}, $php_code);

		/**
		 * If libxml is not updated, we load the HTML by enclosing the whole source within a placeholder DIV tag.
		 * This is to avoid getting the HTML and BODY tags started inside PHP code maybe, because it has a > or <.
		 */
		if (!$libxml_updated) {
			// get rid of the wrapper div tag, added as a placeholder to avoid getting html and body inside php code
			$wrapper = $php_dom->getElementsByTagName('div')->item(0);
			if ($wrapper) {
				// remove all children and store the element
				$wrapper = $wrapper->parentNode->removeChild($wrapper);
				while ($php_dom->firstChild) {
					$php_dom->removeChild($php_dom->firstChild);
				}
				// append children again
				while ($wrapper->firstChild ) {
					$php_dom->appendChild($wrapper->firstChild);
				}
				// get new HTML without the wrapper
				$php_code = $php_dom->saveHTML();
			}
		}
		//

		return $php_code;
	}

	/**
	 * Writes the source code of the template file onto a backup file.
	 * 
	 * @param 	string 	$file 		the basename of the template file.
	 * @param 	string 	$php_code 	the raw source code of the file.
	 * 
	 * @return 	bool 				True on success, false otherwise.
	 */
	public static function backupTemplateFileCode($file, $php_code)
	{
		$fp = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . $file . '.bkp', 'w+');
		if (!$fp) {
			return false;
		}
		
		$bytes = fwrite($fp, $php_code);
		fclose($fp);

		return ($bytes !== false);
	}

	/**
	 * Restores the source code of the template file from the backup file.
	 * 
	 * @param 	string 	$file 		the basename of the template file.
	 * 
	 * @return 	bool 				True on success, false otherwise.
	 */
	public static function restoreTemplateFileCode($file)
	{
		$backup_fpath = dirname(__FILE__) . DIRECTORY_SEPARATOR . $file . '.bkp';
		if (!is_file($backup_fpath)) {
			return false;
		}

		$fp = fopen($backup_fpath, 'r');
		if (!$fp) {
			return false;
		}
		
		$fcode = '';
		while (!feof($fp)) {
			$fcode .= fread($fp, 8192);
		}
		fclose($fp);
		
		if (empty($fcode)) {
			return false;
		}

		return self::writeTemplateFileCode($file, $fcode);
	}

	/**
	 * Compares the new HTML source code of the compiled template file
	 * to the raw source code of the template file. Finds the newly added
	 * tag in the HTML source code and adds it to the same position of the
	 * raw source code of the same template file. Used to add a new tag to the code.
	 * 
	 * @param 	string 	$tag 		the conditional text tag to add.
	 * @param 	string 	$file 		the basename of the template file.
	 * @param 	string 	$html_code 	the full HTML source code where the new tag is.
	 * @param 	string 	$php_code 	the raw source code where the new tag should be added.
	 * 
	 * @return 	string 				the new PHP source code to write onto the file.
	 */
	public static function addTagByComparingSources($tag, $file, $html_code, $php_code)
	{
		if (!class_exists('DOMDocument') || !class_exists('DOMXpath')) {
			// this sucks, we just append the tag to the end of the file
			$php_code .= "\n{$tag}\n";
			
			// log the case
			self::setEditingLog("Classes DOMDocument or DOMXpath are not available (" . __LINE__ . ")");

			return $php_code;
		}

		// backup the file source code no matter what
		self::backupTemplateFileCode($file, $php_code);

		/**
		 * The following constants will produce a different nodePath, and they are available
		 * starting from PHP 5.4 and Libxml >= 2.7.8.
		 */
		$libxml_updated = defined('LIBXML_HTML_NOIMPLIED') && defined('LIBXML_HTML_NODEFDTD');
		//

		// log data
		self::setEditingLog("Libxml support: " . (int)$libxml_updated);

		/**
		 * Suppress warnings for bad markup by using libxml's error handling functions.
		 * Errors could be retrieved by using print_r(libxml_get_errors(), true).
		 */
		libxml_use_internal_errors(true);
		//

		// load HTML source code
		$html_dom = new DOMDocument();
		if ($libxml_updated) {
			$html_dom->loadHTML($html_code, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		} else {
			$html_dom->loadHTML('<div>' . $html_code . '</div>');
		}
		
		// get DOMXPath instance of the html DOM Document
		$html_xpath = new DOMXpath($html_dom);
		// find DOMNodeList from given tag (there should be just one tag)
		$found_nodelist = $html_xpath->query("//*[text()[contains(., '{$tag}')]]");
		
		if (!$found_nodelist || !$found_nodelist->length) {
			// log the case
			self::setEditingLog("tag not found in html source code (" . __LINE__ . ")");

			// tag not found in html source code
			return $php_code;
		}

		// find the path to the first node occurrence of the given tag string
		$tag_path = $found_nodelist->item(0)->getNodePath();
		if (empty($tag_path)) {
			// log the case
			self::setEditingLog("unable to proceed without knowing the path to the tag (" . __LINE__ . ")");

			// unable to proceed without knowing the path to the tag
			return $php_code;
		}

		// log data
		self::setEditingLog("Node Path to tag in HTML source code: " . $tag_path);

		// import the raw php code to DOMDocument
		$php_dom = new DOMDocument();
		if ($libxml_updated) {
			$php_dom->loadHTML($php_code, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		} else {
			$php_dom->loadHTML('<div>' . $php_code . '</div>');
		}

		// get DOMXPath instance of the php DOM Document
		$php_xpath = new DOMXpath($php_dom);

		// adjust html path to tag to comply with the expression for the raw code
		$tag_path = self::adjustDOMXpathQuery($tag_path, $php_xpath);

		// log data
		self::setEditingLog("Adjusted Node Path: " . $tag_path);

		// query the raw source code to find the same path as a DOMNodeList
		$found_nodelist = $php_xpath->query($tag_path);

		if (!$found_nodelist || !$found_nodelist->length) {
			// log the case
			self::setEditingLog("Node Path to tag not found in php source code: {$tag_path} must be invalid (" . __LINE__ . ")");

			// path not found in php source code: $tag_path must be invalid
			return $php_code;
		}

		// create a text node with the special tag string
		$tag_element = $php_dom->createTextNode($tag);
		// append the tag string to the first (and only) path found
		$found_nodelist->item(0)->appendChild($tag_element);

		// obtain the new php source code
		$php_code = $php_dom->saveHTML();

		// log data
		self::setEditingLog("Tag appended to the given path. New template source code before cleaning:\n\n" . $php_code);

		// always clean up the PHP code to avoid breaking the file
		$php_code = self::cleanPHPSourceCode($php_code, $php_dom);

		/**
		 * @see 	the following code can help debugging the source code and entire flow.
		 *
		 * $fp = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'debug.txt', 'w+');
		 * fwrite($fp, implode("\n", self::getEditingLog()) . "\n\n\nclean source is:\n\n-------------\n" . $php_code);
		 * fclose($fp);
		 */

		return $php_code;
	}

	/**
	 * Compares the new HTML source code of the compiled template file
	 * to the raw source code of the template file. Finds the newly added
	 * class attributes in the HTML source code, gets it styling and adds it
	 * to the raw source code of the same template file. Used by the CSS inspector.
	 * 
	 * @param 	array 	$classes 	list of custom/temporary CSS classes to look for.
	 * @param 	string 	$file 		the basename of the template file.
	 * @param 	string 	$html_code 	the full HTML source code where the new classes are.
	 * @param 	string 	$php_code 	the raw source code where the new styles should be added.
	 * 
	 * @return 	string 				the new PHP source code to write onto the file.
	 * 
	 * @throws 	Exception 			if DOMDocument is not supported as nothing could be done.
	 */
	public static function addStylesByComparingSources($classes, $file, $html_code, $php_code)
	{
		if (!class_exists('DOMDocument') || !class_exists('DOMXpath')) {
			// we cannot proceed without these classes
			throw new Exception("DOMDocument or DOMXpath are missing in your PHP installation", 403);
		}

		// backup the file source code no matter what
		self::backupTemplateFileCode($file, $php_code);

		/**
		 * The following constants will produce a different nodePath, and they are available
		 * starting from PHP 5.4 and Libxml >= 2.7.8.
		 */
		$libxml_updated = defined('LIBXML_HTML_NOIMPLIED') && defined('LIBXML_HTML_NODEFDTD');
		//

		// log data
		self::setEditingLog("Libxml support: " . (int)$libxml_updated);

		/**
		 * Suppress warnings for bad markup by using libxml's error handling functions.
		 * Errors could be retrieved by using print_r(libxml_get_errors(), true).
		 */
		libxml_use_internal_errors(true);
		//

		// load HTML source code
		$html_dom = new DOMDocument();
		if ($libxml_updated) {
			$html_dom->loadHTML($html_code, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		} else {
			$html_dom->loadHTML('<div>' . $html_code . '</div>');
		}
		
		// get DOMXPath instance of the html DOM Document
		$html_xpath = new DOMXpath($html_dom);

		// compose pool of styles
		$styles_pool = array();

		foreach ($classes as $css_class) {
			// find DOMNodeList from given CSS class (there should be just one node)
			$found_nodelist = $html_xpath->query("//*[contains(@class, '" . $css_class . "')]");
			if (!$found_nodelist || !$found_nodelist->length) {
				// log the case
				self::setEditingLog("given CSS class {$css_class} not found in html source code (" . __LINE__ . ")");

				// given CSS class not found in html source code
				continue;
			}
			
			// log data
			self::setEditingLog("CSS class {$css_class} found in HTML source code");

			// get the first node
			$node = $found_nodelist->item(0);
			// make sure the node has a style attribute
			if (!$node->hasAttribute('style')) {
				// log the case
				self::setEditingLog("style attribute not found in available tag with CSS class {$css_class} (" . __LINE__ . ")");

				// style attribute not found
				continue;
			}

			// make sure the style attribute is not empty
			$style_attr = $node->getAttribute('style');
			if (!$style_attr || empty($style_attr)) {
				// log the case
				self::setEditingLog("style attribute is empty in available tag with CSS class {$css_class} (" . __LINE__ . ")");

				// style attribute is empty
				continue;
			}

			// compose style information
			$style = new stdClass;
			$style->node_path = $node->getNodePath();
			$style->attribute = $style_attr;
			
			// push style object to the pool
			array_push($styles_pool, $style);
		}

		if (!count($styles_pool)) {
			// no style attributes found to add, unable to proceed
			return $php_code;
		}

		// import the raw php code to DOMDocument
		$php_dom = new DOMDocument();
		if ($libxml_updated) {
			$php_dom->loadHTML($php_code, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		} else {
			$php_dom->loadHTML('<div>' . $php_code . '</div>');
		}

		// get DOMXPath instance of the php DOM Document
		$php_xpath = new DOMXpath($php_dom);

		// iterate all styles to add to the various nodes
		foreach ($styles_pool as $style) {
			// log data
			self::setEditingLog("Node Path to tag to be styled: " . $style->node_path);

			// adjust html path to node to comply with the expression for the raw code
			$node_path = self::adjustDOMXpathQuery($style->node_path, $php_xpath);

			// log data
			self::setEditingLog("Adjusted node path is: " . $node_path);

			// query the raw source code to find the same path as a DOMNodeList
			$found_nodelist = $php_xpath->query($node_path);

			if (!$found_nodelist || !$found_nodelist->length) {
				// log the case
				self::setEditingLog("Node Path not found in php source code for styling: {$node_path} must be invalid (" . __LINE__ . ")");

				// path not found in php source code: $node_path must be invalid
				continue;
			}

			// get the first node
			$node = $found_nodelist->item(0);
			
			// set the style attribute
			$node->setAttribute('style', $style->attribute);

			// obtain the new php source code
			$php_code = $php_dom->saveHTML();
		}

		// log data
		self::setEditingLog("Style(s) added to the source code. New template source code before cleaning:\n\n" . $php_code);

		// always clean up the PHP code to avoid breaking the file
		$php_code = self::cleanPHPSourceCode($php_code, $php_dom);

		return $php_code;
	}

	/**
	 * Appends an execution log to the execution log array.
	 * 
	 * @param 	string 	$log 	the execution string to append.
	 * 
	 * @return 	void
	 */
	public static function setEditingLog($log)
	{
		if (static::$editingLog === null) {
			static::$editingLog = array();
		}

		array_push(static::$editingLog, $log);
	}

	/**
	 * Gets the execution log array for all editing operations.
	 * 
	 * @return 	mixed 	the current editing log array or null.
	 */
	public static function getEditingLog()
	{
		return static::$editingLog;
	}
}
