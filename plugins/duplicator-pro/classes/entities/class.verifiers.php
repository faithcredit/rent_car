<?php

/**
 * @copyright 2016 Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
class DUP_PRO_Verifier_Base
{
    protected $error_text = '';
    public function __construct($error_text = '')
    {
        $this->error_text = $error_text;
    }

    // Returns an error string if succeeded or empty string if failed.
    public function Verify($value)
    {
        return "";
    }
}

/**
 * @copyright 2016 Snap Creek LLC
 */
class DUP_PRO_Required_Verifier extends DUP_PRO_Verifier_Base
{
    public function __construct($error_text = '')
    {
        parent::__construct($error_text);
    }

    // Returns an error string if succeeded or empty string if failed.
    public function Verify($value)
    {
        if (trim($value) == '') {
            return $this->error_text;
        } else {
            return '';
        }
    }
}
