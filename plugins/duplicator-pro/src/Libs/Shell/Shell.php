<?php

namespace Duplicator\Libs\Shell;

use Exception;

class Shell
{
    const AVAILABLE_COMMANDS = Shell::CMD_EXEC | Shell::CMD_POPEN | Shell::CMD_SHELL_EXEC;

    const CMD_SHELL_EXEC = 0b100;
    const CMD_POPEN      = 0b010;
    const CMD_EXEC       = 0b001;

    /**
     * Execute a command in shell
     *
     * @param string $command Shell command to be executed
     * @param int    $useCmd  bitmask, list of shell command can be used CMD_POPEN, CMD_EXEC, CMD_SHELL_EXEC
     *
     * @return false|ShellOutput ShellOutput Object or false on command failure
     * @throws Exception
     */
    public static function runCommand($command, $useCmd = self::CMD_POPEN | self::CMD_EXEC)
    {
        $shellFunction = self::getShellFunction($useCmd);
        $output        = null;
        $code          = null;
        // In case shell_exec is among the commands, you can never use the code to avoid collaterals effects, even if you are using other functions.
        $isCodeAvailable = (($useCmd & self::CMD_SHELL_EXEC) == 0);

        switch ($shellFunction) {
            case self::CMD_POPEN:
                if (($handler = popen($command, 'r')) === false) {
                    return false;
                }
                while (!feof($handler)) {
                    $output[] = fgets($handler);
                }
                $code = pclose($handler);
                break;
            case self::CMD_EXEC:
                if ((exec($command, $output, $code)) === false) {
                    return false;
                }
                break;
            case self::CMD_SHELL_EXEC:
                if (($output = shell_exec($command)) === false) {
                    return false;
                }
                $output = (string) $output;
                $code   = 0;
                break;
            default:
                return false;
        }

        return new ShellOutput($output, $code, $shellFunction, $isCodeAvailable);
    }

    /**
     * Gest list of avaiblescmd funcs
     *
     * @return int bit mask with CMD_POPEN, CMD_EXEC, CMD_SHELL_EXEC
     */
    private static function getAvaliableCmdFuncs()
    {
        static $availableFunctions = null;

        if (is_null($availableFunctions)) {
            if (self::hasDisabledFunctions(['escapeshellarg', 'escapeshellcmd', 'extension_loaded'])) {
                $availableFunctions = 0;
                return $availableFunctions;
            }

            if (!self::hasDisabledFunctions(['popen', 'pclose'])) {
                $availableFunctions = ($availableFunctions | self::CMD_POPEN);
            }

            if (!self::hasDisabledFunctions('exec')) {
                $availableFunctions = ($availableFunctions | self::CMD_EXEC);
            }

            if (!self::hasDisabledFunctions('shell_exec')) {
                $availableFunctions = ($availableFunctions | self::CMD_SHELL_EXEC);
            }
        }

        return $availableFunctions;
    }

    /**
     * Determination of available PHP Shell Functions
     *
     * @param int $useCmd bitmask, list of shell command can be used CMD_POPEN, CMD_EXEC, CMD_SHELL_EXEC
     *
     * @return int|false returns shell function or false on failure
     */
    private static function getShellFunction($useCmd)
    {
        if (self::CMD_SHELL_EXEC & $useCmd & self::getAvaliableCmdFuncs()) {
            return self::CMD_SHELL_EXEC;
        } elseif (self::CMD_POPEN & $useCmd & self::getAvaliableCmdFuncs()) {
            return self::CMD_POPEN;
        } elseif (self::CMD_EXEC & $useCmd & self::getAvaliableCmdFuncs()) {
            return self::CMD_EXEC;
        } else {
            return false;
        }
    }

    /**
     * Check if required functions are disabled disabled
     *
     * @param string|string[] $functions list of functions that might be disabled
     *
     * @return boolean return True if there is a disabled function or false if there is none
     */
    public static function hasDisabledFunctions($functions)
    {
        if (is_scalar($functions)) {
            $functions = [$functions];
        }
        if (array_intersect($functions, self::getDisalbedFunctions())) {
            return true;
        }
        foreach ($functions as $function) {
            if (!function_exists($function)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get list of disabled functions
     *
     * @return string[]
     */
    protected static function getDisalbedFunctions()
    {
        static $funcsList = null;
        if (is_null($funcsList)) {
            $funcsList = [];
            if (function_exists('ini_get')) {
                if (($ini = ini_get('disable_functions')) === false) {
                    $ini = '';
                }
                $funcsList = array_map('trim', explode(',', $ini));

                if (self::isSuhosinEnabled()) {
                    if (($ini = ini_get("suhosin.executor.func.blacklist")) === false) {
                        $ini = '';
                    }
                    $funcsList = array_merge($funcsList, array_map('trim', explode(',', $ini)));
                    $funcsList = array_values(array_unique($funcsList));
                }
            }
        }
        return $funcsList;
    }

    /**
     * Returns true if a test shell command is successful
     *
     * @param int $useCmd bitmask, list of shell command can be used CMD_POPEN, CMD_EXEC, CMD_SHELL_EXEC
     *
     * @return bool
     */
    public static function test($useCmd = self::CMD_POPEN | self::CMD_EXEC)
    {
        static $testsCache = [];

        if (!isset($testsCache[$useCmd])) {
            $result = false;
            if (self::getShellFunction($useCmd) === 0) {
                $result = false;
            } else {
                // Can we issue a simple echo command?
                if (($shellOutput = Shell::runCommand('echo test', $useCmd)) === false) {
                    $result = false;
                } else {
                    $result = (trim($shellOutput->getOutputAsString()) === 'test');
                }
            }

            $testsCache[$useCmd] = $result;
        }
        return $testsCache[$useCmd];
    }

    /**
     * Escape a string to be used as a shell argument with bypass support for Windows
     *
     *  NOTES:
     *      Provides a way to support shell args on Windows OS and allows %,! on Windows command line
     *      Safe if input is know such as a defined constant and not from user input escape shellarg
     *      on Windows with turn %,! into spaces
     *
     * @param string $string string to be escaped
     *
     * @return string
     */
    public static function escapeshellargWindowsSupport($string)
    {
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            if (strstr($string, '%') || strstr($string, '!')) {
                $result = '"' . str_replace('"', '', $string) . '"';
                return $result;
            }
        }
        return escapeshellarg($string);
    }

    /**
     * Get compression param
     *
     * @param boolean $isCompressed string to be escaped
     *
     * @return string
     */
    public static function getCompressionParam($isCompressed)
    {
        if ($isCompressed) {
            $parameter = '-6';
        } else {
            $parameter = '-0';
        }

        return $parameter;
    }

    /**
     * Check if Suhosin Extensions is enabled
     *
     * @return boolean
     */
    public static function isSuhosinEnabled()
    {
        return extension_loaded('suhosin');
    }
}
