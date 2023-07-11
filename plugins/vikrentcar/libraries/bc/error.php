<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	bc (backward compatibility)
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

if (!class_exists('VikError'))
{
	/**
	 * Class used to implement the functionalities of JError.
	 *
	 * @since 1.0
	 */
	abstract class VikError
	{
		/**
		 * Wrapper error method for the handleError() method.
		 *
		 * @throws  Exception 	Throws an exception only when the code is not null.
		 *
		 * @param   string  $code  The application-internal error code for this error.
		 * @param   string  $msg   The error message, which may also be shown the user if need be.
		 *
		 * @return  JException|string  $error  The thrown JException object
		 *
		 * @see     JError::handleError()
		 */
		public static function raiseError($code, $message)
		{
			return self::handleError(E_ERROR, $code, $message);
		}

		/**
		 * Wrapper warning method for the handleError() method.
		 *
		 * @throws  Exception 	Throws an exception only when the code is not null.
		 *
		 * @param   string  $code  The application-internal error code for this error.
		 * @param   string  $msg   The error message, which may also be shown the user if need be.
		 *
		 * @return  JException|string  $error  The thrown JException object
		 *
		 * @see     JError::handleError()
		 */
		public static function raiseWarning($code, $message)
		{
			return self::handleError(E_WARNING, $code, $message);
		}

		/**
		 * Wrapper notice method for the handleError() method.
		 *
		 * @throws  Exception 	Throws an exception only when the code is not null.
		 *
		 * @param   string  $code  The application-internal error code for this error.
		 * @param   string  $msg   The error message, which may also be shown the user if need be.
		 *
		 * @return  JException|string  $error  The thrown JException object
		 *
		 * @see     JError::handleError()
		 */
		public static function raiseNotice($code, $message)
		{
			return self::handleError(E_NOTICE, $code, $message);
		}

		/**
		 * Handle the error in the proper way.
		 *
		 * @throws  Exception 	Throws an exception only when the code is not null.
		 *
		 * @param   string  $level 	The error level - use any of PHP's own error levels for.
		 *                          this: E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR,
		 *                          E_USER_WARNING, E_USER_NOTICE.
		 * @param   string  $code  	The application-internal error code for this error
		 * @param   string  $msg   	The error message, which may also be shown the user if need be.
		 *
		 * @return  JException|string  $error  The thrown JException object
		 *
		 */
		protected static function handleError($level, $code, $message)
		{
			if (!empty($code))
			{
				throw new Exception($message, $code);
			}

			JFactory::getApplication()->enqueueMessage($message, ($level == E_NOTICE ? 'notice' : 'error'));

			return '';
		}
	}
}
