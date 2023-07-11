<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Package\Create\DupArchive;

use DUP_PRO_Log;
use Duplicator\Libs\DupArchive\DupArchiveLoggerBase;

/**
 * Dup archive logger
 */
class Logger extends DupArchiveLoggerBase
{
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
        // rsr todo ignoring flush for now
        DUP_PRO_Log::trace($s, true, $callingFunctionOverride);
    }
}
