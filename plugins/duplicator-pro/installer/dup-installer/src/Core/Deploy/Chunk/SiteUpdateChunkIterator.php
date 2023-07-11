<?php

/**
 * @package   Duplicator/Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace  Duplicator\Installer\Core\Deploy\Chunk;

use ArrayIterator;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Chunking\Iterators\GenericSeekableIteratorInterface;
use DUPX_DB_Tables;
use DUPX_S3_Funcs;
use DUPX_UpdateEngine;

/**
 * Description of class
 *
 * @author andrea
 */
class SiteUpdateChunkIterator implements GenericSeekableIteratorInterface
{
    const STEP_START                   = 'start';
    const STEP_CLEANUP_EXTREA          = 'cleanup_extra';
    const STEP_CLEANUP_PACKAGES        = 'cleanup_packages';
    const STEP_CLEANUP_OPTIONS         = 'cleanup_trans';
    const STEP_SEARCH_AND_REPLACE_INIT = 'init';
    const STEP_SEARCH_AND_REPLACE      = 'search_replace';
    const STEP_REMOVE_MAINTENACE       = 'rem_maintenance';
    const STEP_REMOVE_LICENSE_KEY      = 'rem_licenze_key';
    const STEP_CREATE_ADMIN            = 'create_admin';
    const STEP_CONF_UPDATE             = 'config_update';
    const STEP_GEN_UPD                 = 'gen_update';
    const STEP_GEN_CLEAN               = 'gen_clean';
    const STEP_NOTICE_TEST             = 'notice_test';
    const STEP_CLEANUP_TMP_FILES       = 'cleanup_tmp_files';
    const STEP_SET_FILE_PERMS          = 'set_files_perms';
    const STEP_FINAL_REPORT_NOTICES    = 'final_report';

    /** @var int */
    private static $numIterations = 10;
    /** @var array{l0: ?string, l1: ?string, l2: ?int} */
    protected $position = array(
        'l0' => self::STEP_SEARCH_AND_REPLACE_INIT,
        'l1' => null,
        'l2' => null
    );
    /** @var bool */
    protected $isValid = true;
    /** @var ArrayIterator */
    protected $tablesIterator = null;

    /**
     * Class contructor
     */
    public function __construct()
    {
        $tables               = DUPX_DB_Tables::getInstance()->getReplaceTablesNames();
        $this->tablesIterator = new ArrayIterator($tables);
        $this->rewind();
    }

