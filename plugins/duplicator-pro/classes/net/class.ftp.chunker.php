<?php

defined("ABSPATH") or die("");

use Duplicator\Utils\IncrementalStatusMessage;
use Duplicator\Utils\Net\FTPUploadInfo;

/**
 * Description of cls-ftp-chunker
 */
class DUP_PRO_FTP_Chunker
{
    public $server;
    public $port = 21;
    public $username;
    public $password;
    public $timeout_in_sec    = 90;
    public $ssl               = false;
    public $passive_mode      = false;
    public $echo              = false;
    public $ftp_connection_id = false;
    public $ftp_login_result  = false;
    public function __construct(
        $server,
        $port = 21,
        $username = 'anonymous',
        $password = 'anonymous@gmail.com',
        $timeout_in_sec = 15,
        $ssl = false,
        $passive_mode = false
    ) {
        $this->server         = $server;
        $this->port           = $port;
        $this->username       = $username;
        $this->password       = $password;
        $this->timeout_in_sec = $timeout_in_sec;
        $this->ssl            = $ssl;
        $this->passive_mode   = $passive_mode;
    }

    public function open($statusMsgsObj = null)
    {
        if ($statusMsgsObj === null) {
            $statusMsgsObj = new IncrementalStatusMessage();
        }
        $opened = false;
        $this->close();
        if ($this->ssl) {
            if (function_exists('ftp_ssl_connect')) {
                $statusMsgsObj->addMessage(__('Attempting to open ssl connection', 'duplicator-pro'));
                DUP_PRO_Log::trace("Attempting to open ssl connection");
                $this->ftp_connection_id = ftp_ssl_connect($this->server, $this->port, $this->timeout_in_sec);
            } else {
                $statusMsgsObj->addMessage(__('Attempted to open FTP SSL connection when OpenSSL hasn\'t been statically built into this PHP install', 'duplicator-pro'));
                DUP_PRO_Log::traceError("Attempted to open FTP SSL connection when OpenSSL hasn't been statically built into this PHP install");
                return false;
            }
        } else {
            $this->ftp_connection_id = @ftp_connect($this->server, $this->port, $this->timeout_in_sec);
        }

        if ($this->ftp_connection_id !== false) {
            $message = sprintf(__('Successfully FTP connected to %1$s:%2$d', 'duplicator-pro'), $this->server, $this->port);
            $statusMsgsObj->addMessage($message);
            DUP_PRO_Log::trace($message);
            $statusMsgsObj->addMessage(__('Attempting to log in with username and password', 'duplicator-pro'));
            $this->ftp_login_result = ftp_login($this->ftp_connection_id, $this->username, $this->password);
            if ($this->ftp_login_result) {
                $message = sprintf(__('Logged user %1$s into %2$s', 'duplicator-pro'), $this->username, $this->server);
                $statusMsgsObj->addMessage($message);
                DUP_PRO_Log::trace($message);
                if ($this->passive_mode) {
                    if (ftp_pasv($this->ftp_connection_id, true)) {
                            $statusMsgsObj->addMessage(__('Set connection to passive', 'duplicator-pro'));
                            DUP_PRO_Log::trace('Set connection to passive');
                            $opened = true;
                    } else {
                        $statusMsgsObj->addMessage(__('Couldn\'t set the connection into passive mode', 'duplicator-pro'));
                        DUP_PRO_Log::traceError("Couldn't set the connection into passive mode" . $this->get_info());
                    }
                } else {
                    $opened = true;
                }
            }

            if ($this->ftp_login_result == false) {
                $message = sprintf(__('Error logging in user %1$s, double check your username and password', 'duplicator-pro'), $this->username);
                $statusMsgsObj->addMessage($message);
                DUP_PRO_Log::trace($message);
            }
        } else {
            $message = sprintf(__('Error connecting to FTP server %1$s:%2$d', 'duplicator-pro'), $this->server, $this->port);
            $statusMsgsObj->addMessage($message);
            DUP_PRO_Log::trace($message);
        }

        return $opened;
    }

