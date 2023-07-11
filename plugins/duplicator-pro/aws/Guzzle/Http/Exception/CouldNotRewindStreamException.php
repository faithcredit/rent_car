<?php
namespace DuplicatorPro\Guzzle\Http\Exception;

defined("ABSPATH") or die("");

use DuplicatorPro\Guzzle\Common\Exception\RuntimeException;

class CouldNotRewindStreamException extends RuntimeException implements HttpException {}
