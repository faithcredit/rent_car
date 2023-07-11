<?php

defined("ABSPATH") or die("");

require_once(DUPLICATOR____PATH . '/classes/utilities/tests/class.u.test.abstract.php');
require_once(DUPLICATOR____PATH . '/classes/utilities/tests/class.u.test.result.php');

/**
 * Tests manager class
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
class DUP_PRO_U_Tests_manager
{
    /** @var array<string, array<DUP_PRO_U_Test_abstract>> */
    private $testList = array();
    /** @var ?self */
    private static $instance = null;

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     */
    private function __construct()
    {
    }

    /**
     *
     * @param DUP_PRO_U_Test_abstract $testObj
     * @param string                  $scope
     *
     * @return void
     */
    public function register(DUP_PRO_U_Test_abstract $testObj, $scope)
    {
        if (empty($scope)) {
            throw new Exception('Scope can\'t be emty');
        }

        $this->testList[$scope][] = $testObj;
    }

    /**
     *
     * @param string $scope if empty clear all
     *
     * @return void
     */
    public function clear($scope = '')
    {
        if (empty($scope)) {
            foreach (array_keys($this->testList) as $cScope) {
                $this->clear($cScope);
            }
        } else {
            foreach ($this->testList[$scope] as $test) {
                $test->clear();
            }
        }
    }

    /**
     *
     * @param string $scope if empty clear all
     *
     * @return void
     */
    public function inizialize($scope = '')
    {
        if (empty($scope)) {
            foreach (array_keys($this->testList) as $cScope) {
                $this->inizialize($cScope);
            }
        } else {
            foreach ($this->testList[$scope] as $test) {
                $test->inizialize();
            }
        }
    }

    /**
     * @param string $scope
     *
     * @return array<int, array<DUP_PRO_U_Test_result>> tests results list
     */
    public function test($scope)
    {
        if (!isset($this->testList[$scope])) {
            throw new Exception('Test scope don\'t exist');
        }

        $result = array();

        foreach ($this->testList[$scope] as $test) {
            $result[] = $test->test($scope);
        }

        return $result;
    }
}
