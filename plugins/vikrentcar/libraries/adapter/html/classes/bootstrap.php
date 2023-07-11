<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.html
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Utility class for Bootstrap elements.
 *
 * @since 10.1.16
 */
abstract class JHtmlBootstrap
{
	/**
	 * Array containing information for loaded files.
	 *
	 * @var array  
	 */
	protected static $loaded = array();

	/**
	 * Add javascript support for Bootstrap modals.
	 *
	 * @param   string  $selector  The ID selector for the modal.
	 * @param   array   $params    An array of options for the modal.
	 *                             Options for the modal can be:
	 *                             - backdrop  boolean  Includes a modal-backdrop element.
	 *                             - keyboard  boolean  Closes the modal when escape key is pressed.
	 *                             - show      boolean  Shows the modal when initialized.
	 *                             - remote    string   An optional remote URL to load.
	 *
	 * @return  void
	 */
	public static function modal($selector = 'modal', $params = array())
	{
		// do nothing here
	}

	/**
	 * Method to render a Bootstrap modal.
	 *
	 * @param   string  $selector  The ID selector for the modal.
	 * @param   array   $params    An array of options for the modal.
	 *                             Options for the modal can be:
	 *                             - title        string   The modal title.
	 *                             - backdrop     mixed    A boolean select if a modal-backdrop element should be included (default = true).
	 *                                                     The string 'static' includes a backdrop which doesn't close the modal on click.
	 *                             - keyboard     boolean  Closes the modal when escape key is pressed (default = true).
	 *                             - closeButton  boolean  Display modal close button (default = true).
	 *                             - animation    boolean  Fade in from the top of the page (default = true).
	 *                             - footer       string   Optional markup for the modal footer.
	 *                             - url          string   URL of a resource to be inserted as an `<iframe>` inside the modal body.
	 *                             - height       string   height of the `<iframe>` containing the remote resource.
	 *                             - width        string   width of the `<iframe>` containing the remote resource.
	 * @param   string  $body      Markup for the modal body. Appended after the `<iframe>` if the URL option is set.
	 *
	 * @return  string  HTML markup for a modal.
	 */
	public static function renderModal($selector = 'modal', $params = array(), $body = '')
	{
		if (is_array($params))
		{
			$width 	= isset($params['width'])  ? abs($params['width']) 	: 96;
			$height = isset($params['height']) ? abs($params['height']) : 90;
			$left 	= isset($params['left'])   ? abs($params['left']) 	: $width / 2;

			$style = "width:$width%;height:$height%;margin-left:-$left%;";

			if (isset($params['top']))
			{
				if ($params['top'] === true)
				{
					$top = (100 - $height) / 2;
				}
				else
				{
					$top = $params['top'];
				}

				$style .= "top:$top%;";
			}
		}
		else if (is_string($params))
		{
			// we probably received a style string, use it directly
			$style = $params;

			// then, reset the params array
			$params = array();
		}
		else
		{
			// use some default styles
			$style = "width:96%;height:90%;margin-left:-48%;top:5%;";

			// cast params to array in case of non-scalar argument
			if (!is_scalar($params))
			{
				$params = (array) $params;
			}
			else
			{
				$params = array();
			}
		}

		// remove initial "jmodal-" if set from ID
		$params['id'] 	 = preg_replace('/^jmodal-/', '', $selector);
		$params['body']  = $body;
		$params['style'] = $style;

		// render modal layout
		return JHtml::_('layoutfile', 'html.plugins.modal')->render($params);
	}

