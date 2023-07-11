<?php

defined("ABSPATH") or die("");
// S3 Notes
// Object key is a unique name within the bucket up to 1024 characters ong - you would put full path in here
// Client specifies region and bucket is in a region - unknown ramifications of making these different
// Need to do the following from user
//  * Create bucket if not exists (checkbox)
//  * Path within bucket [first part of object key]
//  * Region for client
//  * Access keys (recommend they create new ones with limited functionality - in the future we could use master user  to create sub users so we don't have access to their entire account)
//  * Storage class - Standard, Stnd1ard/Infrequent Access, Reduced Redundancy
//  * Important metadata: Date (creation date)
//  * Note ALL keys should not be prefixed with / but look like a relative path

require_once(DUPLICATOR____PATH . '/aws/aws-autoloader.php');

use Duplicator\Utils\IncrementalStatusMessage;

class DUP_PRO_S3_Client_UploadInfo
{
    // S3 API LIMIT CAN'T BE LOWER OF 5120 KB
    const UPLOAD_PART_MIN_SIZE_IN_K = 5120;
    const UploadPartSizeBytes       = 2097152;

    public $next_offset   = 0;
    public $error_details = null;
    public $is_complete   = false;
    public $upload_id     = '';
    public $parts         = array();
    public $part_number   = 1;
    public $src_filepath;
    public $bucket;
    public $dest_directory;
    public $upload_part_size = self::UploadPartSizeBytes;
    public $storage_class;

    public function get_key()
    {
        $trimmed_dir = trim($this->dest_directory, '/');
        $basename    = basename($this->src_filepath);

        return "$trimmed_dir/$basename";
    }
}

class DUP_PRO_S3_U
{
    public static function delete_file($s3_client, $bucket, $remote_filepath, $statusMsgsObj = null)
    {
        if ($statusMsgsObj === null) {
            $statusMsgsObj = new IncrementalStatusMessage();
        }
        $success = false;

        try {
            $result        = $s3_client->deleteObject(array('Bucket' => $bucket, 'Key' => $remote_filepath));
            $delete_marker = ((bool) $result->get('DeleteMarker') ? 'true' : 'false');
            $statusMsgsObj->addMessage(sprintf(__('Delete of S3 file "%1$s" succeeded, DeleteMarker = %2$s', 'duplicator-pro'), $remote_filepath, $delete_marker));
            DUP_PRO_Log::trace("Delete of S3 file \"$remote_filepath\" succeeded, DeleteMarker = $delete_marker");
            $success = true;
        } catch (Exception $ex) {
            $statusMsgsObj->addMessage(sprintf(__('Exception when trying to delete S3 file "%1$s" in bucket %2$s. Exception: %3$s', 'duplicator-pro'), $remote_filepath, $bucket, $ex->getMessage()));
            DUP_PRO_Log::trace("Exception when trying to delete S3 file \"$remote_filepath\" in bucket $bucket");
        }

        return $success;
    }

    // Retrieve files in a given directory orderd by creation date
    public static function get_files_in_directory($s3_client, $remote_parent_directory)
    {
        $remote_file_paths = null;
        return $remote_file_paths;
    }

    public static function get_active_multipart_uploads($s3_client, $bucket, $storage_folder)
    {
        DUP_PRO_Log::trace("Looking for bucket $bucket $storage_folder");
        $results = false;

        try {
            $dirname = trim($storage_folder, '/') . '/';

            /* @var DuplicatorPro\Guzzle\Service\Resource\Model */
            $return_val = $s3_client->listMultipartUploads(array(
                'Bucket'    => $bucket,
                'Delimiter' => '/',
                'Prefix'    => $dirname
            ));

            $results = array();
            //since DuplicatorPro\Guzzle\Service\Resource\Model implements ArrayAccess the safest option is to use isset
            //to make sure the Uploads key exists
            if (isset($return_val['Uploads'])) {
                DUP_PRO_Log::trace("**** Uploads key exists ");
                foreach ($return_val['Uploads'] as $upload) {
                    $result            = new stdClass();
                    $result->upload_id = $upload['UploadId'];
                    $result->key       = $upload['Key'];
                    $result->timestamp = strtotime($upload['Initiated']);

                    $results[] = $result;
                }
            } else {
                DUP_PRO_Log::trace("**** Uploads key doesnt exist");
            }
        } catch (Exception $ex) {
            DUP_PRO_Log::trace("Exception when retrieving multipart uploads in bucket $bucket:" . $ex->getMessage());
        }

        return $results;
    }

