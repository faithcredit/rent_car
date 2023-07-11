<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Package\Create;

use Exception;

/**
 * Database info
 */
class DatabaseInfo
{
    /** @var string The SQL file was built with mysqldump or PHP */
    public $buildMode = 'PHP';
    /** @var string[] A unique list of all the charSet table types used in the database */
    public $charSetList = array();
    /** @var string[] A unique list of all the collation table types used in the database */
    public $collationList = array();
    /** @var string[] A unique list of all the engine types used in the database */
    public $engineList = array();
    /** @var bool Does any filtered table have an upper case character in it */
    public $isTablesUpperCase = false;
    /** @var bool Does the database name have any filtered characters in it */
    public $isNameUpperCase = false;
    /** @var string The real name of the database */
    public $name = '';
    /** @var int he full count of all tables in the database */
    public $tablesBaseCount = 0;
    /** @var int The count of tables after the tables filter has been applied */
    public $tablesFinalCount = 0;
    /** @var int The count of tables filtered programmatically for multi-site purposes */
    public $muFilteredTableCount = 0;
    /** @var int The number of rows from all filtered tables in the database */
    public $tablesRowCount = 0;
    /** @var int The estimated data size on disk from all filtered tables in the database */
    public $tablesSizeOnDisk = 0;
    /** @var mixed[] */
    public $tablesList = array();
    /** @var string The database engine (MySQL/MariaDB/Percona) */
    public $dbEngine = '';
    /** @var string The simple numeric version number of the database server @exmaple: 5.5 */
    public $version = '0';
    /** @var string The full text version number of the database server @exmaple: 10.2 mariadb.org binary distribution */
    public $versionComment = '';
    /** @var int Number of VIEWs in the database */
    public $viewCount = 0;
    /** @var int Number of PROCEDUREs in the database */
    public $procCount = 0;
    /** @var int Number of PROCEDUREs in the database */
    public $funcCount = 0;
    /** @var array<string, array{event: string, table: string, timing: string, create: string}> Trigger information */
    public $triggerList = array();

    /**
     * Classs constructor
     */
    public function __construct()
    {
    }

    /**
     * add table info in list
     *
     * @param string   $name           table name
     * @param int      $inaccurateRows This data is intended as a preliminary count and therefore not necessarily accurate
     * @param int      $size           This data is intended as a preliminary count and therefore not necessarily accurate
     * @param int|bool $insertedRows   This value, if other than false, is the exact line value inserted into the dump file
     *
     * @return void
     */
    public function addTableInList($name, $inaccurateRows, $size, $insertedRows = false)
    {
        $this->tablesList[$name] = array(
            'inaccurateRows' => (int) $inaccurateRows,
            'insertedRows'   => $insertedRows,
            'size'           => (int) $size
        );
    }

    /**
     * Set inserted words
     *
     * @param string $name  table name
     * @param int    $count the real inseret rows cont for table
     *
     * @return void
     */
    public function addInsertedRowsInTableList($name, $count)
    {
        if (!isset($this->tablesList[$name])) {
            throw new Exception('No found table ' . $name . ' in table info');
        } else {
            $this->tablesList[$name]['insertedRows'] = (int) $count;
        }
    }

    /**
     * Add triggers to list
     *
     * @return array<string, array{event: string, table: string, timing: string, create: string}>
     */
    public function addTriggers()
    {
        global $wpdb;
        $this->triggerList = array();

        if (!is_array($triggers = $wpdb->get_results("SHOW TRIGGERS", ARRAY_A))) {
            return $this->triggerList;
        }

        foreach ($triggers as $trigger) {
            $name                     = $trigger["Trigger"];
            $create                   = $wpdb->get_row("SHOW CREATE TRIGGER `{$name}`", ARRAY_N);
            $this->triggerList[$name] = array(
                "event" => $trigger["Event"],
                "table" => $trigger["Table"],
                "timing" => $trigger["Timing"],
                "create" => "DELIMITER ;;\n" . $create[2] . ";;\nDELIMITER ;"
            );
        }

        return $this->triggerList;
    }
}
