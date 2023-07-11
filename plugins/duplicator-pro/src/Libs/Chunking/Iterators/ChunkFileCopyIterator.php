<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Chunking\Iterators;

use Exception;

class ChunkFileCopyIterator implements GenericSeekableIteratorInterface
{
    /** @var int<0, max> */
    protected $chunkSize = 0;
    /** @var string[] */
    protected $from = [];
    /** @var string[] */
    protected $to = [];
    /** @var int<0, max> */
    protected $bytesParsed = 0;
    /** @var int<-1, max> */
    protected $totalSize = -1;
    /** @var int[] */
    protected $position = [0,0];
    /** @var int<-1, max> */
    protected $currentSize = -1;
    /** @var string */
    protected $currentFrom = '';
    /** @var string */
    protected $currentTo = '';
    /** @var int<0, max> */
    protected $iterations = 0;

    /**
     * The iterator does not need offset information. The iterator skips files and chunks based on
     * file existence and filesize.
     *
     * @param array<string, string> $replacements array of paths to copy in the format [$from => $to]
     * @param int                   $chunkSize    chunk size if 0 chunk is disabled
     */
    public function __construct($replacements, $chunkSize = 0)
    {
        if (!is_array($replacements)) {
            throw new Exception('Remplacments must be an array');
        }
        $this->from      = array_keys($replacements);
        $this->to        = array_values($replacements);
        $this->chunkSize = $chunkSize;
        $this->rewind();
    }

    /**
     * Set total size of replacemente list
     *
     * @return int total size
     */
    public function setTotalSize()
    {
        $this->totalSize = 0;
        foreach ($this->from as $file) {
            if (!is_file($file)) {
                continue;
            }
            $this->totalSize += filesize($file);
        }

        return $this->totalSize;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->position = [0 ,0];
        $this->setCurrentItem(0);
        $this->bytesParsed = 0;
        $this->iterations  = 0;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool Returns true on success or false on failure.
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return ($this->position[0] < count($this->from));
    }

    /**
     * @param int[] $position position to seek to
     *
     * @return bool
     */
    public function gSeek($position)
    {
        if (!is_array($position) || count($position) !== 2) {
            return false;
        }
        $this->setCurrentItem($position[0], $position[1]);
        return true;
    }

    /**
     *
     * @return array{from: string, to: string, offset: int<0, max>}
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return [
            'from'   => $this->currentFrom,
            'to'     => $this->currentTo,
            'offset' => $this->position[1]
        ];
    }

    /**
     * Return chunk is, 0 no chunk
     *
     * @return int<0, max>
     */
    public function getChunkSize()
    {
        return $this->chunkSize;
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->iterations ++;

        if (($this->position[1] + $this->chunkSize) >= $this->currentSize) {
            if ($this->currentSize > 0) {
                $this->bytesParsed += ($this->currentSize - $this->position[1]);
            }
            $this->setCurrentItem(($this->position[0] + 1), 0);
        } else {
            $this->position[1] += $this->chunkSize;
            $this->bytesParsed += $this->chunkSize;
        }
    }

    /**
     * Set current item
     *
     * @param int<0, max> $index  item index
     * @param int<0, max> $offset item offset
     *
     * @return void
     */
    protected function setCurrentItem($index, $offset = 0)
    {
        $this->position[0] = $index;
        $this->currentFrom = (isset($this->from[$index]) ? $this->from[$index] : '');
        $this->currentTo   = (isset($this->to[$index]) ? $this->to[$index] : '');
        $this->currentSize = (is_file($this->currentFrom) ? filesize($this->currentFrom) : -1);
        $this->position[1] = $offset;
    }

    /**
     * Return current position
     *
     * @return int[]
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Free resources in current iteration
     *
     * @return void
     */
    public function stopIteration()
    {
    }

    /**
     * Return the key of the current element
     *
     * @return string the key
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return implode('_', $this->position);
    }

    /**
     * Return iterations count
     *
     * @return int
     */
    public function itCount()
    {
        return $this->iterations;
    }

    /**
     * Return progress percentage
     *
     * @return float progress percentage or -1 undefined
     */
    public function getProgressPerc()
    {
        if ($this->totalSize < 0) {
            $result = -1;
        } elseif ($this->totalSize == 0) {
            $result = 100;
        } else {
            $result = 100 * $this->bytesParsed / $this->totalSize;
        }

        return (float) $result;
    }
}
