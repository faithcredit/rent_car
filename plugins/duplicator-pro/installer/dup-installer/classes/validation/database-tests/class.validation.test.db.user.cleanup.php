<?php

/**
 * Validation object
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

class DUPX_Validation_test_db_user_cleanup extends DUPX_Validation_abstract_item
{
    protected $errorMessage = '';

    protected function runTest()
    {
        if (DUPX_Validation_database_service::getInstance()->isUserCreated() === false) {
            return self::LV_SKIP;
        }

        if (DUPX_Validation_database_service::getInstance()->cleanUpUser($this->errorMessage)) {
            return self::LV_PASS;
        } else {
            return self::LV_HARD_WARNING;
        }
    }

    public function getTitle()
    {
        return 'User created cleanup';
    }

    protected function hwarnContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-user-cleanup', array(
            'isOk'         => false,
            'dbuser'       => PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_USER),
            'errorMessage' => $this->errorMessage
            ), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-user-cleanup', array(
            'isOk'         => true,
            'dbuser'       => PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_USER),
            'errorMessage' => $this->errorMessage
            ), false);
    }
}
