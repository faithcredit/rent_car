<?php

defined('ABSPATH') or die("");
interface DUP_PRO_iChecker
{
    public function check();
    public function getErrors();
    public function getHelperMessages();
}
