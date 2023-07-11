<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\Models;

use DUP_PRO_Log;
use Duplicator\Libs\Snap\SnapLog;
use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;
use Duplicator\Libs\Snap\SnapWP;
use Error;
use Exception;
use ReflectionClass;
use ReflectionObject;
use wpdb;

/**
 * Abstract Entity
 */
abstract class AbstractEntity
{
    /** @var int<-1,max> */
    protected $id = -1;

    /**
     * Return entity type identifier
     *
     * @return string
     */
    public static function getType()
    {
        // This is to avoid warnings in PHP 5.6 because isn't possibile declare an abstract static method.
        throw new Exception('This method must be extended');
    }

    /**
     * Return entity id
     *
     * @return int
     */
    final public function getId()
    {
        return $this->id;
    }

    /**
     * Set props by array key inpust data
     *
     * @param mixed[]   $data             input data
     * @param ?callable $sanitizeCallback sanitize values callback
     *
     * @return void
     */
    protected function setFromArrayKey($data, $sanitizeCallback = null)
    {
        $reflect = new ReflectionClass($this);
        $props   = $reflect->getProperties();

        foreach ($props as $prop) {
            if (!isset($data[$prop->getName()])) {
                continue;
            }

            if (is_callable($sanitizeCallback)) {
                $value = call_user_func($sanitizeCallback, $prop->getName(), $data[$prop->getName()]);
            } else {
                $value = $data[$prop->getName()];
            }
            $prop->setValue($this, $value);
        }
    }

    /**
     * Initizalize entity from JSON
     *
     * @param string     $json  JSON string
     * @param int<0,max> $rowId Entity row id
     *
     * @return static
     */
    protected static function getEntityFromJson($json, $rowId)
    {
        /** @var static $obj */
        $obj     = JsonSerialize::unserializeToObj($json, static::class);
        $reflect = new ReflectionObject($obj);
        $prop    = $reflect->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($obj, $rowId);

        return $obj;
    }

    /**
     * Save entity
     *
     * @return bool True on success, or false on error.
     */
    public function save()
    {
        $saved = false;
        if ($this->id < 0) {
            $saved = ($this->insert() !== false);
        } else {
            $saved = $this->update();
        }
        return $saved;
    }

    /**
     * Insert entity
     *
     * @return int|false The number of rows inserted, or false on error.
     */
    protected function insert()
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        if ($this->id > -1) {
            throw new Exception('Entity already exists');
        }

        $result = $wpdb->insert(
            self::getTableName(),
            [
                'type' => $this->getType(),
                'data' => false // First I create a row without an object to generate the id, and then I update the row create
            ],
            ['%s', '%s']
        );
        if ($result === false) {
            return false;
        }
        $this->id = $wpdb->insert_id;

