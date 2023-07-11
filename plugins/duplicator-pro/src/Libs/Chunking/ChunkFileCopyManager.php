<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Chunking;

use Duplicator\Libs\Chunking\Iterators\ChunkFileCopyIterator;
use Duplicator\Libs\Chunking\Persistance\NoPersistanceAdapter;
use Duplicator\Libs\Chunking\Persistance\PersistanceAdapterInterface;
use Duplicator\Libs\Snap\SnapIO;
use Exception;

class ChunkFileCopyManager extends ChunkingManager
{
    /** @var int<0, max> */
    protected $chunkSize = 0;
    /** @var ?string */
    protected $lastFrom = null;
    /** @var ?string */
    protected $lastTo = null;
    /** @var ?resource */
    private $fromStream = null;
    /** @var ?resource */
    private $toStream = null;

    /**
     * Class contructor
     *
     * @param mixed $extraData    extra data for manager used on extended classes
     * @param int   $maxIteration max number of iterations
     * @param int   $timeOut      timeout in milliseconds
     * @param int   $throttling   throttling lin milliseconds
     */
    public function __construct($extraData = null, $maxIteration = 0, $timeOut = 0, $throttling = 0)
    {
        $this->chunkSize = $extraData['chunkSize'];

        parent::__construct($extraData, $maxIteration, $timeOut, $throttling);
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if (is_resource($this->fromStream)) {
            fclose($this->fromStream);
        }

        if (is_resource($this->toStream)) {
            fclose($this->toStream);
        }
    }

    /**
     * Execute chunk action
     *
     * @param string                    $key     the current key
     * @param array<string, string|int> $current the current element
     *
     * @return bool
     */
    protected function action($key, $current)
    {
        $current = $this->it->current();
        if (strlen($current['from']) == 0) {
            return true;
        }

        if (is_file($current['from'])) {
            return $this->copyPart($current['from'], $current['to'], $current['offset'], $this->chunkSize);
        } elseif (is_dir($current['from'])) {
            return SnapIO::mkdirP($current['to']);
        } else {
            return false;
        }
    }

    /**
     * Copy part of file
     *
     * @param string       $from   source file path
     * @param string       $to     dest path
     * @param int<0, max>  $offset copy offset
     * @param int<-1, max> $length copy if -1 copy ot the end of file
     *
     * @return bool true on success
     */
    protected function copyPart($from, $to, $offset = 0, $length = -1)
    {
        if ($offset === 0 && file_exists($to)) {
            if (unlink($to) === false) {
                return false;
            }
        }
        if ($length <= 0 || filesize($from) <= $length) {
            return SnapIO::copy($from, $to, true);
        } else {
            $fromStream = $this->getFromStream($from);
            $toStream   = $this->getToStream($to);
            return SnapIO::copyFilePart($fromStream, $toStream, $offset, $length);
        }
    }

    /**
     * Return from stream
     *
     * @param string $from from path
     *
     * @return resource
     */
    protected function getFromStream($from)
    {
        if ($this->lastFrom === $from) {
            return $this->fromStream;
        }
        if (is_resource($this->fromStream)) {
            fclose($this->fromStream);
        }
        if (($this->fromStream = SnapIO::fopen($from, 'r')) === false) {
            throw new Exception('Can\'t open ' . $from . ' file');
        }
        return $this->fromStream;
    }

    /**
     * Return to stream
     *
     * @param string $to to path
     *
     * @return resource
     */
    protected function getToStream($to)
    {
        if ($this->lastTo === $to) {
            return $this->toStream;
        }
        if (is_resource($this->toStream)) {
            fclose($this->toStream);
        }
        if (($this->toStream = SnapIO::fopen($to, 'c+')) === false) {
            throw new Exception('Can\'t open ' . $to . ' file');
        }
        return $this->toStream;
    }

    /**
     * Return iterator
     *
     * @param array<string, mixed> $extraData extra data for manager used on extended classes
     *
     * @return ChunkFileCopyIterator
     */
    protected function getIterator($extraData = null)
    {
        $it = new ChunkFileCopyIterator($extraData['replacements'], $extraData['chunkSize']);
        $it->setTotalSize();
        return $it;
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
        return new NoPersistanceAdapter();
    }
}
