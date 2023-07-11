<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Utils\Crypt;

use DUP_PRO_Log;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\Snap\SnapWP;
use Duplicator\Libs\WpConfig\WPConfigTransformer;
use Error;
use Exception;
use VendorDuplicator\pcrypt;

class CryptBlowfish implements CryptInterface
{
    const AUTH_DEFINE_NAME_OLD = 'DUP_SECURE_KEY'; // OLD define name
    const AUTH_DEFINE_NAME     = 'DUPLICATOR_AUTH_KEY';
    const AUTO_SALT_LEN        = 32;

    /** @var string */
    protected static $tempDefinedKey = null;

    /**
     * Create wp-config dup secure key
     *
     * @param bool $overwrite  if it is false and the key already exists it is not modified
     * @param bool $fromLegacy if true save legacy key
     *
     * @return bool
     */
    public static function createWpConfigSecureKey($overwrite = false, $fromLegacy = false)
    {
        $result = false;

        try {
            if (($wpConfig = SnapWP::getWPConfigPath()) == false) {
                return false;
            }

            if ($fromLegacy) {
                $authVal = self::getLegacyKey();
            } else {
                $authVal = SnapUtil::generatePassword(64, true, true);
            }

            $transformer = new WPConfigTransformer($wpConfig);

            if ($transformer->exists('constant', self::AUTH_DEFINE_NAME_OLD) && !$transformer->exists('constant', self::AUTH_DEFINE_NAME)) {
                $authVal = $transformer->getValue('constant', self::AUTH_DEFINE_NAME_OLD);
                if (!is_writeable($wpConfig)) {
                    throw new Exception('wp-config isn\'t writeable');
                }
                $result = $transformer->update('constant', self::AUTH_DEFINE_NAME, $authVal);
            } elseif ($transformer->exists('constant', self::AUTH_DEFINE_NAME)) {
                if ($overwrite) {
                    if (!is_writeable($wpConfig)) {
                        throw new Exception('wp-config isn\'t writeable');
                    }
                    $result = $transformer->update('constant', self::AUTH_DEFINE_NAME, $authVal);
                }
            } else {
                if (!is_writeable($wpConfig)) {
                    throw new Exception('wp-config isn\'t writeable');
                }
                $result = $transformer->add('constant', self::AUTH_DEFINE_NAME, $authVal);
            }

            if ($result) {
                self::$tempDefinedKey = $authVal;
            }

            // Remove old constant if new one is prepared/exists
            if ($transformer->exists('constant', self::AUTH_DEFINE_NAME_OLD) && $transformer->exists('constant', self::AUTH_DEFINE_NAME)) {
                if (!is_writeable($wpConfig)) {
                    throw new Exception('Can\'t delete old constant ' . self::AUTH_DEFINE_NAME_OLD . ' from wp-config, error: wp-config isn\'t writeable');
                }
                $transformer->remove('constant', self::AUTH_DEFINE_NAME_OLD);
            }
        } catch (Exception $e) {
            DUP_PRO_Log::trace('Can\'t create wp-config secure key, error: ' . $e->getMessage());
        } catch (Error $e) {
            DUP_PRO_Log::trace('Can\'t create wp-config secure key, error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Remove secure key in wp config is exists
     *
     * @return bool
     */
    public static function removeWpConfigSecureKey()
    {
        $result = false;

        try {
            if (($wpConfig = SnapWP::getWPConfigPath()) == false) {
                return false;
            }

            $transformer = new WPConfigTransformer($wpConfig);

            if ($transformer->exists('constant', self::AUTH_DEFINE_NAME)) {
                if (!is_writeable($wpConfig)) {
                    throw new Exception('wp-config isn\'t writeable');
                }

                $result = $transformer->remove('constant', self::AUTH_DEFINE_NAME);
            }

            if (!is_writeable($wpConfig)) {
                throw new Exception('wp-config isn\'t writeable');
            }
        } catch (Exception $e) {
            DUP_PRO_Log::trace('Can remove wp-config secure key, error: ' . $e->getMessage());
        } catch (Error $e) {
            DUP_PRO_Log::trace('Can remove wp-config secure key, error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Get default key encryption
     *
     * @return string
     */
    protected static function getDefaultKey()
    {
        if (self::$tempDefinedKey !== null) {
            return self::$tempDefinedKey;
        } elseif (strlen(constant(self::AUTH_DEFINE_NAME)) > 0) {
            return constant(self::AUTH_DEFINE_NAME);
        } elseif (defined(self::AUTH_DEFINE_NAME_OLD) && strlen(constant(self::AUTH_DEFINE_NAME_OLD)) > 0) {
            return constant(self::AUTH_DEFINE_NAME_OLD);
        } else {
            return self::getLegacyKey();
        }
    }


    /**
     * Get legacy key encryption
     *
     * @return string
     */
    protected static function getLegacyKey()
    {
        $auth_key  = defined('AUTH_KEY') ? AUTH_KEY : 'atk';
        $auth_key .= defined('DB_HOST') ? DB_HOST : 'dbh';
        $auth_key .= defined('DB_NAME') ? DB_NAME : 'dbn';
        $auth_key .= defined('DB_USER') ? DB_USER : 'dbu';
        return hash('md5', $auth_key);
    }


    /**
     * Return encrypt string
     *
     * @param string $string  string to encrypt
     * @param string $key     hash key
     * @param bool   $addSalt if true add HASH salt to string
     *
     * @return string
     */
    public static function encrypt($string, $key = null, $addSalt = false)
    {
        if ($key == null) {
            $key = self::getDefaultKey();
        }

        if ($addSalt) {
            $string = SnapUtil::generatePassword(self::AUTO_SALT_LEN, true, true) . $string . SnapUtil::generatePassword(self::AUTO_SALT_LEN, true, true);
        }

        $crypt           = new pcrypt(MODE_ECB, "BLOWFISH", $key);
        $encrypted_value = $crypt->encrypt($string);
        $encrypted_value = base64_encode($encrypted_value);
        return $encrypted_value;
    }

    /**
     * Encrypt a generic value (scalar o array o object)
     *
     * @param mixed  $value   value to encrypt
     * @param string $key     hash key
     * @param bool   $addSalt if true add HASH salt to string
     *
     * @return string
     */
    public static function encryptValue($value, $key = null, $addSalt = false)
    {
        return self::encrypt(SnapJson::jsonEncode($value), $key, $addSalt);
    }

    /**
     * Return decrypt string
     *
     * @param string $string     string to decrypt
     * @param string $key        hash key
     * @param bool   $removeSalt if true remove HASH salt from string
     *
     * @return string
     */
    public static function decrypt($string, $key = null, $removeSalt = false)
    {
        $string = (string) $string;
        if (strlen($string) === 0) {
            return '';
        }

        if ($key == null) {
            $key = self::getDefaultKey();
        }

        $crypt  = new pcrypt(MODE_ECB, "BLOWFISH", $key);
        $orig   = $string;
        $string = base64_decode($string);
        if (empty($string)) {
            DUP_PRO_Log::traceObject("Bad encrypted string for $orig", debug_backtrace());
        }

        $decrypted_value = $crypt->decrypt($string);
        if ($removeSalt) {
            $decrypted_value = substr($decrypted_value, self::AUTO_SALT_LEN, (strlen($decrypted_value) - (self::AUTO_SALT_LEN * 2)));
        }

        return $decrypted_value;
    }

    /**
     * Return decrypt value
     *
     * @param string $string     string to decrypt
     * @param string $key        hash key
     * @param bool   $removeSalt if true HASH remove salt from string
     *
     * @return mixed
     */
    public static function decryptValue($string, $key, $removeSalt = false)
    {
        $json = self::decrypt($string, $key, $removeSalt);
        return json_decode($json);
    }
}
