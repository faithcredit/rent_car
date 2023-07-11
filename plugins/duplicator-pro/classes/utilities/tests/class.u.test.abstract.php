<?php

defined("ABSPATH") or die("");

/**
 * Test abstract class
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

require_once(DUPLICATOR____PATH . '/classes/utilities/tests/class.u.test.result.php');

abstract class DUP_PRO_U_Test_abstract
{
    /**
     * Return test desc html section
     *
     * @param string $title
     * @param string $content HTML string
     *
     * @return string HTMLl formatted
     */
    protected function getDescSection($title, $content)
    {
        ob_start();
        ?>
        <fieldset class="d_section" >
            <legend><?php echo esc_html($title) ?></legend>
            <?php echo $content; ?>
        </fieldset>
        <?php
        return ob_get_clean();
    }

    /**
     *
     * @param string $title
     * @param string $left
     * @param string $right
     * @param array  $args  see wp_text_diff args
     *
     * @return string
     */
    protected function getDescDiffSection($title, $left, $right, $args)
    {
        return $this->getDescSection($title, wp_text_diff($left, $right, $args));
    }

    abstract public function inizialize();

    abstract public function clear();

    /**
     * @param string $scope
     *
     * @return DUP_PRO_U_Test_result[]
     */
    abstract public function test($scope);
}