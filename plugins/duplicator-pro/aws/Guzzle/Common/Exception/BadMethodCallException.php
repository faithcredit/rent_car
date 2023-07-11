<?php
namespace DuplicatorPro\Guzzle\Common\Exception;

defined("ABSPATH") or die("");

class BadMethodCallException extends \BadMethodCallException implements GuzzleException {}
