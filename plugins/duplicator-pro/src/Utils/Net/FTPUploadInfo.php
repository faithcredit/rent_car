<?php

namespace Duplicator\Utils\Net;

/**
 * Contains some data needed for FTP upload process
 */
class FTPUploadInfo
{
    /** @var int */
    public $next_offset = 0;
    /** @var ?string */
    public $error_details = null;
    /** @var bool */
    public $success = false;
    /** @var bool */
    public $fatal_error = false;
}
