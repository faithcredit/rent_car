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

class DUPX_Validation_test_db_supported_engine extends DUPX_Validation_abstract_item
{
    /** @var string */
    protected $errorMessage = '';
    /** @var string[] */
    protected $invalidEngines = [];
    /** @var string */
    protected $defaultEngine = "";
    /** @var bool */
    protected $engineListRead = false;

    protected function runTest()
    {
        if (DUPX_Validation_database_service::getInstance()->skipDatabaseTests()) {
            return self::LV_SKIP;
        }

        try {
            $this->invalidEngines = DUPX_ArchiveConfig::getInstance()->invalidEngines();
            $this->defaultEngine  = DUPX_DB_Functions::getInstance()->getDefaultEngine();
            $this->engineListRead = true;

            if (empty($this->invalidEngines)) {
                return self::LV_PASS;
            } else {
                return self::LV_HARD_WARNING;
            }
        } catch (Exception $e) {
            $this->errorMessage   = $e->getMessage();
            $this->engineListRead = false;
            return self::LV_HARD_WARNING;
        }
    }

    public function getTitle()
    {
        return 'Database Engine Support';
    }

    protected function failContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-supported-engine', array(
            'testResult'     => $this->testResult,
            'invalidEngines' => $this->invalidEngines,
            'defaultEngine'  => $this->defaultEngine,
            'errorMessage'   => $this->errorMessage,
            'engineListRead' => $this->engineListRead
        ), false);
    }

    protected function hwarnContent()
    {
        return $this->failContent();
    }

    protected function passContent()
    {
        return $this->failContent();
    }
}
