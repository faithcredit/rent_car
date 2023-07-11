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

use Duplicator\Installer\Utils\Log\Log;

/**
 * Validation abstract item
 */
abstract class DUPX_Validation_abstract_item
{
    const LV_FAIL         = 0;
    const LV_HARD_WARNING = 1;
    const LV_SOFT_WARNING = 2;
    const LV_GOOD         = 3;
    const LV_PASS         = 4;
    const LV_SKIP         = 1000;

    /** @var string */
    protected $category = '';
    /** @var ?int Enum LV_*  */
    protected $testResult = null;

    /**
     * Class Constructor
     *
     * @param string $category
     */
    public function __construct($category = '')
    {
        $this->category = $category;
    }

    /**
     *
     * @param bool $reset
     *
     * @return int test result level
     */
    public function test($reset = false)
    {
        if ($reset || is_null($this->testResult)) {
            try {
                Log::resetTime(Log::LV_DEBUG);
                Log::info('START TEST "' . $this->getTitle() . '" [CLASS: ' . get_called_class() . ']');
                $this->testResult = $this->runTest();
            } catch (Exception $e) {
                Log::logException($e, Log::LV_DEFAULT, '      TEST "' . $this->getTitle() . '" EXCEPTION:');
                $this->testResult = self::LV_FAIL;
            } catch (Error $e) {
                Log::logException($e, Log::LV_DEFAULT, '      TEST "' . $this->getTitle() . '" EXCEPTION:');
                $this->testResult = self::LV_FAIL;
            }
            Log::logTime('TEST "' . $this->getTitle() . '" RESULT: ' . $this->resultString() . "\n", Log::LV_DEFAULT, false);
        }

        return $this->testResult;
    }


    /**
     * Run the test
     *
     * @return int Enum LV_* result
     */
    abstract protected function runTest();

    /**
     * If true the test will be displayed in the validation section else will be skipped
     *
     * @return bool
     */
    public function display()
    {
        if ($this->testResult === self::LV_SKIP) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get test category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get test title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Test class ' . get_called_class();
    }

    /**
     * Get test content
     *
     * @return string
     */
    public function getContent()
    {
        try {
            switch ($this->test(false)) {
                case self::LV_SKIP:
                    return $this->skipContent();
                case self::LV_GOOD:
                    return $this->goodContent();
                case self::LV_PASS:
                    return $this->passContent();
                case self::LV_SOFT_WARNING:
                    return $this->swarnContent();
                case self::LV_HARD_WARNING:
                    return $this->hwarnContent();
                case self::LV_FAIL:
                default:
                    return $this->failContent();
            }
        } catch (Exception $e) {
            Log::logException($e, Log::LV_DEFAULT, 'VALIDATION DISPLAY CONTENT ' . get_called_class() . ' RESULT: ' . $this->resultString() . ' EXCEPTION:');
            return 'DISPLAY CONTENT PROBLEM <br>'
                . 'MESSAGE: ' . $e->getMessage() . '<br>'
                . 'TRACE:'
                . '<pre>' . $e->getTraceAsString() . '</pre>';
        } catch (Error $e) {
            Log::logException($e, Log::LV_DEFAULT, 'VALIDATION DISPLAY CONTENT ' . get_called_class() . ' ERROR:');
            return 'DISPLAY CONTENT PROBLEM <br>'
                . 'MESSAGE: ' . $e->getMessage() . '<br>'
                . 'TRACE:'
                . '<pre>' . $e->getTraceAsString() . '</pre>';
        }
    }


    /**
     * Get badge class for result level
     *
     * @return string
     */
    public function getBadgeClass()
    {
        return self::resultLevelToBadgeClass($this->test(false));
    }

    /**
     * Get unique selector
     *
     * @return string
     */
    public function getUniqueSelector()
    {
        return strtolower(str_replace("_", "-", get_called_class()));
    }

    /**
     * Get level label
     *
     * @return string
     */
    public function resultString()
    {
        return self::resultLevelToString($this->test(false));
    }


    /**
     * Level to string
     *
     * @param int $level Enum LV_*
     *
     * @return string
     */
    public static function resultLevelToString($level)
    {
        switch ($level) {
            case self::LV_SKIP:
                return 'skip';
            case self::LV_GOOD:
                return 'good';
            case self::LV_PASS:
                return 'passed';
            case self::LV_SOFT_WARNING:
                return 'soft warning';
            case self::LV_HARD_WARNING:
                return 'hard warning';
            case self::LV_FAIL:
            default:
                return 'failed';
        }
    }


    /**
     * Get badge class for result level
     *
     * @param int $level Enum LV_*
     *
     * @return string
     */
    public static function resultLevelToBadgeClass($level)
    {
        switch ($level) {
            case self::LV_SKIP:
                return '';
            case self::LV_GOOD:
                return 'good';
            case self::LV_PASS:
                return 'pass';
            case self::LV_SOFT_WARNING:
                return 'warn';
            case self::LV_HARD_WARNING:
                return 'hwarn';
            case self::LV_FAIL:
            default:
                return 'fail';
        }
    }

    /**
     * Return content for test status: fail warning
     *
     * @return string
     */
    protected function failContent()
    {
        return 'test result: fail';
    }

    /**
     * Return content for test status: hard warning
     *
     * @return string
     */
    protected function hwarnContent()
    {
        return 'test result: hard warning';
    }

    /**
     * Return content for test status: soft warning
     *
     * @return string
     */
    protected function swarnContent()
    {
        return 'test result: soft warning';
    }

    /**
     * Return content for test status: good
     *
     * @return string
     */
    protected function goodContent()
    {
        return 'test result: good';
    }

    /**
     * Return content for test status: pass
     *
     * @return string
     */
    protected function passContent()
    {
        return 'test result: pass';
    }

    /**
     * Return content for test status: skip
     *
     * @return string
     */
    protected function skipContent()
    {
        return 'test result: skipped';
    }
}
