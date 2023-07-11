<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Package\Storage;

use Duplicator\Libs\Chunking\ChunkFileCopyManager;

/**
 * Chunk manager for storage uploads
 */
class StorageUploadChunkFiles extends ChunkFileCopyManager
{
    /**
     * Return persistance adapter
     *
     * @param mixed $extraData extra data for manager used on extended classes
     *
     * @return UploadPackageFilePersistanceAdapter
     */
    protected function getPersistance($extraData = null)
    {
        return new UploadPackageFilePersistanceAdapter($extraData['upload_info'], $extraData['package']);
    }
}
