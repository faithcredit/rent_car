<?php

/**
 * Utility class used for various task
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    DUP_PRO
 * @subpackage classes/utilities
 * @copyright  (c) 2017, Snapcreek LLC
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 */

defined("ABSPATH") or die("");

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Shell\Shell;

class DUP_PRO_U
{
    /**
     * return absolute path for the directories that are core directories
     *
     * @param $original If true it returns yes the original realpaths and paths, in case they are links, Otherwise it returns only the realpaths.
     *
     * @return string[]
     */
    public static function getWPCoreDirs($original = false)
    {
        $corePaths   = DUP_PRO_Archive::getArchiveListPaths();
        $corePaths[] = $corePaths['abs'] . '/wp-admin';
        $corePaths[] = $corePaths['abs'] . '/wp-includes';

        if ($original) {
            $origPaths   = DUP_PRO_Archive::getOriginalPaths();
            $origPaths[] = $origPaths['abs'] . '/wp-admin';
            $origPaths[] = $origPaths['abs'] . '/wp-includes';

            $corePaths = array_merge($corePaths, $origPaths);
        }

        return array_values(array_unique($corePaths));
    }

    /**
     * return absolute path for the files that are core directories
     *
     * @return array
     */
    public static function getWPCoreFiles()
    {
        return array(
            DUP_PRO_Archive::getArchiveListPaths('wpconfig') . '/wp-config.php'
        );
    }