    public static function abort_multipart_upload($s3_client, $bucket, $key, $upload_id)
    {
        try {
            DUP_PRO_Log::trace("Aborting multipart upload $upload_id");
            $s3_client->abortMultipartUpload(array(
                'Bucket'   => $bucket,
                'Key'      => $key,
                'UploadId' => $upload_id
            ));
        } catch (Exception $ex) {
            DUP_PRO_Log::trace("Exception when aborting multipart upload $upload_id in bucket $bucket:" . $ex->getMessage());
        }
    }

    // Upload a file all in one shot
    // returns true/false for success/failure
    public static function upload_file($s3_client, $bucket, $src_filepath, $remote_directory, $storage_class, $ACL_full_control = true, $dest_filename = '', $statusMsgsObj = null)
    {
        if ($statusMsgsObj === null) {
            $statusMsgsObj = new IncrementalStatusMessage();
        }
        // storage classes: s3 standard, s3 infrequent access, reduced redundency
        $success = false;

        try {
            $filename = !empty($dest_filename) ? $dest_filename : basename($src_filepath);
            $key      = trim($remote_directory, '/');
            $key      = "$key/$filename";

            DUP_PRO_Log::trace("Bucket: $bucket, Key:$key SouceFile:$src_filepath StorageClass:$storage_class");
            if ($ACL_full_control) {
                $statusMsgsObj->addMessage(__('Attempting to upload file with ACL bucket-owner-full-control', 'duplicator-pro'));
                $result = $s3_client->putObject(array(
                    'Bucket'       => $bucket,
                    'Key'          => $key,
                    'SourceFile'   => $src_filepath,
                    'ACL'          => 'bucket-owner-full-control',
                    'StorageClass' => $storage_class,
                ));
            } else {
                $statusMsgsObj->addMessage(__('Attempting to upload file without setting ACL property', 'duplicator-pro'));
                $result = $s3_client->putObject(array(
                    'Bucket'       => $bucket,
                    'Key'          => $key,
                    'SourceFile'   => $src_filepath,
                    'StorageClass' => $storage_class,
                ));
            }

            $result    = $result->getAll();
            $local_md5 = md5_file($src_filepath);
            $s3_md5    = preg_replace('/[^A-Za-z0-9\-]/', '', $result['ETag']);
            $statusMsgsObj->addMessage(__('Got the following headers: ', 'duplicator-pro') . trim(print_r($result, true)));
            DUP_PRO_Log::trace(print_r($result, true));
            DUP_PRO_Log::trace("$local_md5 <===> $s3_md5");

            $success = $local_md5 == $s3_md5;
            if ($success) {
                $statusMsgsObj->addMessage(__('Success: MD5 checksums match', 'duplicator-pro'));
            } else {
                $statusMsgsObj->addMessage(__('Error: MD5 checksums don\'t match', 'duplicator-pro'));
            }
        } catch (Exception $ex) {
            if (!isset($src_filepath)) {
                $src_filepath = 'test file';
            }
            $errorMsg = $ex->getMessage();
            if (strpos($errorMsg, 'Error reading uploaded data') !== false) {
                $errorMsg .= __('. It is possible that your "Secret Key" is wrong.', 'duplicator-pro');
            }
            $statusMsgsObj->addMessage(sprintf(__('Error uploading "%1$s" to S3. Exception: [%2$s] %3$s', 'duplicator-pro'), $src_filepath, get_class($ex), $errorMsg));

            DUP_PRO_Log::trace("Error uploading $src_filepath to S3. Exception: " . $ex);
        }

        return $success;
    }

