<?php

/**
 * Exception for 502 Bad Gateway responses
 *
 * @package Requests\Exceptions
 */
namespace VendorDuplicator\WpOrg\Requests\Exception\Http;

use VendorDuplicator\WpOrg\Requests\Exception\Http;
/**
 * Exception for 502 Bad Gateway responses
 *
 * @package Requests\Exceptions
 */
final class Status502 extends Http
{
    /**
     * HTTP status code
     *
     * @var integer
     */
    protected $code = 502;
    /**
     * Reason phrase
     *
     * @var string
     */
    protected $reason = 'Bad Gateway';
}