    /**
     * Converts an absolute path to a relative path
     *
     * @param string $from The the path relative to $to
     * @param string $to   The full path of the directory to transform
     *
     * @return string  A string of the result
     */
    public static function getRelativePath($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from    = explode('/', $from);
        $to      = explode('/', $to);
        $relPath = $to;

        foreach ($from as $depth => $dir) {
            // find first non-matching dir
            if ($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath   = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    //$relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }

    /**
     * Gets the percentage of one value to another
     * example:
     *     $val1 = 100
     *     $val2 = 400
     *     $res  = 25
     *
     * @param int|float $val1
     * @param int|float $val2
     *
     * @return float  Returns the results
     */
    public static function percentage($val1, $val2, $precision = 0)
    {
        $division = $val1 / (float) $val2;
        $res      = $division * 100;
        return round($res, $precision);
    }

    /**
     * Localize and echo the current text with escaping html
     *
     * @param string $text The text to localize
     *
     * @return void
     */
    public static function esc_html_e($text)
    {
        esc_html_e($text, DUP_PRO_Constants::PLUGIN_SLUG);
    }

    /**
     * Localize and echo the current text with escaping attr
     *
     * @param string $text The text to localize
     *
     * @return void
     */
    public static function esc_attr_e($text)
    {
        esc_attr_e($text, DUP_PRO_Constants::PLUGIN_SLUG);
    }

    /**
     * Localize and return the current text as a variable
     *
     * @param string $text The text to localize
     *
     * @return string Returns the text as a localized variable
     */
    public static function __($text)
    {
        return __($text, DUP_PRO_Constants::PLUGIN_SLUG);
    }

    /**
     * Localize and echo the current text as a variable
     *
     * @param string $text The text to localize
     *
     * @return string Returns the text as a localized variable
     */
    public static function _e($text) // phpcs:ignore
    {
        return _e($text, DUP_PRO_Constants::PLUGIN_SLUG);
    }

    /**
     * Localize and return the current text as a variable with escaping
     *
     * @param string $text The text to localize
     *
     * @return string Returns the text as a localized variable
     */
    public static function esc_html__($text)
    {
        return esc_html__($text, DUP_PRO_Constants::PLUGIN_SLUG);
    }

    /**
     * Localize and return the current text as a variable with escaping attribute
     *
     * @param string $text The text to localize
     *
     * @return string Returns the text as a localized variable
     */
    public static function esc_attr__($text)
    {
        return esc_html__($text, DUP_PRO_Constants::PLUGIN_SLUG);
    }

    /**
     * Display human readable byte sizes
     *
     * @param int $size The size in bytes
     *
     * @return string The size of bytes readable such as 100KB, 20MB, 1GB etc.
     */
    public static function byteSize($size)
    {
        try {
            $size  = (int) $size;
            $units = array('B', 'KB', 'MB', 'GB', 'TB');
            for ($i = 0; $size >= 1024 && $i < 4; $i++) {
                $size /= 1024;
            }
            return round($size, 2) . $units[$i];
        } catch (Exception $e) {
            return "n/a";
        }
    }

    /**
     * Return a string with the elapsed time in seconds
     *
     * @see getMicrotime()
     *
     * @param int|float $end   The final time in the sequence to measure
     * @param int|float $start The start time in the sequence to measure
     *
     * @return string   The time elapsed from $start to $end as 5.89 sec.
     */
    public static function elapsedTime($end, $start)
    {

        return sprintf('%.3f sec.', abs($end - $start));
    }

    /**
     * Return a float with the elapsed time in seconds
     *
     * @see getMicrotime(), elapsedTime()
     *
     * @param int|float $end   The final time in the sequence to measure
     * @param int|float $start The start time in the sequence to measure
     *
     * @return string   The time elapsed from $start to $end as 5.89
     */
    public static function elapsedTimeU($end, $start)
    {
        return sprintf('%.3f', abs($end - $start));
    }

    /**
     * Gets the contents of the file as an attachment type
     *
     * @param string $filepath    The full path the file to read
     * @param string $contentType The header content type to force when pushing the attachment
     *
     * @return void
     */
    public static function getDownloadAttachment($filepath, $contentType)
    {
        // Clean previous or after eny notice texts
        ob_clean();
        ob_start();
        $filename = basename($filepath);

        header("Content-Type: {$contentType}");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: public");

        if (readfile($filepath) === false) {
            throw new Exception(self::__("Couldn't read {$filepath}"));
        }
        ob_end_flush();
    }

    /**
     * Return the path of an executable program
     *
     * @param string $exeFilename A file name or path to a file name of the executable
     *
     * @return string|null Returns the full path of the executable or null if not found
     */
    public static function getExeFilepath($exeFilename)
    {
        $filepath = null;

        if (!Shell::test()) {
            return null;
        }

        $shellOutput = Shell::runCommand("hash $exeFilename 2>&1", Shell::AVAILABLE_COMMANDS);
        if ($shellOutput !== false && $shellOutput->isEmpty()) {
            $filepath = $exeFilename;
        } else {
            $possible_paths = array(
                "/usr/bin/$exeFilename",
                "/opt/local/bin/$exeFilename"
            );

            foreach ($possible_paths as $path) {
                if (@file_exists($path)) {
                    $filepath = $path;
                    break;
                }
            }
        }

        return $filepath;
    }

    /**
     * Get current microtime as a float.  Method is used for simple profiling
     *
     * @see elapsedTime
     *
     * @return float  A float in the form "msec sec", where sec is the number of seconds since the Unix epoch
     */
    public static function getMicrotime()
    {
        return microtime(true);
    }

    /**
     * Gets an SQL lock request
     *
     * @see releaseSqlLock()
     *
     * @return bool Returns true if an SQL lock request was successful
     */
    public static function getSqlLock($lock_name = 'duplicator_pro_lock')
    {
        global $wpdb;

        $query_string = "select GET_LOCK('{$lock_name}', 0)";

        $ret_val = $wpdb->get_var($query_string);

        if ($ret_val == 0) {
            DUP_PRO_Log::trace("Mysql lock {$lock_name} denied");
            return false;
        } elseif ($ret_val == null) {
            DUP_PRO_Log::trace("Error retrieving mysql lock {$lock_name}");
            return false;
        }

        DUP_PRO_Log::trace("Mysql lock {$lock_name} acquired");
        return true;
    }

    /**
     * Gets an SQL lock request
     *
     * @see releaseSqlLock()
     *
     * @return bool    Returns true if an SQL lock request was successful
     */
    public static function isSqlLockLocked($lock_name = 'duplicator_pro_lock')
    {
        global $wpdb;

        $query_string = "select IS_FREE_LOCK('{$lock_name}')";

        $ret_val = $wpdb->get_var($query_string);

        if ($ret_val == 0) {
            DUP_PRO_Log::trace("MySQL lock {$lock_name} is in use");
            return true;
        } elseif ($ret_val == null) {
            DUP_PRO_Log::trace("Error retrieving mysql lock {$lock_name}");
            return false;
        } else {
            DUP_PRO_Log::trace("MySQL lock {$lock_name} is free");
            return false;
        }
    }

    /**
     * Verifies that a correct security nonce was used. If correct nonce is not used, It will cause to die
     *
     * A nonce is valid for 24 hours (by default).
     *
     * @param string     $nonce  Nonce value that was used for verification, usually via a form field.
     * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
     *
     * @return void
     */
    public static function verifyNonce($nonce, $action)
    {
        if (!wp_verify_nonce($nonce, $action)) {
            die('Security issue');
        }
    }

    /**
     * Does the current user have the capability
     * Dies if user doesn't have the correct capability
     *
     * @return void
     */
    public static function checkAjax()
    {
        if (!wp_doing_ajax()) {
            $errorMsg = DUP_PRO_U::esc_html__('You do not have called from AJAX to access this page.');
            DUP_PRO_Log::trace($errorMsg);
            error_log($errorMsg);
            wp_die($errorMsg);
        }
    }

    /**
     * Creates the snapshot directory if it doesn't already exists
     *
     * @return void
     */
    public static function initStorageDirectory($skipIfExists = false)
    {
        $path_ssdir = SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH);
        if (file_exists($path_ssdir) && $skipIfExists) {
            return;
        }
        $path_ssdir_tmp        = SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP);
        $path_ssdir_tmp_import = SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP_IMPORT);
        $path_plugin           = SnapIO::safePath(DUPLICATOR____PATH);
        $path_import           = SnapIO::safePath(DUPLICATOR_PRO_PATH_IMPORTS);

        //--------------------------------
        //CHMOD DIRECTORY ACCESS
        //wordpress root directory
        SnapIO::chmod(duplicator_pro_get_home_path(), 'u+rwx');

        //snapshot directory
        wp_mkdir_p($path_ssdir);
        SnapIO::chmod($path_ssdir, 'u+rwx');

        //snapshot tmp directory
        wp_mkdir_p($path_ssdir_tmp);
        SnapIO::chmod($path_ssdir_tmp, 'u+rwx');

        wp_mkdir_p($path_ssdir_tmp_import);
        SnapIO::chmod($path_ssdir_tmp_import, 'u+rwx');

        wp_mkdir_p($path_import);
        SnapIO::chmod($path_import, 'u+rwx');

        //plugins dir/files
        SnapIO::chmod($path_plugin . 'files', 'u+rwx');

        self::setupStorageDirHtaccess();
        self::setupStorageDirIndexFile();
        self::setupStorageDirRobotsFile();
        self::performHardenProcesses();
    }

