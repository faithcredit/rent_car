<?php

defined("ABSPATH") or die("");

/**
 * Test result object
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
class DUP_PRO_U_Test_result
{
    const TEST_FAIL    = -1;
    const TEST_PASS    = 0;
    const TEST_INFO    = 1;
    const TEST_WARNING = 2;
    const TEST_WAIT    = 3;

    public $pass      = self::TEST_FAIL;
    public $title     = '';
    public $desc      = '';
    public $htmlClass = '';

    /**
     *
     * @param int    $pass
     * @param string $title
     * @param string $desc
     */
    public function __construct($pass = self::TEST_FAIL, $title = '', $desc = '', $htmlClass = '')
    {
        $this->pass      = $pass;
        $this->title     = $title;
        $this->desc      = $desc;
        $this->htmlClass = $htmlClass;
    }

    /**
     *
     * @param string $title
     * @param string $content // html string
     */
    public function addDescSection($title, $content)
    {
        $this->desc .= self::getDescSection($title, $content);
    }

    /**
     * Return test desc html section
     *
     * @param string $title
     * @param string $content HTML string
     *
     * @return string HTML formatted
     */
    protected static function getDescSection($title, $content)
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

    public function getTestHtmlPassCheck()
    {
        return '<span class="' . $this->getTestPassClasses() . '">' . $this->getTestPassLabel() . '</span>';
    }

    public function getTestPassClasses()
    {
        $result = 'test-check ';

        switch ($this->pass) {
            case self::TEST_PASS:
                $result .= 'pass';
                break;
            case self::TEST_INFO:
                $result .= 'info';
                break;
            case self::TEST_WARNING:
                $result .= 'warning';
                break;
            case self::TEST_WAIT:
                $result .= 'wait';
                break;
            case self::TEST_FAIL:
            default:
                $result .= 'fail';
                break;
        }

        return $result;
    }

    public function getTestPassLabel()
    {
        $result = '';
        switch ($this->pass) {
            case self::TEST_PASS:
                $result .= 'Pass';
                break;
            case self::TEST_INFO:
                $result .= 'Info';
                break;
            case self::TEST_WARNING:
                $result .= 'Warn';
                break;
            case self::TEST_WAIT:
                $result .= '<i class="fas fa-circle-notch fa-spin"></i> Wait';
                break;
            case self::TEST_FAIL:
            default:
                $result .= 'Fail';
                break;
        }
        return $result;
    }
}