<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Chunking\Persistance;

use Duplicator\Libs\Chunking\Iterators\GenericSeekableIteratorInterface;

class NoPersistanceAdapter implements PersistanceAdapterInterface
{
    /**
     * Load data from previous iteration if exists
     *
     * @return null Is always null because persistance don't exists
     */
    public function getPersistanceData()
    {
        return null;
    }

    /**
     * Delete stored data if exists
     *
     * @return true Is always true becaus persistanfe don't exists
     */
    public function deletePersistanceData()
    {
        return true;
    }

    /**
     * Save data for next step
     *
     * @param mixed                            $data data to save
     * @param GenericSeekableIteratorInterface $it   current iterator
     *
     * @return false Is always false becaus there no persistance
     */
    public function savePersistanceData($data, GenericSeekableIteratorInterface $it)
    {
        return false;
    }
}
