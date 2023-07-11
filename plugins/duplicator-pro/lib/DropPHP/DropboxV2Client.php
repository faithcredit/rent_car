<?php
defined("ABSPATH") or die("");
/**
 * Dropbox v2 API with wordpress http api
 * https://www.dropbox.com/developers/documentation/http/documentation
 *
 * http://www.upwork.com/fl/albertw6
 * 
 * 
 * @author     Albert Wang <cms90com@gmail.com>
 * @copyright  Albert Wang 2017
 * @version    1.0
 * @license    MIT
 *
 */

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapUtil;

if (!class_exists('DUP_PRO_DropboxV2Client_UploadInfo')) {

    class DUP_PRO_DropboxV2Client_UploadInfo
    {
        public $upload_id;
        public $next_offset;
        public $error_details = null;
        public $file_meta     = null;       // Is non null if upload complete

    }
}

if (!class_exists('DUP_PRO_DropboxV2Client')) {

    class DUP_PRO_DropboxV2Client
    {
        const API_URL         = "https://api.dropboxapi.com/2/";
        const API_CONTENT_URL = "https://content.dropboxapi.com/2/";
        const OAUTH2_URL = 'https://www.dropbox.com/oauth2/';
        const BUFFER_SIZE           = 4096;
        const MAX_UPLOAD_CHUNK_SIZE = 150000000; // 150MB
        const UPLOAD_CHUNK_SIZE     = 4000000; // 4MB
        const CONTENT_HASH_MISMATCH_TAG = "content_hash_mismatch";
        const LOG_PREFIX                = "DROPBOX ENDPOINT: ";

        private $appParams;
        private $consumerToken;
        private $requestToken;
        private $accessToken;
        private $v2AccessToken;
        private $locale;
        private $rootPath;
        private $useCurl;

        function __construct($app_params, $locale = "en", $use_curl = true)
        {
            $this->appParams = $app_params;
            if (empty($app_params['app_key'])) throw new DropboxException("App Key is empty!");

            $this->consumerToken = array('t' => $this->appParams['app_key'], 's' => $this->appParams['app_secret']);
            $this->locale        = $locale;
            $this->rootPath      = empty($app_params['app_full_access']) ? "sandbox" : "dropbox";

            $this->requestToken = null;
            $this->accessToken  = null;
            if (isset($this->appParams['v2_access_token'])) {
                $this->v2AccessToken = $this->appParams['v2_access_token'];
            }

            //$this->useCurl = function_exists('curl_init');
            $this->useCurl = true; // we don't use fopen any more $use_curl;

            if ($this->useCurl) {
                self::trace("Using cURL for Dropbox transfers");
            } else {
                self::trace("Using FOpen URL for Dropbox transfers");
            }
        }

        public function createAuthUrl()
        {
            return self::OAUTH2_URL.'authorize?client_id='.$this->appParams['app_key'].'&response_type=code';
        }

        /**
          return access_token or false
         */
        public function authenticate($auth_code)
        {
            /*
              https://www.dropbox.com/developers/documentation/http/documentation#oa2-token
             */
            $url      = self::OAUTH2_URL.'token';
            $args = array(
                'body' => array(
                        'client_id' => $this->appParams['app_key'],
                        'client_secret' => $this->appParams['app_secret'],
                        'code' => $auth_code,
                        'grant_type' => 'authorization_code',
                )
            );
            $args = $this->injectExtraReqArgs($args);

            $args['timeout'] = 30;
            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                DUP_PRO_Log::traceObject("Something wrong with when try to get v2_access_token with code", $response);
                return false;
            } else {
//                 {"state":"success","msg":{"headers":{},"body":"{\"access_token\": \"Vv4HPoqZMtYAAAAAAAB1jFMBk8fQK7MPYOU4cGsr8jOO4vjHAM8487E_MFkKCniX
// \", \"token_type\": \"bearer\", \"uid\": \"170627281\", \"account_id\": \"dbid:AAA_0dSBhRpPefHEH3w4EzjV-3T5IUkTPnI
// \"}","response":{"code":200,"message":"OK"},"cookies":[],"filename":null,"http_response":{"data":null
// ,"headers":null,"status":null}},"data":""}
                DUP_PRO_Log::traceObject("Got v2 access_token", $response);
                $ret_obj = json_decode($response['body']);
                if (isset($ret_obj->access_token)) {
                    return $ret_obj->access_token;
                } else {
                    return false;
                }
            }
        }

        /**
         * Sets a previously retrieved (and stored) access token.
         * 
         * @access public
         * @param string|object $token The Access Token
         * @return none
         */
        public function SetAccessToken($token)
        {
            if (empty($token['v2_access_token'])) throw new DropboxException('Passed invalid access token.');

            if (isset($token['v2_access_token'])) {
                $this->v2AccessToken = $token['v2_access_token'];
            }
        }

        /**
         * Checks if an access token has been set.
         * 
         * @access public
         * @return boolean Authorized or not
         */
        public function IsAuthorized()
        {
            if (empty($this->v2AccessToken)) return false;
            return true;
        }

        // ##################################################
        // API Functions
        /**
         * Retrieves information about the user's account.
         * 
         * @access public
         * @return object Account info object. See https://www.dropbox.com/developers/reference/api#account-info
         */
        public function GetAccountInfo()
        {
            /*
              {"account_id": "dbid:AAA_0dSBhRpPefHEH3w4EzjV-3T5IUkTPnI", "name": {"given_name": "nice", "surname": "cool", "familiar_name": "nice", "display_name": "nice cool", "abbreviated_name": "nc"}, "email": "opensoftcoder@gmail.com", "email_verified": true, "disabled": false, "country": "HK", "locale": "en", "referral_link": "https://db.tt/dQzXutEytF", "is_paired": false, "account_type": {".tag": "basic"}}
             */
            return $this->apiCall("users/get_current_account");
        }

        public function revokeToken()
        {
            /*
              https://www.dropbox.com/developers/documentation/http/documentation#auth-token-revoke
             */
            return $this->apiCall("auth/token/revoke");
        }

        /**
         * Get file list of a dropbox folder.
         * 
         * @access public
         * @param string|object $dropbox_path Dropbox path of the folder
         * @return array An array with metadata of files/folders keyed by paths 
         */
        public function GetFiles($dropbox_path = '', $recursive = false, $include_deleted = false)
        {
            /* :
              https://www.dropbox.com/developers/documentation/http/documentation#files-list_folder
             */
            $dropbox_path = $this->getFormatedPath($dropbox_path);

            $data         = $this->apiCall('files/list_folder', 'POST', array('path' => $dropbox_path));
            $tag          = '.tag';
            $returns      = array();
            foreach ($data->entries as $key => $entry) {
                if ('file' == $entry->$tag) {
                    $tmp_obj            = new stdClass();
                    $tmp_obj->file_path = $entry->path_display;
                    $tmp_obj->modified  = $entry->client_modified;
                    $returns[]          = $tmp_obj;
                }
            }
            return $returns;
        }

        /**
         * Get file or folder metadata
         * 
         * @access public
         * @param $dropbox_path string Dropbox path of the file or folder
         */
        public function GetMetadata($path, $include_deleted = false, $rev = null)
        {
            $path = $this->getFormatedPath($path);
            return $this->apiCall("files/get_metadata", "POST", compact('path'));
        }

        public function DownloadFile($dropbox_file, $dest_path = '', $rev = null, $progress_changed_callback = null)
        {
            $dropbox_file      = $this->getFormatedPath($dropbox_file);
            $params['api_arg'] = array('path' => $dropbox_file);
            $path              = 'files/download';
            $url               = self::API_CONTENT_URL.$path;
            $args              = array(
                'method' => 'POST',
                'timeout' => 180,
                'blocking' => true,
                'stream' => true,
                'filename' => $dest_path,
                'headers' => array(
                    'Authorization' => 'Bearer '.$this->v2AccessToken,
                    'Content-Type' => '',
                    'Dropbox-API-Arg' => json_encode($params['api_arg'])
                )
            );
            $args = $this->injectExtraReqArgs($args);
            $response          = wp_remote_request($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                DUP_PRO_Log::traceObject("Something wrong with apiCall on DownloadFile", $response);
                return false;
            } else {
                /*
                  dropbox-api-result: {"name": "test4.php", "path_lower": "/sandbox.cms90.com/test4/test4.php", "path_display": "/sandbox.cms90.com/test4/test4.php", "id": "id:Hln_x8l6_aAAAAAAAAAAEA", "client_modified": "2017-03-06T08:01:02Z", "server_modified": "2017-03-06T08:01:02Z", "rev": "1154ca0b05", "size": 1310, "content_hash": "c5e876cefb8b7176af487678cb4b8f64b80320ffc1a39889cd531b7cba656fd2"}
                 */
                return json_decode($response['headers']['dropbox-api-result']);
            }
        }

        /**
         * @param string $src_file                path to src file
         * @param string $dropbox_path           path in remote location
         * @param int    $upload_chunk_size      chunk size
         * @param int    $max_upload_time_in_sec time in seconds
         * @param int    $offset                 current offset
         * @param string $upload_id              upload if of session
         * @param int    $server_load_delay      server load delay in milliseconds
         * @return DUP_PRO_DropboxV2Client_UploadInfo
         * @throws Exception
         */
        public function upload_file_chunk($src_file, $dropbox_path = '', $upload_chunk_size = self::UPLOAD_CHUNK_SIZE, $max_upload_time_in_sec = 15, $offset = 0, $upload_id = null,
                                          $server_load_delay = 0)
        {
            self::trace("start");

            try {
                $dropbox_path = $this->getFormatedPath($dropbox_path);

                $dropbox_client_upload_info              = new DUP_PRO_DropboxV2Client_UploadInfo();
                $dropbox_client_upload_info->next_offset = $offset;
                $dropbox_client_upload_info->upload_id   = $upload_id;

                self::trace("offset coming in=$offset chunk size=$upload_chunk_size dropbox path=$dropbox_path");
                $file_size = filesize($src_file);

                if (($fh = fopen($src_file, 'rb')) === false) {
                    throw new Exception("problem opening $src_file");
                }

                SnapIO::fseek($fh, $offset);

                $start_time  = time();
                $time_passed = 0;

                self::trace("upload_id=$upload_id filesize = $file_size max_upload_time=$max_upload_time_in_sec");

                while (feof($fh) == false && (time() - $start_time) < $max_upload_time_in_sec) {

                    if($server_load_delay > 0) {
                        usleep($server_load_delay);
                    }

                    if(($content = fread($fh, $upload_chunk_size)) === false) {
                        throw new Exception('Error reading archive for Dropbox transmission');
                    }

                    if (empty($upload_id)) {
                        $upload_id = $this->sendFirstChunk($content);
                        $offset += strlen($content);
                        continue;
                    }

                    self::trace("append v2 with offset {$offset}");

                    $upload_return = $this->apiCall(
                        'files/upload_session/append_v2',
                        'POST',
                        array(
                            'api_arg' => array(
                                'cursor' => array(
                                    'session_id' => $upload_id,
                                    'offset' => $offset
                                ),
                                'close' => false,
                                'content_hash' => $this->getContentHash($content)
                            ),
                            'content' => $content
                        ),
                        true
                    );

                    DUP_PRO_Log::info("UPLOAD RETURN ". print_r($upload_return, true));

                    if (
                        is_object($upload_return) && property_exists($upload_return, "error") &&
                        property_exists($upload_return->error, "correct_offset")
                    ) {
                        $wrongOffset = $offset;
                        $offset = intval($upload_return->error->correct_offset, 10);
                        DUP_PRO_Log::info("Detected wrong offset in upload to Dropbox: $wrongOffset. "
                            . "Replaced it with correct offset: $offset");
                    } else if (
                        $upload_return === false ||
                        (is_object($upload_return) && property_exists($upload_return, "error_summary"))
                    ) {
                        throw new Exception("problem making call to upload_session/append_v2 for offset {$offset} - " . $upload_return->error_summary);
                    } else {
                        $offset += strlen($content);
                    }
                }

                self::trace("Time passed=$time_passed");
                if (@feof($fh)) {
                    self::trace("end of file");
                    $dropbox_client_upload_info->file_meta = $this->finishChunkedUpload($upload_id, $offset, $dropbox_path);
                }

                SnapIO::fclose($fh);
            } catch (Exception $ex) {
                $dropbox_client_upload_info->error_details = $ex->getMessage();
            }

            $dropbox_client_upload_info->upload_id   = $upload_id;
            $dropbox_client_upload_info->next_offset = $offset;

            return $dropbox_client_upload_info;
        }

        /**
         * @param string $content binary content to be sent
         * @throws Exception
         */
        private function sendFirstChunk($content)
        {
            $upload_return = $this->apiCall(
                'files/upload_session/start',
                'POST',
                array(
                    'api_arg' => array(
                        'close'        => false,
                        'content_hash' => $this->getContentHash($content)
                    ),
                    'content' => $content
                ),
                true
            );

            if ($upload_return === false || (is_object($upload_return) && property_exists($upload_return, "error_summary"))) {
                throw new \Exception("problem making call to upload_session/start - " . $upload_return->error_summary);
            }

            return $upload_return->session_id;
        }

        /**
         * @param string $upload_id    the upload id
         * @param int    $offset       offset
         * @param string $dropbox_path dropbox path
         * @return mixed
         * @throws Exception
         */
        private function finishChunkedUpload($upload_id, $offset, $dropbox_path)
        {
            $return = $this->apiCall(
                'files/upload_session/finish',
                'POST',
                array(
                    'api_arg' => array(
                        'cursor' => array(
                            'session_id' => $upload_id,
                            'offset' => $offset
                        ),
                        "commit" => array(
                            "path" => $dropbox_path,
                            "mode" => "add",
                            "autorename" => true,
                            "mute" => false
                        )
                    ),
                    'content' => null
                ),
                true
            );

            if (null == $return) {
                usleep(500);
                throw new Exception("**** Upload finish dropbox API call given null value! Something going wrong.");
            }

            return $return;
        }

        /**
         * Upload a file to dropbox
         * 
         * @access public
         * @param $src_file string Local file to upload
         * @param $dropbox_path string Dropbox path for destination
         * @return object Dropbox file metadata
         */
        public function UploadFile($src_file, $dropbox_path, $overwrite = true, $parent_rev = null)
        {
            // Delete any file that may be there ahead of time
            try {
                self::trace("Deleting dropbox files $dropbox_path");
                $this->Delete($dropbox_path);
            } catch (Exception $ex) {
                // Bury any exceptions
            }

            $dropbox_path = $this->getFormatedPath($dropbox_path);
            $file_size    = filesize($src_file);

            if ($file_size > self::MAX_UPLOAD_CHUNK_SIZE) {
                //chunk upload
            }

            /* upload a single file */
            $content = file_get_contents($src_file);
            if (strlen($content) == 0) throw new DropboxException("Could not read file $src_file or file is empty!");

            $params['api_arg'] = array('path' => $dropbox_path);
            // $params['content_size']=$file_size;
            $params['content'] = $content;
            return $this->apiCall('files/upload', 'POST', $params, true);
        }

        public function checkFileHash($file_metadata,$file)
        {
            $dropbox_hash = $file_metadata->content_hash;
            $local_hash = $this->getFileHash($file);
            self::trace("$local_hash <===> $dropbox_hash");
            return $local_hash == $dropbox_hash;
        }

        public function getFileHash($file)
        {
            $result = '';
            $sum_string = '';
            $chunksize =  4 * 1024 * 1024;
            $handle = fopen($file,"r");

            while (!feof($handle)) {
                $file_chunk = fread($handle,$chunksize);
                $sum_string .= hash("sha256",$file_chunk,true);
            }

            $result = hash("sha256",$sum_string);

            return $result;
        }

        public function getContentHash($content)
        {
            $len       = strlen($content);
            $chunkSize =  4 * 1024 * 1024;
            $sumString = '';
            for ($i=0; $i < $len/$chunkSize; $i++) {
                $chunk      = substr($content, $i*$chunkSize, $chunkSize);
                $sumString .= hash("sha256", $chunk, true);
            }

            return hash("sha256", $sumString);
        }



        /**
         * Creates a new folder in the DropBox
         * 
         * @access public
         * @param $path string The path to the new folder to create
         * @return object Dropbox folder metadata
         */
        function CreateFolder($path)
        {
            $path = $this->getFormatedPath($path);
            return $this->apiCall("files/create_folder", "POST", array('path' => $path, 'autorename' => false));
        }

        /**
         * Delete file or folder
         * 
         * @access public
         * @param $path mixed The path or metadata of the file/folder to be deleted.
         * @return object Dropbox metadata of deleted file or folder
         */
        function Delete($path)
        {
            $path = $this->getFormatedPath($path);
            return $this->apiCall("files/delete", "POST", array('path' => $path));
        }

        function getFormatedPath($path)
        {
            $path = trim($path, '/');
            $path = str_replace('//', '/', $path);
            $path = '/'.$path;
            return $path;
        }

        public function getQuota() {
            return $this->apiCall('users/get_space_usage'); 
        }

        private function apiCall($path, $method = "POST", $params = array(), $content_call = false)
        {
            if ($content_call) {
                $url  = self::API_CONTENT_URL.$path;
                $args = array(
                    'timeout' => 180,
                    'blocking' => true,
                    'method' => $method,
                    'headers' => array(
                        'Authorization' => 'Bearer '.$this->v2AccessToken,
                        'Content-Type' => 'application/octet-stream',
                        'Dropbox-API-Arg' => json_encode($params['api_arg'])
                    // 'Content-Length' => $params['content_size']
                    ),
                    'body' => $params['content']
                );
            } else {
                $url  = self::API_URL.$path;
                $body = 'null';
                if (!empty($params)) {
                    $body = json_encode($params);
                }
                $args = array(
                    'timeout' => 10,
                    'blocking' => true,
                    'method' => $method,
                    'headers' => array(
                        'Authorization' => 'Bearer '.$this->v2AccessToken,
                        'Content-Type' => 'application/json',
                    ),
                    'body' => $body
                );
            }

            $args = $this->injectExtraReqArgs($args);
            $response = wp_remote_request($url, $args);

            $params['content'] = '';
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                DUP_PRO_Log::traceObject("Something wrong with apiCall", $response);
                DUP_PRO_Log::traceObject("Params", $params);
                return false;
            } else {
                if (isset($response['body'])) {
                    $ret_obj = json_decode($response['body']);
                    return $ret_obj;
                } else {
                    return false;
                }
            }
        }

        private function injectExtraReqArgs($opts) {
            $global = DUP_PRO_Global_Entity::getInstance();
            $opts['sslverify'] = $global->ssl_disableverify ? false : true;
            if (!$global->ssl_useservercerts) {
                $opts['sslcertificates'] = DUPLICATOR_PRO_CERT_PATH;
            }
            return $opts;
        }

        /**
         * Make trace log with prefix
         *
         * @param string $str log message
         */
        private static function trace($str)
        {
            DUP_PRO_Log::trace(self::LOG_PREFIX . $str);
        }
    }
    if (!class_exists('DropboxException')) {

        class DropboxException extends Exception
        {

            public function __construct($err = null, $isDebug = FALSE)
            {
                if (is_null($err)) {
                    $el            = error_get_last();
                    $this->message = $el['message'];
                    $this->file    = $el['file'];
                    $this->line    = $el['line'];
                } else $this->message = $err;
                self::log_error($err);
                if ($isDebug) {
                    self::display_error($err, TRUE);
                }
            }

            public static function log_error($err)
            {
                error_log($err, 0);
            }

            public static function display_error($err, $kill = FALSE)
            {
                print_r($err);
                if ($kill === FALSE) {
                    die();
                }
            }
        }
    }
}