    public function create_directory($directory)
    {
        $parts = explode('/', $directory);
        // 2013/06/11/username

        $part_count = count($parts);

        //foreach($parts as $part)
        for ($pidx = 0; $pidx < $part_count; $pidx++) {
            $part = $parts[$pidx];
            if (trim($part) != '') {
                if (($pidx == 1) && (substr($directory, 0, 1) == '/')) {
                    $part = '/' . $part;
                }

                if (!@ftp_chdir($this->ftp_connection_id, $part)) {
                    DUP_PRO_Log::trace("Creating directory $part since it doesn't exist");
                    @ftp_mkdir($this->ftp_connection_id, $part);
                    @ftp_chdir($this->ftp_connection_id, $part);
                }
            }
        }

        return @ftp_chdir($this->ftp_connection_id, $directory);
    }

    public function directory_exists($directory)
    {
        return @ftp_chdir($this->ftp_connection_id, $directory);
    }

    public function close()
    {
        $closed = false;
        if ($this->ftp_connection_id !== false) {
            DUP_PRO_Log::traceObject("closing ftp connection", $this->ftp_connection_id);
            $closed = ftp_close($this->ftp_connection_id);
        } else {
            $closed = true;
        }

        return $closed;
    }

    public function is_opened()
    {
        return (($this->ftp_connection_id != false) && ($this->ftp_login_result == true));
    }

    public function upload_file($source_filepath, $storage_folder, $dest_filename = '')
    {
        $uploaded = false;
        if ($this->is_opened()) {
            $offset     = 0;
            $timeout    = 15;
            $start_time = time();
            while (!$uploaded) {
                DUP_PRO_Log::trace("file calling upload chunk offset=$offset");

                $ftp_upload_info = $this->upload_chunk($source_filepath, $storage_folder, $timeout, $offset, 0, $dest_filename);
                DUP_PRO_Log::trace("after upload chunk file");
                $offset = $ftp_upload_info->next_offset;
                if ($ftp_upload_info->success) {
                    DUP_PRO_Log::trace("1");
                    $uploaded = true;
                } elseif ($ftp_upload_info->error_details != null) {
                    DUP_PRO_Log::traceError("Error uploading $source_filepath to $storage_folder: $ftp_upload_info->error_details");
                    break;
                } elseif (time() - $start_time >= $timeout) {
                    DUP_PRO_Log::traceError("File transfer timed out");
                } else {
                    DUP_PRO_Log::trace("2");
                //   $offset = $ftp_upload_info->next_offset;
                }
            }
        } else {
            DUP_PRO_Log::traceError("Tried to upload file when connection wasn't opened. Info:" . $this->get_info());
        }

        return $uploaded;
    }