    // Will either upload it successfully or populate $upload_info->error_details
    public static function upload_file_chunk($s3_client, &$s3_client_uploadinfo, $max_upload_time_in_sec = 15, $server_load_delay = 0)
    {
        /* @var $s3_client DuplicatorPro\Aws\S3\S3Client */
        /* @var $s3_client_uploadinfo DUP_PRO_S3_Client_UploadInfo */

        try {
            if (file_exists($s3_client_uploadinfo->src_filepath) == false) {
                $message = "{$s3_client_uploadinfo->src_filepath} doesn't exist!";

                DUP_PRO_Log::trace($message);

                $s3_client_uploadinfo->error_details = $message;

                return $s3_client_uploadinfo;
            }

            if ($s3_client_uploadinfo->upload_id == '') {
                try {
                    $response = $s3_client->createMultipartUpload(array(
                        'Bucket'       => $s3_client_uploadinfo->bucket,
                        'Key'          => $s3_client_uploadinfo->get_key(),
                        'StorageClass' => $s3_client_uploadinfo->storage_class
                    ));

                    $s3_client_uploadinfo->upload_id = $response['UploadId'];

                    return $s3_client_uploadinfo;
                } catch (Exception $ex) {
                    $message = sprintf(
                        DUP_PRO_U::__('Problem starting multipart upload from %1$s to %2$s in bucket %3$s (chunk_size_in_k %5$s) %4$s'),
                        $s3_client_uploadinfo->src_filepath,
                        $s3_client_uploadinfo->dest_directory,
                        $s3_client_uploadinfo->bucket,
                        $ex->getMessage(),
                        $s3_client_uploadinfo->upload_part_size
                    );

                    DUP_PRO_Log::trace($message);
                    $s3_client_uploadinfo->error_details = $message;

                    return $s3_client_uploadinfo;
                }
            }

            // Upload the various parts.
            $handle   = fopen($s3_client_uploadinfo->src_filepath, "rb");
            $filesize = filesize($s3_client_uploadinfo->src_filepath);

            if ($handle != false) {
                fseek($handle, $s3_client_uploadinfo->next_offset);

                $start_time  = time();
                $time_passed = 0;

                while (!$s3_client_uploadinfo->is_complete && !feof($handle) && ($time_passed < $max_upload_time_in_sec)) {
                    if ($server_load_delay !== 0) {
                        usleep($server_load_delay);
                    }

                    $amount_left = $filesize - $s3_client_uploadinfo->next_offset;

                    if ($amount_left > $s3_client_uploadinfo->upload_part_size) {
                        $read_amount = $s3_client_uploadinfo->upload_part_size;
                    } else {
                        $read_amount = $amount_left;
                    }

                    DUP_PRO_Log::trace("About to upload part {$s3_client_uploadinfo->part_number} with read amount $read_amount at offset {$s3_client_uploadinfo->next_offset}");

                    $response = $s3_client->uploadPart(array(
                        'Bucket'     => $s3_client_uploadinfo->bucket,
                        'Key'        => $s3_client_uploadinfo->get_key(),
                        'UploadId'   => $s3_client_uploadinfo->upload_id,
                        'PartNumber' => $s3_client_uploadinfo->part_number,
                        'Body'       => fread($handle, $read_amount),
                    ));

                    $s3_client_uploadinfo->parts[] = array(
                        'PartNumber' => $s3_client_uploadinfo->part_number++,
                        'ETag'       => trim($response['ETag'], '"')
                    );

                    $s3_client_uploadinfo->next_offset += $s3_client_uploadinfo->upload_part_size;

                    if ($s3_client_uploadinfo->next_offset < $filesize) {
                        fseek($handle, $s3_client_uploadinfo->next_offset);
                    } else {
                        $s3_client_uploadinfo->is_complete = true;
                    }

                    $time_passed = time() - $start_time;
                }

                if ($s3_client_uploadinfo->is_complete) {
                    DUP_PRO_Log::trace("S3 transfer is complete!");

                    // Correct the parts array since the etags have problems being stored with quotes

                    $fixed_parts = array();

                    foreach ($s3_client_uploadinfo->parts as $part) {
                        if (is_array($part)) {
                            $fixed_part['PartNumber'] = $part['PartNumber'];
                            $fixed_part['ETag']       = '"' . $part['ETag'] . '"';
                        } else {
                            $fixed_part['PartNumber'] = $part->PartNumber;
                            $fixed_part['ETag']       = '"' . $part->ETag . '"';
                        }

                        $fixed_parts[] = $fixed_part;
                    }
                    DUP_PRO_Log::trace(print_r($fixed_parts, true));
                    try {
                        DUP_PRO_Log::traceObject("complete multipart $s3_client_uploadinfo->bucket {$s3_client_uploadinfo->get_key()} $s3_client_uploadinfo->upload_id", $fixed_parts);
                        $result = $s3_client->completeMultipartUpload(array(
                            'Bucket'   => $s3_client_uploadinfo->bucket,
                            'Key'      => $s3_client_uploadinfo->get_key(),
                            'UploadId' => $s3_client_uploadinfo->upload_id,
                            'Parts'    => $fixed_parts
                        ));

                        $local_ETag = self::calculateETag($s3_client_uploadinfo->src_filepath, $s3_client_uploadinfo->upload_part_size);
                        $s3_ETag    = preg_replace('/[^A-Za-z0-9\-]/', '', $result->get("ETag"));
                        DUP_PRO_Log::trace("$local_ETag <===> $s3_ETag");

                        if ($s3_ETag != $local_ETag) {
                            throw new Exception("MD5 checksums don't match.");
                        }

                        DUP_PRO_Log::trace(print_r($result, true));

                        $bucket           = $s3_client_uploadinfo->bucket;
                        $key              = $s3_client_uploadinfo->get_key();
                        $is_object_exists = $s3_client->doesObjectExist($bucket, $key);
                        if (!$is_object_exists) {
                            throw new Exception("Archive is not exist on the bucket at the completion of multi part upload");
                        }

                        DUP_PRO_Log::traceObject('Completed multipart upload', $result);
                    } catch (Exception $ex) {
                        $message = sprintf(
                            DUP_PRO_U::__('Problem uploading multipart upload from %1$s to %2$s in bucket %3$s %4$s'),
                            $s3_client_uploadinfo->src_filepath,
                            $s3_client_uploadinfo->dest_directory,
                            $s3_client_uploadinfo->bucket,
                            $ex->getMessage()
                        );

                        DUP_PRO_Log::traceError($message);
                        $s3_client_uploadinfo->error_details = $message;
                    }
                }

                fclose($handle);
            } else {
                $s3_client_uploadinfo->error_details = "Error opening $s3_client_uploadinfo->src_filepath";
            }
        } catch (Exception $ex) {
            $s3_client_uploadinfo->error_details = "Error uploading to S3: " . $ex->getMessage();
        }

        return $s3_client_uploadinfo;
    }

