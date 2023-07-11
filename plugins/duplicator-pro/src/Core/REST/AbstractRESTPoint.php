<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\REST;

use DUP_PRO_Log;
use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapUtil;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Abstract REST point
 */
abstract class AbstractRESTPoint
{
    const REST_NAMESPACE = 'duplicator/v1';

    /** @var array<string, mixed> */
    protected $args = [];
    /** @var bool */
    protected $override = false;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->args['methods']             = $this->getMethods();
        $this->args['callback']            = array($this, 'callback');
        $this->args['permission_callback'] = array($this, 'permission');
        $this->args['args']                = $this->getArgs();
    }

    /**
     * Get current endpoint route
     *
     * @return string
     */
    abstract protected function getRoute();

    /**
     * Rest api permission callback
     *
     * @param \WP_REST_Request $request REST request
     *
     * @return boolean
     */
    abstract public function permission(\WP_REST_Request $request);

    /**
     * Return methods of current rest point
     *
     * @return string|string[]
     */
    protected function getMethods()
    {
        return 'GET';
    }

    /**
     * Return args of current rest point
     *
     * @return array<string, mixed>
     */
    protected function getArgs()
    {
        return array();
    }

    /**
     * Return true if current rest point is enable
     *
     * @return boolean
     */
    public function isEnable()
    {
        return true;
    }

    /**
     * Registers REST API route.
     *
     * @return bool True on success, false on error.
     */
    public function register()
    {
        if (!$this->isEnable()) {
            return true;
        }

        return register_rest_route(self::REST_NAMESPACE, $this->getRoute(), $this->args, $this->override);
    }

    /**
     * REST callback logic
     *
     * @param \WP_REST_Request $request REST request
     *
     * @return \WP_REST_Response|false rest response or false on failure
     */
    public function callback(\WP_REST_Request $request)
    {
        $invalidOutput = '';
        $exception     = null;
        $responseBase  = array(
            'success'     => false,
            'message'     => ''
        );
        $result        = false;
        ob_start();

        try {
            $result = call_user_func(array($this, 'respond'), $request, $responseBase);
        } catch (\Exception $e) {
            $exception = $e;
        } catch (\Error $e) {
            $exception = $e;
        }

        if (!is_null($exception)) {
            $response['success'] = false;
            $response['message'] = SnapLog::getTextException($exception);
            $result              = new \WP_REST_Response($response, 200);
        }

        $invalidOutput = SnapUtil::obCleanAll();
        ob_end_clean();
        if (strlen($invalidOutput) > 0) {
            DUP_PRO_Log::trace('REST CALL INVALID OUTPUT: ' . $invalidOutput);
        }

        return $result;
    }

    /**
     * REST endpoint logic
     *
     * @param WP_REST_Request      $request      REST request
     * @param array<string, mixed> $responseBase response base data
     *
     * @return WP_REST_Response
     */
    abstract protected function respond(WP_REST_Request $request, $responseBase);
}
