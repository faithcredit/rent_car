<?php

/**
 * JsonSerialize class
 *
 * @package Amk\JsonSerialize
 */
namespace VendorDuplicator\Amk\JsonSerialize;

use Exception;
use stdClass;
/**
 * This class serializes and deserializes a variable in json keeping the class type and saving also private objects
 */
class JsonSerialize extends AbstractJsonSerializeObjData
{
    /**
     * Return json string
     *
     * @param mixed       $value value to serialize
     * @param int         $flags json_encode flags
     * @param int<1, max> $depth json_encode depth
     *
     * @link https://www.php.net/manual/en/function.json-encode.php
     *
     * @return string|false  Returns a JSON encoded string on success or false on failure.
     */
    public static function serialize($value, $flags = 0, $depth = 512)
    {
        $jsonData = self::valueToJsonData($value, $flags);
        $json = \version_compare(\PHP_VERSION, '5.5', '>=') ? \json_encode($jsonData, $flags, $depth) : \json_encode($jsonData, $flags);
        // If json_encode() was successful, no need to do more sanity checking.
        if (\false !== $json || $flags & self::JSON_SKIP_SANITIZE) {
            return $json;
        }
        try {
            $jsonData = self::sanitizeData($jsonData, $depth);
        } catch (Exception $e) {
            return \false;
        }
        return \version_compare(\PHP_VERSION, '5.5', '>=') ? \json_encode($jsonData, $flags, $depth) : \json_encode($jsonData, $flags);
    }
    /**
     * Returns value ready to be serialized, for objects returns a value key array, other data types are unchanged
     *
     * @param mixed   $value value to serialize
     * @param integer $flags JsonSerialize flags
     *
     * @return mixed Returns values ready to be serialized
     */
    public static function serializeToData($value, $flags = 0)
    {
        return self::valueToJsonData($value, $flags);
    }
    /**
     * Unserialize from json
     *
     * @param string      $json  json string
     * @param int<1, max> $depth json_decode depth
     * @param int         $flags json_decode flags
     *
     * @link https://www.php.net/manual/en/function.json-decode.php
     *
     * @return mixed
     */
    public static function unserialize($json, $depth = 512, $flags = 0)
    {
        $publicArray = \json_decode($json, \true, $depth, $flags);
        return self::jsonDataToValue($publicArray, $flags);
    }
    /**
     * Unserialize from json
     *
     * @param string             $json  json string
     * @param JsonUnserializeMap $map   values mapping
     * @param int<1, max>        $depth json_decode depth
     * @param int                $flags json_decode flags
     *
     * @link https://www.php.net/manual/en/function.json-decode.php
     *
     * @return mixed
     */
    public static function unserializeWithMap($json, JsonUnserializeMap $map, $depth = 512, $flags = 0)
    {
        $publicArray = \json_decode($json, \true, $depth, $flags);
        $map->setCurrent('');
        return self::jsonDataToValue($publicArray, $flags, $map);
    }
    /**
     * Unserialize json on passed object
     *
     * @param string        $json  json string
     * @param object|string $obj   object or class name to fill
     * @param int<1, max>   $depth json_decode depth
     * @param int           $flags json_decode flags
     *
     * @link https://www.php.net/manual/en/function.json-decode.php
     *
     * @return object
     */
    public static function unserializeToObj($json, $obj, $depth = 512, $flags = 0)
    {
        if (\is_object($obj)) {
        } elseif (\is_string($obj) && \class_exists($obj)) {
            $obj = self::getObjFromClass($obj);
        } else {
            throw new Exception('invalid obj param');
        }
        $value = \json_decode($json, \true, $depth, $flags);
        if (!\is_array($value)) {
            throw new Exception('json value isn\'t an array');
        }
        return self::fillObjFromValue($value, $obj, $flags);
    }
    /**
     * Perform sanity checks on data that shall be encoded to JSON.
     *
     * @param mixed $data  Variable (usually an array or object) to encode as JSON.
     * @param int   $depth Maximum depth to walk through $data. Must be greater than 0.
     *
     * @return mixed The sanitized data that shall be encoded to JSON.
     *
     * @throws Exception If depth limit is reached.
     *
     * From Worpdress function _wp_json_sanity_check
     * @see    https://github.com/WordPress/WordPress/blob/master/wp-includes/functions.php
     */
    public static function sanitizeData($data, $depth)
    {
        if ($depth < 0) {
            throw new Exception('Reached depth limit');
        }
        if (\is_array($data)) {
            $output = array();
            foreach ($data as $id => $el) {
                // Don't forget to sanitize the ID!
                if (\is_string($id)) {
                    $clean_id = self::convertString($id);
                } else {
                    $clean_id = $id;
                }
                // Check the element type, so that we're only recursing if we really have to.
                if (\is_array($el) || \is_object($el)) {
                    $output[$clean_id] = self::sanitizeData($el, $depth - 1);
                } elseif (\is_string($el)) {
                    $output[$clean_id] = self::convertString($el);
                } else {
                    $output[$clean_id] = $el;
                }
            }
        } elseif (\is_object($data)) {
            $output = new stdClass();
            foreach ((array) $data as $id => $el) {
                if (\is_string($id)) {
                    $clean_id = self::convertString($id);
                } else {
                    $clean_id = $id;
                }
                if (\is_array($el) || \is_object($el)) {
                    $output->{$clean_id} = self::sanitizeData($el, $depth - 1);
                } elseif (\is_string($el)) {
                    $output->{$clean_id} = self::convertString($el);
                } else {
                    $output->{$clean_id} = $el;
                }
            }
        } elseif (\is_string($data)) {
            return self::convertString($data);
        } else {
            return $data;
        }
        return $output;
    }
    /**
     * Convert a string to UTF-8, so that it can be safely encoded to JSON.
     *
     * @param string $string The string which is to be converted.
     *
     * @return string The checked string.
     *
     * From Worpdress function _wp_json_convert_string
     * @see    https://github.com/WordPress/WordPress/blob/master/wp-includes/functions.php
     */
    protected static function convertString($string)
    {
        static $use_mb = null;
        if (\is_null($use_mb)) {
            $use_mb = \function_exists('mb_convert_encoding');
        }
        if ($use_mb) {
            $encoding = \mb_detect_encoding($string, null, \true);
            if ($encoding) {
                return \mb_convert_encoding($string, 'UTF-8', $encoding);
            } else {
                return \mb_convert_encoding($string, 'UTF-8', 'UTF-8');
            }
        } else {
            return self::checkInvalidUtf8($string, \true);
        }
    }
    /**
     * Checks for invalid UTF8 in a string.
     *
     * @param string $string The text which is to be checked.
     * @param bool   $strip  Optional. Whether to attempt to strip out invalid UTF8. Default false.
     *
     * @return string The checked text.
     *
     * From Worpdress function wp_check_invalid_utf8
     * @see    https://github.com/WordPress/WordPress/blob/master/wp-includes/formatting.php
     */
    protected static function checkInvalidUtf8($string, $strip = \false)
    {
        $string = (string) $string;
        if (0 === \strlen($string)) {
            return '';
        }
        // Check for support for utf8 in the installed PCRE library once and store the result in a static.
        static $utf8_pcre = null;
        if (!isset($utf8_pcre)) {
            $utf8_pcre = @\preg_match('/^./u', 'a');
        }
        // We can't demand utf8 in the PCRE installation, so just return the string in those cases.
        if (!$utf8_pcre) {
            return $string;
        }
        if (1 === @\preg_match('/^./us', $string)) {
            return $string;
        }
        // Attempt to strip the bad chars if requested (not recommended).
        if ($strip && \function_exists('iconv')) {
            return (string) \iconv('utf-8', 'utf-8', $string);
        }
        return '';
    }
}
