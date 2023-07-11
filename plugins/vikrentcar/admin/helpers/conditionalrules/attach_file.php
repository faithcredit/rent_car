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
 * Class handler for conditional rule "attach file".
 * 
 * @since 	1.15.0 (J) - 1.3.0 (WP)
 */
class VikRentCarConditionalRuleAttachFile extends VikRentCarConditionalRule
{
	/**
	 * Class constructor will define the rule name, description and identifier.
	 */
	public function __construct()
	{
		// call parent constructor
		parent::__construct();

		$this->ruleName = JText::_('VRC_CONDTEXT_RULE_ATTFILES');
		$this->ruleDescr = JText::_('VRC_CONDTEXT_RULE_ATTFILES_DESCR');
		$this->ruleId = basename(__FILE__);
	}

	/**
	 * Displays the rule parameters.
	 * 
	 * @return 	void
	 */
	public function renderParams()
	{
		$serv_base_path = $this->getServerBasePath(true);
		?>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRC_FILE_FROM_MEDIAMNG'); ?></div>
			<div class="vrc-param-setting">
				<?php echo $this->vrc_app->getMediaField($this->inputName('attachment'), $this->getParam('attachment')); ?>
			</div>
		</div>
		<div class="vrc-param-container">
			<div class="vrc-param-label"><?php echo JText::_('VRC_FILE_FROM_LOCALDIR'); ?></div>
			<div class="vrc-param-setting">
				<input type="text" name="<?php echo $this->inputName('attachment_local'); ?>" value="<?php echo $this->getParam('attachment_local', ''); ?>" />
				<span class="vrc-param-setting-comment"><?php echo JText::sprintf('VRC_FILE_FROM_LOCALDIR_HELP', $serv_base_path); ?></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Tells whether the rule is compliant.
	 * 
	 * @return 	bool 	True on success, false otherwise.
	 */
	public function isCompliant()
	{
		// this is not a real filter-rule, so we always return true
		return true;
	}

	/**
	 * Override callback action method to add an attachment.
	 * 
	 * @return 	void
	 */
	public function callbackAction()
	{
		/**
		 * DO NOT use `getServerBasePath()` because on WordPress it would
		 * repeat /wp-content/uploads/. In this case it is correct to 
		 * directly use ABSPATH.
		 * 
		 * @since 1.5
		 */
		// $serv_base_path = $this->getServerBasePath();
		if (defined('ABSPATH'))
		{
			$serv_base_path = ABSPATH;
		}
		else
		{
			$serv_base_path = JPATH_SITE;
		}

		$serv_base_path = rtrim($serv_base_path, DIRECTORY_SEPARATOR);

		$attachment = $this->getParam('attachment', '');

		if (!empty($attachment)) {
			// make sure to convert it to a proper full path
			if (strpos($attachment, $serv_base_path) === false && !is_file($attachment)) {
				// relative path obtained from the media manager
				if (substr($attachment, 0, 1) == DIRECTORY_SEPARATOR) {
					$attachment = substr($attachment, 1);
				}
				$attachment = $serv_base_path . DIRECTORY_SEPARATOR . $attachment;
			}
			// register a file attachment
			VikRentCar::addEmailAttachment($attachment);
		}

		$attachment_local = $this->getParam('attachment_local', '');

		if (!empty($attachment_local) && $attachment_local != $serv_base_path) {
			// register a file attachment
			VikRentCar::addEmailAttachment($attachment_local);
		}

		return;
	}

	/**
	 * Internal function for this rule only.
	 * 
	 * @param 	bool 	$trailing 	whether to add a trailing directory separator.
	 * 
	 * @return 	string
	 */
	protected function getServerBasePath($trailing = false)
	{
		$base = '';
		if (defined('JPATH_SITE')) {
			$base = JPATH_SITE;
		} elseif (function_exists('wp_upload_dir')) {
			$updir = wp_upload_dir();
			$base = $updir['basedir'];
		}

		if ($trailing) {
			$base .= DIRECTORY_SEPARATOR;
		}

		return $base;
	}

}
