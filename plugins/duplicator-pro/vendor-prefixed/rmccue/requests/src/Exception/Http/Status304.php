<?php

/**
 * Exception for 304 Not Modified responses
 *
 * @package Requests\Exceptions
 */
namespace VendorDuplicator\WpOrg\Requests\Exception\Http;

use VendorDuplicator\WpOrg\Requests\Exception\Http;
/**
 * Exception for 304 Not Modified responses
 *
 * @package Requests\Exceptions
 */
final class Status304 extends Http
{
    /**
     * HTTP status code
     *
     * @var integer
     */
    protected $code = 304;
    /**
     * Reason phrase
     *
     * @var string
     */
    protected $reason = 'Not Modified';
}
