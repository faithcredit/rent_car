<?php
namespace DuplicatorPro\Guzzle\Http\Exception;

defined("ABSPATH") or die("");

/**
 * Exception when a client error is encountered (4xx codes)
 */
class ClientErrorResponseException extends BadResponseException {}
