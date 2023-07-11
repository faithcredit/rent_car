<?php

defined("ABSPATH") or die("");

require_once(DUPLICATOR____PATH . '/lib/onedrive/autoload.php');
abstract class DUP_PRO_OneDrive_Config
{
    const ONEDRIVE_CLIENT_ID               = '15fa3a0d-b7ee-447c-8093-7bfcf30b0797';
    const ONEDRIVE_CLIENT_SECRET           = 'ahYN901]gvemuEUKKB45}|_';
    const ONEDRIVE_REDIRECT_URI            = 'https://snapcreek.com/misc/onedrive/redir3.php';
    const ONEDRIVE_ACCESS_SCOPE            = array("onedrive.appfolder", "offline_access");
    const ONEDRIVE_BUSINESS_ACCESS_SCOPE   = array("onedrive.readwrite", "offline_access");
    const MICROSOFT_GRAPH_ENDPOINT         = 'https://graph.microsoft.com/';
    const MSGRAPH_ACCESS_SCOPE             = array(
        'openid',
        'offline_access',
        'files.readwrite.appfolder',
    );
    const MSGRAPH_ALL_FOLDERS_ACCESS_SCOPE = array(
        'openid',
        'offline_access',
        'files.readwrite',
    );
}

class DUP_PRO_Onedrive_U
{
    public static function get_raw_onedrive_client($use_msgraph_api = false)
    {
        $opts     = array(
            'client_id' => DUP_PRO_OneDrive_Config::ONEDRIVE_CLIENT_ID,
            'use_msgraph_api' => $use_msgraph_api,
        );
        $opts     = self::injectExtraReqArgs($opts);
        $onedrive = new DuplicatorPro\Krizalys\Onedrive\Client($opts);
        return $onedrive;
    }

    public static function get_onedrive_client_from_state($state, $use_msgraph_api = false)
    {
        $opts     = array(
            'client_id' => DUP_PRO_OneDrive_Config::ONEDRIVE_CLIENT_ID,
            'state' => $state,
            'use_msgraph_api' => $use_msgraph_api,
        );
        $opts     = self::injectExtraReqArgs($opts);
        $onedrive = new DuplicatorPro\Krizalys\Onedrive\Client($opts);
        return $onedrive;
    }

    private static function injectExtraReqArgs($opts)
    {
        $global            = DUP_PRO_Global_Entity::getInstance();
        $opts['sslverify'] = $global->ssl_disableverify ? false : true;
        if (!$global->ssl_useservercerts) {
            $opts['ssl_capath'] = DUPLICATOR_PRO_CERT_PATH;
        }
        return $opts;
    }

    public static function get_onedrive_auth_url_and_client($args)
    {
        $onedrive     = self::get_raw_onedrive_client($args['use_msgraph_api']);
        $redirect_uri = DUP_PRO_OneDrive_Config::ONEDRIVE_REDIRECT_URI;
        if (!$args['use_msgraph_api'] && $args['is_business']) {
            $onedrive->setBusinessMode();
        }

        $scopes = self::get_scope_array($args);
// Gets a log in URL with sufficient privileges from the OneDrive API.
        $url = $onedrive->getLogInUrl($scopes, $redirect_uri);
        \DUP_PRO_Log::trace($url);
        return ['url' => $url,'client' => $onedrive];
    }

    public static function get_onedrive_logout_url($use_msgraph_api = false)
    {
        if ($use_msgraph_api) {
// Ref.: https://docs.microsoft.com/en-us/onedrive/developer/rest-api/getting-started/graph-oauth?view=odsp-graph-online
            $base_url   = "https://login.microsoftonline.com/common/oauth2/v2.0/logout";
            $fields_arr = [
                "client_id" => DUP_PRO_OneDrive_Config::ONEDRIVE_CLIENT_ID,
                "post_logout_redirect_uri" => DUP_PRO_OneDrive_Config::ONEDRIVE_REDIRECT_URI
            ];
            $fields     = http_build_query($fields_arr);
        } else {
            $base_url     = "https://login.live.com/oauth20_logout.srf";
            $redirect_uri = DUP_PRO_OneDrive_Config::ONEDRIVE_REDIRECT_URI;
            $fields_arr   = [
                "client_id" => DUP_PRO_OneDrive_Config::ONEDRIVE_CLIENT_ID,
                "redirect_uri" => DUP_PRO_OneDrive_Config::ONEDRIVE_REDIRECT_URI
            ];
            $fields       = http_build_query($fields_arr);
        }

        $logout_url = $base_url . "?$fields";
        return $logout_url;
    }

    public static function get_scope_array($args)
    {
        if ($args['use_msgraph_api']) {
            if ($args['msgraph_all_folders_read_write_perm']) {
                return DUP_PRO_OneDrive_Config::MSGRAPH_ALL_FOLDERS_ACCESS_SCOPE;
            } else {
                return DUP_PRO_OneDrive_Config::MSGRAPH_ACCESS_SCOPE;
            }
        } else {
            if (!$args['is_business']) {
                return DUP_PRO_OneDrive_Config::ONEDRIVE_ACCESS_SCOPE;
            } else {
                return DUP_PRO_OneDrive_Config::ONEDRIVE_BUSINESS_ACCESS_SCOPE;
            }
        }
    }

    /**
     * We want to display error_description ($onedrive_client_state->token->data->error_description)
     * of OneDrive Client State to the user, but for some error codes
     * ($onedrive_client_state->token->data->error_codes) it is necessary to prepend additional error
     * message to describe the cause of the problem more precisely. This function defines and returns
     * additional error messages for some error codes of OneDrive Client State.
     *
     * @return string[]
     */
    public static function getAdditionalErrorMessages()
    {
        static $error_messages_arr = array();
        if (count($error_messages_arr) == 0) {
            // Here we define additional error messages for some error codes that could appear in
            // $onedrive_client_state->token->data->error_codes when finalizing authorization.
            // Define them like this: $error_messages_arr['<error_code>'] = "<additional_message>";
            // In case when error code matches, additional error message defined here will be prepended to
            // $onedrive_client_state->token->data->error_description and displayed to the user
            // when finalizing authorization. It is done to describe the cause of the problem more precisely,
            // because for some error codes content of error_description is not enough.
            $error_messages_arr['9002313'] = __("You probably entered wrong authorization code. " .
                "Make sure that you copy only code part into Step 2 field, not any additional text. " .
                "Also, make sure that you copy the whole code, not only part of it. ", 'duplicator-pro');
            // For error codes '70000' and '9002313' we define the same additional error message...
            $error_messages_arr['70000'] = $error_messages_arr['9002313'];
        }
        return $error_messages_arr;
    }

    /**
     * Checks for error in OneDrive Client State.
     * Returns null if there is no error, otherwise returns string describing the error.
     *
     * @param object $onedrive_client_state
     *
     * @return string|null
     */
    public static function getErrorMessageBasedOnClientState($onedrive_client_state)
    {
        if (
            isset($onedrive_client_state->token->data->error_description) &&
            isset($onedrive_client_state->token->data->error_codes)
        ) {
            $error_messages_arr = self::getAdditionalErrorMessages();
            $error_codes        = $onedrive_client_state->token->data->error_codes;
            $error_message      = "";
            foreach ($error_codes as $error_code) {
                if (isset($error_messages_arr[$error_code])) {
                    $error_message .= $error_messages_arr[$error_code] . "</br>";
                }
            }
            $error_message .= $onedrive_client_state->token->data->error_description;
            return $error_message;
        }
        return null;
    }
}