    /**
     * Attempts to create a secure .htaccess file in the download directory
     *
     * @return void
     */
    protected static function setupStorageDirHtaccess()
    {
        try {
            $fileName = SnapIO::safePathTrailingslashit(DUPLICATOR_PRO_SSDIR_PATH) . '.htaccess';

            if (DUP_PRO_Global_Entity::getInstance()->storage_htaccess_off) {
                @unlink($fileName);
            } elseif (!file_exists($fileName)) {
                $fileContent = <<<FILECONTENT
# Duplicator config, In case of file downloading problem, you can disable/enable it in Settings/Sotrag plugin settings

Options -Indexes
<IfModule mod_headers.c>
    <FilesMatch "\.(daf)$">
        ForceType application/octet-stream
        Header set Content-Disposition attachment
    </FilesMatch>
</IfModule>
FILECONTENT;
                if (file_put_contents($fileName, $fileContent) === false) {
                    throw new Exception('Can\'t create ' . $fileName);
                }
            }
        } catch (Exception $ex) {
            DUP_PRO_Log::Trace("Unable create file htaccess {$fileName} msg:" . $ex->getMessage());
        }
    }

    /**
     * Attempts to create an index.php file in the backups directory
     *
     * @return void
     */
    protected static function setupStorageDirIndexFile()
    {
        try {
            $fileName = SnapIO::safePathTrailingslashit(DUPLICATOR_PRO_SSDIR_PATH) . 'index.php';
            if (!file_exists($fileName)) {
                $fileContent = <<<FILECONTENT
<?php
// silence;
FILECONTENT;
                if (file_put_contents($fileName, $fileContent) === false) {
                    throw new Exception('Can\'t create file ' . $fileName);
                }
            }
        } catch (Exception $ex) {
            DUP_PRO_Log::Trace("Unable create index.php {$fileName} msg:" . $ex->getMessage());
        }
    }

