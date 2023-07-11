<?php

defined("ABSPATH") or die("");

use Duplicator\Libs\Snap\SnapOS;
use Duplicator\Libs\Shell\Shell;

class DUP_PRO_PHP_Log
{
    /**
     * GET ERROR LOG DIRECT PATH
     *
     * @param ?string $custom Custom path
     * @param bool    $unsafe If is true, function only check is file exists but not chmod and type
     *
     * @return false|string Return path or false on fail
     */
    public static function get_path($custom = null, $unsafe = false)
    {
        // Find custom path
        if (!empty($custom)) {
            if ($unsafe === true && file_exists($custom) && is_file($custom)) {
                return $custom;
            } elseif (is_file($custom) && is_readable($custom)) {
                return $custom;
            } else {
                return false;
            }
        }

        $path = self::find_path($unsafe);
        if ($path !== false) {
            return strtr($path, array(
                '\\' => '/',
                '//' => '/'
            ));
        }

        return false;
    }

    /**
     * GET ERROR LOG DATA
     *
     * @param int    $limit       Number of lines
     * @param string $time_format Time format how you like to see in log
     *
     * @return false|array  Log or false on failure
     */
    public static function get_log($limit = 200, $time_format = "Y-m-d H:i:s")
    {
        return self::parse_log($limit, $time_format);
    }

    /**
     * GET FILENAME FROM PATH
     *
     * @param string $path
     *
     * @return false|string Filename or false on failure
     */
    public static function get_filename($path)
    {

        if ($path === false || !is_readable($path) || !is_file($path)) {
            return false;
        }
        return basename($path);
    }

    /**
     * CLEAR PHP ERROR LOG
     *
     * @return bool
     */
    public static function clear_log()
    {
        return self::clear_error_log();
    }

    /**
    * Parses the PHP error log to an array.
    *
    * @param int     $limit
    * @param string  $time_format
    *
    * @return array|false return array log or false on failure
    */
    private static function parse_log($limit = 200, $time_format = "Y-m-d H:i:s")
    {
        $parsedLogs = array();
        $path       = self::find_path();
        $contents   = null;
        if ($path === false) {
            return false;
        }

        try {
            // Good old shell can solve this in less of second
            if (!SnapOS::isWindows()) {
                $shellOutput = Shell::runCommand("tail -{$limit} {$path}", Shell::AVAILABLE_COMMANDS);
                if ($shellOutput !== false) {
                    $contents = $shellOutput->getOutputAsString();
                }
            }

            // Shell fail on various cases, now we are ready to rock
            if (empty($contents)) {
                // If "SplFileObject" is available use it
                if (class_exists('SplFileObject') && class_exists('LimitIterator')) {
                    $file = new SplFileObject($path, 'rb');
                    $file->seek(PHP_INT_MAX);
                    $last_line = $file->key();
                    if ($last_line > 0) {
                        ++$limit;
                        $lines    = new LimitIterator($file, (($last_line - $limit) <= 0 ? 0 : $last_line - $limit), ($last_line > 1 ? ($last_line + 1) : $last_line));
                        $contents = iterator_to_array($lines);
                        $contents = join("\n", $contents);
                    }
                } else {
                    // Or good old fashion fopen()
                    $contents = null;
                    $limit    = ($limit + 2);
                    $lines    = array();
                    if ($fp = fopen($path, "rb")) {
                        while (!feof($fp)) {
                                    $line = fgets($fp, 4096);
                                    array_push($lines, $line);
                            if (count($lines) > $limit) {
                                array_shift($lines);
                            }
                        }
                        fclose($fp);
                        if (count($lines) > 0) {
                            foreach ($lines as $a => $line) {
                                $contents .= "\n{$line}";
                            }
                        }
                    } else {
                        return false;
                    }
                }
            }
        } catch (Exception $exc) {
            return false;
        }

        // Little magic with \n
        $contents = trim($contents, "\n");
        $contents = preg_replace("/\n{2,}/U", "\n", $contents);
        $lines    = explode("\n", $contents);
        /* DEBUG */
        if (isset($_GET['debug_log']) && $_GET['debug_log'] == 'true') {
            echo '<pre style="background:#fff; padding:10px;word-break: break-all;display:block;white-space: pre-line;">', var_export($contents, true),'</pre>';
        }

        // Must clean memory ASAP
        unset($contents);
        // Let's arse things on the right way
        $currentLineNumberCount = count($lines);
        for ($currentLineNumber = 0; $currentLineNumber < $currentLineNumberCount; ++$currentLineNumber) {
            $currentLine = trim($lines[$currentLineNumber]);
            // Normal error log line starts with the date & time in []
            if ('[' === substr($currentLine, 0, 1)) {
                // Get the datetime when the error occurred
                $dateArr = array();
                preg_match('~^\[(.*?)\]~', $currentLine, $dateArr);
                $currentLine   = str_replace($dateArr[0], '', $currentLine);
                $currentLine   = trim($currentLine);
                $dateArr       = explode(' ', $dateArr[1]);
                $errorDateTime = date($time_format, strtotime($dateArr[0] . ' ' . $dateArr[1]));
                // Get the type of the error
                $errorType = null;
                if (false !== strpos($currentLine, 'PHP Warning')) {
                    $currentLine = str_replace('PHP Warning:', '', $currentLine);
                    $currentLine = trim($currentLine);
                    $errorType   = 'WARNING';
                } elseif (false !== strpos($currentLine, 'PHP Notice')) {
                    $currentLine = str_replace('PHP Notice:', '', $currentLine);
                    $currentLine = trim($currentLine);
                    $errorType   = 'NOTICE';
                } elseif (false !== strpos($currentLine, 'PHP Fatal error')) {
                    $currentLine = str_replace('PHP Fatal error:', '', $currentLine);
                    $currentLine = trim($currentLine);
                    $errorType   = 'FATAL';
                } elseif (false !== strpos($currentLine, 'PHP Parse error')) {
                    $currentLine = str_replace('PHP Parse error:', '', $currentLine);
                    $currentLine = trim($currentLine);
                    $errorType   = 'SYNTAX';
                } elseif (false !== strpos($currentLine, 'PHP Exception')) {
                    $currentLine = str_replace('PHP Exception:', '', $currentLine);
                    $currentLine = trim($currentLine);
                    $errorType   = 'EXCEPTION';
                }

                if (false !== strpos($currentLine, ' on line ')) {
                    $errorLine   = explode(' on line ', $currentLine);
                    $errorLine   = trim($errorLine[1]);
                    $currentLine = str_replace(' on line ' . $errorLine, '', $currentLine);
                } else {
                    $errorLine   = substr($currentLine, strrpos($currentLine, ':') + 1);
                    $currentLine = str_replace(':' . $errorLine, '', $currentLine);
                }

                $errorFile   = explode(' in ', $currentLine);
                $errorFile   = (isset($errorFile[1]) ? trim($errorFile[1]) : '');
                $currentLine = str_replace(' in ' . $errorFile, '', $currentLine);
                // The message of the error
                $errorMessage = trim($currentLine);
                $parsedLogs[] = array(
                    'dateTime'   => $errorDateTime,
                    'type'       => $errorType,
                    'file'       => $errorFile,
                    'line'       => (int)$errorLine,
                    'message'    => $errorMessage,
                    'stackTrace' => array()
                );
            } elseif ('Stack trace:' === $currentLine) {
                // Stack trace beginning line
                $stackTraceLineNumber = 0;
                for (++$currentLineNumber; $currentLineNumber < $currentLineNumberCount; ++$currentLineNumber) {
                    $currentLine = null;
                    if (isset($lines[$currentLineNumber])) {
                        $currentLine = trim($lines[$currentLineNumber]);
                    }
                    // If the current line is a stack trace line
                    if ('#' === substr($currentLine, 0, 1)) {
                        $parsedLogsKeys                                 = array_keys($parsedLogs);
                        $parsedLogsLastKey                              = end($parsedLogsKeys);
                        $currentLine                                    = str_replace('#' . $stackTraceLineNumber, '', $currentLine);
                        $parsedLogs[$parsedLogsLastKey]['stackTrace'][] = trim($currentLine);
                        ++$stackTraceLineNumber;
                    } else {
                        // If the current line is the last stack trace ('thrown in...')
                        break;
                    }
                }
            }
        }

        rsort($parsedLogs);
        return $parsedLogs;
    }