    /**
     * Iterator rewind
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->isValid  = true;
        $this->position = array(
            'l0' => self::STEP_START,
            'l1' => null,
            'l2' => null
        );
        $this->tablesIterator->rewind();
    }

    /**
     * Iteratornext
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        switch ($this->position['l0']) {
            case self::STEP_START:
                $this->position['l0'] = self::STEP_CLEANUP_OPTIONS;
                break;
            case self::STEP_CLEANUP_OPTIONS:
                $this->position['l0'] = self::STEP_CLEANUP_EXTREA;
                break;
            case self::STEP_CLEANUP_EXTREA:
                $this->position['l0'] = self::STEP_CLEANUP_PACKAGES;
                break;
            case self::STEP_CLEANUP_PACKAGES:
                $this->position['l0'] = self::STEP_SEARCH_AND_REPLACE_INIT;
                break;
            case self::STEP_SEARCH_AND_REPLACE_INIT:
                if ($this->getNextSearchReplacePosition(true)) {
                    // if search and replace is valid go to STEP_SEARCH_AND_REPLACE
                    $this->position['l0'] = self::STEP_SEARCH_AND_REPLACE;
                } else {
                    // if search and replace isn't valid skip STEP_SEARCH_AND_REPLACE and go to STEP_REMOVE_MAINTENACE
                    $this->position['l0'] = self::STEP_REMOVE_MAINTENACE;
                }
                break;
            case self::STEP_SEARCH_AND_REPLACE:
                if (!$this->getNextSearchReplacePosition()) {
                    $this->position['l0'] = self::STEP_REMOVE_MAINTENACE;
                }
                break;
            case self::STEP_REMOVE_MAINTENACE:
                $this->position['l0'] = self::STEP_REMOVE_LICENSE_KEY;
                break;
            case self::STEP_REMOVE_LICENSE_KEY:
                $this->position['l0'] = self::STEP_CONF_UPDATE;
                break;
            case self::STEP_CONF_UPDATE:
                $this->position['l0'] = self::STEP_GEN_UPD;
                break;
            case self::STEP_GEN_UPD:
                $this->position['l0'] = self::STEP_GEN_CLEAN;
                break;
            case self::STEP_GEN_CLEAN:
                $this->position['l0'] = self::STEP_CREATE_ADMIN;
                break;
            case self::STEP_CREATE_ADMIN:
                $this->position['l0'] = self::STEP_NOTICE_TEST;
                break;
            case self::STEP_NOTICE_TEST:
                $this->position['l0'] = self::STEP_CLEANUP_TMP_FILES;
                break;
            case self::STEP_CLEANUP_TMP_FILES:
                $this->position['l0'] = self::STEP_SET_FILE_PERMS;
                break;
            case self::STEP_SET_FILE_PERMS:
                $this->position['l0'] = self::STEP_FINAL_REPORT_NOTICES;
                break;
            case self::STEP_FINAL_REPORT_NOTICES:
            default:
                $this->position['l0'] = null;
                $this->isValid        = false;
        }
    }

    /**
     * Go haead in tables position
     *
     * @param bool $init if true init engine
     *
     * @return bool
     */
    private function getNextSearchReplacePosition($init = false)
    {
        $valid                = true;
        $s3func               = DUPX_S3_Funcs::getInstance();
        $pages                = isset($s3func->cTableParams['pages']) ? $s3func->cTableParams['pages'] : 0;
        $this->position['l2'] = (int) $this->position['l2'];

        $this->position['l2']++;
        if ($this->position['l2'] < $pages) {
            /* NEXT PAGE */
            Log::info('ITERATOR INCREMENT PAGE: ' . $this->position['l2'] . ' PAGES[' . $pages . ']', 3);
            $s3func->cTableParams['page'] = $this->position['l2'];
        } else {
            if ($init) {
                DUPX_UpdateEngine::loadInit();
                Log::info('ITERATOR FIRST TABLE: ' . $this->position['l2'] . ' PAGES[' . $pages . ']', 3);
                $this->tablesIterator->rewind();
            } else {
                Log::info('ITERATOR INCREMENT TABLE: ' . $this->position['l2'] . ' PAGES[' . $pages . ']', 3);
                if ($s3func->cTableParams['updated']) {
                    $s3func->report['updt_tables']++;
                }
                $this->tablesIterator->next();
            }
            $this->position['l1'] = $this->tablesIterator->key();
            $this->position['l2'] = 0;

            // search first table with rows and columns
            while ($this->tablesIterator->valid()) {
                Log::info('ITERATOR CHECK TABLE: ' . $this->tablesIterator->current(), 3);
                // init table params if isn't initialized
                if (DUPX_UpdateEngine::initTableParams($this->tablesIterator->current())) {
                    // table with columns and rows found
                    break;
                }
                // NEXT TABLE
                $this->tablesIterator->next();
            }

            if ($this->tablesIterator->valid()) {
                $this->position['l1'] = $this->tablesIterator->key();
                $this->position['l2'] = 0;
            } else {
                $this->position['l1'] = null;
                $this->position['l2'] = null;
                $s3func->cTableParams = null;
                DUPX_UpdateEngine::loadEnd();
                DUPX_UpdateEngine::replaceSiteTable();
                DUPX_UpdateEngine::replaceBlogsTable();
                DUPX_UpdateEngine::logStats();
                DUPX_UpdateEngine::logErrors();
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * Set position
     *
     * @param mixed[] $position position
     *
     * @return bool true on success, false on fail
     */
    public function gSeek($position)
    {
        $this->position = $position;
        switch ($this->position['l0']) {
            case self::STEP_SEARCH_AND_REPLACE:
                $this->tablesIterator->seek($this->position['l1']);
                break;
            default:
        }

        return true;
    }

    /**
     * Get current position
     *
     * @return array{l0: string, l1: ?string, l2: ?int}
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Return position key
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return implode('_', $this->position);
    }

    /**
     * Get current
     *
     * @return array{l0: string, l1: ?string, l2: ?int}
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $result       = array(
            'l0' => $this->position['l0'],
            'l1' => null,
            'l2' => null
        );
        $result['l0'] = $this->position['l0'];

        switch ($this->position['l0']) {
            case self::STEP_SEARCH_AND_REPLACE:
                $result['l1'] = $this->tablesIterator->current();
                $result['l2'] = $this->position['l2'];
                break;
            default:
        }
        return $result;
    }

    /**
     * Stop iteration and free resource
     *
     * @return void
     */
    public function stopIteration()
    {
        switch ($this->position['l0']) {
            case self::STEP_SEARCH_AND_REPLACE:
                DUPX_UpdateEngine::commitAndSave();
                break;
            default:
        }
    }

    /**
     * Return valid status
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->isValid;
    }

    /**
     * Get iterations number
     *
     * @return int
     */
    public function itCount()
    {
        return self::$numIterations;
    }

    /**
     * Return progress percentage
     *
     * @return float progress percentage
     */
    public function getProgressPerc()
    {
        $result = 0;
        $s3Func = DUPX_S3_Funcs::getInstance();

        switch ($this->position['l0']) {
            case self::STEP_SEARCH_AND_REPLACE_INIT:
                $result = 5;
                break;
            case self::STEP_SEARCH_AND_REPLACE:
                $lowLimit      = 10;
                $higthLimit    = 90;
                $stepDelta     = $higthLimit - $lowLimit;
                $tables        = DUPX_DB_Tables::getInstance()->getReplaceTablesNames();
                $tableDelta    = $stepDelta / (count($tables) + 1);
                $singePagePerc = $tableDelta / ($s3Func->cTableParams['pages'] + 1);
                $result        = round($lowLimit + ($tableDelta * (int) $this->position['l1']) + ($singePagePerc * (int) $this->position['l2']), 2);
                break;
            case self::STEP_REMOVE_MAINTENACE:
                $result = 90;
                break;
            case self::STEP_REMOVE_LICENSE_KEY:
                $result = 91;
                break;
            case self::STEP_CREATE_ADMIN:
                $result = 92;
                break;
            case self::STEP_CONF_UPDATE:
                $result = 93;
                break;
            case self::STEP_GEN_UPD:
                $result = 94;
                break;
            case self::STEP_GEN_CLEAN:
                $result = 95;
                break;
            case self::STEP_NOTICE_TEST:
                $result = 96;
                break;
            case self::STEP_CLEANUP_TMP_FILES:
                $result = 97;
                break;
            case self::STEP_SET_FILE_PERMS:
                $result = 98;
                break;
            case self::STEP_FINAL_REPORT_NOTICES:
                $result = 100;
                break;
            default:
        }
        return $result;
    }
}