    /**
     * Uploads a chunk of a file to the FTP server
     *
     * @param string $source_filepath        The full path to the file to upload
     * @param string $storage_folder         The folder on the FTP server to upload to
     * @param int    $max_upload_time_in_sec The maximum time to spend uploading in seconds
     * @param int    $offset                 The offset in the file to start uploading from
     * @param int    $server_load_delay      The number of seconds to delay after each chunk upload
     * @param string $dest_filename          The name of the file on the FTP server.  If blank then the source file name is used
     *
     * @return FTPUploadInfo
     */
    public function upload_chunk($source_filepath, $storage_folder, $max_upload_time_in_sec = 15, $offset = 0, $server_load_delay = 0, $dest_filename = '')
    {
        DUP_PRO_Log::trace("FTP CHUNK OFFSET IN=$offset");

        $ftp_upload_info = new FTPUploadInfo();
        if ($this->is_opened()) {
            $start_time = time();
            DUP_PRO_Log::trace("call upload chunk");
            $local_file_handle = fopen($source_filepath, 'rb');
            if ($local_file_handle !== false) {
                if (fseek($local_file_handle, $offset) != 0) {
                    $error_message = sprintf(DUP_PRO_U::__('Couldnt seek to $offset in %1$s'), $source_filepath);
                    DUP_PRO_Log::trace($error_message);
                    $ftp_upload_info->error_details = $error_message;
                    $ftp_upload_info->next_offset   = $offset;
                    DUP_PRO_Log::trace("closing local file handle");
                    fclose($local_file_handle);
                    DUP_PRO_Log::trace("local file handle closed");
                    return $ftp_upload_info;
                }

                $filename      = !empty($dest_filename) ? $dest_filename : basename($source_filepath);
                $dest_filepath = "$storage_folder/$filename";

                if ($offset == 0) {
                    DUP_PRO_Log::trace("Deleting $dest_filepath before attempting to upload it");
    // Delete any file that may be there already
                    $this->delete($dest_filepath);
                }

                $time_passed = time() - $start_time;
        // $ret = ftp_nb_fput($this->ftp_connection_id, $dest_filepath, $local_file_handle, FTP_BINARY, $offset);
                if ($offset == 0) {
                    $ret = ftp_nb_fput($this->ftp_connection_id, $dest_filepath, $local_file_handle, FTP_BINARY);
                } else {
                    $ftp_size = ftp_size($this->ftp_connection_id, $dest_filepath);
                    $ret      = ftp_nb_fput($this->ftp_connection_id, $dest_filepath, $local_file_handle, FTP_BINARY, $ftp_size);
                }


                $next_offset = $offset;

                while (($ret == FTP_MOREDATA) && ($time_passed < $max_upload_time_in_sec)) {
                    if ($server_load_delay !== 0) {
                        usleep($server_load_delay);
                    }
                    $next_offset = ftell($local_file_handle);


                    $time_passed = time() - $start_time;
                    if ($time_passed < $max_upload_time_in_sec) {
                        $ret = ftp_nb_continue($this->ftp_connection_id);
                    }
                }

                if ($ret == FTP_FAILED) {
                    $error_message = sprintf(DUP_PRO_U::__('FTP failed during transfer of %1$s'), $source_filepath);
                    DUP_PRO_Log::trace($error_message);
                    $ftp_upload_info->error_details = $error_message;
                    $ftp_upload_info->next_offset   = $offset;
                } elseif ($ret == FTP_FINISHED) {
                    DUP_PRO_Log::trace("ftp finished with offset $next_offset");

                    $next_offset = $this->finish_file_chunk($local_file_handle, $next_offset);


                    if ($next_offset == -1) {
                        $ftp_upload_info->error_details = DUP_PRO_U::__('Problem finishing file chunk transfer');
                        $ftp_upload_info->fatal_error   = true;

                        $this->delete($dest_filepath);
                    } else {
                        $ftp_upload_info->next_offset = $next_offset;
                    }

                    $ftp_size   = ftp_size($this->ftp_connection_id, $dest_filepath);
                    $local_size = filesize($source_filepath);
                /*
                    error_log('$ftp_size -> '.$ftp_size);
                    error_log('$local_size -> '.$local_size);
                    */

                    // rsr temp
            //       $ftp_size = 1;

                    if (($ftp_size != -1) && ($ftp_size != $local_size)) {
                        $error_message = sprintf(DUP_PRO_U::__('FTP size mismatch for %1$s. Local file=%2$d bytes while server\'s file is %3$d bytes.'), $source_filepath, $local_size, $ftp_size);
                        DUP_PRO_Log::trace($error_message);
                        $ftp_upload_info->error_details = $error_message;
                        $ftp_upload_info->fatal_error   = true;
                        $this->delete($dest_filepath);
                    } else {
                        DUP_PRO_Log::trace("ftp size={$ftp_size}");
                        DUP_PRO_Log::trace(sprintf(DUP_PRO_U::__('FTP sizes match for %1$s. Local file=%2$d bytes while server\'s file is %3$d bytes.'), $source_filepath, $local_size, $ftp_size));
                        $ftp_upload_info->success = true;
                    }
                } else {
                    DUP_PRO_Log::trace("timed out so saving off offset $next_offset");


                    $next_offset = $this->finish_file_chunk($local_file_handle, $next_offset);

                    if ($next_offset == -1) {
                        $ftp_upload_info->error_details = DUP_PRO_U::__('Problem finishing file chunk transfer');
                        $ftp_upload_info->fatal_error   = true;
                    } else {
                        DUP_PRO_Log::trace("FTP CHUNK OFFSET OUT=$next_offset");
                        $ftp_upload_info->next_offset = $next_offset;
                    }


                     //$ret = FTP_MOREDATA
                }

                DUP_PRO_Log::trace("closing local file handle");
                fclose($local_file_handle);
                DUP_PRO_Log::trace("local file handle closed");
            } else {
                $message = sprintf(DUP_PRO_U::__('Error opening %1$ for FTP'), $source_filepath);
                DUP_PRO_Log::trace($message);
                $ftp_upload_info->error_details = $message;
            }
        } else {
            $message                        = "Tried to upload file when connection wasn't opened. Info:" . $this->get_info();
            $ftp_upload_info->error_details = $message;
            DUP_PRO_Log::trace($message);
        }

        // $this->eo('ftp_upload_info', $ftp_upload_info);
        return $ftp_upload_info;
    }

