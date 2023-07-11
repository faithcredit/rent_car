<?php

/**
 * REST point for overwrite subsites
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\RESTPoints;

use DUP_PRO_Log;
use Duplicator\Core\CapMng;
use Duplicator\Installer\Core\Params\Models\SiteOwrMap;
use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapURL;
use Exception;
use WP_Error;
use WP_REST_Request;

class SubsiteActions extends \Duplicator\Core\REST\AbstractRESTPoint
{
    /**
     * return REST point route string
     *
     * @return string
     */
    protected function getRoute()
    {
        return '/multisite/subsite/actions';
    }

    /**
     * True if REST point is avaiable
     *
     * @return boolean
     */
    public function isEnable()
    {
        return is_multisite();
    }

    /**
     * avaiable methods
     *
     * @return string[]
     */
    public function getMethods()
    {
        return array('GET', 'POST');
    }

    /**
     * Current user permission check
     *
     * @param WP_REST_Request $request request data
     *
     * @return WP_Error|true
     */
    public function permission(WP_REST_Request $request)
    {
        if (!CapMng::can(CapMng::CAP_IMPORT, false) || !is_super_admin() || !check_ajax_referer('wp_rest', false, false)) {
            return new WP_Error('rest_forbidden', esc_html__('You cannot execute this action.'));
        }
        return true;
    }

    /**
     * REST point arguments
     *
     * @return array<string, mixed>
     */
    protected function getArgs()
    {
        return array(
            'data' => array(
                'required'          => true,
                'type'              => 'string',
                'description'       => 'Subsite overwrite mapping',
                'validate_callback' => function ($param, \WP_REST_Request $request, $key) {
                    $param = json_decode($param, true);
                    if (!is_array($param)) {
                        return false;
                    }

                    foreach ($param as $item) {
                        if (!isset($item['sourceId']) || !is_int($item['sourceId'])) {
                            return false;
                        }
                        if (!isset($item['targetId']) || !is_int($item['targetId'])) {
                            return false;
                        }
                        if (!isset($item['newSlug'])  || !is_string($item['newSlug'])) {
                            return false;
                        }
                        if (!isset($item['blogName'])  || !is_string($item['blogName'])) {
                            return false;
                        }
                        switch ($item['targetId']) {
                            case SiteOwrMap::NEW_SUBSITE_WITH_SLUG:
                                if (strlen($item['newSlug']) == 0) {
                                    return false;
                                }
                                break;
                            case SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN:
                                $parsedURL = SnapURL::parseUrl($item['newSlug']);
                                if (!is_array($parsedURL) || $parsedURL['host'] == false || $parsedURL['path'] == false) {
                                    return false;
                                }
                                break;
                            default:
                                if (
                                    $item['targetId'] <= SiteOwrMap::NEW_SUBSITE_NOT_VALID ||
                                    get_site($item['targetId']) == null
                                ) {
                                    return false;
                                }
                        }
                    }
                    return true;
                }
            )
        );
    }

    /**
     * REST poing logic
     *
     * @param \WP_REST_Request $request      REQUEST data
     * @param mixed[]          $responseBase response base data
     *
     * @return \WP_REST_Response
     */
    protected function respond(\WP_REST_Request $request, $responseBase)
    {
        $response                 = $responseBase;
        $response['subsitesInfo'] = array();

        if (!class_exists('WP_Network')) {
            throw new Exception('the current version of wordpress does not support this action.');
        }

        $data = json_decode($request->get_param('data'), true);
        if (!is_array($data)) {
            throw new Exception('Can\'t decode data');
        }

        DUP_PRO_Log::trace('PARAM DATA ' . SnapLog::v2str($data));
        $currentUserId = get_current_user_id();

        foreach ($data as $subsiteInfo) {
            if ($subsiteInfo['targetId'] > 0) {
                self::addUserAdminAtSubsite($subsiteInfo['targetId']);
                if (($info = \DUP_PRO_MU::getSubsiteInfoById($subsiteInfo['targetId'])) == false) {
                    throw new Exception('Already exists subsite errorL Can\'t read new subsite info ID: ' . $subsiteInfo['targetId']);
                }
            } else {
                $newBlogId = self::createNewSubsite($subsiteInfo['targetId'], $subsiteInfo['newSlug'], $subsiteInfo['blogName'], $currentUserId);
                if (($info = \DUP_PRO_MU::getSubsiteInfoById($newBlogId)) == false) {
                    throw new Exception('Create subsite error: Can\'t read new subsite info ID: ' . $newBlogId);
                }
            }

            $subsiteInfo['info']        = $info;
            $response['subsitesInfo'][] = $subsiteInfo;
        }

        $response['success'] = true;

        return new \WP_REST_Response($response, 200);
    }

    /**
     * Add current logged in user at subsite id
     *
     * @param int $blogId blog id
     *
     * @return bool
     */
    protected static function addUserAdminAtSubsite($blogId)
    {
        $userId = get_current_user_id();

        if (is_user_member_of_blog($userId, $blogId)) {
            return true;
        }

        $result = add_user_to_blog($blogId, $userId, 'administrator');
        if ($result instanceof WP_Error) {
            throw new \Exception($result->get_error_message());
        }

        return true;
    }

    /**
     * Create new subsite
     *
     * @param int    $type        Enum (SiteOwrMap::NEW_SUBSITE_WITH_SLUG, SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN)
     * @param string $newSlug     subsite slug
     * @param string $blogTitle   blog title
     * @param int    $adminUserId admin user
     *
     * @return int return new subsite id
     */
    protected static function createNewSubsite($type, $newSlug, $blogTitle, $adminUserId)
    {
        $networkId = function_exists('get_current_network_id') ? get_current_network_id() : 1;
        $wpNetwork = \WP_Network::get_instance($networkId);

        switch ($type) {
            case SiteOwrMap::NEW_SUBSITE_WITH_SLUG:
                if (defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL) {
                    $domain = $newSlug . '.' . SnapURL::wwwRemove($wpNetwork->domain);
                    $path   = $wpNetwork->path;
                } else {
                    $domain = $wpNetwork->domain;
                    $path   = trailingslashit($wpNetwork->path) . $newSlug;
                }
                break;
            case SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN:
                $parsedURL = SnapURL::parseUrl($newSlug);
                $domain    = $parsedURL['host'];
                $path      = trailingslashit($parsedURL['path']);
                break;
            default:
                throw new Exception('Invalid type [' . $type . '] of new subsite creation');
        }

        $newBlogId = wpmu_create_blog($domain, $path, $blogTitle, $adminUserId);

        if ($newBlogId instanceof WP_Error) {
            throw new Exception($newBlogId->get_error_message());
        }

        return $newBlogId;
    }
}
