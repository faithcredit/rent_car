<?php

namespace Duplicator\Installer\REST;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapIO;
use Exception;
use VendorDuplicator\WpOrg\Requests\Requests;
use VendorDuplicator\WpOrg\Requests\Response;

class RESTPoints
{
    const DUPLICATOR_NAMESPACE = 'duplicator/v1/';

    /** @var string */
    private $nonce = '';
    /** @var string */
    private $basicAuthUser = "";
    /** @var string */
    private $basicAuthPassword = "";
    /** @var string */
    private $url = '';
    /** @var ?self */
    private static $instance = null;

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
        $paramsManager = PrmMng::getInstance();
        $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);

        if (is_array($overwriteData)) {
            if (
                isset($overwriteData['restUrl']) &&
                strlen($overwriteData['restUrl']) > 0 &&
                isset($overwriteData['restNonce']) &&
                strlen($overwriteData['restNonce']) > 0
            ) {
                $this->url   = SnapIO::untrailingslashit($overwriteData['restUrl']);
                $this->nonce = $overwriteData['restNonce'];
            }

            if (strlen($overwriteData['restAuthUser']) > 0) {
                $this->basicAuthUser     = $overwriteData['restAuthUser'];
                $this->basicAuthPassword = $overwriteData['restAuthPassword'];
            }
        }
    }

    /**
     * Check if REST is avaiable
     *
     * @param bool   $reset        re-check
     * @param string $errorMessage Error message
     *
     * @return bool
     */
    public function checkRest($reset = false, &$errorMessage = "")
    {
        static $success = null;
        if (is_null($success) || $reset) {
            try {
                $success = true;
                if (strlen($this->nonce) == 0) {
                    throw new Exception("Nonce is not set.");
                }

                if (strlen($testUrl  = $this->getRestUrl('versions')) === 0) {
                    throw new Exception("Couldn't get REST API backed URL to do tests. REST API URL was empty.");
                }

                $response = Requests::get($testUrl, array(), $this->getRequestAuthOptions());
                if ($response->success == false) {
                    Log::info(Log::v2str($response));
                    throw new Exception("REST API request on $testUrl failed");
                }

                if (($result = json_decode($response->body, true)) === null) {
                    throw new Exception("Can't decode json.");
                }

                if (!isset($result["dup"])) {
                    Log::info('RESPONSE BODY ' . Log::v2str($response->body));
                    throw new Exception("Did not receive the expected result.");
                }
            } catch (Exception $ex) {
                $success      = false;
                $errorMessage = $ex->getMessage();
                Log::info("FAILED REST API CHECK. MESSAGE: " . $ex->getMessage());
            }
        }
        return $success;
    }

    /**
     * Return wp and dup version
     *
     * @return false|string[] false on failure
     */
    public function getVersions()
    {
        $response = Requests::get($this->getRestUrl('versions'), array(), $this->getRequestAuthOptions());
        if (!$response->success) {
            return false;
        }

        if (($result = json_decode($response->body)) === null) {
            return false;
        }

        return $result;
    }

    /**
     * Create new subsites
     *
     * @param string $data         data
     * @param int    $numSubisites subsites number
     * @param string $errorMessage essromessage
     *
     * @return false|array<string, mixed> return subistes info or false on failure
     */
    public function subsiteActions($data, $numSubisites, &$errorMessage = '')
    {
        if (Log::isLevel(Log::LV_DETAILED)) {
            Log::info('SUBSITE ACTION CALL NUM SUBISTES ' . $numSubisites . ' DATA: ' . $data);
        }

        $options = $this->getRequestAuthOptions();

        // ten seconds foreach subsite
        $options['timeout'] = 10 * max(1, $numSubisites);

        /** @var Response */
        $response = Requests::post(
            $this->getRestUrl('multisite/subsite/actions'),
            array(),
            array(
                'data'   => $data
            ),
            $options
        );

        if (($result = json_decode($response->body, true)) === null) {
            Log::info('REST CALL: can\'t decode json ' . $response->body);
            $errorMessage = 'REST CALL: can\'t decode json response';
            return false;
        }

        if (!$response->success) {
            Log::info('REST CALL FAIL REPONSE: ' . Log::v2str($response));

            if (isset($result['message'])) {
                $errorMessage = $result['message'];
            } else {
                $errorMessage = 'REST call fail, error code: ' . $response->status_code;
            }
            return false;
        } elseif (Log::isLevel(Log::LV_DEBUG)) {
            Log::info('REST CALL REPONSE: ' . Log::v2str($response));
        }

        if (!$result['success']) {
            if (isset($result['message'])) {
                $errorMessage = $result['message'];
            } else {
                $errorMessage = 'REST call fail, invalid reponse values';
            }
            return false;
        }

        return $result['subsitesInfo'];
    }

    /**
     * Return request auth options
     *
     * @return array<string, mixed>
     */
    private function getRequestAuthOptions()
    {
        return array(
            'auth'   => new RESTAuth($this->nonce, $this->basicAuthUser, $this->basicAuthPassword),
            'verify' => false,
            'verifyname' => false
        );
    }

    /**
     * Return RST URL
     *
     * @param string $subPath sub path
     *
     * @return string
     */
    private function getRestUrl($subPath = '')
    {
        return $this->url ? $this->url . '/' . self::DUPLICATOR_NAMESPACE . $subPath : '';
    }
}
