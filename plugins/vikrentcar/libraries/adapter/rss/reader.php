<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.rss
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.rss.feed');
JLoader::import('adapter.rss.optinexception');

/**
 * Helper class used to read the RSS feed provided by VikWP.
 *
 * @since 10.1.31
 */
class JRssReader
{
	/**
	 * A list of instances.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * A list of feed channels (URL).
	 *
	 * @var array
	 */
	protected $channels;

	/**
	 * The name of the callee;
	 *
	 * @var string
	 */
	protected $plugin;

	/**
	 * Returns the singleton of this class.
	 *
	 * @param 	mixed 	$url     Either the feed URL or an array.
	 * @param 	string  $plugin  The plugin that called this class. If not
	 * 							 specified, it will be taken from the request.
	 *
	 * @return 	self 	The instance of this object.
	 */
	public static function getInstance($url, $plugin = null)
	{
		if (!$plugin)
		{
			// try to recover plugin name from request if not specified
			$plugin = JFactory::getApplication()->input->get('option', 'vikplugin');	
		}

		// serialize arguments
		$sign = serialize(func_get_args());

		// check whether the instance already exists
		if (!isset(static::$instances[$sign]))
		{
			// instantiate only once
			static::$instances[$sign] = new static($url, $plugin);
		}

		return static::$instances[$sign];
	}

	/**
	 * Class constructor.
	 *
	 * @param 	mixed 	$url     Either the feed URL or an array.
	 * @param 	string  $plugin  The plugin that called this class.
	 */
	protected function __construct($url, $plugin)
	{
		$this->channels = (array) $url;
		$this->plugin   = (string) $plugin;
	}

	/**
	 * Choose whether to opt in to the RSS feed services.
	 *
	 * @param 	boolean  $status  True to opt in, false to the decline.
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	public function optIn($status = true)
	{
		$user = JFactory::getUser();

		if ($user->guest)
		{
			// throw exception in case of guest user
			throw new Exception('Guest users cannot opt-in to the RSS service', 403);
		}

		if ($status)
		{
			// register opt-in date
			$status = JDate::getInstance()->toSql();
		}
		else
		{
			// service declined
			$status = 0;
		}

		// register user choice
		update_user_meta($user->id, $this->plugin . '_rss_optin', $status);

		return $this;
	}

	/**
	 * Checks whether the user opted in the RSS feed services.
	 *
	 * @param 	string 	$date    True to return the opt-in date.
	 *
	 * @return 	mixed   $status  True if opted in, false if declined.
	 * 							 A JDate instance in case $date is true.
	 *
	 * @throws 	Exception  In case the user didn't decide yet.
	 */
	public function optedIn($date = false)
	{
		$user = JFactory::getUser();

		if ($user->guest)
		{
			// ignore choice of guest users
			return false;
		}

		// retrieve user choice
		$choice = get_user_meta($user->id, $this->plugin . '_rss_optin', true);

		// make sure a choice was made
		if ($choice === false || $choice === '')
		{
			// missing choice, throw exception
			throw new JRssOptInException();
		}

		if ($date && $choice)
		{
			// return opt-in date
			return new JDate($choice);
		}

		return (bool) $choice;
	}

	/**
	 * Returns a list of RSS permalinks.
	 *
	 * @return 	array
	 */
	public function getChannels()
	{
		return $this->channels;
	}

	/**
	 * Download a list of feeds from the registered channels.
	 *
	 * @param 	array 	$options  A configuration array.
	 * 							  - start 	the starting index to take feeds;
	 * 							  - limit 	the number of feeds to take;
	 * 							  - new 	true to retrieve only feeds never seen;
	 * 							  - order 	the sorting mode (asc or desc).
	 *
	 * @return 	JRssFeed[]  An array of feeds.
	 */
	public function download(array $options = array())
	{
		// get opt-in date
		$optinDate = $this->optedIn(true);

		// make sure the user opted in the RSS service
		if (!$optinDate)
		{
			// authorization denied, do not go ahead
			throw new Exception('Missing RSS authorization', 403);
		}

		// read feed channels
		$feed = fetch_feed($this->channels);

		if ($feed instanceof WP_Error)
		{
			// get SimplePie error
			$error = $feed->get_error_message();

			// extract first element as long as error is an array
			while (is_array($error))
			{
				$error = array_shift($error);
			}

			// something went wrong, propagate error
			throw new Exception($error, (int) $feed->get_error_code());
		}

		$list = array();

		$start = isset($options['start']) ? abs($options['start']) : 0;
		$limit = isset($options['limit']) ? abs($options['limit']) : 0;

		// check if we should retrive only the visible feeds
		$strict = isset($options['new']) ? (bool) $options['new'] : false;

		// check if we should retrieve the feeds in ascending order
		$ordering = isset($options['order']) ? strtolower($options['order']) : 'desc';

		if ($strict || $ordering == 'asc')
		{
			// take all the items without limits because
			// we need to check whether the feeds are visible
			$items = $feed->get_items();

			if ($ordering == 'asc')
			{
				// load items in reverse order from oldest to newest
				$items = array_reverse($items);
			}
		}
		else
		{
			// take the feeds according to the specified limits
			$items = $feed->get_items($start, $limit);
		}

		// iterate multi-feed property to scan all the supported feeds
		foreach ($items as $data)
		{
			// encapsulate feed data
			$feed = new JRssFeed($data, $this->plugin);

			// check if we should take only visible feeds
			if (!$strict || ($feed->isVisible() && $optinDate < $feed->date))
			{
				// take feed
				$list[] = $feed;
			}
		}

		if ($strict)
		{
			// prepare base arguments for array_splice
			$args = array(&$list, $start);

			if ($limit)
			{
				// consider limit only if specified, because
				// array_splice take care of the number of
				// arguments instead of on their values
				$args[] = $limit;
			}

			// take only the feeds inside the specified range
			$list = call_user_func_array('array_splice', $args);
		}

		if ($limit == 1)
		{
			// take directly the first element
			return array_shift($list);
		}

		return $list;
	}
}
