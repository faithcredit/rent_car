<?php

/**
 * Build insert iterator
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapLog;

/**
 * Dump database tables for PHPDump (single and multi)
 */
class DUP_PRO_DB_Build_Iterator implements Iterator
{
    const TEMP_COUNTER_FILE_PREFIX      = 'dup_pro_db_build_progress_';
    const UPDATE_POINTER_FILE_EACH_ROWS = 500;

    /** @var ?string store file where pute last offsets */
    private $storeProgressFile = null;
    /** @var bool is true if use store progress file */
    private $isStoreProgress = false;
    /** @var bool */
    private $isValid = false;
    /** @var string[] tables list to iterate */
    private $tables = array();
    /** @var int count of tables */
    private $numTables = 0;
    /** @var int current table index */
    private $tableIndex = -1;
    /** @var int current table offset */
    private $tableOffset = 0;
    /** @var int table rows */
    private $tableRows = 0;
    /** @var mixed is last index offset, can be last primary key or unique key, single o compound */
    private $lastIndexOffset = 0;
    /** @var int total rows insered count. */
    private $totalRowsOffset = 0;
    /** @var int files size */
    private $fileSize = 0;
    /** @var bool This value becomes true only by calling a specific function and returns false to the first next row or table. */
    private $lastIsCompleteInsert = false;
    /** @var callable function called at the beginning of the table parsing. */
    private $startTableCallback = null;
    /** @var callable function called at the end of the table parsing. */
    private $endTableCallback = null;
    /** @var int */
    private $storePointerRowCount = 0;

    /**
     *
     * @param string[]      $tables
     * @param string|null   $storeProgressFile  // if null the store progress file system isn\'t used. I'ts faster
     * @param callable|null $startTableCallback // callback called at the begin of current table insert
     * @param callable|null $endTableCallback   // callback called at the end of current table insert
     *
     * @throws Exception
     */
    public function __construct($tables, $storeProgressFile = null, $startTableCallback = null, $endTableCallback = null)
    {
        $this->tables    = (array) $tables;
        $this->numTables = count($this->tables);

        if ($this->setStoreProgressFile($storeProgressFile) == false) {
            throw new Exception('Can\t set progress file');
        }
        $this->setPosition();

        if (is_callable($startTableCallback)) {
            $this->startTableCallback = $startTableCallback;
        }

        if (is_callable($endTableCallback)) {
            $this->endTableCallback = $endTableCallback;
        }
    }

    /**
     * set current position if progress file exists or rewrind the iterator
     *
     * @return void
     *
     * @throws Exception
     */
    protected function setPosition()
    {
        if (!$this->isStoreProgress) {
            $this->rewind();
            return;
        }

        DUP_PRO_Log::trace("LOAD DATA DATABASE ITERATOR");

        if (($content = file_get_contents($this->storeProgressFile)) === false) {
            throw new Exception('Can\'t read database store progress file');
        }

        if (strlen($content) === 0) {
            throw new Exception('Store progress file is empty');
        }

        if (($data = json_decode($content)) === null) {
            throw new Exception('Can\'t decode json progress data content: ' . SnapLog::v2str($content));
        }

        $this->tableIndex           = $data[0];
        $this->tableOffset          = $data[1];
        $this->lastIndexOffset      = is_scalar($data[2]) ? $data[2] : (array) $data[2];
        $this->totalRowsOffset      = $data[3];
        $this->lastIsCompleteInsert = $data[4];
        $this->tableRows            = $data[5];
        $this->fileSize             = $data[6];
        $this->isValid              = $data[7];

        DUP_PRO_Log::trace("SET POSITION TABLE INDEX " . $this->tableIndex . " OFFSET INDEX " . SnapLog::v2str($this->lastIndexOffset));
    }

    /**
     * save current position in progress file if initialized
     *
     * @return bool
     * @throws Exception // if fwrite fail
     */
    protected function saveCounterFile()
    {
        $this->storePointerRowCount = 0;

        if (!$this->isStoreProgress) {
            return true;
        }

        $data = array(
            $this->tableIndex,
            $this->tableOffset,
            $this->lastIndexOffset,
            $this->totalRowsOffset,
            $this->lastIsCompleteInsert,
            $this->tableRows,
            $this->fileSize,
            $this->isValid
        );

        if (($dataEncoded = json_encode($data)) === false) {
            throw new Exception('Can\'t encode database iterator pointer DATA: ' . SnapLog::v2str($data));
        }

        // file_put_content is less optimized than fopen,
        // fwrite but in some serve keep the hadler file open and do fseek in massive way rarely generate corrupted files.
        // So writing and closing the file is the safest method.
        if (file_put_contents($this->storeProgressFile, $dataEncoded) !== strlen($dataEncoded)) {
            throw new Exception('Can\'t write database store progress file');
        }

        return true;
    }

