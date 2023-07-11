<?php

defined("ABSPATH") or die("");
/**
 * Lower level utilities - no dependencies on anything else
 * Required for low level utility methods that json_encode requires
 */
class DUP_PRO_Low_U
{
    public static function getPublicProperties($object)
    {
        $publics = get_object_vars($object);
        unset($publics['id']);
        // Disregard anything that starts with '_'
        foreach ($publics as $key => $value) {
            if (DUP_PRO_STR::startsWith($key, '_')) {
                unset($publics[$key]);
            }
        }

        return $publics;
    }

    public static function isValidMD5($md5Candidate)
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5Candidate);
    }
}
