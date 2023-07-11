<?php

namespace DuplicatorPro\Krizalys\Onedrive;
defined("ABSPATH") or die("");
class ResumableUploader
{
    /**
     * @var Client An instance of the OneDrive Client
     */
    private $_client;

    /**
     * @var string This sessions upload url
     */
    private $_uploadUrl;

    /**
     * @var int Expiration time of session
     */
    private $_expirationTime;

    /**
     * @var int Chunk size
     */
    private $_chunkSize = DUPLICATOR_PRO_ONEDRIVE_UPLOAD_CHUNK_DEFAULT_SIZE_IN_KB;
    
    /**
     * @var int Offset to start uploading next chunk from
     */
    private $_fileOffset = 0;

    /**
     * @var string Path to file that is being uploaded
     */
    private $_sourcePath;

    /**
     * @var integer size of source file 
     */
    private $_sourceFileSize = null;

    /**
     * @var null The upload error message
     */
    private $_error = null;

    /**
     * @var bool The chunk upload success status
     */
    private $_success = false;

    /**
     * @var bool Is file uploaded completely
     */
    private $_completed = false;

    /**
     * @var File The uploaded file
     */
    private $_file = null;

    /**
     * ResumableUploader constructor.
     * @param Client $client An instance of the OneDrive Client
     * @param string $sourcePath Path to file that is being uploaded
     * @param object $resumable An object which contains the uploadUrl and Expiration Time
     *
     */
    public function __construct(Client $client, $sourcePath, $resumable = null)
    {
        $this->_client = $client;
        $this->_sourcePath = $sourcePath;
        $this->_sourceFileSize = filesize($this->_sourcePath);
        if ($resumable != null && property_exists($resumable, "uploadUrl")) {
            $this->_uploadUrl = $resumable->uploadUrl;
            $this->_expirationTime = $resumable->expirationTime;
        }
        $global = \DUP_PRO_Global_Entity::getInstance();
        $this->_chunkSize = ($global->onedrive_upload_chunksize_in_kb * KB_IN_BYTES);
    }

    /**
     * @param object $resumable An object which contains the uploadUrl and Expiration Time
     */
    public function setFromData($resumable)
    {
        $this->_uploadUrl = $resumable->uploadUrl;
        $this->_expirationTime = $resumable->expirationDateTime;
    }

    /**
     * @param string $filename The name the file will have in OneDrive
     * @param string $destPath The path tp the destination Folder, default is root
     *
     * @throws \Exception
     */
    public function obtainResumableUploadUrl($path)
    {
        $postFix = $this->_client->use_msgraph_api ? 'createUploadSession' : 'upload.createSession';
        $path = $this->_client->route_prefix."drive/special/approot:/" . $path . ":/".$postFix;

        $resumable = $this->_client->apiPost($path, []);
        if (property_exists($resumable, "uploadUrl")) {
            $this->_uploadUrl = $resumable->uploadUrl;
            $this->_expirationTime = strtotime($resumable->expirationDateTime);
        } else {
            throw new \Exception("Couldn't obtain resumable upload URL");
        }
    }

    /**
     * @return string The upload url
     */
    public function getUploadUrl()
    {
        return $this->_uploadUrl;
    }

    /**
     * @return int The upload session expiration time
     *
     */
    public function getExpirationTime()
    {
        return $this->_expirationTime;
    }

    /**
     * @return object An object which contains the expected ranges
     */
    public function getUploadStatus()
    {
        return $this->_client->apiGet($this->_uploadUrl);
    }

    /**
     * @return int Where to start the upload from
     */
    public function getUploadOffset()
    {
        if(!$this->_completed){
            return $this->_fileOffset;
        }
        // return $this->_sourceFileSize;
        return filesize($this->_sourcePath);
    }

    public function setUploadOffset($offset){
        $this->_fileOffset = $offset;
    }

    /**
     * @return int The next chunk size to be uploaded
     */
    public function getChunkSize()
    {
        return ($this->_sourceFileSize - $this->_fileOffset > $this->_chunkSize) ? $this->_chunkSize : $this->_sourceFileSize - $this->_fileOffset;
    }

    /**
     * @return array The headers for the upload
     */
    public function getHeaders()
    {
        $chunkSize = $this->getChunkSize();
        $this->_fileOffset = $this->getUploadOffset();
        $headers = [
            "Content-Length: " . $chunkSize,
            "Content-Range: bytes " . $this->_fileOffset . "-" . ($this->_fileOffset + $chunkSize - 1) . "/" . $this->_sourceFileSize
        ];

        return $headers;
    }

    /**
     * @return string Path to file that is being uploaded
     */
    public function getSourcePath()
    {
        return $this->_sourcePath;
    }

    /**
     * @return object The resumable object
     */
    public function getResumable()
    {
        return (object)[
            "uploadUrl" => $this->_uploadUrl,
            "expirationTime" => $this->_expirationTime
        ];
    }

    /**
     * @return string|null The error message
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * @return bool The upload success state
     */
    public function success()
    {
        return $this->_success;
    }

    /**
     * @param File $file Sets the completed file
     */
    public function setFile(File $file)
    {
        $this->_file = $file;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function completed()
    {
        return $this->_completed;
    }

    public function sha1CheckSum($file)
    {
        return $this->_file->sha1CheckSum($file);
    }

    /**
     * @param resource $stream Stream of the chunk being uploaded
     * @return object The upload status
     */
    public function uploadChunk($stream)
    {
        $headers = $this->getHeaders();
        \DUP_PRO_Log::trace("Headers of chunk ".print_r($headers,true));
        if ($this->_uploadUrl !== null) {
            try {

                // From other branch $result = $this->_client->apiPut($this->_uploadUrl, $stream, $headers, $this->_sourceFileSize);
                /**
                 * The fourth parameter $size will be passed to the CURL option CURLOPT_INFILESIZE
                 * Passing chunk-size from here because fstat($stat)[7] is giving wrong size and It cause the error 
                 *      Error: HTTP Error 400. The request has an invalid header name.
                 */
                $result = $this->_client->apiPut($this->_uploadUrl, $stream, $headers, $this->getChunkSize());
                \DUP_PRO_Log::trace("Result ".print_r($result, true));

                $this->_success = true;
                if (property_exists($result, "name")) {
                    $this->_completed = true;
                    $file = new File($this->_client, $result->id, $result);
                    $this->_file = $file;
                }

                // SnapCreek Custom code to set file offset from response
                if (property_exists($result, "nextExpectedRanges") && isset($result->nextExpectedRanges[0])) {
                    $next_expected_range_parts = explode('-', $result->nextExpectedRanges[0]);
                    $next_expected_range_parts[0] = intval($next_expected_range_parts[0]);
                    if ($next_expected_range_parts[0] > 0) {
                        // error_log('^^ Setting file offset from response ^^');
                        $this->_fileOffset = $next_expected_range_parts[0];
                    }
                }
            } catch (\Exception $exception) {
                $this->_success = false;
                $this->_error = $exception->getMessage();
            }
        } else {
            $this->_success = false;
            $this->_error = "You have to set _uploadUrl to make an upload";
        }

        // Attempt to test self killing
        /*
        if (time() % 5 === 0) {
            error_log('Attempting to make custom error');
            // $this->_error = "Custom Error";
            Throw new Exception('Custom error');
        }
        */
    }

}