    /**
     * rewind current iterator (reset all offset and table counts)
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        DUP_PRO_Log::infoTrace("REWIND DATABASE ITERATOR");
        $this->tableIndex           = -1;
        $this->tableOffset          = 0;
        $this->lastIndexOffset      = 0;
        $this->totalRowsOffset      = 0;
        $this->lastIsCompleteInsert = true;
        $this->tableRows            = 0;
        $this->fileSize             = 0;
        $this->storePointerRowCount = 0;
        $this->next();
    }

    public function removeCounterFile()
    {
        if (file_exists($this->storeProgressFile)) {
            unlink($this->storeProgressFile);
        }
        $this->isStoreProgress   = false;
        $this->storeProgressFile = null;
    }

    /**
     * open store pregress file, if don't exists create and initialize it.
     *
     * @param string $storeProgressFile
     *
     * @return boolean
     */
    public function setStoreProgressFile($storeProgressFile = null)
    {
        $this->storeProgressFile = null;
        $this->isStoreProgress   = false;

        if (empty($storeProgressFile)) {
            return true;
        }

        if (($fileExists = file_exists($storeProgressFile))) {
            if (!is_writable($storeProgressFile)) {
                return false;
            }
        } elseif (!is_writable(dirname($storeProgressFile))) {
            return false;
        }

        $this->storeProgressFile = $storeProgressFile;
        $this->isStoreProgress   = true;
        if (!$fileExists) {
            $this->rewind();
        }

        return true;
    }

    /**
     * next element (table) of iterator, put all table offsets at 0 and count tableRows
     *
     * If set call endTableCallback and startTableCallback
     *
     * @return boolean
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        if ($this->tableIndex >= 0 && is_callable($this->endTableCallback)) {
            call_user_func($this->endTableCallback, $this);
        }

        $this->tableOffset     = 0;
        $this->lastIndexOffset = 0;
        $this->tableRows       = 0;
        $this->tableIndex++;

        if (($this->isValid = ($this->tableIndex < $this->numTables))) {
            $res             = DUP_PRO_DB::getTablesRows($this->current());
            $this->tableRows = $res[$this->current()];

            if (is_callable($this->startTableCallback)) {
                call_user_func($this->startTableCallback, $this);
            }
            DUP_PRO_Log::infoTrace("INSERT ROWS TABLE[INDEX:" . $this->tableIndex . "] " . $this->tables[$this->tableIndex] . " NUM ROWS: " . $this->tableRows);
        }
        $this->saveCounterFile();
        return $this->isValid;
    }

    /**
     * increment current table offsets and update store process file if exists
     *
     * @param mixed $lastIndexOffset
     *
     * @return int // return total rows parsed count
     */
    public function nextRow($lastIndexOffset = 0, $addFileSize = 0)
    {
        $this->totalRowsOffset++;
        $this->tableOffset++;
        $this->lastIndexOffset      = $lastIndexOffset;
        $this->lastIsCompleteInsert = false;
        $this->fileSize            += $addFileSize;
        $this->storePointerRowCount++;

        if ($this->storePointerRowCount >= self::UPDATE_POINTER_FILE_EACH_ROWS) {
            $this->saveCounterFile();
        }

        return $this->totalRowsOffset;
    }

    /**
     * set last is complete inster at true and save it in store preocess file.
     */
    public function setLastIsCompleteInsert($addFileSize = 0)
    {
        $this->fileSize            += $addFileSize;
        $this->lastIsCompleteInsert = true;
        $this->saveCounterFile();
    }

    /**
     *
     * @param int $fileSize
     */
    public function addFileSize($fileSize = 0)
    {
        $this->fileSize += $fileSize;
        $this->saveCounterFile();
    }

    /**
     *
     * @return bool
     */
    public function isCurrentTableOffsetValid()
    {
        return $this->tableOffset < $this->tableRows;
    }

    /**
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->isValid;
    }

    /**
     *
     * @return string|bool // current table name or false if isn\'t valid
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->isValid ? $this->tables[$this->tableIndex] : false;
    }

    /**
     *
     * @return int // table rows of current table
     */
    public function getCurrentRows()
    {
        return $this->tableRows;
    }

    /**
     *
     * @return int // current offset of current table
     */
    public function getCurrentOffset()
    {
        return $this->tableOffset;
    }

    /**
     *
     * @return mixed // last index offset selecte, can be a primary key or mixed unique key also composed
     */
    public function getLastIndexOffset()
    {
        return $this->lastIndexOffset;
    }

    /**
     *
     * @return int // total rows dumped
     */
    public function getTotalsRowsOffset()
    {
        return $this->totalRowsOffset;
    }

    /**
     *
     * @return int // stored file size
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     *
     * @return bool // return true if the last inserted sub loop is completed
     */
    public function lastIsCompleteInsert()
    {
        return $this->lastIsCompleteInsert;
    }

    /**
     *
     * @return int // current table index
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->tableIndex;
    }

    /**
     *
     * @return int // num table to process
     */
    public function count()
    {
        return $this->numTables;
    }
}
