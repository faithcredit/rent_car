<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\DupArchive\Headers;

use Duplicator\Libs\DupArchive\DupArchive;
use Exception;

abstract class AbstractDupArchiveHeader
{
    const MAX_FILED_LEN = 1024;

    /**
     * Get header from archive
     *
     * @param resource $archiveHandle archive resource
     *
     * @return static
     */
    abstract public function readFromArchive($archiveHandle);

    /**
     * Write header to archive
     *
     * @param resource $archiveHandle archive resource
     *
     * @return int bytes written
     */
    abstract public function writeToArchive($archiveHandle);

    /**
     * Get file header
     *
     * @param resource $archiveHandle archvie resource
     * @param string   $ename         header enum
     *
     * @return string
     */
    public static function getHeaderField($archiveHandle, $ename)
    {
        $expectedStart = '<' . $ename . '>';
        $expectedEnd   = '</' . $ename . '>';

        $startingElement = fread($archiveHandle, strlen($expectedStart));

        if ($startingElement !== $expectedStart) {
            throw new Exception(
                "Invalid starting element. Was expecting {$expectedStart} but got {$startingElement}",
                DupArchive::EXCEPTION_CODE_INVALID_MARKER
            );
        }

        $headerString = stream_get_line($archiveHandle, self::MAX_FILED_LEN, $expectedEnd);

        if ($headerString === false) {
            throw new Exception('Error reading line.', DupArchive::EXCEPTION_CODE_EXTRACT_ERROR);
        }

        return $headerString;
    }
}
