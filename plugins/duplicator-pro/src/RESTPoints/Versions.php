<?php

/**
 * REST point to get duplicator and wordpress versions
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\RESTPoints;

use Duplicator\Core\CapMng;

class Versions extends \Duplicator\Core\REST\AbstractRESTPoint
{
    /**
     * return REST point route string
     *
     * @return string
     */
    protected function getRoute()
    {
        return '/versions';
    }

    /**
     *
     * @param \WP_REST_Request $request      request data
     * @param mixed[]          $responseBase response base data
     *
     * @return \WP_REST_Response
     */
    protected function respond(\WP_REST_Request $request, $responseBase)
    {
        global $wp_version;

        $response = $responseBase;

        $response['wp']  = $wp_version;
        $response['dup'] = DUPLICATOR_PRO_VERSION;

        return new \WP_REST_Response($response, 200);
    }

    /**
     *
     * @param \WP_REST_Request $request request data
     *
     * @return \WP_Error|boolean
     */
    public function permission(\WP_REST_Request $request)
    {
        if (!CapMng::can(CapMng::CAP_BASIC, false) || !check_ajax_referer('wp_rest', false, false)) {
            return new \WP_Error('rest_forbidden', esc_html__('You cannot execute this action.'));
        }
        return true;
    }
}
