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

class DUPX_Validation_test_cpnl_new_user extends DUPX_Validation_abstract_item
{
    private $user = null;

    protected function runTest()
    {
        if (
            DUPX_Validation_database_service::getInstance()->skipDatabaseTests() ||
            PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_VIEW_MODE) !== 'cpnl' ||
            PrmMng::getInstance()->getValue(PrmMng::PARAM_CPNL_DB_USER_CHK) != true
        ) {
            return self::LV_SKIP;
        }

        DUPX_Validation_database_service::getInstance()->setSkipOtherTests(true);
        if ((DUPX_Validation_database_service::getInstance()->cpnlCreateDbUser($this->user)) === false) {
            return self::LV_FAIL;
        } else {
            DUPX_Validation_database_service::getInstance()->setSkipOtherTests(false);
            return self::LV_PASS;
        }
    }

    public function getTitle()
    {
        return 'Create Database User';
    }

    protected function failContent()
    {
        return dupxTplRender('parts/validation/database-tests/cpnl-create-user', array(
            'isOk'        => false,
            'dbuser'      => PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_USER),
            'dbpass'      => PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_PASS),
            'errorMessage' => $this->user['status']
            ), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/cpnl-create-user', array(
            'isOk'        => true,
            'dbuser'      => PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_USER),
            'dbpass'      => '*****',
            'errorMessage' => ''
            ), false);
    }
}
