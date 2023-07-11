<?php

defined("ABSPATH") or die("");
require_once(DUPLICATOR____PATH . '/classes/utilities/class.u.php');
//Enum used to define the various test statues
final class DUP_PRO_CTRL_Status
{
    const ERROR     = -2;
    const FAILED    = -1;
    const UNDEFINED = 0;
    const SUCCESS   = 1;
}

/**
 * Base class for all controllers
 *
 * @package    Duplicator
 * @subpackage classes/ctrls
 */
class DUP_PRO_CTRL_Base
{
    //Represents the name of the Nonce Action
    public $Action;
//The return type valiad options: PHP, JSON-AJAX, JSON
    public $returnType = 'JSON-AJAX';
    public function setResponseType($type)
    {
        $opts = array('PHP', 'JSON-AJAX', 'JSON');
        if (!in_array($type, $opts)) {
            throw new Exception('The $type param must be one of the following: ' . implode(',', $opts) . ' for the following function [' . __FUNCTION__ . ']');
        }
        $this->returnType = $type;
    }

    //Merges $_POST params with custom parameters.
    public function postParamMerge($params = array())
    {
        $params = is_array($params) ? $params : array();
        return array_merge($_POST, $params);
    }

    //Merges $_GET params with custom parameters.
    public function getParamMerge($params)
    {
        $params = is_array($params) ? $params : array();
        return array_merge($_GET, $params);
    }
}

/**
 * A class structure used to report on controller methods
 *
 * @package    Duplicator
 * @subpackage classes/ctrls
 */
class DUP_PRO_CTRL_Report
{
    //Properties
    public $runTime;
    public $returnType;
    public $results;
    public $status;
}

/**
 * A class used format all controller responses in a consistent format.  Every controller response will
 * have a Report and Payload structure.  The Payload is an array of the result responses.  The Report is used to
 * report on the overall status of the controller method
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    Duplicator
 * @subpackage classes/ctrls
 * @copyright  (c) 2017, Snap Creek LLC
 */
class DUP_PRO_CTRL_Result
{
    //Properties
    public $report;
    public $payload;
    private $timeStart;
    private $timeEnd;
    private $CTRL;

    public function __construct(DUP_PRO_CTRL_Base $CTRL_OBJ)
    {
        $this->timeStart = $this->microtimeFloat();
        $this->CTRL      = $CTRL_OBJ;
    //Report Data
        $this->report             = new DUP_PRO_CTRL_Report();
        $this->report->returnType = $CTRL_OBJ->returnType;
    }

    /**
     * Used to process a controller request
     *
     * @param null|array|object $payload The response object that will be returned
     * @param int               $status  Enum $status The status of a response
     *
     * @return string|self Returns a PHP object or json encoded object
     */
    public function process($payload, $status = DUP_PRO_CTRL_Status::UNDEFINED)
    {
        if (is_array($this->payload)) {
            $this->payload[]       = $payload;
            $this->report->results = count($this->payload);
        } else {
            $this->payload         = $payload;
            $this->report->results = (is_array($payload)) ? count($payload) : 1;
        }

        $this->report->status = $status;
        $this->getProcessTime();
        switch ($this->CTRL->returnType) {
            case 'JSON':
                return json_encode($this);
            case 'PHP':
                return $this;
            default:
                if (!headers_sent()) {
                    if ($status === DUP_PRO_CTRL_Status::ERROR) {
                        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                        die();
                    } else {
                        header('Content-Type: application/json');
                    }
                }
                die(json_encode($this));
        }
    }

    /**
     * Used to process an error response
     *
     * @param object $exception The PHP exception object
     *
     * @return void
     */
    public function processError($exception)
    {
        $payload            = array();
        $payload['Message'] = $exception->getMessage();
        $payload['File']    = $exception->getFile();
        $payload['Line']    = $exception->getLine();
        $payload['Trace']   = $exception->getTraceAsString();
        $this->process($payload, DUP_PRO_CTRL_Status::ERROR);
        die(json_encode($this));
    }

    private function getProcessTime()
    {
        $this->timeEnd         = $this->microtimeFloat();
        $this->report->runTime = $this->timeEnd - $this->timeStart;
    }

    private function microtimeFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }
}
