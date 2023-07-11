<?php

namespace Duplicator\Core\Models;

use wpdb;

/**
 * Entity than have multiple items in database
 */
class AbstractEntityList extends AbstractEntity
{
    /**
     * Get entity by id
     *
     * @param int<0, max> $id entity id
     *
     * @return static|false Return entity istance of false on failure
     */
    public static function getById($id)
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $query = $wpdb->prepare("SELECT * FROM " . self::getTableName() . " WHERE ID = %d", $id);
        if (($row = $wpdb->get_row($query, ARRAY_A)) === null) {
            return false;
        }

        if ($row['type'] !== static::getType()) {
            return false;
        }

        return static::getEntityFromJson($row['data'], (int) $row['id']);
    }

    /**
     * Check if entity id exists
     *
     * @param int $id entity id
     *
     * @return bool true if exists false otherwise
     */
    public static function exists($id)
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $query = $wpdb->prepare("SELECT COUNT(*) FROM " . self::getTableName() . " WHERE ID = %d AND type = %s", $id, static::getType());
        if (($count = $wpdb->get_var($query)) === null) {
            return false;
        }

        return $count > 0;
    }

    /**
     * Return the number of entities of current type
     *
     * @return int<0, max>
     */
    public static function count()
    {
        return (int) parent::countItemsFromDatabase();
    }

    /**
     * Delete entity by id
     *
     * @param int $id entity id
     *
     * @return bool true on success of false on failure
     */
    public static function deleteById($id)
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        if ($id < 0) {
            return true;
        }

        if (
            $wpdb->delete(
                self::getTableName(),
                ['id' => $id],
                ['%d']
            ) === false
        ) {
            return false;
        }

        return true;
    }

    /**
     * Get all entities of current type
     *
     * @param int<0, max>                          $page           current page, if $pageSize is 0 o 1 $pase is the offset
     * @param int<0, max>                          $pageSize       page size, 0 return all entities
     * @param callable                             $sortCallback   sort function on items result
     * @param callable                             $filterCallback filter on items result
     * @param array{'col': string, 'mode': string} $orderby        query ordder by
     *
     * @return static[]|false return entities list of false on failure
     */
    public static function getAll(
        $page = 0,
        $pageSize = 0,
        $sortCallback = null,
        $filterCallback = null,
        $orderby = ['col' => 'id', 'mode' => 'ASC']
    ) {
        return parent::getItemsFromDatabase($page, $pageSize, $sortCallback, $filterCallback, $orderby);
    }

    /**
     * Delete all entity of current type
     *
     * @return int<0,max>|false The number of rows updated, or false on error.
     */
    public static function deleteAll()
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        return $wpdb->delete(
            self::getTableName(),
            ['type' => static::getType()],
            ['%s']
        );
    }
}
