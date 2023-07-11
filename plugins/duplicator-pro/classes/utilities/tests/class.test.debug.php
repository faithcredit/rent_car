<?php

defined("ABSPATH") or die("");

/**
 * Test to debug test manager
 * Never used in production
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    DUP_PRO
 * @subpackage classes/utilities/test
 * @copyright  (c) 2017, Snapcreek LLC
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since      3.7.9
 */
class DUP_PRO_Test_debug extends DUP_PRO_U_Test_abstract
{
    public function clear()
    {
    }

    public function inizialize()
    {
        self::clear();
    }

    /**
     * @param string $scope
     *
     * @return DUP_PRO_U_Test_result[]
     */
    public function test($scope)
    {
        $result = array();

        $test = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_PASS, 'test pass title');
        $test->addDescSection('Test section', 'Test <b>content</b>');
        $result[] = $test;

        $test = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_FAIL, 'test fail title');
        $test->addDescSection('Test section', 'Test <b>content fail</b>');
        $result[] = $test;

        $test = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_INFO, 'test info title');
        $test->addDescSection('Test section', 'Test <b>content fail</b>');
        $result[] = $test;

        $test = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_WARNING, 'test warning title');
        $test->addDescSection('Test section', 'Test <b>content fail</b>');
        $result[] = $test;

        return $result;
    }
}
