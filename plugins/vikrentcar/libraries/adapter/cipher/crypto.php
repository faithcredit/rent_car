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

JLoader::import('adapter.cipher.key');

/**
 * Crypt cipher for encryption, decryption and key generation via the php-encryption library.
 *
 * @since 10.1.20
 */
class JCryptCipherCrypto
{
	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string     $data  The encrypted string to decrypt.
	 * @param   JCryptKey  $key   The key object to use for decryption.
	 *
	 * @return  string     The decrypted data string.
	 *
	 * @throws  RuntimeException
	 */
	public function decrypt($data, JCryptKey $key)
	{
		throw new RuntimeException(sprintf('Missing %s implementation', __METHOD__), 501);
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string     $data  The data string to encrypt.
	 * @param   JCryptKey  $key   The key object to use for encryption.
	 *
	 * @return  string     The encrypted data string.
	 *
	 * @throws  RuntimeException
	 */
	public function encrypt($data, JCryptKey $key)
	{
		throw new RuntimeException(sprintf('Missing %s implementation', __METHOD__), 501);
	}

	/**
	 * Method to generate a new encryption key object.
	 *
	 * @param   array 	   $options  Key generation options.
	 *
	 * @return  JCryptKey
	 *
	 * @throws  RuntimeException
	 */
	public function generateKey(array $options = array())
	{
		throw new RuntimeException(sprintf('Missing %s implementation', __METHOD__), 501);
	}
}
