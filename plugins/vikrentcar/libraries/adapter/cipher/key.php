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
 * Encryption key object for the Wordpress Platform.
 *
 * @since  10.1.20
 */
class JCryptKey
{
	/**
	 * The private key.
	 *
	 * @var string
	 */
	public $private;

	/**
	 * The public key.
	 *
	 * @var string
	 */
	public $public;

	/**
	 * The key type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Constructor.
	 *
	 * @param   string  $type     The key type.
	 * @param   string  $private  The private key.
	 * @param   string  $public   The public key.
	 */
	public function __construct($type, $private = null, $public = null)
	{
		// set the key type
		$this->type = (string) $type;

		// set the optional public/private key strings
		$this->private = isset($private) ? (string) $private : null;
		$this->public  = isset($public)  ? (string) $public  : null;
	}

	/**
	 * Magic method to return some protected property values.
	 *
	 * @param   string  $name  The name of the property to return.
	 *
	 * @return  mixed
	 */
	public function __get($name)
	{
		if ($name == 'type')
		{
			return $this->type;
		}

		trigger_error('Cannot access property ' . __CLASS__ . '::' . $name, E_USER_WARNING);
	}
}