	/**
	 * Adds javascript support for Bootstrap popovers.
	 *
	 * @param 	string 	$selector   Selector for the popover.
	 * @param 	array 	$options    An array of options for the popover.
	 * 					Options for the popover can be:
	 * 					- animation  boolean          apply a css fade transition to the popover
	 *                  - html       boolean          Insert HTML into the popover. If false, jQuery's text method will be used to insert
	 *                                                content into the dom.
	 *                  - placement  string|function  how to position the popover - top | bottom | left | right
	 *                  - selector   string           If a selector is provided, popover objects will be delegated to the specified targets.
	 *                  - trigger    string           how popover is triggered - hover | focus | manual
	 *                  - title      string|function  default title value if `title` tag isn't present
	 *                  - content    string|function  default content value if `data-content` attribute isn't present
	 *                  - delay      number|object    delay showing and hiding the popover (ms) - does not apply to manual trigger type
	 *                                                If a number is supplied, delay is applied to both hide/show
	 *                                                Object structure is: delay: { show: 500, hide: 100 }
	 *                  - container  string|boolean   Appends the popover to a specific element: { container: 'body' }
	 *
	 * @return 	void
	 */
	public static function popover($selector = '.wpPopover', $params = array())
	{
		$sign = serialize(array($selector, $params));

		// only load once
		if (isset(static::$loaded[__METHOD__][$sign]))
		{
			return;
		}

		/**
		 * Always disable HTML sanitizing to allow any kind of tags.
		 *
		 * @since 10.1.27
		 */
		$params['sanitize'] = false;

		/**
		 * In case the "container" attribute is not set,
		 * always place the popover within the body.
		 *
		 * @since 10.1.28
		 */
		if (!isset($params['container']))
		{
			$params['container'] = 'body';
		}

		$data = $params ? json_encode($params) : '{}';
		JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {
	jQuery('$selector').popover($data);
});
JS
		);

		// set static array
		static::$loaded[__METHOD__][$sign] = true;
	}

	/**
	 * Add javascript support for Bootstrap tooltips.
	 *
	 * Add a title attribute to any element in the form:
	 * title="title::text"
	 *
	 * @param   string  $selector  The ID selector for the tooltip.
	 * @param   array   $params    An array of options for the tooltip.
	 *                             Options for the tooltip can be:
	 *                             - animation  boolean          Apply a CSS fade transition to the tooltip
	 *                             - html       boolean          Insert HTML into the tooltip. If false, jQuery's text method will be used to insert
	 *                                                           content into the dom.
	 *                             - placement  string|function  How to position the tooltip - top | bottom | left | right
	 *                             - selector   string           If a selector is provided, tooltip objects will be delegated to the specified targets.
	 *                             - title      string|function  Default title value if `title` tag isn't present
	 *                             - trigger    string           How tooltip is triggered - hover | focus | manual
	 *                             - delay      integer          Delay showing and hiding the tooltip (ms) - does not apply to manual trigger type
	 *                                                           If a number is supplied, delay is applied to both hide/show
	 *                                                           Object structure is: delay: { show: 500, hide: 100 }
	 *                             - container  string|boolean   Appends the popover to a specific element: { container: 'body' }
	 *
	 * @return  void
	 */
	public static function tooltip($selector = '.hasTooltip', $params = array())
	{
		$sign = serialize(array($selector, $params));

		// only load once
		if (isset(static::$loaded[__METHOD__][$sign]))
		{
			return;
		}

		/**
		 * If the container has not been specified,
		 * always append tooltips at the end of the body.
		 *
		 * @since 10.1.30
		 */
		if (!isset($params['container']))
		{
			$params['container'] = 'body';
		}

		$data = $params ? json_encode($params) : '{}';
		JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {
	jQuery('$selector').tooltip($data);
});
JS
		);

		// set static array
		static::$loaded[__METHOD__][$sign] = true;
	}

	/**
	 * Creates a tab pane.
	 *
	 * @param   string  $selector  The pane identifier.
	 * @param   array   $params    The parameters for the pane.
	 *
	 * @return  string
	 */
	public static function startTabSet($selector = 'myTab', $params = array())
	{
		$sign = md5(serialize(array($selector, $params)));

		if (!isset(static::$loaded[__METHOD__][$sign]))
		{
			$opt = array();
			// setup options object
			$opt['active'] = (isset($params['active']) && $params['active']) ? (string) $params['active'] : '';

			// Set static array
			static::$loaded[__METHOD__][$sign]     = true;
			static::$loaded[__METHOD__][$selector] = $opt;
		}

		return JHtml::_('layoutfile', 'html.bootstrap.starttabset')->render(array('selector' => $selector));
	}

	/**
	 * Closes the current tab pane.
	 *
	 * @return  string  HTML to close the pane.
	 */
	public static function endTabSet()
	{
		return JHtml::_('layoutfile', 'html.bootstrap.endtabset')->render();
	}

	/**
	 * Begins the display of a new tab content panel.
	 *
	 * @param   string  $selector  Identifier of the panel.
	 * @param   string  $id        The ID of the div element.
	 * @param   string  $title     The title text for the new UL tab.
	 *
	 * @return  string  HTML to start a new panel.
	 */
	public static function addTab($selector, $id, $title)
	{
		$active = (static::$loaded['JHtmlBootstrap::startTabSet'][$selector]['active'] == $id) ? ' active' : '';

		return JHtml::_('layoutfile', 'html.bootstrap.addtab')->render(array('id' => $id, 'active' => $active, 'selector' => $selector, 'title' => $title));
	}

	/**
	 * Closes the current tab content panel.
	 *
	 * @return  string  HTML to close the pane.
	 */
	public static function endTab()
	{
		return JHtml::_('layoutfile', 'html.bootstrap.endtab')->render();
	}
}
