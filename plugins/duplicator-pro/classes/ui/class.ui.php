<?php

defined("ABSPATH") or die("");
class DUP_PRO_UI
{
    public static function echoBoolean($val)
    {
        echo $val ? 'true' : 'false';
    }

    public static function echoChecked($val)
    {
        echo $val ? 'checked' : '';
    }

    public static function echoDisabled($val)
    {
        echo $val ? 'disabled' : '';
    }

    public static function echoSelected($val)
    {
        echo $val ? 'selected' : '';
    }

    public static function getSelected($val)
    {
        return ($val ? 'selected' : '');
    }
}