    /**
     * Clear error log file
     *
     * @return bool true on success or false on failure.
     */
    private static function clear_error_log()
    {
        // Get error log
        $path = self::find_path();
        // Get log file name
        $filename = self::get_filename($path);
        // Reutn error
        if (!$filename) {
            return false;
        }

        $dir = dirname($path);
        $dir = strtr($dir, array(
            '\\' => '/',
            '//' => '/'
        ));
        unlink($path);
        return touch($dir . '/' . $filename);
    }

    /**
     * Find PHP error log file
     *
     * @param bool $unsafe
     *
     * @return false|string return path or false on failure
     */
    private static function find_path($unsafe = false)
    {

        // If ini_get is enabled find path
        if (function_exists('ini_get')) {
            $path = ini_get('error_log');
            if ($unsafe === true && file_exists($path) && is_file($path)) {
                return $path;
            }

            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        // HACK: If ini_get is disabled, try to parse php.ini
        if (function_exists('php_ini_loaded_file') && function_exists('parse_ini_file')) {
            $ini_path = php_ini_loaded_file();
            if (is_file($ini_path) && is_readable($ini_path)) {
                $parse_ini = parse_ini_file($ini_path);
                if ($unsafe === true && isset($parse_ini["error_log"]) && file_exists($parse_ini["error_log"]) && is_file($parse_ini["error_log"])) {
                    return $parse_ini["error_log"];
                }

                if (isset($parse_ini["error_log"]) && file_exists($parse_ini["error_log"]) && is_readable($parse_ini["error_log"])) {
                    return $parse_ini["error_log"];
                }
            }
        }

        // PHP.ini fail or not contain informations what we need. Let's look on few places
        $possible_places    = array(

            // Look into root
            duplicator_pro_get_home_path(),

            // Look out of root
            dirname(duplicator_pro_get_home_path()),

            //Other places
            '/etc/httpd/logs',
            '/var/log/apache2',
            '/var/log/httpd',
            '/var/log',
            '/var/www/html',
            '/var/www',

            // Some wierd cases
            duplicator_pro_get_home_path() . '/logs',
            duplicator_pro_get_home_path() . '/log',
            dirname(duplicator_pro_get_home_path()) . '/logs',
            dirname(duplicator_pro_get_home_path()) . '/log',
            '/etc/httpd/log',
            '/var/logs/apache2',
            '/var/logs/httpd',
            '/var/logs',
            '/var/www/html/logs',
            '/var/www/html/log',
            '/var/www/logs',
            '/var/www/log',
        );
        $possible_filenames = array(
            'error.log',
            'error_log',
            'php_error',
            'php5-fpm.log',
            'error_log.txt',
            'php_error.txt',
        );
        foreach ($possible_filenames as $filename) {
            foreach ($possible_places as $possibility) {
                $possibility = $possibility . '/' . $filename;
                if ($unsafe === true && file_exists($possibility) && is_file($possibility)) {
                    return $possibility;
                } elseif (is_file($possibility) && is_readable($possibility)) {
                    return $possibility;
                }
            }
        }

        return false;
    }
}
