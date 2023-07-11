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
class DUP_PRO_Test_package_build extends DUP_PRO_U_Test_abstract
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
        $result   = array();
        $test     = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_WAIT, 'Package initialized', '', 'test_check_pack_init no_display');
        $result[] = $test;
        $test     = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_WAIT, 'Package scan', '', 'test_check_pack_scan no_display');
        $result[] = $test;
        $test     = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_WAIT, 'Package build start', '', 'test_check_pack_start no_display');
        $result[] = $test;
        $test     = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_WAIT, 'Package db done', '', 'test_check_database no_display');
        $result[] = $test;
        $test     = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_WAIT, 'Package archive done', '', 'test_check_archive no_display');
        $result[] = $test;
        $test     = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_WAIT, 'Package storage done', '', 'test_check_storage no_display');
        $result[] = $test;
        $test     = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_WAIT, 'Package build completed', '', 'test_check_completed no_display');
        $result[] = $test;
        $test     = new DUP_PRO_U_Test_result(DUP_PRO_U_Test_result::TEST_WAIT, 'Package cleanup', '', 'test_check_clean no_display');
        $result[] = $test;
        return $result;
    }
}
