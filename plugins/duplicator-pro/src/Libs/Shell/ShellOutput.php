<?php

namespace Duplicator\Libs\Shell;

use Exception;

class ShellOutput
{
    /** @var int ENUM of shell command can be Shell::[CMD_POPEN,CMD_EXEC] */
    private $method = 0;
    /** @var string[] */
    private $output = [];
    /** @var int */
    private $code = -1;
    /** @var bool */
    private $isCodeAvaiable = true;

    /**
     * Initialise the Shell Output Response with real output
     *
     * @param null|string|string[] $output         Shell Output Lines
     * @param int                  $code           Shell Output return code
     * @param int                  $method         ENUM of shell command can be Shell::[CMD_POPEN,CMD_EXEC,CMD_SHELL_EXEC]
     * @param bool                 $isCodeAvaiable is false attempting to read the code generates an exception
     */
    public function __construct($output, $code, $method, $isCodeAvaiable = true)
    {
        if (is_scalar($output) || is_null($output)) {
            $output = (string) $output;
            if (strlen($output) == 0) {
                $output = [];
            } elseif (($output = preg_split("/(\r\n|\n|\r)/", (string) $output)) === false) {
                $output = [];
            }
        }

        if ($method == 0) {
            throw new Exception('Invalid method');
        }

        $this->output         = self::formatOutput($output);
        $this->code           = (int) $code;
        $this->method         = $method;
        $this->isCodeAvaiable = (bool) $isCodeAvaiable;
    }

    /**
     * Format the Shell Output
     *
     * @param string[] $output Initial Shell Output
     *
     * @return string[] return Array of formatted Shell Output Lines
     */
    private static function formatOutput($output)
    {
        foreach ($output as $key => $line) {
            $line = preg_replace('~\r\n?~', "\n", $line);
            if (strlen($line) == 0 || substr($line, -1) !== "\n") {
                $line .= "\n";
            }
            $output[$key] = $line;
        }
        return $output;
    }

    /**
     * Get complete Shell Output as a string
     *
     * @return string complete Shell output as a string
     */
    public function getOutputAsString()
    {
        return implode('', $this->output);
    }

    /**
     * Get complete Shell Output
     *
     * @return string[] complete Shell output as array lines
     */
    public function getArrayWithAllOutputLines()
    {
        return $this->output;
    }


    /**
     * Get complete Shell output return code
     *
     * @return integer shell output return code
     */
    public function getCode()
    {
        if (!$this->isCodeAvaiable) {
            throw new Exception('The shell command return code is not available.');
        }
        return $this->code;
    }

    /**
     * Get Shell PHP Function used for the Shell Output
     *
     * @return int ENUM of shell command can be Shell::[CMD_POPEN,CMD_EXEC,CMD_SHELL_EXEC]
     */
    public function getOutputMethod()
    {
        return $this->method;
    }

    /**
     * Get Shell PHP Function used for the Shell Output
     *
     * @return string Shell php function name
     */
    public function getOutputMethodName()
    {
        switch ($this->method) {
            case Shell::CMD_POPEN:
                return 'popen';
            case Shell::CMD_EXEC:
                return 'exec';
            case Shell::CMD_SHELL_EXEC:
                return 'shell_exec';
            default:
                return 'unknown';
        }
    }

    /**
     * Check if Shell Output response is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return (strlen(trim($this->getOutputAsString())) == 0);
    }
}
