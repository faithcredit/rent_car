<?php

/**
 * Logger for dup archive
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Core\Deploy\DupArchive;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\DupArchive\DupArchiveLoggerBase;
use Exception;

/**
 * Logger for dup archive
 */
class DawsLogger extends DupArchiveLoggerBase
{
    /**
     * Init logger
     *
     * @return void
     */
    public static function init()
    {
        set_error_handler(array(__CLASS__, "terminateMissingVariables"), E_ERROR);
    }

    /**
     * Log function
     *
     * @param string    $s                       string to log
     * @param boolean   $flush                   if true flish log
     * @param ?callable $callingFunctionOverride call back function
     *
     * @return void
     */
    public function log($s, $flush = false, $callingFunctionOverride = null)
    {
        Log::info($s, Log::LV_DEFAULT, $flush);
    }

    /**
     * Throw exception on php error
     *
     * @param int    $errno   errno
     * @param string $errstr  error message
     * @param string $errfile file
     * @param int    $errline line
     *
     * @return bool
     */
    public static function terminateMissingVariables($errno, $errstr, $errfile, $errline)
    {
        Log::info("ERROR $errno, $errstr, {$errfile}:{$errline}");
        /**
         * INTERCEPT ON processRequest AND RETURN JSON STATUS
         */
        throw new Exception("ERROR:{$errfile}:{$errline} | " . $errstr, $errno);

        return true; // @phpstan-ignore-line
    }
}
