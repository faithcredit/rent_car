<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	layout
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Factory class to render common parts of the plugin.
 *
 * @since 	1.0
 */
abstract class VikRentCarLayoutHelper
{
	/**
	 * Renders the system messages.
	 *
	 * @param 	array 	 $queue  The messages queue.
	 * @param 	boolean  $echo 	 True to echo the layout, false to return it.
	 *
	 * @return 	mixed 	 True when the layout is echoed, otherwise the layout string.
	 */
	public static function renderSystemMessages(array $queue = null, $echo = true)
	{
		$app = JFactory::getApplication();

		if (is_null($queue))
		{
			// do not flush the messages queue if the 
			// application is going to do a JS redirect
			if ($app->shouldRedirect())
			{
				$queue = array();
			}
			else
			{
				$queue = $app->getMessagesQueue();
			}
		}

		/**
		 * Get setting from config to evaluate whether the messages should be grouped.
		 * This setting is not installed by default and can be added on a second time to
		 * turn off messages grouping.
		 *
		 * Here's the query to turn off this feature:
		 * INSERT INTO `#__options` (`option_name`, `option_value`) VALUES
		 * ('groupsysmessages', 0)
		 */
		$should_be_grouped = get_option('groupsysmessages', 1);

		if ($should_be_grouped)
		{
			// manipulate queue to group contiguous messages that share the same type
			$tmp = array();

			for ($i = 0; $i < count($queue); $i++)
			{
				// make sure we have a valid message object
				if (isset($queue[$i]->type))
				{
					// extract last message from temporary list
					$last = end($tmp);

					// in case the list is empty or the type of the current
					// message doesn't match the previous one, push it
					// within the temporary list as new element
					if (empty($tmp) || $last->type != $queue[$i]->type)
					{
						$tmp[] = $queue[$i];
					}
					// otherwise append this message to the previous one
					else
					{
						$last->message   = (array) $last->message;
						$last->message[] = $queue[$i]->message;
					}
				}
			}

			// overwrite standard queue
			$queue = $tmp;
		}

		$layout = new JLayoutFile('html.system.messages', null, array('component' => 'com_vikrentcar'));
		$output = $layout->render(array('queue' => $queue));

		if ($echo)
		{
			echo $output;
			return true;
		}

		return $output;
	}

	/**
	 * Renders the plugin toolbar.
	 *
	 * @param 	JToolbar  $bar   The toolbar to render.
	 * @param 	boolean   $echo  True to echo the layout, false to return it.
	 *
	 * @return 	mixed 	  True when the layout is echoed, otherwise the layout string.
	 */
	public static function renderToolbar($bar = null, $echo = true)
	{
		if (is_null($bar) || !$bar instanceof JToolbar)
		{
			$bar = JToolbar::getInstance();
		}

		$output = '';

		// render toolbar only if it contains at least a button or the title is set
		if ($bar->hasButtons() || $bar->hasTitle())
		{
			// open toolbar
			$layout = new JLayoutFile('html.toolbar.open', null, array('component' => 'com_vikrentcar'));
			$output .= $layout->render(array('bar' => $bar));

			// render contents
			foreach ($bar->getButtons() as $button)
			{
				$layoutId = $button->getLayoutId();

				if (!is_array($layoutId))
				{
					$layoutId = array($layoutId);
				}

				// specify base path (null)
				$layoutId[] = null;
				// force component
				$layoutId[] = array('component' => 'com_vikrentcar');

				// use reflection to support multiple arguments
				$reflect = new ReflectionClass('JLayoutFile');

				// [0] layout id, [1] base path, [2] options
				$layout = $reflect->newInstanceArgs($layoutId);
				$output .= $layout->render($button->getDisplayData());
			}

			// close toolbar
			$layout = new JLayoutFile('html.toolbar.close', null, array('component' => 'com_vikrentcar'));
			$output .= $layout->render();
		}

		if ($echo)
		{
			echo $output;
			return true;
		}

		return $output;
	}

	/**
	 * Renders the current body page.
	 *
	 * @param 	string 	 $html   The HTML to print.
	 * @param 	boolean  $echo 	 True to echo the layout, false to return it.
	 *
	 * @return 	mixed 	 True when the layout is echoed, otherwise the layout string.
	 */
	public static function renderBody($html, $echo = true)
	{
		$layout = new JLayoutFile('html.system.body', null, array('component' => 'com_vikrentcar'));
		$output = $layout->render(array('html' => $html));

		if ($echo)
		{
			echo $output;
			return true;
		}

		return $output;
	}
}
