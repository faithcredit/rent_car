<?php

/**
 * Exception for 503 Service Unavailable responses
 *
 * @package Requests\Exceptions
 */
namespace VendorDuplicator\WpOrg\Requests\Exception\Http;

use VendorDuplicator\WpOrg\Requests\Exception\Http;
/**
 * Exception for 503 Service Unavailable responses
 *
 * @package Requests\Exceptions
 */
final class Status503 extends Http
{
    /**
     * HTTP status code
     *
     * @var integer
     */
    protected $code = 503;
    /**
     * Reason phrase
     *
     * @var string
     */
    protected $reason = 'Service Unavailable';
}
