<?php
/** 
 * @package     VikRentCar
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2022 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * VikRentCar cron job factory.
 *
 * @since 1.3.0
 */
class VRCCronFactory
{
	use VRCFactoryAware;

	/**
	 * Class contructor.
	 */
	public function __construct()
	{
		$this->instanceClassPrefix = 'VikRentCarCronJob';
	}

	/**
	 * Children classes can override this method to rearrange the ordering
	 * of the elements created through this factory class.
	 * 
	 * @param   array  &$list  The list of instances.
	 * 
	 * @return  void
	 */
	protected function rearrangeInstances(&$list)
	{
		// sort cron jobs by title
		uasort($list, function($a, $b)
		{
			return strcasecmp($a->getTitle(), $b->getTitle());
		});
	}

	/**
	 * Children classes can override this method to make sure that the
	 * created instance is compliant with the factory requirements.
	 * 
	 * @param   mixed    $object  The object to validate.
	 * 
	 * @return  boolean  True if valid, false otherwise.
	 */
	protected function isInstanceValid($object)
	{
		return $object instanceof VRCCronJob;
	}
}
