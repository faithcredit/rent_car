<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax\FileTransfer;

use DUP_PRO_Log;
use VendorDuplicator\WpOrg\Requests\Requests;
use Duplicator\Libs\Snap\SnapURL;
use Exception;

class RemoteDownloadCustom
{
    /**
     * Apply Dropbox remote download data filter
     *
     * @param array{url: string, archiveName: string, chunkTime: float, maxRetrials: int} $downloadData download data
     *
     * @return array{url: string, archiveName: string, chunkTime: float, maxRetrials: int}
     */
    public static function dropboxRemoteUrlFilter($downloadData)
    {
        $parseUrl = SnapURL::parseUrl($downloadData['url']);
        if (SnapURL::wwwRemove($parseUrl['host']) === 'dropbox.com') {
            $downloadData['maxRetrials'] = 0;
            parse_str($parseUrl['query'], $queryVals);
            if (isset($queryVals['dl'])) {
                $queryVals['dl']   = 1;
                $parseUrl['query'] = http_build_query($queryVals);
                $realURL           = SnapURL::buildUrl($parseUrl);
                DUP_PRO_Log::trace("Real Dropbox URL: $realURL");
                $downloadData['url'] = $realURL;
            }
            $downloadData['chunkTime'] = 20;
        }
        return $downloadData;
    }

    /**
     * Apply Google drive remote download data filter
     *
     * @param array{url: string, archiveName: string, chunkTime: float, maxRetrials: int} $downloadData download data
     *
     * @return array{url: string, archiveName: string, chunkTime: float, maxRetrials: int}
     */
    public static function gDriveRemoteUrlFilter($downloadData)
    {
        $url      = $downloadData['url'];
        $parseUrl = SnapURL::parseUrl($url);
        if (SnapURL::wwwRemove($parseUrl['host']) === 'drive.google.com') {
            $downloadData['maxRetrials'] = 0;
            // $url example: https://drive.google.com/file/d/10BQxD48Qf2eq8vg5uIiRxSd_ho8DczKW/view?usp=sharing
            // Take id from $url, then use it to form this link:
            // https://drive.google.com/uc?id=10BQxD48Qf2eq8vg5uIiRxSd_ho8DczKW&export=download&confirm=t
            $revUrl  = strrev($url);
            $pattern = "/\\/(.+?)\\//"; // Take text between last 2 signs '/'
            $result  = preg_match($pattern, $revUrl, $matches);
            if (!$result) {
                throw new Exception("Could not get id from the GDrive URL.");
            }
            $id          = strrev($matches[1]);
            $indirectURL = "https://drive.google.com/uc?id=" . $id . "&export=download&confirm=t";

            // Get cookies from view file page
            $response = Requests::get(
                $url,
                array(),
                array(
                    'timeout' => 60,
                    'follow_redirects' => false
                )
            );

            if ($response->success == false) {
                throw new Exception("Could not get real download url from the GDrive website");
            }

            $cookies = property_exists($response, 'cookies') ? $response->cookies : null;
            DUP_PRO_Log::trace("Real GDrive URL: $indirectURL");
            $downloadData['url'] = $indirectURL;

            // Extracting archive name from response headers of $indirectURL
            $response = Requests::get(
                $indirectURL,
                array(
                    'Range' => "bytes=0-0"
                ),
                array(
                    'timeout' => 60,
                    'cookies' => $cookies
                )
            );

            if (
                $response->success == false ||
                !isset($response->headers["content-disposition"]) ||
                strlen($response->headers["content-disposition"]) == 0
            ) {
                throw new Exception("Could not get archive name for GDrive url: $url, unexpected headers");
            }

            $pattern = "/filename=\"(.+)\"/msU";
            $result  = preg_match($pattern, $response->headers["content-disposition"], $matches);
            if (!$result) {
                throw new Exception("Could not get archive name for GDrive url: $url, no filename in headers");
            }
            $archiveName = $matches[1];
            DUP_PRO_Log::trace("Archive name on GDrive: $archiveName");
            $downloadData['archiveName'] = $archiveName;
            $downloadData['chunkTime']   = 10;

            // Preserve cookies for later use in future chunk requests
            if (session_id() == "") {
                session_start();
            }
            $_SESSION["duplicator_pro_import_from_link_cookies"] = property_exists($response, 'cookies') ? $response->cookies : null;
        }

        return $downloadData;
    }

    /**
     * Apply OneDrive remote download data filter
     *
     * @param array{url: string, archiveName: string, chunkTime: float, maxRetrials: int} $downloadData download data
     *
     * @return array{url: string, archiveName: string, chunkTime: float, maxRetrials: int}
     */
    public static function oneDriveRemoteUrlFilter($downloadData)
    {
        $url      = $downloadData['url'];
        $parseUrl = SnapURL::parseUrl($url);
        if (SnapURL::wwwRemove($parseUrl['host']) === '1drv.ms') {
            $downloadData['maxRetrials'] = 3;
            // According to instructions from:
            // https://towardsdatascience.com/how-to-get-onedrive-direct-download-link-ecb52a62fee4
            $base64Value = base64_encode($url);
            $encodedUrl  = "u!" . rtrim($base64Value, "=");
            $encodedUrl  = str_replace('+', '-', str_replace('/', '_', $encodedUrl));
            $nextURL     = "https://api.onedrive.com/v1.0/shares/$encodedUrl/root/content";
            DUP_PRO_Log::trace("Next step OneDrive URL: $nextURL");

            // Extracting archive name and real url from headers of $nextURL
            $response = Requests::get(
                $nextURL,
                array(
                    'Range' => "bytes=0-0"
                ),
                array(
                    'timeout' => 60,
                    //'protocol_version' => 1.1,
                    //'transport' => "\\VendorDuplicator\\WpOrg\\Requests\\Transport\\Fsockopen",
                    'verify' => false,
                    'verifyname' => false
                )
            );

            if (
                $response->success == false ||
                !isset($response->headers["content-disposition"]) ||
                strlen($response->headers["content-disposition"]) == 0 ||
                !isset($response->headers["content-location"]) ||
                strlen($response->headers["content-location"]) == 0
            ) {
                throw new Exception("Could not get direct url and archive name for OneDrive url: $url, unexpected headers");
            }
            $pattern = "/filename=\"(.+)\"/msU";
            $result  = preg_match($pattern, $response->headers["content-disposition"], $matches);
            if (!$result) {
                throw new Exception("Could not get archive name for OneDrive url: $url, no filename in headers");
            }
            $archiveName                 = $matches[1];
            $downloadData['archiveName'] = $archiveName;
            DUP_PRO_Log::trace("Archive name on OneDrive: $archiveName");

            $realURL = $response->headers["content-location"];
            DUP_PRO_Log::trace("Real OneDrive URL: $realURL");
            $downloadData['url'] = $realURL;

            $downloadData['chunkTime'] = 5;
        }
        return $downloadData;
    }
}
