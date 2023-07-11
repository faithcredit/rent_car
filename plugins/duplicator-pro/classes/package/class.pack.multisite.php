<?php

defined("ABSPATH") or die("");
if (!defined('DUPLICATOR_PRO_VERSION')) {
    exit;
} // Exit if accessed directly

use Duplicator\Libs\Snap\SnapDB;

class DUP_PRO_Multisite
{
    public $FilterSites      = array();
    protected $tablesFilters = null;

    /**
     * Filter props on json encode
     *
     * @return string[]
     */
    public function __sleep()
    {
        $props = array_keys(get_object_vars($this));
        return array_diff($props, array('tablesFilters'));
    }

    public function getDirsToFilter()
    {
        if (!empty($this->FilterSites)) {
            $path_arr       = array();
            $wp_content_dir = str_replace("\\", "/", WP_CONTENT_DIR);
            foreach ($this->FilterSites as $site_id) {
                if ($site_id == 1) {
                    if (DUP_PRO_MU::getGeneration() == DUP_PRO_MU_Generations::ThreeFivePlus) {
                            $uploads_dir = $wp_content_dir . '/uploads';
                        foreach (scandir($uploads_dir) as $node) {
                            $fullpath = $uploads_dir . '/' . $node;
                            if ($node == '.' || $node == '.htaccess' || $node == '..') {
                                        continue;
                            }
                            if (is_dir($fullpath)) {
                                if ($node != 'sites') {
                                    $path_arr[] = $fullpath;
                                }
                            }
                        }
                    } else {
                                $path_arr[] = $wp_content_dir . '/uploads';
                    }
                } else {
                    if (file_exists($wp_content_dir . '/uploads/sites/' . $site_id)) {
                        $path_arr[] = $wp_content_dir . '/uploads/sites/' . $site_id;
                    }
                    if (file_exists($wp_content_dir . '/blogs.dir/' . $site_id)) {
                        $path_arr[] = $wp_content_dir . '/blogs.dir/' . $site_id;
                    }
                }
            }
            return $path_arr;
        } else {
            return array();
        }
    }

    public function getTablesToFilter()
    {
        if (is_null($this->tablesFilters)) {
            global $wpdb;
            $this->tablesFilters = array();
            if (!empty($this->FilterSites)) {
                $prefixes = array();
                foreach ($this->FilterSites as $site_id) {
                    $prefix = $wpdb->get_blog_prefix($site_id);
                    if ($site_id == 1) {
                            $default_tables = array(
                            'commentmeta',
                            'comments',
                            'links',
                            //'options', include always options table
                            'postmeta',
                            'posts',
                            'terms',
                            'term_relationships',
                            'term_taxonomy',
                            'termmeta',
                                    );
                            foreach ($default_tables as $tb) {
                                $this->tablesFilters[] = $prefix . $tb;
                            }
                    } else {
                        $prefixes[] = $prefix;
                    }
                }

                if (count($prefixes)) {
                    foreach ($prefixes as &$value) {
                        $value = SnapDB::quoteRegex($value);
                    }
                    $regex     = '^(' . implode('|', $prefixes) . ').+';
                    $sql_query = "SHOW TABLES WHERE Tables_in_" . esc_sql(DB_NAME) . " REGEXP '" . esc_sql($regex) . "'";
                    DUP_PRO_Log::trace('TABLE QUERY PREFIX FILTER: ' . $sql_query);
                    $sub_tables          = $wpdb->get_col($sql_query);
                    $this->tablesFilters = array_merge($this->tablesFilters, $sub_tables);
                }
            }
            DUP_PRO_Log::traceObject('TABLES TO FILTERS:', $this->tablesFilters);
        }
        return $this->tablesFilters;
    }
}