    private function finish_file_chunk($local_file_handle, $next_offset)
    {
        $matches = false;
        $tries   = 0;
        while (!$matches && ($tries < 2)) {
            $prev_offset = $next_offset;
            $next_offset = ftell($local_file_handle);
            $matches     = ($next_offset == $prev_offset);

            DUP_PRO_Log::trace("Finish file chunk next_offset=$next_offset prev_offset=$prev_offset");

            $tries++;
        // Want to give it a couple seconds after it matches to settle down
            usleep(2000000);
        }

        if ($matches) {
            return $next_offset;
        } else {
            DUP_PRO_Log::trace("Never was able to finish file chunk transfer");
            return -1;
        }
    }

    public function get_info()
    {
        $ssl_string     = DUP_PRO_STR::boolToString($this->ssl);
        $passive_string = DUP_PRO_STR::boolToString($this->passive_mode);
        return sprintf(DUP_PRO_U::__('Server:%1$s Port:%2$d User:%3$s SSL:%4$s Passive:%5$s'), $this->server, $this->port, $this->username, $ssl_string, $passive_string);
    }

    public function get_filelist($directory = '.')
    {
        $items = array();
        if ($this->is_opened()) {
            $parameters = "$directory";
            $items      =  ftp_nlist($this->ftp_connection_id, $parameters);

            for ($i = 0; $i < count($items); $i++) {
                $items[$i] = basename($items[$i]);
            }
        } else {
            $items   = false;
            $message = "Tried to upload file when connection wasn't opened. Info:" . $this->get_info();
            DUP_PRO_Log::trace($message);
        }

        return $items;
    }

    public function delete($file_path)
    {
        $ret_val = false;
        if ($this->is_opened()) {
            $ret_val = @ftp_delete($this->ftp_connection_id, $file_path);
            if ($ret_val) {
                DUP_PRO_Log::trace("Successfully deleted $file_path from " . $this->server);
            }
        } else {
            DUP_PRO_Log::trace("Tried to upload file when connection wasn't opened. Info:" . $this->get_info());
        }

        return $ret_val;
    }

    // Using straight up downloading right now - not using chunking
    public function download_file($remote_source_filepath, $local, $is_local_directory = true)
    {
        $ret_val = false;
        if ($is_local_directory) {
            $filename            = basename($remote_source_filepath);
            $local_dest_filepath = "$local/$filename";
        } else {
            $local_dest_filepath = $local;
        }

        $ret_val = ftp_get($this->ftp_connection_id, $local_dest_filepath, $remote_source_filepath, FTP_BINARY);
        if ($ret_val == false) {
            DUP_PRO_Log::trace("Error downloading $remote_source_filepath into $local_dest_filepath. FTP Info: " . $this->get_info());
        }

        return $ret_val;
    }
}
