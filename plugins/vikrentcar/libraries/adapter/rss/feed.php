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

JLoader::import('adapter.rss.optinexception');

/**
 * Build a feed starting from the given SimplePie XML element.
 *
 * @since 10.1.31
 */
class JRssFeed
{
	/**
	 * A list of cached feeds.
	 *
	 * @var array
	 */
	protected static $userFeeds = array();

	/**
	 * A unique ID of the feed.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The title of the feed.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * A summary of the feed.
	 * The full content in case the summary was not specified.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * The full content of the feed.
	 *
	 * @var string
	 */
	protected $content;
	
	/**
	 * The category to which the feed belong.
	 *
	 * @var string
	 */
	protected $category;

	/**
	 * The GMT publication date.
	 *
	 * @var string
	 */
	protected $date;

	/**
	 * The RSS permalink.
	 *
	 * @var string
	 */
	protected $permalink;

	/**
	 * Stores the current user ID.
	 *
	 * @var integer
	 */
	protected $userId;

	/**
	 * Stores the plugin callee.
	 *
	 * @var string
	 */
	protected $plugin;

	/**
	 * Class constructor.
	 *
	 * @param 	mixed 	$element  The XML feed element.
	 * @param 	string 	$plugin   The plugin that reads the feed.
	 */
	public function __construct($element, $plugin)
	{
		if ($element instanceof SimplePie_Item)
		{
			// extract data from SimplePie Item
			$this->id          = md5($element->get_id());
			$this->title       = $element->get_title();
			$this->description = $element->get_description();
			$this->content     = $element->get_content();
			$this->category    = $element->get_category();
			$this->date        = new JDate($element->get_gmdate('Y-m-d H:i:s'));
			$this->permalink   = $element->get_permalink();
		}
		else
		{
			// extract data from array/object
			foreach ((array) $element as $k => $v)
			{
				if (property_exists($this, $k))
				{
					$this->{$k} = $v;
				}
			}
		}

		$this->userId = JFactory::getUser()->id;

		$this->plugin = (string) $plugin;
	}

	/**
	 * Magic method used to access internal properties.
	 *
	 * @param 	string 	$name 	The property name.
	 *
	 * @return 	mixed 	The value of the property.
	 */
	public function __get($name)
	{
		if ($name === 'category')
		{
			return $this->category ? $this->category->get_label() : '';
		}
		else if (isset($this->{$name}))
		{
			return $this->{$name};
		}

		return null;
	}

	/**
	 * Magic method used to check whether a property exists.
	 *
	 * @param 	string 	 $name 	The property name.
	 *
	 * @return 	boolean  True in case the property is set.
	 */
	public function __isset($name)
	{
		return isset($this->{$name});
	}

	/**
	 * Marks the feed as already seen.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function dismiss()
	{
		if ($this->userId)
		{
			// dismiss the feed
			static::setUserFeed($this->userId, $this->plugin, $this->id, 0);
		}

		return $this;
	}

	/**
	 * Display feed again in a second time.
	 *
	 * @param  boolean  $status  True if seen, false otherwise.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	public function delay($minutes = 60)
	{
		if ($this->userId)
		{
			// delay the feed
			static::setUserFeed($this->userId, $this->plugin, $this->id, $minutes);
		}

		return $this;
	}

	/**
	 * Checks whether the feed should be displayed or not.
	 *
	 * @return 	boolean  True to display the feed, false otherwise.
	 */
	public function isVisible()
	{
		if (!$this->userId)
		{
			// never visible for users
			return false;
		}

		// get user feeds
		$feeds = static::getUserFeeds($this->userId, $this->plugin);

		// check if the feed was registered
		if (!isset($feeds[$this->id]))
		{
			// feed not registered, can display it
			return true;
		}

		// check whether the feed was dismissed
		if (!$feeds[$this->id])
		{
			// already dismissed, do not display
			return false;
		}

		// then check whether we reached the delay threshold
		return JDate::getInstance('now') >= JDate::getInstance($feeds[$this->id]);
	}

	/**
	 * Returns a list of user feeds.
	 *
	 * @param 	integer  $userId  The user ID.
	 * @param 	string 	 $plugin  The callee.
	 *
	 * @return 	&array   A lookup containing all the viewed feeds.
	 */
	protected static function &getUserFeeds($userId, $plugin)
	{
		if (!isset(static::$userFeeds[$userId]))
		{
			// recover user lookup
			$feed = get_user_meta($userId, $plugin . '_rss_feeds', true);

			// cache result
			static::$userFeeds[$userId] = $feed ? (array) $feed : array();
		}

		return static::$userFeeds[$userId];
	}

	/**
	 * Flags the status of the specified feed.
	 *
	 * @param 	integer  $userId  The user ID.
	 * @param 	string 	 $plugin  The callee.
	 * @param 	string 	 $feedId  The feed ID.
	 * @param 	integer  $value   The flag value. Specify 0 to dismiss the feed.
	 * 							  Any other positive value will delay the feed
	 * 							  by the specified minutes.
	 *
	 * @return 	void
	 */
	protected static function setUserFeed($userId, $plugin, $feedId, $value)
	{
		// get user feeds
		$feeds = static::getUserFeeds($userId, $plugin);

		// take only positive values
		$value = abs($value);

		if ($value)
		{
			// create limit date
			$date = new JDate('+' . $value . ' minutes');

			// register SQL date
			$value = $date->toSql();
		}

		// register value
		$feeds[$feedId] = $value;

		// permanently update
		update_user_meta($userId, $plugin . '_rss_feeds', $feeds);
	}
}
