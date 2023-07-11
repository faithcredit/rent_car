<?php

/**
 * @package   Duplicator/Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace  Duplicator\Installer\Core\Deploy\Chunk;

use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Utils\Log\LogHandler;
use Duplicator\Libs\Chunking\Persistance\FileJsonPersistanceAdapter;
use Duplicator\Libs\Chunking\Iterators\GenericSeekableIteratorInterface;
use DUPX_S3_Funcs;

class SiteUpdateChunkPersistanceAdapter extends FileJsonPersistanceAdapter
{
    /**
     * Load data from previous iteration if exists
     *
     * @return mixed
     */
    public function getPersistanceData()
    {
        if (($data = parent::getPersistanceData()) != null) {
            Log::info("CHU`NK LOAD DATA: POSITION " . implode(' / ', $data['position']), 2);
            return $data['position'];
        } else {
            Log::info("CHUNK LOAD DATA: IS NULL ");
            return null;
        }
    }

    /**
     * Delete stored data if exists
     *
     * @return bool This function returns true on success, or FALSE on failure.
     */
    public function deletePersistanceData()
    {
        Log::info("CHUNK DELETE STORED DATA FILE:" . Log::v2str($this->path), 2);
        return parent::deletePersistanceData();
    }

    /**
     * Save data for next step
     *
     * @param mixed                            $data data to save
     * @param GenericSeekableIteratorInterface $it   current iterator
     *
     * @return bool This function returns true on success, or FALSE on failure.
     */
    public function savePersistanceData($data, GenericSeekableIteratorInterface $it)
    {
        // store s3 func data
        $s3Funcs                          = DUPX_S3_Funcs::getInstance();
        $s3Funcs->report['chunk']         = 1;
        $s3Funcs->report['chunkPos']      = $data;
        $s3Funcs->report['pass']          = 0;
        $s3Funcs->report['progress_perc'] = $it->getProgressPerc();
        $s3Funcs->saveData();

        // managed output for timeout shutdown
        LogHandler::setShutdownReturn(LogHandler::SHUTDOWN_TIMEOUT, JsonSerialize::serialize($s3Funcs->getJsonReport()));

        /**
         * store position post and globals
         */
        $gData = array(
            'position' => $data
        );

        Log::info("CHUNK SAVE DATA: POSITION " . implode(' / ', $data), 2);
        return parent::savePersistanceData($gData, $it);
    }
}
