<?php

/**
 * @package Duplicator\Installer
 */

namespace Duplicator\Installer\Bootstrap;

use Exception;

class LogHandler
{
    /** @var bool */
    private static $initialized = false;

    /** @var callable */
    private static $logCallback = null;

    /**
     * This function only initializes the error handler the first time it is called
     *
     * @param callable $logCallback log callback
     *
     * @return void
     */
    public static function initErrorHandler($logCallback)
    {
        if (!self::$initialized) {
            if (!is_callable($logCallback)) {
                throw new Exception('Log callback must be callable');
            }
            self::$logCallback = $logCallback;

            @set_error_handler(array(__CLASS__, 'error'));
            @register_shutdown_function(array(__CLASS__, 'shutdown'));
            self::$initialized = true;
        }
    }

    /**
     * Error handler
     *
     * @param integer $errno   Error level
     * @param string  $errstr  Error message
     * @param string  $errfile Error file
     * @param integer $errline Error line
     *
     * @return bool
     */
    public static function error($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
                $log_message = self::getMessage($errno, $errstr, $errfile, $errline);
                if (call_user_func(self::$logCallback, $log_message) === false) {
                    $log_message = "Can\'t wrinte logfile\n\n" . $log_message;
                }
                die('<pre>' . htmlspecialchars($log_message) . '</pre>');
            case E_NOTICE:
            case E_WARNING:
            default:
                $log_message = self::getMessage($errno, $errstr, $errfile, $errline);
                call_user_func(self::$logCallback, $log_message);
                break;
        }

        return true;
    }

    /**
     * Get message from error
     *
     * @param int    $errno   errno
     * @param string $errstr  message
     * @param string $errfile file
     * @param int    $errline line
     *
     * @return string
     */
    private static function getMessage($errno, $errstr, $errfile, $errline)
    {
        $result = '[PHP ERR]';
        switch ($errno) {
            case E_ERROR:
                $result .= '[FATAL]';
                break;
            case E_WARNING:
                $result .= '[WARN]';
                break;
            case E_NOTICE:
                $result .= '[NOTICE]';
                break;
            default:
                $result .= '[ISSUE]';
                break;
        }
        $result .= ' MSG:';
        $result .= $errstr;
        $result .= ' [CODE:' . $errno . '|FILE:' . $errfile . '|LINE:' . $errline . ']';
        return $result;
    }

    /**
     * Shutdown handler
     *
     * @return void
     */
    public static function shutdown()
    {
        if (($error = error_get_last())) {
            LogHandler::error($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
}
