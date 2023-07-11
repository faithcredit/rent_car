<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Utils\HTTP;

use VendorDuplicator\Amk\JsonSerialize\AbstractJsonSerializable;
use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;
use VendorDuplicator\WpOrg\Requests\Requests;
use VendorDuplicator\WpOrg\Requests\Response;
use VendorDuplicator\WpOrg\Requests\Response\Headers;

class DynamicChunkRequests extends AbstractJsonSerializable
{
    /** @var int */
    const CHUNK_SIZE_MIN = 10240; // 10k in bytes
    /** @var int */
    const CHUNK_SIZE_MAX = 104857600; // 100MB in bytes
    /** @var float */
    const DEFAULT_CHUNK_TIME = 5.0; // seconds, can be a float number

    /** @var string real download url */
    protected $downloadUrl = '';
    /** @var float */
    protected $chunkTime = self::DEFAULT_CHUNK_TIME;
    /** @var int */
    protected $offset = 0;
    /** @var int */
    protected $fullSize = -1;
    /** @var float */
    protected $lastSize = -1;
    /** @var float */
    protected $lastTime = -1;
    /** @var bool */
    protected $complete = false;
    /** @var mixed[] */
    protected $extraData = [];

    /**
     * Class constructor
     *
     * @param string  $url       request URL
     * @param mixed[] $extraData Extra data, useful to save for persistence
     */
    public function __construct($url = "", $extraData = [])
    {
        $this->downloadUrl = $url;
        $this->extraData   = (array) $extraData;
    }

    /**
     * Set downloadUrl
     *
     * @param string $url Download url address
     *
     * @return void
     */
    public function setDownloadUrl($url)
    {
        $this->downloadUrl = $url;
    }

    /**
     * Set chunk time
     *
     * @param int|float $time in seconds
     *
     * @return float
     */
    public function setChunkTime($time)
    {
        $maxTime         = SnapUtil::phpIniGet('max_execution_time', 30, 'float') - 5;
        $this->chunkTime = (float) max(1, min($time, $maxTime));
        return $this->chunkTime;
    }

    /**
     * Main interface for HTTP requests.
     *
     * This function wraps the Requests::request function of Wordpress application a dynamic range based on class settings
     *
     * @param array<string, mixed>      $headers        Extra headers to send with the request
     * @param array<string, mixed>|null $data           Data to send either as a query string for GET/HEAD requests, or in the body for POST requests
     * @param string                    $type           HTTP request type (Requests constants)
     * @param array<string, mixed>      $options        Options for the request (see description for more information)
     * @param bool                      $resetOnFailure If true in case of failure, the chunk status is reset
     *
     * @return Response|bool returns xxx or false if no request was made,
     *                                false is not necessarily an error. Simply that the offset is out of range
     */
    public function request($headers = array(), $data = array(), $type = Requests::GET, $options = array(), $resetOnFailure = true)
    {
        if (($range = $this->getRequestRange()) === false) {
            $this->lastSize = -1;
            $this->lastTime = -1;
            $this->complete = true;
            return false;
        }

        $headers['Range'] = 'bytes=' . $range;
        \DUP_PRO_Log::trace('REQUEST HEADERS ' . SnapLog::v2str($headers));
        $startTime          = microtime(true);
        $options['timeout'] = ($this->chunkTime * 100); // make sure avoid the request timeout
        // $options['protocol_version'] = 1.1;
        // $options['transport'] = "\\VendorDuplicator\\WpOrg\\Requests\\Transport\\Fsockopen";

        try {
            $response = Requests::request($this->downloadUrl, $headers, $data, $type, $options);
        } catch (Exception $e) {
            $response          = new Response();
            $response->success = false;
        }

        if ($response->success !== true) {
            if ($resetOnFailure) {
                $this->lastSize = -1;
                $this->lastTime = -1;
                $this->complete = true;
            }
            return $response;
        }

        $headers        = $response->headers->getAll();
        $this->lastTime = microtime(true) - $startTime;
        $this->lastSize = (int) self::getLastHeaderValue($response->headers, 'content-length', -1);
        if ($this->lastSize == -1) {
            /**
             * @todo Implement a protocol extension system based on cloud type.
             */
            $this->lastSize = (int) self::getLastHeaderValue($response->headers, 'x-dropbox-content-length', -1);
        }
        if ($this->lastSize == -1) {
            // In case of OneDrive sometimes there is no content-length in headers.
            // In order to prevent download speed from getting too low because of lastSize being -1
            // (because of missing content-length), we take lastSize from last used $range.
            $dashPos        = strpos($range, '-');
            $this->lastSize = (int)substr($range, $dashPos + 1) - (int)substr($range, 0, $dashPos) + 1;
        }
        \DUP_PRO_Log::trace('REMOTE RESPONSE CONTENT LEN ' . SnapLog::v2str($this->lastSize));

        if ($response->status_code == 200) {
            $this->fullSize = $this->lastSize;
            $this->offset   = $this->lastSize + 1;
            $this->complete = true;
            return $response;
        }

        $matches      = array();
        $contentRange = self::getLastHeaderValue($response->headers, 'content-range', '');
        \DUP_PRO_Log::trace('REMOTE RESPONSE CONTENT RANGE ' . SnapLog::v2str($contentRange) . "\n");

        if (
            $response->status_code != 206 ||
            preg_match('/bytes\s+(\d+)-(\d+)\/(\d+|\*)/', $contentRange, $matches) !== 1
        ) {
            $this->lastSize = -1;
            $this->complete = true;
            return $response;
        }

        $this->fullSize = ($matches[3] == '*' ? -1 : (int) $matches[3]);
        $this->offset   = ((int) $matches[2]) + 1;
        $this->complete = ($this->offset >= $this->fullSize);

        return $response;
    }

