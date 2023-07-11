<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Package\Storage;

use DUP_PRO_Package;
use DUP_PRO_Package_Upload_Info;
use Duplicator\Libs\Chunking\Persistance\PersistanceAdapterInterface;
use Duplicator\Libs\Chunking\Iterators\GenericSeekableIteratorInterface;

class UploadPackageFilePersistanceAdapter implements PersistanceAdapterInterface
{
    /** @var DUP_PRO_Package_Upload_Info */
    protected $uploadInfo;
    /** @var DUP_PRO_Package */
    protected $package;

    /**
     * @param DUP_PRO_Package_Upload_Info $uploadInfo upload info object
     * @param DUP_PRO_Package             $package    package object
     */
    public function __construct(DUP_PRO_Package_Upload_Info $uploadInfo, DUP_PRO_Package $package)
    {
        $this->uploadInfo = $uploadInfo;
        $this->package    = $package;
    }

    /**
     * Load data from previous iteration if exists
     *
     * @return mixed
     */
    public function getPersistanceData()
    {
        return empty($this->uploadInfo->chunkPosition) ? null : $this->uploadInfo->chunkPosition;
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
        $this->uploadInfo->progress      = $it->getProgressPerc();
        $this->uploadInfo->chunkPosition = $data;

        return $this->package->update();
    }

    /**
     * delete data
     *
     * @return bool
     */
    public function deletePersistanceData()
    {
        $this->uploadInfo->chunkPosition = [];
        return $this->package->update();
    }
}
