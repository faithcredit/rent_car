<?php

defined("ABSPATH") or die("");

use Duplicator\Utils\Net\FTPUploadInfo;

class DUP_PRO_FTPcURL
{
    /** @var string FTP host */
    private $server;
    /** @var int FTP port */
    private $port = 21;
    /** @var string FTP username */
    private $username;
    /** @var string FTP password */
    private $password;
    /** @var string remote FTP directory */
    private $directory = '/';
    /** @var integer timeout in seconds */
    private $timeout_in_sec = 90;
    /** @var boolean whether FTP is ssl ftp or not */
    private $ssl = false;
    /** @var boolean whether FTP is passive mode or not */
    private $passive_mode = false;

    /**
     * class constructor
     *
     * @param string  $server         FTP host
     * @param integer $port           FTP port
     * @param string  $username       FTP username
     * @param string  $password       FTP password
     * @param string  $directory      remote FTP directory
     * @param integer $timeout_in_sec timeout in seconds
     * @param boolean $ssl            whether it is ssl ftp or not
     * @param boolean $passive_mode   whether it is passive mode or not
     *
     * @return void
     */
    public function __construct($server, $port = 21, $username = 'anonymous', $password = 'anonymous@gmail.com', $directory = '/', $timeout_in_sec = 15, $ssl = false, $passive_mode = false)
    {
        if (empty($server)) {
            throw new InvalidArgumentException(DUP_PRO_U::esc_html__('Invalid $server argument with the empty in DUP_PRO_FTPcURL class constructor.'));
        }

        if (!is_string($server)) {
            throw new InvalidArgumentException(sprintf(DUP_PRO_U::esc_html__('Invalid $server argument with the value %s in DUP_PRO_FTPcURL class constructor.')), $server);
        }

        if (empty($port)) {
            throw new InvalidArgumentException(DUP_PRO_U::esc_html__('Invalid $port argument with the empty in DUP_PRO_FTPcURL class constructor.'));
        }
        $port = (int) $port;
        if ($port <= 0) {
            throw new InvalidArgumentException(sprintf(DUP_PRO_U::esc_html__('Invalid $port argument with the value %s in DUP_PRO_FTPcURL class constructor.')), $port);
        }

        if (empty($username)) {
            throw new InvalidArgumentException(DUP_PRO_U::esc_html__('Invalid $username argument with the empty in DUP_PRO_FTPcURL class constructor.'));
        }
        if (!is_string($username)) {
            throw new InvalidArgumentException(sprintf(DUP_PRO_U::esc_html__('Invalid $username argument with the value %s in DUP_PRO_FTPcURL class constructor.')), $username);
        }

        if (empty($password)) {
            throw new InvalidArgumentException(DUP_PRO_U::esc_html__('Invalid $password argument with the empty in DUP_PRO_FTPcURL class constructor.'));
        }
        if (!is_string($password)) {
            throw new InvalidArgumentException(sprintf(DUP_PRO_U::esc_html__('Invalid $password argument with the value %s in DUP_PRO_FTPcURL class constructor.')), $password);
        }

        if (empty($directory)) {
            throw new InvalidArgumentException(DUP_PRO_U::esc_html__('Invalid $directory argument with the empty in DUP_PRO_FTPcURL class constructor.'));
        }
        if (!is_string($directory)) {
            throw new InvalidArgumentException(sprintf(DUP_PRO_U::esc_html__('Invalid $directory argument with the value %s in DUP_PRO_FTPcURL class constructor.')), $directory);
        }

        if (empty($timeout_in_sec)) {
            throw new InvalidArgumentException(DUP_PRO_U::esc_html__('Invalid $timeout_in_sec argument with the empty in DUP_PRO_FTPcURL class constructor.'));
        }
        $timeout_in_sec = (int) $timeout_in_sec;
        if ($timeout_in_sec <= 0) {
            throw new InvalidArgumentException(sprintf(DUP_PRO_U::esc_html__('Invalid $timeout_in_sec argument with the value %s in DUP_PRO_FTPcURL class constructor.')), $timeout_in_sec);
        }

        if (!is_bool($ssl)) {
            throw new InvalidArgumentException(sprintf(DUP_PRO_U::esc_html__('Invalid $ssl argument with the value %s in DUP_PRO_FTPcURL class constructor.')), $ssl);
        }

        if (!is_bool($passive_mode)) {
            throw new InvalidArgumentException(sprintf(DUP_PRO_U::esc_html__('Invalid $passive_mode argument with the value %s  in DUP_PRO_FTPcURL class constructor.')), $passive_mode);
        }

        $this->server         = $server;
        $this->port           = $port;
        $this->username       = $username;
        $this->password       = $password;
        $this->directory      = $directory;
        $this->timeout_in_sec = $timeout_in_sec;
        $this->ssl            = $ssl;
        $this->passive_mode   = $passive_mode;
    }

