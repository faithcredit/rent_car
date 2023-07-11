<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snapcreek LLC
 */

namespace Duplicator\Utils;

use DUP_PRO_Log;
use Duplicator\Utils\Exceptions\ChunkingTimeoutException;
use Exception;
use VendorDuplicator\phpseclib\Crypt\RSA;
use VendorDuplicator\phpseclib\Net\SFTP;

/**
 * SFTP class adapter
 */
class SFTPAdapter
{
    /** @var string */
    protected $server = '';
    /** @var int */
    protected $port = 22;
    /** @var string */
    protected $username = '';
    /** @var string */
    protected $password = '';
    /** @var string */
    protected $privateKey = '';
    /** @var string */
    protected $privateKeyPassword = '';
    /** @var ?IncrementalStatusMessage */
    protected $messages = null;
    /** @var float */
    private $timeLimit = -1.0;
    /** @var float */
    private $timeStart = -1.0;
    /** @var SFTP */
    private $sftp = null;

    /**
     * Class contructor
     *
     * @param string $server             hosting domain or ip address
     * @param int    $port               hosting port
     * @param string $username           hosting username
     * @param string $password           hosting password
     * @param string $privateKey         hosting private key
     * @param string $privateKeyPassword hosting private key password
     */
    public function __construct(
        $server,
        $port = 22,
        $username = '',
        $password = '',
        $privateKey = '',
        $privateKeyPassword = ''
    ) {
        if (strlen($server) == 0) {
            throw new Exception(__('Server name is required to make sftp connection', 'duplicator-pro'));
        }

        if ($port <= 0) {
            throw new Exception(__('Server port is required to make sftp connection', 'duplicator-pro'));
        }

        if (strlen($username) == 0) {
            throw new Exception(__('Username is required to make sftp connection', 'duplicator-pro'));
        }

        if (strlen($password) == 0 && strlen($privateKey) == 0) {
            throw new Exception(__(
                'You should provide either sftp user pasword or the private key to make sftp connection',
                'duplicator-pro'
            ));
        }

        if (strlen($privateKey) > 0 && strlen($privateKeyPassword) == 0) {
            throw new Exception(__(
                'You should provide private key password',
                'duplicator-pro'
            ));
        }

        $this->server             = $server;
        $this->port               = $port;
        $this->username           = $username;
        $this->password           = $password;
        $this->privateKey         = $privateKey;
        $this->privateKeyPassword = $privateKeyPassword;

        $this->sftp = new SFTP($this->server, $this->port);
    }

    /**
     * Class destructor
     *
     * @return void
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Connect to an SFTP Server
     *
     * @return bool true on success, false on failure
     */
    public function connect()
    {
        if ($this->messages === null) {
            $this->messages = new IncrementalStatusMessage();
        }

        $this->disconnect();

        if (!empty($this->privateKey)) {
            $key = $this->getPrivateKey();
        } else {
            $key = null;
        }

        $this->messages->addMessage(sprintf(
            __('Connecting to SFTP server %1$s:%2$d', 'duplicator-pro'),
            $this->server,
            $this->port
        ));
        DUP_PRO_Log::trace("Connect to SFTP server " . $this->server . ':' . $this->port);
        $this->messages->addMessage(sprintf(__('Attempting to login to SFTP server %1$s', 'duplicator-pro'), $this->server));
        DUP_PRO_Log::trace("Attempting to login to SFTP server " . $this->server);

        if (!is_null($key)) {
            $this->messages->addMessage(__('Login to SFTP using private key', 'duplicator-pro'));
            DUP_PRO_Log::trace("Login to SFTP using private key");
            if ($this->sftp->login($this->username, $key)) {
                $this->messages->addMessage(__('Successfully connected to server using private key', 'duplicator-pro'));
                DUP_PRO_Log::infoTrace('Successfully connected to server using private key');
            } else {
                $this->messages->addMessage(__('Error opening SFTP connection using private key', 'duplicator-pro'));
                return false;
            }
        } else {
            DUP_PRO_Log::trace("Login to SFTP");
            if ($this->sftp->login($this->username, $this->password)) {
                $this->messages->addMessage(__('Successfully connected to server using password', 'duplicator-pro'));
                DUP_PRO_Log::infoTrace('Successfully connected to server using password');
            } else {
                $this->messages->addMessage(__('Error opening SFTP connection using password', 'duplicator-pro'));
                return false;
            }
        }
        return true;
    }

