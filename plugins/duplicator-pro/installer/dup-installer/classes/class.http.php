<?php

defined("DUPXABSPATH") or die("");
/**
 *  Http Class Utility
 */
class DUPX_HTTP
{
    /**
     *  Do an http post request with curl or php code
     *
     *  @param string    $url     A URL to get.  If $params is not null then all query strings will be removed.
     *  @param string[]  $params  A valid key/pair combo $data = array('key1' => 'value1', 'key2' => 'value2');
     *  @param ?string[] $headers Optional header elements
     *
     *  @return string|bool a string or FALSE on failure.
     */
    public static function get($url, $params = array(), $headers = null)
    {
        //PHP GET
        if (!function_exists('curl_init')) {
            return self::php_get_post($url, $params, $headers = null, 'GET');
        }

        //Remove query string if $params are passed
        $full_url = $url;
        if (count($params)) {
            $url      = preg_replace('/\?.*/', '', $url);
            $full_url = $url . '?' . http_build_query($params);
        }
        $headers_on = isset($headers) && array_count_values($headers);
        $ch         = curl_init();
        // Return contents of transfer on curl_exec
        // Allow self-signed certs
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $full_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, $headers_on);
        if ($headers_on) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * Check to see if the internet is accessible
     *
     * @param string $url  A URL e.g without prefix "ajax.googleapis.com"
     * @param ?int   $port A valid port number
     *
     * @return bool
     */
    public static function is_url_active($url, $port, $timeout = 5)
    {
        if (function_exists('fsockopen')) {
            $port      = isset($port) && is_integer($port) ? $port : 80;
            $connected = @fsockopen($url, $port, $errno, $errstr, $timeout);
            // website and port
            if ($connected) {
                $is_conn = true;
                @fclose($connected);
            } else {
                $is_conn = false;
            }
            return $is_conn;
        } else {
            return false;
        }
    }

    //PHP POST or GET requets
    private static function php_get_post($url, $params, $headers = null, $method = 'POST')
    {
        $full_url = $url;
        if ($method == 'GET' && count($params)) {
            $url      = preg_replace('/\?.*/', '', $url);
            $full_url = $url . '?' . http_build_query($params);
        }

        $data = array('http' => array(
                'method'  => $method,
                'content' => http_build_query($params)));
        if ($headers !== null) {
            $data['http']['header'] = $headers;
        }
        $ctx = stream_context_create($data);
        $fp  = @fopen($full_url, 'rb', false, $ctx);
        if (!$fp) {
            throw new Exception("Problem with $full_url");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new Exception("Problem reading data from $full_url");
        }
        return $response;
    }
}