    /**
     * curl call
     *
     * @param string $path    where the curl call occur
     * @param array  $options configuration options
     *
     * @throws Exception upon error
     *
     * @return string
     */
    private function curl_call($path, $options = array())
    {
        $ch  = curl_init();
        $url = sprintf('ftp://%s:%d/%s', $this->server, $this->port, $this->untrailing_left_slash_path($path));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $this->username, $this->password));

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout_in_sec);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);

        if ($this->ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
            curl_setopt($ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
        } else {
            // To do based on settings verify peer
            $global = DUP_PRO_Global_Entity::getInstance();

            $setopts[CURLOPT_SSL_VERIFYPEER] = $global->ssl_disableverify ? false : true;
            $setopts[CURLOPT_SSL_VERIFYHOST] = $global->ssl_disableverify ? 0 : 2;
            if (!$global->ssl_useservercerts) {
                $setopts[CURLOPT_CAINFO] = DUPLICATOR_PRO_CERT_PATH;
                $setopts[CURLOPT_CAPATH] = DUPLICATOR_PRO_CERT_PATH;
            }
        }

        if ($this->passive_mode) {
            curl_setopt($ch, CURLOPT_FTP_USE_EPSV, true);
        } else {
            curl_setopt($ch, CURLOPT_FTP_USE_EPRT, true);
            curl_setopt($ch, CURLOPT_FTPPORT, 0);
        }

        foreach ($options as $name => $value) {
            curl_setopt($ch, $name, $value);
        }

        if (($response = curl_exec($ch)) === false) {
            if (($errno = curl_errno($ch))) {
                switch ($errno) {
                    case 6:
                    case 7:
                        throw new Exception(DUP_PRO_U::esc_html__('Unable to connect to FTP server. Please check your FTP hostname, port, and active mode settings.'));
                    case 9:
                        throw new Exception(DUP_PRO_U::esc_html__('Unable to change FTP directory. Please ensure that you have permission on the server.'));
                    case 23:
                        throw new Exception(DUP_PRO_U::esc_html__('Unable to download file from FTP server. Please ensure that you have enough disk space.'));
                    case 28:
                        throw new Exception(DUP_PRO_U::esc_html__('Connecting to FTP server timed out. Please check FTP hostname, port, username, password, and active mode settings.'));
                    case 67:
                        throw new Exception(DUP_PRO_U::esc_html__('Unable to login to FTP server. Please check your username and password.'));
                    default:
                        throw new Exception(sprintf(DUP_PRO_U::esc_html__('Unable to connect to FTP. Error code: %s.'), $errno));
                }
            }
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code >= 400) {
            if (isset($this->messages[$http_code])) {
                throw new Exception(sprintf(DUP_PRO_U::esc_html__('Error code: %s.'), $this->messages[ $http_code ]));
            } else {
                throw new Exception(sprintf(DUP_PRO_U::esc_html__('Error code: %s.'), $http_code));
            }
        }

        return $response;
    }

    /**
     * test ftp connection
     *
     * @throws Exception upon error
     * @return boolean
     */
    public function test_conn()
    {
        try {
            $this->curl_call(
                '/',
                array(
                    CURLOPT_TIMEOUT => $this->timeout_in_sec,
                )
            );
        } catch (Exception $e) {
            $message = sprintf(DUP_PRO_U::esc_html__('Error connecting to FTP server %1$s:%2$d. Exception Error: %3$s.'), $this->server, $this->port, $e->getMessage());
            DUP_PRO_Log::trace($message);
            throw $e;
        }
        return true;
    }

    /**
     * create a dir
     *
     * @param string $folder_path a dir path which need to create
     *
     * @throws Exception upon error
     * @return boolean
     */
    public function create_directory($folder_path)
    {
        try {
            $this->curl_call(
                $folder_path,
                array(
                    CURLOPT_FTP_CREATE_MISSING_DIRS => true,
                )
            );
        } catch (Exception $e) {
            DUP_PRO_Log::trace("Error creating folder $folder_path when using FTP cURL. FTP Info: " . $this->get_info() . ' Exception Error: ' . $e->getMessage());
            return false;
        }
        DUP_PRO_Log::trace("Successfully created folder $folder_path by FTP cURL.");
        return true;
    }

    /**
     * Upload file
     *
     * @param string $local_file_path  local file path
     * @param string $dest_filename    destination file name
     *
     * @return bool
     */
    public function upload_file($local_file_path, $dest_filename = '')
    {
        $remote_file_path = $this->untrailing_left_slash_path($this->directory) . "/" . $dest_filename;
        $file_size        = filesize($local_file_path);
        // Upload file from file stream
        $success = false;
        if (($file_stream = fopen($local_file_path, 'rb'))) {
            $options = array(
                CURLOPT_UPLOAD => true,
                // by chance If FTP curl storage set and In btw a user has deleted storage dir from a FTP program
                CURLOPT_FTP_CREATE_MISSING_DIRS => true,
                CURLOPT_INFILE => $file_stream,
                CURLOPT_INFILESIZE => $file_size,
                CURLOPT_NOPROGRESS => true,
            );
            // curl_call throws Exception in case of failure
            // curl_call returns curl_exec response, which we don't need here
            $this->curl_call($remote_file_path, $options);
            fclose($file_stream);
            $success = true;
        }
        return $success;
    }

    /**
     * download file
     *
     * @param string  $remote_file_path   file path relative to $this->directory
     * @param string  $local              either local file path or local dir, depends on the $is_local_directory
     * @param boolean $is_local_directory whether $local is local directory or full path to download
     *
     * @throws Exception upon error
     * @return boolean
     */
    public function download_file($remote_file_path, $local, $is_local_directory = true)
    {
        $remote_file_path = $this->untrailing_left_slash_path($remote_file_path);

        if ($is_local_directory) {
            $filename        = basename($remote_file_path);
            $local_file_path = "$local/$filename";
        } else {
            $local_file_path = $local;
        }

        if (($file_stream = fopen($local_file_path, 'wb'))) {
            try {
                $options = array(
                    CURLOPT_FILE => $file_stream,
                    CURLOPT_NOPROGRESS => true,
                );
                $this->curl_call(sprintf('/%s/%s', $this->directory, $remote_file_path), $options);
            } catch (Exception $e) {
                DUP_PRO_Log::trace(
                    "Error downloading " . $remote_file_path . " into " . $local_file_path .
                    ". FTP Info: " . $this->get_info() . ' Exception Error: ' . $e->getMessage()
                );
                throw $e;
            }
            fclose($file_stream);
        } else {
            $errorMsg = "Error downloading " . $remote_file_path . " into " . $local_file_path . ". " .
                        "Could not open " . $local_file_path . " for writing.";
            DUP_PRO_Log::trace($errorMsg);
            throw new Exception($errorMsg);
        }
        return true;
    }

    /**
     * Checks if remote directory exists
     *
     * @param string $storage_folder remote folder path
     *
     * @return boolean
     */
    public function directory_exists($storage_folder)
    {
        $directoryExists = false;
        try {
            $directoryExists = is_array($this->raw_list_folder($storage_folder));
        } catch (Exception $ex) {
            $directoryExists = false;
        }
        return $directoryExists;
    }

    /**
     * delete remote file
     *
     * @param string $file_path remote file path
     *
     * @throws Exception upon error
     * @return boolean
     */
    public function delete($file_path)
    {
        $file_path = $this->untrailing_left_slash_path($file_path);
        try {
            $this->curl_call(
                sprintf('/%s/', $this->directory),
                array(
                    CURLOPT_QUOTE => array(sprintf('DELE /%s', $this->untrailing_left_slash_path(sprintf('/%s/%s', $this->directory, $file_path)))),
                )
            );
        } catch (Exception $e) {
            DUP_PRO_Log::trace("Failed to delete " . $file_path . " from " . $this->server . ". Error code: " . $e->getCode() . ". Error Message:" . $e->getMessage());
            return false;
        }
        DUP_PRO_Log::trace("Successfully deleted " . $file_path . " from " . $this->server);
        return true;
    }

    /**
     * upload file by chunking mode
     *
     * @param string  $source_filepath        local file path
     * @param string  $storage_folder         remote folder
     * @param integer $max_upload_time_in_sec max upload time in sec in a chunk
     * @param integer $offset                 file offset
     * @param integer $server_load_delay
     * @param string  $dest_filename          remote file name
     *
     * @return FTPUploadInfo
     */
    public function upload_chunk($source_filepath, $storage_folder, $max_upload_time_in_sec = 15, $offset = 0, $server_load_delay = 0, $dest_filename = '')
    {
        DUP_PRO_Log::trace("----------------------------");
        DUP_PRO_Log::trace("FTP CURL CHUNK UPLOAD START");
        DUP_PRO_Log::trace("FTP CHUNK OFFSET IN=$offset");

        $ftp_upload_info = new FTPUploadInfo();
        $start_time      = time();

        $remote_file_path = !empty($dest_filename) ? $dest_filename : basename($source_filepath);
        try {
            DUP_PRO_Log::trace("call upload chunk for FTP cURL");
            $fp = fopen($source_filepath, 'rb');
            if ($fp) {
                // Read file chunk data
                while (
                    fseek($fp, $offset) !== -1
                    && $file_chunk_data = fread($fp, DUPLICATOR_PRO_FTP_CURL_CHUNK_SIZE)
                ) {
                    $ret = $this->upload_file_chunk($file_chunk_data, $remote_file_path, $offset);
                    if ($ret) {
                        $offset = ftell($fp);
                    }

                    $time_passed = time() - $start_time;
                    if ($time_passed < $max_upload_time_in_sec) {
                        $offset = ftell($fp);
                        DUP_PRO_Log::trace("Timed out $max_upload_time_in_sec so saving off offset $offset");
                        break;
                    }
                    if ($server_load_delay !== 0) {
                        usleep($server_load_delay);
                    }
                }
                $ftp_upload_info->next_offset = $offset;
            } else {
                $message = sprintf(DUP_PRO_U::esc_html__('Error opening %1$ for cURL FTP'), $source_filepath);
                DUP_PRO_Log::trace($message);
                $ftp_upload_info->error_details = $message;
                $ftp_upload_info->next_offset   = $offset;
            }
            DUP_PRO_Log::trace("closing local file handle");
            fclose($fp);

            $source_filesize = filesize($source_filepath);
            if ($source_filesize <= $ftp_upload_info->next_offset) {
                $ftp_size = $this->get_file_size($remote_file_path);

                if (!$ftp_size && $ftp_size != $source_filesize) {
                    $error_message = sprintf(DUP_PRO_U::esc_html__('cURL FTP size mismatch for %1$s. Local file=%2$d bytes while server\'s file is %3$d bytes.'), $source_filepath, $source_filesize, $ftp_size);

                    DUP_PRO_Log::trace($error_message);
                    $ftp_upload_info->error_details = $error_message;
                    $ftp_upload_info->fatal_error   = true;

                    $this->delete($remote_file_path);
                } else {
                    DUP_PRO_Log::trace("FTP cURL upload file finish");
                    $ftp_upload_info->success = true;
                }
            } else {
                DUP_PRO_Log::trace("FTP cURL upload file is not finished yet..");
            }
        } catch (Exception $e) {
            $message                        = "Tried to upload file when connection wasn't opened. Info:" . $this->get_info() . ' Exception Error: ' . $e->getMessage();
            $ftp_upload_info->error_details = $message;
            DUP_PRO_Log::trace($message);
            $ftp_upload_info->next_offset = $offset;
        }
        DUP_PRO_Log::trace("FTP CURL CHUNK UPLOAD END");
        DUP_PRO_Log::trace("----------------------------");

        return $ftp_upload_info;
    }

    /**
     * upload file chunk with given data
     *
     * @param string  $file_chunk_data
     * @param string  $remote_file_path
     * @param integer $file_range_start
     *
     * @throws Exception upon error
     * @return boolean
     */
    private function upload_file_chunk($file_chunk_data, $remote_file_path, $file_range_start = 0)
    {
        $remote_file_path = $this->untrailing_left_slash_path($remote_file_path);

        if (($file_chunk_stream = fopen('php://temp', 'wb+')) === false) {
            return true;
        }

        if (($file_chunk_size = fwrite($file_chunk_stream, $file_chunk_data)) === false) {
            return true;
        }

        rewind($file_chunk_stream);
        $this->curl_call(
            sprintf('/%s/%s', $this->directory, $remote_file_path),
            array(
                CURLOPT_UPLOAD => true,
                CURLOPT_FTPAPPEND  => true,
                CURLOPT_INFILE => $file_chunk_stream,
                CURLOPT_INFILESIZE => $file_chunk_size,
            )
        );
        fclose($file_chunk_stream);
        return true;
    }

    /**
     * get remote file size
     *
     * @param string $remote_file_name
     *
     * @return bool|integer gives size of remote file or false on failure
     */
    public function get_file_size($remote_file_name)
    {
        $items = $this->raw_list_folder($this->directory);
        foreach ($items as $item) {
            if ($meta = preg_split('/\s+/', $item)) {
                if (!empty($meta[8]) && !empty($meta[4]) && $meta[8] == $remote_file_name) {
                    return (int) $meta[4];
                }
            }
        }
        return false;
    }

    /**
     * get information of the FTP
     *
     * @return string
     */
    public function get_info()
    {
        $ssl_string     = DUP_PRO_STR::boolToString($this->ssl);
        $passive_string = DUP_PRO_STR::boolToString($this->passive_mode);

        return sprintf(DUP_PRO_U::esc_html__('Server:%1$s Port:%2$d User:%3$s Directory:%4$s SSL:%5$s Passive:%6$s.'), $this->server, $this->port, $this->username, $this->directory, $ssl_string, $passive_string);
    }

    /**
     * get all files in remote folder
     *
     * @param string $folder_path remote folder path
     *
     * @throws Exception upon error
     * @return array
     */
    public function get_filelist($folder_path)
    {
        $items = $this->raw_list_folder($folder_path);
        $files = array();
        foreach ($items as $item) {
            if ($meta = preg_split('/\s+/', $item)) {
                if (!empty($meta[8])) {
                    $files[] = trim($meta[8]);
                }
            }
        }
        DUP_PRO_Log::trace("Get file list from cURL FTP: " . print_r($files, true));
        return $files;
    }

    /**
     * get all files meta information
     *
     * @param string $folder_path remote dir path
     *
     * @throws Exception upon error
     * @return array
     */
    private function raw_list_folder($folder_path)
    {
        $folder_path = $this->untrailing_left_slash_path($folder_path);
        $items       = $this->curl_call(
            sprintf('/%s/', $folder_path),
            array(
                CURLOPT_CUSTOMREQUEST => 'LIST -tlA',
            )
        );
        return explode("\n", $items);
    }

    /**
     * clean path with removing left slash
     *
     * @param string $path
     *
     * @return string
     */
    private function untrailing_left_slash_path($path)
    {
        return ltrim(preg_replace('/[\\\\\/]+/', '/', $path), '/');
    }
}