    /**
     * Disconnect SFTP connection
     *
     * @return void
     */
    protected function disconnect()
    {
        if (!$this->sftp->isConnected()) {
            return;
        }
        DUP_PRO_Log::infoTrace("Disconnect SFTP Connection");
        $this->sftp->disconnect();
    }

    /**
     * Set an SFTP Private Key
     *
     * @return RSA return key object or false
     */
    protected function getPrivateKey()
    {
        if (strlen($this->privateKey) == 0) {
            throw new Exception('Private key is null');
        }

        $key = new RSA();
        if (!empty($this->privateKeyPassword)) {
            DUP_PRO_Log::trace("Get Private Key Object with Password");
            $key->setPassword($this->privateKeyPassword);
        }
        DUP_PRO_Log::trace("Get Private Object Key");
        $key->loadKey($this->privateKey);
        DUP_PRO_Log::trace("Private Key Loaded");
        return $key;
    }

    /**
     * Checks whether a file or directory exists
     *
     * @param string $path file path
     *
     * @return bool
     */
    public function fileExists($path)
    {
        return $this->sftp->file_exists($path);
    }

    /**
     * Gets file size
     *
     * @param string $path file path
     *
     * @return mixed
     */
    public function filesize($path)
    {
        return $this->sftp->filesize($path);
    }

    /**
     * Deletes a file on the SFTP server.
     *
     * @param string $path      file path
     * @param bool   $recursive true delete recurively
     *
     * @return bool
     */
    public function delete($path, $recursive = true)
    {
        return $this->sftp->delete($path, $recursive);
    }

    /**
     * Returns a list of files in the given directory
     *
     * @param string $dir       dir to stan
     * @param bool   $recursive if true scan recurively
     *
     * @return mixed
     */
    public function filesList($dir = '.', $recursive = false)
    {
        return $this->sftp->nlist($dir, $recursive);
    }

    /**
     * Uploads a file to the SFTP server.
     *
     * @param string          $remote_file remote file name
     * @param string|resource $data        local file data
     * @param int             $offset      offset from which data transfer will continue (not used if -1)
     *
     * @return bool
     */
    public function put($remote_file, $data, $offset = -1)
    {
        return $this->sftp->put(
            $remote_file,
            $data,
            SFTP::SOURCE_LOCAL_FILE | SFTP::RESUME,
            $offset,
            $offset,
            array($this, 'uploadProgress')
        );
    }

    /**
     * Create directory recursively
     *
     * @param string $storagePath storage directory path
     *
     * @return string return the directory path
     */
    public function mkDirRecursive($storagePath = '')
    {
        if (strlen($storagePath) == 0) {
            throw new Exception('Storage Folder is null.');
        }

        if (!$this->sftp->isConnected()) {
            throw new Exception('You must connect to SFTP before making directory.');
        }

        $storageFolders = explode("/", $storagePath);
        $path           = '';
        foreach ($storageFolders as $dir) {
            $path = $path . '/' . $dir;
            if (!$this->sftp->file_exists($path)) {
                if (!$this->sftp->mkdir($path)) {
                    $errorMessage = 'Directory not created ' . $path . '. Make sure you have write permissions on your SFTP server.';
                    throw new Exception($errorMessage);
                }
            }
        }
        return $storagePath;
    }

    /**
     * Method that should be used to start the chunking count before the files get sent
     *
     * @param float $timeLimit Time in seeconds to limit the chunked upload to
     *
     * @return void
     */
    public function startChunkingTimer($timeLimit = -1)
    {
        $this->timeStart = microtime(true);
        $this->timeLimit = $timeLimit;
    }

    /**
     * Call back that can be used for phpseclib to exit the process during chunking
     *
     * @param float $sent size of the sent chunf of the file
     *
     * @return bool
     */
    public function uploadProgress($sent)
    {
        if ($this->timeLimit > -1) {
            if (microtime(true) - $this->timeStart >= $this->timeLimit) {
                throw new ChunkingTimeoutException("Time Limit Was Reached");
            }
        }
        return true;
    }

    /**
     * @return callable
     */
    public function getCallProgressCallback()
    {
        return array($this, 'uploadProgress');
    }

    /**
     * St incremental messags manager
     *
     * @param IncrementalStatusMessage $messages messages
     *
     * @return void
     */
    public function setMessages(IncrementalStatusMessage $messages)
    {
        $this->messages = $messages;
    }
}
