<?php
namespace DuplicatorPro\Guzzle\Http\Exception;

defined("ABSPATH") or die("");

/**
 * Exception when a server error is encountered (5xx codes)
 */
class ServerErrorResponseException extends BadResponseException {}
