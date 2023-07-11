<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Utils\Crypt;

//DUP_PRO_Crypt
class CryptCustom implements CryptInterface
{
    /**
     * Return encrypt string
     *
     * @param string $string string to encrypt
     * @param string $key    hash key
     *
     * @return string
     */
    public static function encrypt($string, $key)
    {
        $result = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $char    = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char    = chr(ord($char) + ord($keychar));
            $result .= $char;
        }

        return urlencode(base64_encode($result));
    }

    /**
     * Return decrypt string
     *
     * @param string $string string to decrypt
     * @param string $key    hash key
     *
     * @return string
     */
    public static function decrypt($string, $key)
    {
        $result = '';
        $string = urldecode($string);
        $string = base64_decode($string);

        for ($i = 0; $i < strlen($string); $i++) {
            $char    = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char    = chr(ord($char) - ord($keychar));
            $result .= $char;
        }

        return $result;
    }

    /**
     * Encrypt function by def key
     *
     * @param string $string stringto encrypt
     *
     * @return string
     */
    public static function scramble($string)
    {
        return self::encrypt($string, self::sk1() . self::sk2());
    }

    /**
     * Decrypt function by def key
     *
     * @param string $string encrypted string
     *
     * @return string
     */
    public static function unscramble($string)
    {
        return self::decrypt($string, self::sk1() . self::sk2());
    }

    /**
     * Get sk key 1
     *
     * @return string
     */
    public static function sk1()
    {
        return 'fdas' . self::encrypt('v1', 'abx');
    }

    /**
     * Get sk key 2
     *
     * @return string
     */
    public static function sk2()
    {
        return 'fres' . self::encrypt('v2', 'ad3x');
    }
}
