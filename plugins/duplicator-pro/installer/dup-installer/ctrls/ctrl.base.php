<?php

/**
 * Base controller class for installer controllers
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\CTRL\Base
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Security;
use Duplicator\Installer\Core\Bootstrap;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;

require_once(DUPX_INIT . '/ctrls/classes/class.ctrl.s0.php');
require_once(DUPX_INIT . '/ctrls/classes/class.ctrl.s4.php');

class DUPX_CTRL
{
    const ACTION_STEP_INIZIALIZED  = 'initialized';
    const ACTION_STEP_ON_VALIDATE  = 'on-validate';
    const ACTION_STEP_SET_TEMPLATE = 'settpm';

    /** @var ?self */
    protected static $instance = null;
    /** @var bool|string */
    protected $pageView = false;
    /** @var mixed[] */
    protected $extraParamsPage = array();

    /**
     * Get instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
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
     * Main controller
     *
     * @return void
     */
    public function mainController()
    {
        $paramsManager = PrmMng::getInstance();
        $ctrlAction    = $paramsManager->getValue(PrmMng::PARAM_CTRL_ACTION);
        $stepAction    = $paramsManager->getValue(PrmMng::PARAM_STEP_ACTION);
        Log::info("\n" . '---------------', Log::LV_DETAILED);
        Log::info('CONTROLLER ACTION: ' . Log::v2str($ctrlAction), Log::LV_DETAILED);
        if (!empty($stepAction)) {
            Log::info('STEP ACTION: ' . Log::v2str($stepAction));
        }
        Log::info('---------------' . "\n", Log::LV_DETAILED);
        DUPX_Template::getInstance()->setTemplate(PrmMng::getInstance()->getValue(PrmMng::PARAM_TEMPLATE));
        if (Bootstrap::isInit()) {
            if (!DUPX_Ctrl_Params::setParamsStep0()) {
                Log::info('PARAMS AREN\'T VALID', Log::LV_DETAILED);
                Log::error('PARAMS AREN\'T VALID');
            }
            DUPX_Ctrl_S0::stepHeaderLog();
        }

        if (
            $ctrlAction !== 'help' &&
            Security::getInstance()->getSecurityType() != Security::SECURITY_NONE
        ) {
            Log::info('SECURE CHECK -> GO TO SECURE PAGE');
            $this->pageView = 'secure';
            return;
        }

        switch ($ctrlAction) {
            case "ctrl-step1":
                if ($stepAction === DUPX_CTRL::ACTION_STEP_SET_TEMPLATE) {
                    $paramsManager->setValueFromInput(PrmMng::PARAM_TEMPLATE);
                    Log::info('NEW TEMPLATE:' . $paramsManager->getValue(PrmMng::PARAM_TEMPLATE));
                    $paramsManager->save();
                    DUPX_Template::getInstance()->setTemplate($paramsManager->getValue(PrmMng::PARAM_TEMPLATE));
                }
                $this->pageView = 'step1';
                break;
            case "ctrl-step2":
                $this->pageView = 'step2';
                break;
            case "ctrl-step3":
                $this->pageView = 'step3';
                break;
            case "ctrl-step4":
                DUPX_Ctrl_S4::updateFinalReport();
                $this->pageView = 'step4';
                break;
            case "help":
                $this->pageView = 'help';
                break;
            default:
                Log::error('No valid action request ' . $ctrlAction);
        }
    }

    /**
     * Set exception page
     *
     * @return void
     */
    public function setExceptionPage(Exception $e)
    {
        Log::info("--------------------------------------");
        Log::info('EXCEPTION: ' . $e->getMessage());
        Log::info('TRACE:');
        Log::info($e->getTraceAsString());
        Log::info("--------------------------------------");
        $this->extraParamsPage['exception'] = $e;
        $this->pageView                     = 'exception';
    }

    /**
     * Render page
     *
     * @return string
     */
    public function renderPage()
    {
        Log::logTime('RENDER PAGE ' . Log::v2str($this->pageView), Log::LV_DETAILED);
        $echo                                 = false;
        $paramsManager                        = PrmMng::getInstance();
        $this->extraParamsPage['bodyClasses'] = 'template_' . $paramsManager->getValue(PrmMng::PARAM_TEMPLATE);
        if ($paramsManager->getValue(PrmMng::PARAM_DEBUG_PARAMS)) {
            $this->extraParamsPage['bodyClasses'] .= ' debug-params';
        }
        $result = '';

        switch ($this->pageView) {
            case 'secure':
                $result = dupxTplRender('page-secure', $this->extraParamsPage, $echo);
                break;
            case 'step1':
                $result = dupxTplRender('page-step1', $this->extraParamsPage, $echo);
                break;
            case 'step2':
                $result = dupxTplRender('page-step2', $this->extraParamsPage, $echo);
                break;
            case 'step3':
                $result = dupxTplRender('page-step3', $this->extraParamsPage, $echo);
                break;
            case 'step4':
                $result = dupxTplRender('page-step4', $this->extraParamsPage, $echo);
                DUPX_NOTICE_MANAGER::getInstance()->finalReportLog(
                    array('general', 'files', 'database', 'search_replace', 'plugins')
                );
                break;
            case 'exception':
                $result = dupxTplRender('page-exception', $this->extraParamsPage, $echo);
                break;
            case 'help':
                $result = dupxTplRender('page-help', $this->extraParamsPage, $echo);
                break;
            case false:
                // no page
                break;
            default:
                Log::error('No valid render page ' . Log::v2str($this->pageView));
        }
        Log::logTime('END RENDER PAGE');
        return self::renderPostProcessings($result);
    }

    /**
     * Apply post processing to string
     *
     * @param string $string string input
     *
     * @return string
     */
    public static function renderPostProcessings($string)
    {
        return str_replace(array(
            DUPX_Package::getArchiveFileHash(),
            DUPX_Package::getPackageHash()), '[HASH]', $string);
    }
}
