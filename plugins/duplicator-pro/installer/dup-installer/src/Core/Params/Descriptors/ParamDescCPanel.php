<?php

/**
 * CPanel params descriptions
 *
 * @category  Duplicator
 * @package   Installer
 * @author    Snapcreek <admin@snapcreek.com>
 * @copyright 2011-2021  Snapcreek LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 */

namespace Duplicator\Installer\Core\Params\Descriptors;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Core\Params\Descriptors\ParamsDescriptors;
use Duplicator\Installer\Core\Params\Items\ParamItem;
use Duplicator\Installer\Core\Params\Items\ParamForm;
use Duplicator\Installer\Core\Params\Items\ParamFormPass;
use Duplicator\Installer\Core\Params\Items\ParamOption;
use Duplicator\Libs\Snap\SnapUtil;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescCPanel implements DescriptorInterface
{
    const INVALID_EMPTY = 'can\'t be empty';

    /**
     * Init params
     *
     * @param (ParamItem|ParamForm)[] $params params list
     *
     * @return void
     */
    public static function init(&$params)
    {
        $params[PrmMng::PARAM_CPNL_CAN_SELECTED] = new ParamItem(
            PrmMng::PARAM_CPNL_CAN_SELECTED,
            ParamItem::TYPE_BOOL,
            array(
                'default' => true
            )
        );

        $params[PrmMng::PARAM_CPNL_HOST] = new ParamForm(
            PrmMng::PARAM_CPNL_HOST,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'default'          => "https://" . parse_url(DUPX_ROOT_URL, PHP_URL_HOST) . ":2083",
                'sanitizeCallback' => array(SnapUtil::class, 'sanitizeNSCharsNewline'),
                'validateCallback' => array(__CLASS__, 'validateNoEmptyIfCpanel'),
                'invalidMessage'   => self::INVALID_EMPTY
            ),
            array(
                'label'          => 'CPanel host:',
                'wrapperClasses' => array('revalidate-on-change'),
                'attr'           => array(
                    'required'    => 'true',
                    'placeholder' => 'cPanel url'
                ),
                'subNote'        => '<span id="cpnl-host-warn">'
                . 'Caution: The cPanel host name and URL in the browser address bar do not match, '
                . 'in rare cases this may be intentional.'
                . 'Please be sure this is the correct server to avoid data loss.</span>',
                'postfix'        => array('type' => 'button', 'label' => 'get', 'btnAction' => 'DUPX.getcPanelURL(this);')
            )
        );

        $params[PrmMng::PARAM_CPNL_USER] = new ParamForm(
            PrmMng::PARAM_CPNL_USER,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'default'          => '',
                'sanitizeCallback' => array(SnapUtil::class, 'sanitizeNSCharsNewline'),
                'validateCallback' => array(__CLASS__, 'validateNoEmptyIfCpanel'),
                'invalidMessage'   => self::INVALID_EMPTY
            ),
            array(
                'label'          => 'CPanel username:',
                'wrapperClasses' => array('revalidate-on-change'),
                'attr'           => array(
                    'required'             => 'required',
                    'data-parsley-pattern' => '/^[\w.-~]+$/',
                    'placeholder'          => 'cPanel username'
                )
            )
        );

        $params[PrmMng::PARAM_CPNL_PASS] = new ParamFormPass(
            PrmMng::PARAM_CPNL_PASS,
            ParamFormPass::TYPE_STRING,
            ParamFormPass::FORM_TYPE_PWD_TOGGLE,
            array(
            'default'          => '',
            'sanitizeCallback' => array(SnapUtil::class, 'sanitizeNSCharsNewline'),
            'validateCallback' => array(__CLASS__, 'validateNoEmptyIfCpanel'),
            'invalidMessage'   => self::INVALID_EMPTY
            ),
            array(
            'label'          => 'CPanel password:',
            'wrapperClasses' => array('revalidate-on-change'),
            'attr'           => array(
                'required'    => 'true',
                'placeholder' => 'cPanel password'
            )
            )
        );

        /** @var ParamForm */
        $paramFormObj = $params[PrmMng::PARAM_DB_ACTION];
        $paramFormObj = $paramFormObj->getCopyWithNewName(
            PrmMng::PARAM_CPNL_DB_ACTION,
            array(),
            array(
                'status' => ParamForm::STATUS_DISABLED
            )
        );
        // force create database enable for cpanel
        $paramFormObj->setOptionStatus(0, ParamOption::OPT_ENABLED);
        $params[PrmMng::PARAM_CPNL_DB_ACTION] = $paramFormObj;


        $params[PrmMng::PARAM_CPNL_PREFIX] = new ParamForm(
            PrmMng::PARAM_CPNL_PREFIX,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_HIDDEN,
            array(
                'default'          => '',
                'sanitizeCallback' => array(SnapUtil::class, 'sanitizeNSCharsNewline')
            ),
            array(
                'label'            => 'Control panel prefix',
                'renderLabel'      => false
            )
        );


        /** @var ParamForm */
        $paramFormObj = $params[PrmMng::PARAM_DB_HOST];
        $paramFormObj = $paramFormObj->getCopyWithNewName(
            PrmMng::PARAM_CPNL_DB_HOST,
            array(
                'default'          => 'localhost',
                'sanitizeCallback' => array(SnapUtil::class, 'sanitizeNSCharsNewline'),
                'validateCallback' => array(__CLASS__, 'validateNoEmptyIfCpanel'),
                'invalidMessage'   => self::INVALID_EMPTY
            ),
            array(
                'status' => ParamForm::STATUS_DISABLED,
                'attr'   => array(
                    'required' => 'true'
                )
            )
        );

        $params[PrmMng::PARAM_CPNL_DB_HOST] = $paramFormObj;

        $params[PrmMng::PARAM_CPNL_DB_NAME_SEL] = new ParamForm(
            PrmMng::PARAM_CPNL_DB_NAME_SEL,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
            'default'          => null,
            'sanitizeCallback' => array(SnapUtil::class, 'sanitizeNSCharsNewline')
            ),
            array(
            'label'          => 'Database:',
            'wrapperClasses' => array('revalidate-on-change'),
            'status'         => ParamForm::STATUS_DISABLED,
            'attr'           => array(
                'required'             => 'true',
                'data-parsley-pattern' => '^((?!-- Select Database --).)*$'
            ),
            'subNote'        => '<span class="s2-warning-emptydb">'
            . 'Warning: The selected "Action" above will remove <u>all data</u> from this database!'
            . '</span>'
            )
        );

        /** @var ParamForm */
        $paramFormObj = $params[PrmMng::PARAM_DB_NAME];
        $paramFormObj = $paramFormObj->getCopyWithNewName(
            PrmMng::PARAM_CPNL_DB_NAME_TXT,
            array(
                'default'          => '',
                'sanitizeCallback' => array(SnapUtil::class, 'sanitizeNSCharsNewline'),
                'validateCallback' => function ($value, ParamItem $paramObj) {
                    $paramManager = PrmMng::getInstance();
                    if (
                        $paramManager->getValue(PrmMng::PARAM_DB_VIEW_MODE) === 'cpnl' &&
                        $paramManager->getValue(PrmMng::PARAM_CPNL_DB_ACTION) === \DUPX_DBInstall::DBACTION_CREATE
                    ) {
                        return ParamsDescriptors::validateNotEmpty($value, $paramObj);
                    }

                    return true;
                },
                'invalidMessage' => self::INVALID_EMPTY
            ),
            array(
                'label'   => 'Database:',
                'status'  => ParamForm::STATUS_DISABLED,
                'attr'    => array(
                    'required'                      => 'true',
                    'data-parsley-pattern'          => '/^[\w.-~]+$/',
                    'data-parsley-errors-container' => '#cpnl-dbname-txt-error'
                ),
                'subNote' => '<span id="cpnl-dbname-txt-error"></span>',
                'prefix'  => array('type' => 'label', 'label' => '', 'id' => 'cpnl-prefix-dbname')
            )
        );

        $params[PrmMng::PARAM_CPNL_DB_NAME_TXT] = $paramFormObj;

        $params[PrmMng::PARAM_CPNL_DB_USER_SEL] = new ParamForm(
            PrmMng::PARAM_CPNL_DB_USER_SEL,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'          => null,
                'sanitizeCallback' => array(SnapUtil::class, 'sanitizeNSCharsNewline')
            ),
            array(
                'label'          => 'User:',
                'wrapperClasses' => array('revalidate-on-change'),
                'status'         => ParamForm::STATUS_DISABLED,
                'attr'           => array(
                    'required'             => 'true',
                    'data-parsley-pattern' => '^((?!-- Select User --).)*$'
                )
            )
        );

        /** @var ParamForm */
        $paramFormObj = $params[PrmMng::PARAM_DB_USER];
        $paramFormObj = $paramFormObj->getCopyWithNewName(
            PrmMng::PARAM_CPNL_DB_USER_TXT,
            array(
                'default'          => '',
                'sanitizeCallback' => array(SnapUtil::class, 'sanitizeNSCharsNewline'),
                'validateCallback' => function ($value, ParamItem $paramObj) {
                    $paramManager = PrmMng::getInstance();
                    if (
                        $paramManager->getValue(PrmMng::PARAM_DB_VIEW_MODE) === 'cpnl' &&
                        $paramManager->getValue(PrmMng::PARAM_CPNL_DB_USER_CHK) === true
                    ) {
                        return ParamsDescriptors::validateNotEmpty($value, $paramObj);
                    }

                    return true;
                },
                'invalidMessage' => self::INVALID_EMPTY
            ),
            array(
                'label'   => 'User:',
                'status'  => ParamForm::STATUS_DISABLED,
                'attr'    => array(
                    'required'                      => 'true',
                    'data-parsley-pattern'          => '/^[a-zA-Z0-9-_]+$/',
                    'data-parsley-errors-container' => '#cpnl-dbuser-txt-error',
                    'data-parsley-cpnluser'         => "16"
                ),
                'subNote' => '<span id="cpnl-dbuser-txt-error"></span>',
                'prefix'  => array('type' => 'label', 'label' => '', 'id' => 'cpnl-prefix-dbuser')
            )
        );

        $params[PrmMng::PARAM_CPNL_DB_USER_TXT] = $paramFormObj;

        $params[PrmMng::PARAM_CPNL_DB_USER_CHK] = new ParamForm(
            PrmMng::PARAM_CPNL_DB_USER_CHK,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
            'default' => false
            ),
            array(
            'label'          => ' ',
            'wrapperClasses' => array('revalidate-on-change'),
            'checkboxLabel'  => 'Create New Database User'
            )
        );

        /** @var ParamForm */
        $paramFormObj = $params[PrmMng::PARAM_DB_PASS];
        $paramFormObj = $paramFormObj->getCopyWithNewName(
            PrmMng::PARAM_CPNL_DB_PASS,
            array(),
            array(
                'status' => ParamForm::STATUS_DISABLED,
                'attr'   => array(
                    'required' => 'true'
                )
            )
        );

        $params[PrmMng::PARAM_CPNL_DB_PASS] = $paramFormObj;

        $params[PrmMng::PARAM_CPNL_IGNORE_PREFIX] = new ParamForm(
            PrmMng::PARAM_CPNL_IGNORE_PREFIX,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => false
            ),
            array(
                'label'          => 'CPanel Prefix',
                'wrapperClasses' => array('revalidate-on-change'),
                'checkboxLabel'  => 'Ignore',
                'attr'           => array(
                    'onclick' => 'DUPX.cpnlPrefixIgnore();'
                )
            )
        );
    }

    /**
     * Validate function for cpanle params
     *
     * @param mixed     $value    input value
     * @param ParamItem $paramObj current param object
     *
     * @return boolean
     */
    public static function validateNoEmptyIfCpanel($value, ParamItem $paramObj)
    {
        if (PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_VIEW_MODE) !== 'cpnl') {
            return true;
        }
        return ParamsDescriptors::validateNotEmpty($value, $paramObj);
    }

    /**
     * Update params after overwrite logic
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function updateParamsAfterOverwrite($params)
    {
    }
}
