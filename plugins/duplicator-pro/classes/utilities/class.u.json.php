<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapJson;

class DUP_PRO_JSON_U
{
    public static function customEncode($value, $iteration = 1)
    {
        $encoded = SnapJson::jsonEncodePPrint($value);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                DUP_PRO_Log::trace("#### no json errors so returning");
                return $encoded;
            case JSON_ERROR_DEPTH:
                throw new RuntimeException('Maximum stack depth exceeded'); // or trigger_error() or throw new Exception()
            case JSON_ERROR_STATE_MISMATCH:
                throw new RuntimeException('Underflow or the modes mismatch'); // or trigger_error() or throw new Exception()
            case JSON_ERROR_CTRL_CHAR:
                throw new RuntimeException('Unexpected control character found');
            case JSON_ERROR_SYNTAX:
                throw new RuntimeException('Syntax error, malformed JSON'); // or trigger_error() or throw new Exception()
            case JSON_ERROR_UTF8:
                if ($iteration == 1) {
                    DUP_PRO_Log::trace("#### utf8 error so redoing");
                    $clean = self::makeUTF8($value);
                    return self::customEncode($clean, $iteration + 1);
                } else {
                    throw new RuntimeException('UTF-8 error loop');
                }
            default:
                throw new RuntimeException('Unknown error'); // or trigger_error() or throw new Exception()
        }
    }

    public static function decode($json, $assoc = false)
    {
        return json_decode($json, $assoc);
    }

    /* ========================================================
     * PRIVATE METHODS
     * =====================================================  */
    private static function makeUTF8($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = self::makeUTF8($value);
            }
        } elseif (is_string($mixed)) {
            return utf8_encode($mixed);
        }
        return $mixed;
    }
}
