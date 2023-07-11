<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Chunking\Persistance;

use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;
use Duplicator\Libs\Chunking\Iterators\GenericSeekableIteratorInterface;
use Exception;

class FileJsonPersistanceAdapter implements PersistanceAdapterInterface
{
    /** @var string persistance file path */
    protected $path = '';

    /**
     * Class contructor
     *
     * @param string $path persistance file path
     */
    public function __construct($path)
    {
        if (!is_string($path) || strlen($path) == 0) {
            throw new Exception('Persistance file path must be a string and can\'t be empty');
        }
        $this->path = $path;
    }

    /**
     * Load data from previous iteration if exists
     *
     * @return mixed
     */
    public function getPersistanceData()
    {
        if (file_exists($this->path)) {
            if (($data = file_get_contents($this->path)) === false) {
                return null;
            }
            return json_decode($data, true);
        } else {
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
        return (file_exists($this->path) ? unlink($this->path) : true);
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
        if (($json = JsonSerialize::serialize($data)) === false) {
            return false;
        }
        return (file_put_contents($this->path, $json) !== false);
    }
}
