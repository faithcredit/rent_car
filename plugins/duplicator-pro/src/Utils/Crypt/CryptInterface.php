<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Utils\Crypt;

interface CryptInterface
{
    /**
     * Return encrypt string
     *
     * @param string $string string to encrypt
     * @param string $key    hash key
     *
     * @return string
     */
    public static function encrypt($string, $key);

    /**
     * Return decrypt string
     *
     * @param string $string string to decrypt
     * @param string $key    hash key
     *
     * @return string
     */
    public static function decrypt($string, $key);
}
