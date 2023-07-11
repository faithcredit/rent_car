<?php

use Duplicator\Libs\Snap\SnapWP;

defined('ABSPATH') or die("");
require_once ABSPATH . '/wp-admin/includes/upgrade.php';
require_once(DUPLICATOR____PATH . '/classes/environment/interface.checker.php');
class DUP_PRO_DB_Checker implements DUP_PRO_iChecker
{
    /** @var string */
    public $table = '';
    /** @var mixed[] */
    public $errors = [];
    /** @var string[] */
    public $helper_messages = [];

    public function __construct()
    {
        global $wpdb;
        //The longest default wp table name is "term_relationships" (18 chars)
        //so making the test table name the same length or smaller will mean we are okay (in total below 64)
        $this->table = $wpdb->base_prefix . "dup_pro_tmp_table"; //17 chars
    }

    public function dropTable()
    {
        try {
            global $wpdb;
            $table_name = $this->table;
            $sql        = "DROP TABLE IF EXISTS `{$table_name}`;";
            $result     = $wpdb->query($sql);
            return !$this->doesTableExist($table_name);
        } catch (Exception $e) {
            $this->errors['dropTable'][] = $e;
            return false;
        }
    }

    public function insert($args = array())
    {
        try {
            $table_name = $this->table;
            if (!$this->doesTableExist($table_name)) {
                return false;
            }

            global $wpdb;
            $defaults = array(
                'id' => '',
                'test_varchar' => 'nice duplicator pro',
                'test_int' => 234,
            );
            $args     = wp_parse_args($args, $defaults);
// $table_name =   $wpdb->prefix . "duplicator_pro_test_tmp_table";
            // remove row id to determine if new or update
            $row_id = (int) $args['id'];
            unset($args['id']);
            if (!$row_id) {
            // insert a new
                if ($wpdb->insert($table_name, $args)) {
                    return $wpdb->insert_id;
                }
            } else {
            // do update method here
                if ($wpdb->update($table_name, $args, array('id' => $row_id))) {
                    return $row_id;
                }
            }

            return false;
        } catch (Exception $e) {
            $this->errors['insert'][] = $e;
            return false;
        }
    }

    public function doesTableExist($table_name)
    {
        if (empty($table_name)) {
            return false;
        }
        global $wpdb;
        //https://developer.wordpress.org/reference/functions/maybe_create_table/
        // $query = $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->esc_like( $table_name ) );
        try {
            // $result=$wpdb->get_var( $query );
            $query = "SHOW TABLES LIKE '$table_name'";
            $value = $wpdb->get_var($query);
            if (!is_null($value) && strcasecmp($value, $table_name) == 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            $this->errors['doesTableExist'][] = $e;
            return false;
        }
    }

    /**
     * Create test table
     *
     * @global \wpdb $wpdb
     *
     * @return bool
     */
    public function createTable()
    {
        try {
            global $wpdb;
            $table_name = $this->table;
            if (strlen($table_name) > 64) {
                throw new Exception("Test table name is longer than 64 chars!");
            }

            //PRIMARY KEY must have 2 spaces before for dbDelta to work
            $sql = <<<SQL
CREATE TABLE `{$table_name}` (
id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
test_varchar VARCHAR(250) NOT NULL,
test_int INT(11) NOT NULL,
PRIMARY KEY  (id));
SQL;
            SnapWP::dbDelta($sql);
            return $this->doesTableExist($table_name);
        } catch (Exception $e) {
            $this->errors['createTable'][] = $e;
            return false;
        }
    }

    public function check()
    {
        $created  = $this->createTable();
        $inserted = $this->insert();
        $passed   = $created && $inserted;
// If can't drop the temp table that's ok since we don’t need that functionality in the plugin
        $this->dropTable();
        if (!$passed) {
            $user       = '<b style="color:red;">' . DB_USER . '</b>';
            $db         = '<b style="color:red;">' . DB_NAME . '</b>';
            $evaluation = '';
            if (!$created) {
                $evaluation = DUP_PRO_U::__("couldn't create table");
            }

            if (!$inserted) {
                if ($evaluation != '') {
                    $evaluation .= ', ';
                }
                $evaluation .= DUP_PRO_U::__("couldn't insert data");
            }
            $this->helper_messages[] = sprintf(DUP_PRO_U::__('Duplicator Pro has been disabled due to insufficient database rights (%1$s). '
            . 'Please give database user %2$s full rights to database %3$s'), $evaluation, $user, $db);
        }

        return $passed;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Return messages helper
     *
     * @return string[]
     */
    public function getHelperMessages()
    {
        return $this->helper_messages;
    }
}
