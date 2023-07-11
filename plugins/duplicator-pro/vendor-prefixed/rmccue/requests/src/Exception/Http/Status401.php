<?php

/**
 * Exception for 401 Unauthorized responses
 *
 * @package Requests\Exceptions
 */
namespace VendorDuplicator\WpOrg\Requests\Exception\Http;

use VendorDuplicator\WpOrg\Requests\Exception\Http;
/**
 * Exception for 401 Unauthorized responses
 *
 * @package Requests\Exceptions
 */
final class Status401 extends Http
{
    /**
     * HTTP status code
     *
     * @var integer
     */
    protected $code = 401;
    /**
     * Reason phrase
     *
     * @var string
     */
    protected $reason = 'Unauthorized';
}