    public static function download_file($s3_client, $bucket, $remote_directory, $remote_filename, $local_filepath, $overwrite_local = true)
    {
        /* @var $s3_client S3Client */
        $success = false;

        DUP_PRO_Log::trace("1");
        if ($overwrite_local || (file_exists($local_filepath) === false)) {
            DUP_PRO_Log::trace("2");
            $trimmed_dir = trim($remote_directory, '/');
            $key         = "$trimmed_dir/$remote_filename";

            DUP_PRO_Log::trace("bucket: $bucket key:$key saveas:$local_filepath");
            try {
                $result = $s3_client->getObject(array(
                    'Bucket' => $bucket,
                    'Key'    => $key,
                    'SaveAs' => $local_filepath
                ));

                DUP_PRO_Log::traceObject('result', $result);

                $success = true;
            } catch (Exception $ex) {
                $message = DUP_PRO_U::__("Problem downloading $key in bucket $bucket and saving to $local_filepath") . $ex->getMessage();

                DUP_PRO_Log::trace($message);
            }
        } else {
            DUP_PRO_Log::trace("3");
            DUP_PRO_Log::trace("Attempted to download a file to $local_filepath but that file already exists!");
        }

        DUP_PRO_Log::trace("4");
        return $success;
    }

    public static function calculateETag($src_file, $chunksize)
    {
        $result          = '';
        $sum_string      = '';
        $handle          = fopen($src_file, "r");
        $number_of_parts = 0;

        while (!feof($handle)) {
            $file_chunk  = fread($handle, $chunksize);
            $sum_string .= hash("md5", $file_chunk, true);
            $number_of_parts++;
        }

        $result = hash("md5", $sum_string) . '-' . $number_of_parts;

        return $result;
    }

    public static function get_s3_client($region, $access_key, $secret_key, $endpoint = '')
    {
        $args = array(
            'version'     => '2006-03-01',
            'region'      => $region,
            'signature'   => 'v4',
            'credentials' => array('key' => $access_key, 'secret' => $secret_key),
        );

        if ('' != $endpoint) {
            if (!preg_match("~^(?:f|ht)tps?://~i", $endpoint)) {
                $endpoint = "https://" . $endpoint;
            }
            $args['endpoint'] = $endpoint;
        }

        $client = DuplicatorPro\Aws\S3\S3Client::factory($args); // @phpstan-ignore-line

        $global = DUP_PRO_Global_Entity::getInstance();

        $opts = array();
        if ($global->ipv4_only) {
            $opts[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }
        $client->setConfig($opts);

        $verify_peer = $global->ssl_disableverify ? false : true;
        $verify_host = $global->ssl_disableverify ? 0 : 2;
        $ssl_ca_cert = false;
        if (!$global->ssl_useservercerts) {
            $ssl_ca_cert = DUPLICATOR_PRO_CERT_PATH;
        }
        $client->setSslVerification($ssl_ca_cert, $verify_peer, $verify_host);

        return $client;
    }
}
