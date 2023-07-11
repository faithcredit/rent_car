<?php

/**
 * @package   Duplicator/Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Core\Deploy\Database;

use Duplicator\Installer\Utils\Log\Log;
use DUPX_DB;
use DUPX_DB_Functions;
use mysqli;

/**
 * Database utilities
 */
class DbUtils
{
    /**
     * Update option value or create if don't exists
     *
     * @param mysqli  $dbh      database resource
     * @param string  $name     option name
     * @param string  $value    option value
     * @param ?string $prefix   table prefix, if null is wp main prefix
     * @param bool    $autoload option autoload
     *
     * @return bool true on success, false on failure
     */
    public static function updateWpOption(mysqli $dbh, $name, $value, $prefix = null, $autoload = true)
    {

        $table = DUPX_DB_Functions::getOptionsTableName($prefix);
        Log::info('UPDATE OPTION ' . $name . ' ON TABLE ' . $table);
        $escapedTable = mysqli_real_escape_string($dbh, $table);
        $name         = mysqli_real_escape_string($dbh, $name);
        $value        = mysqli_real_escape_string($dbh, $value);
        $autoload     = ($autoload ? 'yes' : 'no');

        $query = "INSERT INTO `" . $escapedTable  . "` (option_name, option_value,  autoload) "     .
            "VALUES ('" . $name . "','" . $value . "', '" . $autoload . "') " .
            "ON DUPLICATE KEY UPDATE " .
                "option_value = '" .  $value . "', " .
                "autoload = '" .  $autoload . "'";
        return (DUPX_DB::mysqli_query($dbh, $query) !== false);
    }
}