    /**
    * Attempts to create a robots.txt file in the backups directory
    * to prevent search engines
    *
    * @return void
    */
    protected static function setupStorageDirRobotsFile()
    {
        try {
            $fileName = SnapIO::safePathTrailingslashit(DUPLICATOR_PRO_SSDIR_PATH) . 'robots.txt';
            if (!file_exists($fileName)) {
                $fileContent = <<<FILECONTENT
User-agent: *
Disallow: /
FILECONTENT;
                if (file_put_contents($fileName, $fileContent) === false) {
                    throw new Exception('Can\'t create ' . $fileName);
                }
            }
        } catch (Exception $ex) {
            DUP_PRO_Log::Trace("Unable create robots.txt {$fileName} msg:" . $ex->getMessage());
        }
    }

    /**
    * Run various secure processes to harden the backups dir
    *
    * @return void
    */
    public static function performHardenProcesses()
    {
        $backupsDir = SnapIO::safePathTrailingslashit(DUPLICATOR_PRO_SSDIR_PATH);

        //Edge Case: Remove any installer dirs
        $dupInstallFolder = $backupsDir . "dup-installer";
        if (file_exists($dupInstallFolder)) {
            SnapIO::rrmdir($dupInstallFolder);
        }

        //Rename installer php files to .bak
        SnapIO::regexGlobCallback(
            $backupsDir,
            function ($path) {
                $parts   = pathinfo($path);
                $newPath = $parts['dirname'] . '/' . $parts['filename'] . DUP_PRO_Installer::INSTALLER_SERVER_EXTENSION;
                SnapIO::rename($path, $newPath);
            },
            array(
                'regexFile'     => '/^.+_installer.*\.php$/',
                'regexFolder'   => false,
                'recursive'     => true,
            )
        );
    }

    /**
     * Return true if curl lib exists
     *
     * @return bool
     */
    public static function isCurlExists()
    {
        return function_exists('curl_version');
    }

    /**
     * Checks if curl_multi_exec exists
     *
     * @return bool
     */
    public static function curlMultiEnabled()
    {
        if (!self::isCurlExists()) {
            return false;
        }

        return function_exists("curl_multi_exec");
    }

    /**
     * Rturn true if sql lock is set
     *
     * @param string $lock_name lock nam
     *
     * @return bool
     */
    public static function checkSqlLock($lock_name = 'duplicator_pro_lock')
    {
        global $wpdb;

        $query_string = "SELECT IS_USED_LOCK('{$lock_name}')";
        $ret_val      = $wpdb->get_var($query_string);

        return $ret_val > 0;
    }

    /**
     * Releases the SQL lock request
     *
     * @see getSqlLock()
     *
     * @return void
     */
    public static function releaseSqlLock($lock_name = 'duplicator_pro_lock')
    {
        global $wpdb;

        $query_string = "select RELEASE_LOCK('{$lock_name}')";
        $ret_val      = $wpdb->get_var($query_string);

        if ($ret_val == 0) {
            DUP_PRO_Log::trace("Failed releasing sql lock {$lock_name} because it wasn't established by this thread");
        } elseif ($ret_val == null) {
            DUP_PRO_Log::trace("Tried to release sql lock {$lock_name} but it didn't exist");
        } else {
            // Lock was released
            DUP_PRO_Log::trace("SQL lock {$lock_name} released");
        }
    }

    /**
     * Sets a value or returns a default
     *
     * @param mixed $val     The value to set
     * @param mixed $default The value to default to if the val is not set
     *
     * @return mixed  A value or a default
     */
    public static function setVal($val, $default = null)
    {
        return isset($val) ? $val : $default;
    }