    /**
     * Reset current chunk download.
     *
     * @return void
     */
    public function reset()
    {
        $this->offset   = 0;
        $this->fullSize = -1;
        $this->lastSize = -1;
        $this->lastTime = -1;
        $this->complete = false;
    }

    /**
     * Return complete status
     *
     * @return bool
     */
    public function isComplete()
    {
        return $this->complete;
    }

    /**
     * Calculate request range
     *
     * @return string|bool
     */
    protected function getRequestRange()
    {
        \DUP_PRO_Log::trace('LAST SIZE ' . SnapLog::v2str($this->lastSize) . ' TIME ' . SnapLog::v2str($this->lastTime) . ' CHUNK TIME ' . $this->chunkTime);

        if ($this->fullSize >= 0 && $this->offset >= $this->fullSize) {
            return false;
        }

        if ($this->lastSize <= 0 || $this->lastTime <= 0) {
            return ($this->offset . '-' . ($this->offset + self::CHUNK_SIZE_MIN - 1));
        }

        $size = SnapUtil::getIntBetween(
            floor($this->lastSize / $this->lastTime * $this->chunkTime),
            self::CHUNK_SIZE_MIN,
            self::CHUNK_SIZE_MAX
        );

        \DUP_PRO_Log::trace('NEW SIZE ' . SnapLog::v2str($size));
        if (($this->offset + $size) >= $this->fullSize) {
            // It is important to explicitly specify right side of range, because range
            // might be parsed in caller's function, so numbers are expected
            return ($this->offset . '-' . ($this->fullSize - 1));
        }

        return ($this->offset . '-' . ($this->offset + $size - 1));
    }

    /**
     * Function that returns the last value of a key in the header.
     * In case of redirect of the demand the values can be multiple therefore the last one makes reference to the last loaded URL
     *
     * @param Headers $headers response headers
     * @param string  $key     header key
     * @param mixed   $default default value if header don't exists
     *
     * @return mixed
     */
    protected static function getLastHeaderValue(Headers $headers, $key, $default = false)
    {
        if (($result = $headers->getValues($key)) === null) {
            return $default;
        }

        return end($result);
    }

    /**
     * Get extra data array or singl valur if key is set
     *
     * @param string $key     extra data key, if empty return all data
     * @param mixed  $default default value if key don't exists
     *
     * @return mixed
     */
    public function getExtraData($key = '', $default = false)
    {
        if (strlen($key) == 0) {
            return $this->extraData;
        }

        return (isset($this->extraData[$key]) ? $this->extraData[$key] : $default);
    }

    /**
     * Set extra data value
     *
     * @param string $key   extra data key
     * @param mixed  $value extra data value
     *
     * @return void
     */
    public function setExtraData($key, $value)
    {
        $this->extraData[$key] = $value;
    }

    /**
     * Get the value of fullSize
     *
     * @return int -1 if is unknown
     */
    public function getFullSize()
    {
        return $this->fullSize;
    }
}
