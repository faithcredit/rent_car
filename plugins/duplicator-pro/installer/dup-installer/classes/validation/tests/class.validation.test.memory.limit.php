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

use Duplicator\Libs\Snap\SnapUtil;

class DUPX_Validation_test_memory_limit extends DUPX_Validation_abstract_item
{
    const MIN_MEMORY_LIMIT = '256M';

    /** @var int */
    private $memoryLimit = -1;

    protected function runTest()
    {
        if (($memoryLimit = @ini_get('memory_limit')) === false || strlen($memoryLimit) == 0) {
            return self::LV_SKIP;
        }

        $this->memoryLimit = is_numeric($memoryLimit) ? (int) $memoryLimit : SnapUtil::convertToBytes($memoryLimit);
        if ($this->memoryLimit < 0) {
            return self::LV_SKIP;
        }

        if ($this->memoryLimit >= SnapUtil::convertToBytes(self::MIN_MEMORY_LIMIT)) {
            return self::LV_GOOD;
        }

        return self::LV_SOFT_WARNING;
    }

    public function getTitle()
    {
        return 'PHP Memory Limit';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/memory-limit', array(
            'memoryLimit'      => $this->memoryLimit,
            'minMemoryLimit'   => self::MIN_MEMORY_LIMIT,
            'isOk'             => false
        ), false);
    }

    protected function goodContent()
    {
        return dupxTplRender('parts/validation/tests/memory-limit', array(
            'memoryLimit'      => DUPX_U::readableByteSize($this->memoryLimit),
            'minMemoryLimit'   => self::MIN_MEMORY_LIMIT,
            'isOk'             => true
        ), false);
    }
}
