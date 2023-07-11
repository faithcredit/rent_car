<?php

/**
 * @package   Duplicator/Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace  Duplicator\Installer\Core\Deploy\Chunk;

use Duplicator\Installer\Core\Deploy\Database\DbCleanup;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Chunking\ChunkingManager;
use Duplicator\Libs\Chunking\Persistance\PersistanceAdapterInterface;
use DUPX_S3_Funcs;
use DUPX_UpdateEngine;

/**
 * Chunk manager step 3
 */
class SiteUpdateChunkManager extends ChunkingManager
{
    /**
     * Return iterator
     *
     * @param mixed $extraData extra data for manager used on extended classes
     *
     * @return SiteUpdateChunkIterator
     */
    protected function getIterator($extraData = null)
    {
        return new SiteUpdateChunkIterator();
    }

    /**
     * Return persistance adapter
     *
     * @param mixed $extraData extra data for manager used on extended classes
     *
     * @return PersistanceAdapterInterface
     */
    protected function getPersistance($extraData = null)
    {
        return new SiteUpdateChunkPersistanceAdapter($GLOBALS["CHUNK_DATA_FILE_PATH"]);
    }

    /**
     * Exec action on current position
     *
     * @param mixed $key     Current iterator key
     * @param mixed $current Current iterator position
     *
     * @return bool return true on success, false on failure
     */
    protected function action($key, $current)
    {
        $s3FuncsManager = DUPX_S3_Funcs::getInstance();

        Log::info('CHUNK ACTION: CURRENT [' . implode('][', $current) . ']');

        switch ($current['l0']) {
            case SiteUpdateChunkIterator::STEP_START:
                $s3FuncsManager->initLog();
                $s3FuncsManager->initChunkLog($this->maxIteration, $this->timeOut, $this->throttling, $GLOBALS['DATABASE_PAGE_SIZE']);
                break;
            case SiteUpdateChunkIterator::STEP_CLEANUP_OPTIONS:
                DbCleanup::cleanupOptions();
                break;
            case SiteUpdateChunkIterator::STEP_CLEANUP_EXTREA:
                DbCleanup::cleanupExtra();
                break;
            case SiteUpdateChunkIterator::STEP_CLEANUP_PACKAGES:
                DbCleanup::cleanupPackages();
                break;
            case SiteUpdateChunkIterator::STEP_SEARCH_AND_REPLACE_INIT:
                break;
            case SiteUpdateChunkIterator::STEP_SEARCH_AND_REPLACE:
                DUPX_UpdateEngine::evaluateTableRows($current['l1'], $current['l2']);
                DUPX_UpdateEngine::commitAndSave();
                break;
            case SiteUpdateChunkIterator::STEP_REMOVE_MAINTENACE:
                $s3FuncsManager->removeMaintenanceMode();
                break;
            case SiteUpdateChunkIterator::STEP_REMOVE_LICENSE_KEY:
                $s3FuncsManager->removeLicenseKey();
                break;
            case SiteUpdateChunkIterator::STEP_CREATE_ADMIN:
                $s3FuncsManager->createNewAdminUser();
                break;
            case SiteUpdateChunkIterator::STEP_CONF_UPDATE:
                $s3FuncsManager->configFilesUpdate();
                break;
            case SiteUpdateChunkIterator::STEP_GEN_UPD:
                $s3FuncsManager->generalUpdate();
                break;
            case SiteUpdateChunkIterator::STEP_GEN_CLEAN:
                $s3FuncsManager->generalCleanup();
                $s3FuncsManager->forceLogoutOfAllUsers();
                $s3FuncsManager->duplicatorMigrationInfoSet();
                break;
            case SiteUpdateChunkIterator::STEP_NOTICE_TEST:
                $s3FuncsManager->checkForIndexHtml();
                $s3FuncsManager->noticeTest();
                break;
            case SiteUpdateChunkIterator::STEP_CLEANUP_TMP_FILES:
                $s3FuncsManager->cleanupTmpFiles();
                break;
            case SiteUpdateChunkIterator::STEP_SET_FILE_PERMS:
                $s3FuncsManager->setFilePermsission();
                break;
            case SiteUpdateChunkIterator::STEP_FINAL_REPORT_NOTICES:
                $s3FuncsManager->finalReportNotices();
                break;
            default:
        }

        /**
         * At each iteration save the status in case of exit with timeout
         */
        return $this->saveData();
    }

    /**
     * stop iteration without save data.
     * It is already saved every iteration.
     *
     * @param bool $saveData not used
     *
     * @return mixed
     */
    public function stop($saveData = false)
    {
        return parent::stop(false);
    }
}