        if ($this->update() === false) {
            $this->delete();
            return false;
        }
        return $this->id;
    }

    /**
     * Update entity
     *
     * @return bool True on success, or false on error.
     */
    protected function update()
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        if ($this->id < 0) {
            throw new Exception('Entity don\'t exists in database');
        }

        return ($wpdb->update(
            self::getTableName(),
            [
                'type' => $this->getType(),
                'data' => JsonSerialize::serialize($this, JsonSerialize::JSON_SKIP_CLASS_NAME | JSON_PRETTY_PRINT)
            ],
            ['id' => $this->id],
            ['%s', '%s'],
            ['%d']
        ) !== false);
    }

    /**
     * Delete current entity
     *
     * @return bool True on success, or false on error.
     */
    public function delete()
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        if ($this->id < 0) {
            return true;
        }

        if (
            $wpdb->delete(
                self::getTableName(),
                ['id' => $this->id],
                ['%d']
            ) === false
        ) {
            return false;
        }

        $this->id = -1;
        return true;
    }

    /**
     * Entity table name
     *
     * @return string
     */
    public static function getTableName()
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        return $wpdb->base_prefix . 'duplicator_pro_entities';
    }

    /**
     * Get entities of current type
     *
     * @param int<0, max>                          $page           current page, if $pageSize is 0 o 1 $pase is the offset
     * @param int<0, max>                          $pageSize       page size, 0 return all entities
     * @param callable                             $sortCallback   sort function on items result
     * @param callable                             $filterCallback filter on items result
     * @param array{'col': string, 'mode': string} $orderby        query ordder by
     *
     * @return static[]|false return entities list of false on failure
     */
    protected static function getItemsFromDatabase(
        $page = 0,
        $pageSize = 0,
        $sortCallback = null,
        $filterCallback = null,
        $orderby = ['col' => 'id', 'mode' => 'ASC']
    ) {
        try {
            /** @var wpdb $wpdb */
            global $wpdb;

            $offset   = $page * max(1, $pageSize);
            $pageSize = ($pageSize ? $pageSize : PHP_INT_MAX);
            $orderCol = isset($orderby['col']) ? $orderby['col'] : 'id';
            $order    = isset($orderby['mode']) ? $orderby['mode'] : 'ASC';

            $query = $wpdb->prepare(
                "SELECT * FROM " . self::getTableName() . " WHERE type = %s ORDER BY {$orderCol} {$order} LIMIT %d OFFSET %d",
                static::getType(),
                $pageSize,
                $offset
            );

            if (($rows = $wpdb->get_results($query, ARRAY_A)) === null) {
                throw new Exception('Get item query fail');
            }

            $instances = array();
            foreach ($rows as $row) {
                $instances[] = static::getEntityFromJson($row['data'], (int) $row['id']);
            }

            if (is_callable($filterCallback)) {
                $instances = array_filter($instances, $filterCallback);
            }

            if (is_callable($sortCallback)) {
                usort($instances, $sortCallback);
            } else {
                $instances = array_values($instances);
            }
        } catch (Exception $e) {
            DUP_PRO_Log::traceError(SnapLog::getTextException($e));
            return false;
        } catch (Error $e) {
            DUP_PRO_Log::traceError(SnapLog::getTextException($e));
            return false;
        }

        return $instances;
    }

    /**
     * Get ids of current type
     *
     * @param int<0, max>                          $page           current page, if $pageSize is 0 o 1 $pase is the offset
     * @param int<0, max>                          $pageSize       page size, 0 return all entities
     * @param callable                             $sortCallback   sort function on items result
     * @param callable                             $filterCallback filter on items result
     * @param array{'col': string, 'mode': string} $orderby        query ordder by
     *
     * @return int[]|false return entities list of false on failure
     */
    protected static function getIdsFromDatabase(
        $page = 0,
        $pageSize = 0,
        $sortCallback = null,
        $filterCallback = null,
        $orderby = ['col' => 'id', 'mode' => 'ASC']
    ) {
        try {
            /** @var wpdb $wpdb */
            global $wpdb;

            $offset   = $page * max(1, $pageSize);
            $pageSize = ($pageSize ? $pageSize : PHP_INT_MAX);
            $orderCol = isset($orderby['col']) ? $orderby['col'] : 'id';
            $order    = isset($orderby['mode']) ? $orderby['mode'] : 'ASC';

            $query = $wpdb->prepare(
                "SELECT id FROM " . self::getTableName() . " WHERE type = %s ORDER BY {$orderCol} {$order} LIMIT %d OFFSET %d",
                static::getType(),
                $pageSize,
                $offset
            );

            if (($rows = $wpdb->get_results($query, ARRAY_A)) === null) {
                throw new Exception('Get item query fail');
            }

            $ids = array();
            foreach ($rows as $row) {
                $ids[] = (int) $row['id'];
            }

            if (is_callable($filterCallback)) {
                $ids = array_filter($ids, $filterCallback);
            }

            if (is_callable($sortCallback)) {
                usort($ids, $sortCallback);
            } else {
                $ids = array_values($ids);
            }
        } catch (Exception $e) {
            DUP_PRO_Log::traceError(SnapLog::getTextException($e));
            return false;
        } catch (Error $e) {
            DUP_PRO_Log::traceError(SnapLog::getTextException($e));
            return false;
        }

        return $ids;
    }

    /**
     * Count entity items
     *
     * @return int|false
     */
    protected static function countItemsFromDatabase()
    {
        try {
            /** @var wpdb $wpdb */
            global $wpdb;

            $query = $wpdb->prepare(
                "SELECT COUNT(*) FROM " . self::getTableName() . " WHERE type = %s",
                static::getType()
            );

            if (($count = $wpdb->get_var($query)) === null) {
                throw new Exception('Get item query fail');
            }
        } catch (Exception $e) {
            DUP_PRO_Log::traceError(SnapLog::getTextException($e));
            return false;
        } catch (Error $e) {
            DUP_PRO_Log::traceError(SnapLog::getTextException($e));
            return false;
        }

        return (int) $count;
    }


    /**
     * Init entity table
     *
     * @return string[] Strings containing the results of the various update queries.
     */
    final public static function initTable()
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name      = static::getTableName();

        //PRIMARY KEY must have 2 spaces before for dbDelta to work
        $sql = "CREATE TABLE `{$table_name}` (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            type VARCHAR(100) NOT NULL,
            data LONGTEXT NOT NULL,
            PRIMARY KEY  (id),
            KEY type_idx (type)) 
            $charset_collate;";

        return SnapWP::dbDelta($sql);
    }
}