    /**
     * Check is set and not empty, sets a value or returns a default
     *
     * @param mixed $val     The value to set
     * @param mixed $default The value to default to if the val is not set
     *
     * @return mixed  A value or a default
     */
    public static function isEmpty($val, $default = null)
    {
        return isset($val) && !empty($val) ? $val : $default;
    }

    /**
     * Returns the last N lines of a file. Simular to tail command
     *
     * @param string $filepath The full path to the file to be tailed
     * @param int    $lines    The number of lines to return with each tail call
     *
     * @return false|string The last N parts of the file, false on failure
     */
    public static function tailFile($filepath, $lines = 2)
    {
        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false) {
            return false;
        }

        // Sets buffer size
        $buffer = 256;

        // Jump to last character
        fseek($f, -1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") {
            $lines -= 1;
        }

        // Start reading
        $output = '';
        $chunk  = '';

        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);
            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);
            // Read a chunk and prepend it to our output
            $output = ($chunk  = fread($f, $seek)) . $output;
            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
        }

        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }
        fclose($f);
        return trim($output);
    }

    /**
     * Check given table is exist in real
     *
     * @param string $table string Table name
     *
     * @return bool
     */
    public static function isTableExists($table)
    {
        // It will clear the $GLOBALS['wpdb']->last_error var
        $GLOBALS['wpdb']->flush();
        $sql = "SELECT 1 FROM `" . esc_sql($table) . "` LIMIT 1;";
        $ret = $GLOBALS['wpdb']->get_var($sql);
        if (empty($GLOBALS['wpdb']->last_error)) {
            return true;
        }
        return false;
    }

    /**
     * Finds if its a valid executable or not
     *
     * @param string $cmd A non zero length executable path to find if that is executable or not.
     *
     * @return bool
     */
    public static function isExecutable($cmd)
    {
        if (strlen($cmd) == 0) {
            return false;
        }

        if (
            @is_executable($cmd)
            || !Shell::runCommand($cmd, Shell::AVAILABLE_COMMANDS)->isEmpty()
            || !Shell::runCommand($cmd . ' -?', Shell::AVAILABLE_COMMANDS)->isEmpty()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Look into string and try to fix its natural expected value type
     *
     * @param mixed $data Simple string
     *
     * @return mixed value with it's natural string type
     */
    public static function valType($data)
    {
        if (is_string($data)) {
            if (is_numeric($data)) {
                if ((int) $data == $data) {
                    return (int) $data;
                } elseif ((float) $data == $data) {
                    return (float) $data;
                }
            } elseif (in_array(strtolower($data), array('true', 'false'), true)) {
                return ($data == 'true');
            }
        } elseif (is_array($data)) {
            foreach ($data as $key => $str) {
                $data[$key] = DUP_PRO_U::valType($str);
            }
        }
        return $data;
    }

    /**
     * TODO: Migrate method over to SnapURL
     * Validate is SSL active
     *
     * @return boolean true/false
     */
    public static function is_ssl()
    {
        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            return true;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
            return true;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'https') {
            return true;
        }
        if (isset($_SERVER['HTTP_CF_VISITOR'])) {
            $visitor = json_decode($_SERVER['HTTP_CF_VISITOR']);
            if (is_object($visitor) && property_exists($visitor, 'scheme') && $visitor->scheme == 'https') {
                return true;
            }
        }
        return false;
    }

    /**
     * Check given var is curl resource or instance of CurlHandle or CurlMultiHandle
     *  It is used for check curl_init() return, because
     *      curl_init() returns resource in lower PHP version than 8.0
     *      curl_init() returns class instance in PHP version 8.0
     *  Ref. https://php.watch/versions/8.0/resource-CurlHandle
     *
     * @param $var resource|object var to check
     *
     * @return boolean
     */
    public static function isCurlResourceOrInstance($var)
    {
        // CurlHandle class instance return of curl_init() in php 8.0
        // CurlMultiHandle class instance return of curl_multi_init() in php 8.0

        if (is_resource($var) || ($var instanceof CurlHandle) || ($var instanceof CurlMultiHandle)) {
            return true;
        } else {
            return false;
        }
    }
}
