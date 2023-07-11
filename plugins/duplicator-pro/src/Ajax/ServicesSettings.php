<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax;

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Core\CapMng;
use Duplicator\Libs\Snap\SnapUtil;

class ServicesSettings extends AbstractAjaxService
{
    const USERS_PAGE_SIZE =  10;
    /**
     * Init ajax calls
     *
     * @return void
     */
    public function init()
    {
        $this->addAjaxCall("wp_ajax_duplicator_settings_cap_users_list", "capUsersList");
    }

    /**
     * Return user list for capabilites select
     *
     * @return mixed[]
     */
    public static function capUsersListCallback()
    {
        $searchStr = SnapUtil::sanitizeNSChars($_POST['search']);
        $page      = SnapUtil::sanitizeIntInput(INPUT_POST, 'page', 1);

        $result = [
            'results' => [],
            'pagination' => [
                'more' => false
            ]
        ];

        if ($page == 1) {
            foreach (CapMng::getSelectableRoles() as $role => $roleName) {
                if (stripos($role, $searchStr) !== false) {
                    $result['results'][] = [
                        'id' => $role,
                        'text' => $roleName
                    ];
                }
            }
        }

        if (License::can(License::CAPABILITY_CAPABILITIES_MNG_PLUS)) {
            $args = array(
                'search'         => '*' . SnapUtil::sanitizeNSChars($_POST['search']) . '*',
                'search_columns' => array( 'user_login', 'user_email' ),
                'number' => self::USERS_PAGE_SIZE,
                'paged' => $page,
            );

            $users = get_users($args);
            foreach ($users as $user) {
                $result['results'][] = [
                    'id' => $user->ID,
                    'text' => $user->user_email
                ];
            }
            $args  = array(
                'search'         => '*' . SnapUtil::sanitizeNSChars($_POST['search']) . '*',
                'search_columns' => array( 'user_login', 'user_email' ),
                'number' => self::USERS_PAGE_SIZE,
                'paged' => $page + 1
            );
            $users = get_users($args);

            $result['pagination']['more'] = count($users) > 0;
        }

        return $result;
    }

    /**
     * Import upload action
     *
     * @return void
     */
    public function capUsersList()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'capUsersListCallback'),
            'duplicator_settings_cap_users_list',
            $_POST['nonce'],
            CapMng::CAP_SETTINGS
        );
    }
}
