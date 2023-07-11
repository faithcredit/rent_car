<?php

defined('ABSPATH') or die("");
require_once(DUPLICATOR____PATH . '/classes/environment/class.db.checker.php');
class DUP_PRO_Environment_Checker implements DUP_PRO_iChecker
{
    /** @var DUP_PRO_DB_Checker[] */
    public $checkers = [];
    /** @var mixed[] */
    public $errors = [];
    /** @var mixed[] */
    public $helper_messages = [];

    public function __construct()
    {
        $this->checkers['db']  = new DUP_PRO_DB_Checker();
        $this->errors          = array();
        $this->helper_messages = array();
    }

    public function check()
    {
        $total_result = true;
        foreach ($this->checkers as $id => $checker) {
            $passed       = $checker->check();
            $total_result = $total_result && $passed;
            if (!$passed) {
                $this->errors[$id]          = $checker->getErrors();
                $this->helper_messages[$id] = $checker->getHelperMessages();
            }
        }

        return $total_result;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getHelperMessages()
    {
        return $this->helper_messages;
    }
}
