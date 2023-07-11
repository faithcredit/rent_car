<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\DupArchive\States;

use Duplicator\Libs\DupArchive\Headers\DupArchiveHeader;
use Duplicator\Libs\DupArchive\Processors\DupArchiveProcessingFailure;

/**
 * Dup archive state base
 */
abstract class DupArchiveStateBase
{
    const MAX_FAILURE = 1000;

    /** @var DupArchiveHeader */
    public $archiveHeader = null;
    /** @var string */
    public $basePath = '';
    /** @var string */
    public $archivePath = '';
    /** @var int<0,max> */
    public $currentFileOffset = 0;
    /** @var int<0,max> */
    public $archiveOffset = 0;
    /** @var int<-1,max> */
    public $timeSliceInSecs = -1;
    /** @var bool */
    public $working = false;
    /** @var DupArchiveProcessingFailure[] */
    public $failures = array();
    /** @var int<0,max> */
    public $failureCount = 0;
    /** @var int<-1,max> */
    public $startTimestamp = -1;
    /** @var int<0,max> */
    public $throttleDelayInUs = 0;
    /** @var int<-1,max> */
    public $timeoutTimestamp = -1;
    /** @var bool */
    public $timerEnabled = true;
    /** @var bool */
    public $isRobust = false;

    /**
     * Class constructor
     *
     * @param DupArchiveHeader $archiveHeader archive header
     */
    public function __construct(DupArchiveHeader $archiveHeader)
    {
        $this->archiveHeader = $archiveHeader;
    }

    /**
     * Save state functon
     *
     * @return void
     */
    public function save()
    {
    }

    /**
     * Check if is present a critical failure
     *
     * @return boolean
     */
    public function isCriticalFailurePresent()
    {
        if (count($this->failures) > 0) {
            foreach ($this->failures as $failure) {
                if ($failure->isCritical) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Reset values
     *
     * @return void
     */
    public function reset()
    {
        $this->basePath          = '';
        $this->archivePath       = '';
        $this->currentFileOffset = 0;
        $this->archiveOffset     = 0;
        $this->timeSliceInSecs   = -1;
        $this->working           = false;
        $this->failures          = array();
        $this->failureCount      = 0;
        $this->startTimestamp    = -1;
        $this->throttleDelayInUs = 0;
        $this->timeoutTimestamp  = -1;
        $this->timerEnabled      = true;
        $this->isRobust          = false;
    }

    /**
     * Che failure summary
     *
     * @param boolean $includeCritical include critical failures
     * @param boolean $includeWarnings include warnings failures
     *
     * @return string
     */
    public function getFailureSummary($includeCritical = true, $includeWarnings = false)
    {
        if (count($this->failures) > 0) {
            $message = '';

            foreach ($this->failures as $failure) {
                if ($includeCritical || !$failure->isCritical) {
                    $message .= "\n" . $this->getFailureString($failure);
                }
            }

            return $message;
        } else {
            if ($includeCritical) {
                if ($includeWarnings) {
                    return 'No errors or warnings.';
                } else {
                    return 'No errors.';
                }
            } else {
                return 'No warnings.';
            }
        }
    }

    /**
     * Return failure string from item
     *
     * @param DupArchiveProcessingFailure $failure failure item
     *
     * @return string
     */
    public function getFailureString(DupArchiveProcessingFailure $failure)
    {
        $s = '';

        if ($failure->isCritical) {
            $s = 'CRITICAL: ';
        }

        return "{$s}{$failure->subject} : {$failure->description}";
    }

    /**
     * Add failure item
     *
     * @param int     $type        failure type enum
     * @param string  $subject     failure subject
     * @param string  $description failure description
     * @param boolean $isCritical  true if is critical
     *
     * @return DupArchiveProcessingFailure|false false if max filures is reachd
     */
    public function addFailure($type, $subject, $description, $isCritical = true)
    {
        $this->failureCount++;
        if ($this->failureCount > self::MAX_FAILURE) {
            return false;
        }

        $failure = new DupArchiveProcessingFailure();

        $failure->type        = $type;
        $failure->subject     = $subject;
        $failure->description = $description;
        $failure->isCritical  = $isCritical;

        $this->failures[] = $failure;

        return $failure;
    }

    /**
     * Set start time
     *
     * @return void
     */
    public function startTimer()
    {
        if ($this->timerEnabled) {
            $this->timeoutTimestamp = time() + $this->timeSliceInSecs;
        }
    }

    /**
     * Check if is timeout
     *
     * @return bool
     */
    public function timedOut()
    {
        if ($this->timerEnabled) {
            if ($this->timeoutTimestamp != -1) {
                return time() >= $this->timeoutTimestamp;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
