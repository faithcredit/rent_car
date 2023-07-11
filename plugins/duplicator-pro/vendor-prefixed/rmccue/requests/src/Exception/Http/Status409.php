<?php

/**
 * Exception for 409 Conflict responses
 *
 * @package Requests\Exceptions
 */
namespace VendorDuplicator\WpOrg\Requests\Exception\Http;

use VendorDuplicator\WpOrg\Requests\Exception\Http;
/**
 * Exception for 409 Conflict responses
 *
 * @package Requests\Exceptions
 */
final class Status409 extends Http
{
    /**
     * HTTP status code
     *
     * @var integer
     */
    protected $code = 409;
    /**
     * Reason phrase
     *
     * @var string
     */
    protected $reason = 'Conflict';
}
