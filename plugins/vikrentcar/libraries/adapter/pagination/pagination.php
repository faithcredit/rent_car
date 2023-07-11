<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.pagination
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * This class provides a common interface for content 
 * pagination for the Wordpress CMS plugins.
 *
 * @since 10.0
 */
class JPagination
{
	/**
	 * The layout file to use for rendering the pagination.
	 *
	 * @var JLayoutFile
	 */
	private static $_layout = null;

	/**
	 * The total number of items.
	 *
	 * @var integer
	 */
	protected $total;

	/**
	 * The offset of the item to start at.
	 *
	 * @var integer
	 */
	protected $limitstart;

	/**
	 * The number of items to display per page.
	 *
	 * @var integer
	 */
	protected $limit;

	/**
	 * Prefix used for request variables.
	 * 
	 * @var string
	 * @since 10.1.44
	 */
	protected $prefix;

	/**
	 * A list of additional URL params.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * Class constructor.
	 *
	 * @param   integer  $total       The total number of items.
	 * @param   integer  $limitstart  The offset of the item to start at.
	 * @param   integer  $limit       The number of items to display per page.
	 * @param   string   $prefix      The prefix used for request variables.
	 */
	public function __construct($total, $limitstart, $limit, $prefix = '')
	{
		$this->total      = $total;
		$this->limitstart = $limitstart;
		$this->limit      = $limit;
		$this->prefix     = $prefix;
	}

	/**
	 * Sets the global layout file renderer.
	 *
	 * @param 	JLayoutFile  $layout  The layout file object.
	 *
	 * @return 	void
	 */
	public static function setLayout(JLayoutFile $layout)
	{
		static::$_layout = $layout;
	}

	/**
	 * Gets the global layout file renderer.
	 *
	 * @return 	JLayoutFile  The layout file object.
	 */
	public static function getLayout()
	{
		if (!static::$_layout)
		{
			// use default layout if not specified
			static::$_layout = new JLayoutFile('html.system.pagination');
		}
		
		return static::$_layout;
	}

	/**
	 * Method to set an additional URL parameter to be added 
	 * to all pagination class generated links.
	 *
	 * @param   string  $key    The name of the URL parameter for which to set a value.
	 * @param   mixed   $value  The value to set for the URL parameter.
	 *
	 * @return  mixed   The old value for the parameter.
	 */
	public function setAdditionalUrlParam($key, $value)
	{
		// get the old value to return and set the new one for the URL parameter
		$result = isset($this->params[$key]) ? $this->params[$key] : null;

		// if the passed parameter value is null unset the parameter, otherwise set it to the given value
		if ($value === null)
		{
			unset($this->params[$key]);
		}
		else
		{
			$this->params[$key] = $value;
		}

		return $result;
	}

	/**
	 * Method to get an additional URL parameter (if it exists) to be added to
	 * all pagination class generated links.
	 *
	 * @param   string  $key  The name of the URL parameter for which to get the value.
	 *
	 * @return  mixed   The value if it exists, otherwise null.
	 */
	public function getAdditionalUrlParam($key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : null;
	}

	/**
	 * Create and return the pagination page list string.
	 * For example: Previous, Next, 1 2 3 ... x.
	 *
	 * @return  string  Pagination page list string.
	 */
	public function getPagesLinks()
	{
		return $this->getListFooter();
	}

	/**
	 * Return the pagination footer.
	 *
	 * @return  string  Pagination footer.
	 *
	 * @uses 	buildPaginationData()
	 */
	public function getListFooter()
	{
		$layout = static::getLayout();

		// make sure a layout has been set
		if (is_null($layout))
		{
			return '';
		}

		$data = $this->buildPaginationData();

		// use the pagination only if we have 2 or more pages
		if ($data['pages'] <= 1)
		{
			return '';
		}

		$app = JFactory::getApplication();

		// set pagination URLs
		$data['links'] = [];

		if ($app->isAdmin())
		{
			// include script to handle pagination events
			JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {
	Joomla.getPagination('{$data['prefix']}')
		.setTotal({$data['total']})
		.setLimit({$data['lim']})
		.setStart({$data['lim0']})
		.setListener(document.adminForm);
});
JS
			);

			$data['links']['first'] = 'href="javascript: void(0);" onclick="Joomla.getPagination(\'' . $data['prefix'] . '\').first();"';
			$data['links']['prev']  = 'href="javascript: void(0);" onclick="Joomla.getPagination(\'' . $data['prefix'] . '\').prev();"';
			$data['links']['next']  = 'href="javascript: void(0);" onclick="Joomla.getPagination(\'' . $data['prefix'] . '\').next();"';
			$data['links']['last']  = 'href="javascript: void(0);" onclick="Joomla.getPagination(\'' . $data['prefix'] . '\').last();"';
		}
		else
		{
			// get current URI
			$current = JUri::getInstance();

			// replace or inject each additional param
			foreach ($this->params as $k => $v)
			{
				$current->setVar($k, $v);
			}

			$seek = array(
				'first' => 0,
				'prev'	=> $data['lim0'] - $data['lim'],
				'next'	=> $data['lim0'] + $data['lim'],
				'last'	=> ($data['pages'] - 1) * $data['lim'],
			);

			foreach ($seek as $k => $v)
			{
				// replace limitstart
				$current->setVar($this->prefix . 'limitstart', $v);
				// route the URI
				$data['links'][$k] = 'href="' . JRoute::_($current) . '"';
			}
		}

		return $layout->render($data);
	}

	/**
	 * Helper method to build the pagination data array.
	 *
	 * @param 	array 	&$data 	The array data to fill.
	 *
	 * @return 	array 	The resulting data array.
	 */
	protected function buildPaginationData(array &$data = null)
	{
		if (is_null($data))
		{
			$data = [];
		}

		$data = [];
		$data['prefix'] = $this->prefix;
		$data['total'] 	= $this->total;
		$data['lim0']	= $this->limitstart;
		$data['lim']	= $this->limit;
		$data['pages']	= ceil($this->total / $this->limit);

		// lim0 : page = total : pages
		// page = lim0 * pages / total
		$data['page'] = floor($this->limitstart * $data['pages'] / $this->total) + 1;

		return $data;
	}
}
