<?php
/** 
 * @package     VikWP - Libraries
 * @subpackage  adapter.factory
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Abstract Factory pattern.
 *
 * @since 10.0
 */
abstract class JFactory
{
	/**
	 * Application CMS adapter instance.
	 *
	 * @var JApplication
	 */
	private static $application = null;

	/**
	 * Language adapter instance.
	 *
	 * @var JLanguage
	 */
	private static $language = null;

	/**
	 * Document adapter instance.
	 *
	 * @var JDocument
	 */
	private static $document = null;

	/**
	 * Access point to retrieve the current database instance.
	 *
	 * @return 	JDatabase  Global database adapter.
	 */
	public static function getDbo()
	{
		JLoader::import('adapter.database.database');

		global $wpdb;

		return JDatabase::getInstance($wpdb);
	}

	/**
	 * Get an application object.
	 * Returns the global application object, only creating it if it doesn't already exist.
	 *
	 * @return 	JApplication  The application adapter.
	 */
	public static function getApplication()
	{
		if (static::$application === null)
		{
			JLoader::import('adapter.application.application');

			static::$application = new JApplication();
		}

		return static::$application;
	}

	/**
	 * Get a language object.
	 * Returns the global language object, only creating it if it doesn't already exist.
	 *
	 * @return  JLanguage 	The language adapter.
	 */
	public static function getLanguage()
	{
		if (static::$language === null)
		{
			JLoader::import('adapter.language.language');

			static::$language = JLanguage::getInstance();
		}

		return static::$language;
	}

	/**
	 * Get a session object.
	 * Returns the global session object, only creating it if it doesn't already exist.
	 *
	 * @param 	array 	$options  An array containing session options (@unused).
	 *
	 * @return  JSession 	The session adapter.
	 */
	public static function getSession(array $options = array())
	{
		JLoader::import('adapter.session.session');

		return JSession::getInstance();
	}

	/**
	 * Get a document object.
	 * Returns the global document object, only creating it if it doesn't already exist.
	 *
	 * @return  JDocument 	The document adapter.
	 */
	public static function getDocument()
	{
		if (static::$document === null)
		{
			JLoader::import('adapter.application.document');

			static::$document = new JDocument();
		}

		return static::$document;
	}

	/**
	 * Get a user object.
	 * Returns the global user object, only creating it if it doesn't already exist.
	 *
	 * @param 	integer  $id  The primary key value of the user to load.
	 *
	 * @return  JUser 	 The user adapter.
	 */
	public static function getUser($id = null)
	{
		JLoader::import('adapter.user.user');

		return JUser::getInstance($id);
	}

	/**
	 * Creates a new instance of the specified editor.
	 *
	 * @param 	string 	 $name 	The editor name type.
	 *
	 * @return 	JEditor  The instance of the editor.
	 */
	public static function getEditor($name = null)
	{
		JLoader::import('adapter.editor.editor');

		if (is_null($name))
		{
			$name = JFactory::getApplication()->get('editor');
		}

		return JEditor::getInstance($name);
	}

	/**
	 * Return the JDate object.
	 *
	 * @param   mixed 	$time      The initial time for the JDate object
	 * @param   mixed 	$tzOffset  The timezone offset.
	 *
	 * @return  JDate 	The date object.
	 */
	public static function getDate($time = 'now', $tzOffset = null)
	{
		JLoader::import('adapter.date.date');

		return JDate::getInstance($time, $tzOffset);
	}

	/**
	 * Get a mailer object.
	 *
	 * Returns the global JMail object, only creating it if it doesn't already exist.
	 *
	 * @return 	JMail 	The mail object.
	 */
	public static function getMailer()
	{
		JLoader::import('adapter.mail.mail');

		/**
		 * Always return a new instance of JMail without caching it.
		 * This avoids having a JMail instance already filled-in when
		 * this method is called more than once.
		 *
		 * @since 10.1.10
		 */
		return new JMail;
	}

	/**
	 * Returns the configuration registry.
	 *
	 * @return 	JConfig  The configuration object.
	 * 
	 * @since 	10.1.4
	 */
	public static function getConfig()
	{
		JLoader::import('adapter.config.config');

		return new JConfig;
	}
}
