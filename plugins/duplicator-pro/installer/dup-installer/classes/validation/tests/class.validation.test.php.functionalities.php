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
use Duplicator\Libs\Snap\FunctionalityCheck;

class DUPX_Validation_test_php_functionalities extends DUPX_Validation_abstract_item
{
    /** @var FunctionalityCheck[] */
    protected $functionalities = array();

    /**
     * Class contructor
     */
    public function __construct($category = '')
    {
        parent::__construct($category);
        $this->functionalities = self::getFunctionalitiesCheckList();
    }

    protected function runTest()
    {
        if (FunctionalityCheck::checkList($this->functionalities)) {
            return self::LV_PASS;
        } elseif (FunctionalityCheck::checkList($this->functionalities, true)) {
            return self::LV_HARD_WARNING;
        } else {
            return self::LV_FAIL;
        }
    }

    public function getTitle()
    {
        return 'PHP Functions and Classes';
    }

    protected function failContent()
    {
        return dupxTplRender('parts/validation/tests/php-functionalities', array(
            'functionalities' => $this->functionalities,
            'testResult' => $this->testResult
        ), false);
    }

    protected function passContent()
    {
        return $this->failContent();
    }

    protected function hwarnContent()
    {
        return $this->failContent();
    }

    /**
     * Get list of functionalities to check
     *
     * @return FunctionalityCheck[]
     */
    protected static function getFunctionalitiesCheckList()
    {
        $result = [];

        $archiveEngine = PrmMng::getInstance()->getValue(PrmMng::PARAM_ARCHIVE_ENGINE);

        if ($archiveEngine == DUP_PRO_Extraction::ENGINE_ZIP || $archiveEngine == DUP_PRO_Extraction::ENGINE_ZIP_CHUNK) {
            $result[] = new FunctionalityCheck(
                FunctionalityCheck::TYPE_CLASS,
                \ZipArchive::class,
                true,
                'https://www.php.net/manual/en/class.ziparchive.php',
                '<i style="font-size:12px">'
                    . '<a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-060-q" target="_blank">'
                    . 'Overview on how to enable ZipArchive</i></a>'
            );
        }

        $result[] = new FunctionalityCheck(
            FunctionalityCheck::TYPE_FUNCTION,
            'json_encode',
            true,
            'https://www.php.net/manual/en/function.json-encode.php'
        );

        $functionality = new FunctionalityCheck(
            FunctionalityCheck::TYPE_FUNCTION,
            'token_get_all',
            false,
            'https://www.php.net/manual/en/function.token-get-all',
            "Required for parsing the contents of the wp-config.php file. "
                . "If test failed, to avoid problems during the installation the handling of the wp-config.php "
                . "file has been disabled (the setting 'Wordpress wp-config.php' under Advanced Mode > Options > "
                . "Advanced > Configuration files has been set to 'Do nothing'.)"
        );
        $functionality->setFailCallback(function (FunctionalityCheck $item) {
            PrmMng::getInstance()->setValue(PrmMng::PARAM_WP_CONFIG, 'nothing');
            PrmMng::getInstance()->save();
        });
        $result[] = $functionality;

        $result[] = new FunctionalityCheck(
            FunctionalityCheck::TYPE_FUNCTION,
            'file_get_contents',
            true,
            'https://www.php.net/manual/en/function.file-get-contents.php'
        );
        $result[] = new FunctionalityCheck(
            FunctionalityCheck::TYPE_FUNCTION,
            'file_put_contents',
            true,
            'https://www.php.net/manual/en/function.file-put-contents.php'
        );
        $result[] = new FunctionalityCheck(
            FunctionalityCheck::TYPE_FUNCTION,
            'mb_strlen',
            true,
            'https://www.php.net/manual/en/mbstring.installation.php'
        );

        return $result;
    }
}
