<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax\FileTransfer;

use DUP_PRO_Package_Importer;
use DUP_PRO_U;
use VendorDuplicator\WpOrg\Requests\Requests;
use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Utils\HTTP\DynamicChunkRequests;
use Exception;

class ImportUpload
{
    const P2P_TIMEOUT = 15; // seconds, can be a float number

    const MODE_UPLOAD_LOCAL    = 'upload'; // Upload archive from local PC
    const MODE_DOWNLOAD_REMOTE = 'remote'; // Download archive from remote URL
    const MODE_UPLOADED        = 'uploaded'; // Archive is already uploaded

    const STATUS_CHUNKING = 'chunking';
    const STATUS_COMPLETE = 'complete';

    const INIT_REMOTE_URL_DATA_RETRIALS = 2;

    /** @var string */
    protected $mode = '';
    /** @var string*/
    protected $status = self::STATUS_CHUNKING;
    /** @var bool */
    protected $isImportable = false;
    /** @var string */
    protected $archivePath = '';
    /** @var string  */
    protected $installerPageLink = '';
    /** @var string  */
    protected $htmlDetails = '';
    /** @var string  */
    protected $created = '';
    /** @var string  */
    protected $invalidMessage = '';
    /** @var int */
    protected $archiveSize = -1;
    /** @var false|DynamicChunkRequests */
    protected $remoteChunk = false;

    /**
     * Class constructor
     *
     * @param string $mode        upload mode
     * @param string $archivePath archive path, use in mode uploaded
     */
    public function __construct($mode, $archivePath = '')
    {
        switch ($mode) {
            case self::MODE_UPLOAD_LOCAL:
            case self::MODE_DOWNLOAD_REMOTE:
                break;
            case self::MODE_UPLOADED:
                if (strlen($archivePath) == 0 || !is_file($archivePath)) {
                    throw new Exception('Invalid archive');
                }
                $this->archivePath = $archivePath;
                break;
            default:
                throw new Exception('Invalid transfer mode');
        }
        $this->mode = $mode;

        add_filter('duplicator_pro_remote_download_data', array(RemoteDownloadCustom::class, 'dropboxRemoteUrlFilter'));
        add_filter('duplicator_pro_remote_download_data', array(RemoteDownloadCustom::class, 'gDriveRemoteUrlFilter'));
        add_filter('duplicator_pro_remote_download_data', array(RemoteDownloadCustom::class, 'oneDriveRemoteUrlFilter'));
    }

    /**
     * Exec upload and return result
     *
     * @return mixed[]
     */
    public function exec()
    {
        if (!file_exists(DUPLICATOR_PRO_PATH_IMPORTS)) {
            SnapIO::mkdir(DUPLICATOR_PRO_PATH_IMPORTS, 0755, true);
        }

        switch ($this->mode) {
            case self::MODE_UPLOAD_LOCAL:
                $this->uploadLocal();
                break;
            case self::MODE_DOWNLOAD_REMOTE:
                $this->remoteDownload();
                break;
            case self::MODE_UPLOADED:
                $this->setCompleteData();
                break;
        }

        return JsonSerialize::serializeToData($this, JsonSerialize::JSON_SKIP_CLASS_NAME);
    }

    /**
     * Upload in local mode
     *
     * @return void
     */
    protected function uploadLocal()
    {
        $archiveName = isset($_FILES["file"]["name"]) ? sanitize_text_field($_FILES["file"]["name"]) : null;
        if (!preg_match(DUPLICATOR_PRO_ARCHIVE_REGEX_PATTERN, $archiveName)) {
            throw new Exception(__("Invalid archive file name. Please use the valid archive file!", 'duplicator-pro'));
        }
        $archiveNameTemp = isset($_FILES["file"]["tmp_name"]) ? sanitize_text_field($_FILES["file"]["tmp_name"]) : null;

        $currentChunk = filter_input(INPUT_POST, 'chunk', FILTER_VALIDATE_INT, array('options' => array('default' => false)));
        $numChunks    = filter_input(INPUT_POST, 'chunks', FILTER_VALIDATE_INT, array('options' => array('default' => false)));

        $this->archivePath = DUPLICATOR_PRO_PATH_IMPORTS . '/' . $archiveName;

        if ($numChunks !== false) {
            //CHUNK MODE
            $archivePart = $this->getArchivePart();

            // Clean last upload part leaved as it is (The situation in which user navigate to another url while uploading archive file path)
            if ($currentChunk === 0 && file_exists($archivePart)) {
                @unlink($archivePart);
            }

            SnapIO::appendFileToFile($archiveNameTemp, $archivePart);

            if ($currentChunk == ($numChunks - 1)) {
                if (SnapIO::rename($archivePart, $this->archivePath, true) === false) {
                    throw new Exception('Can\'t rename file part to file');
                }
                $this->setCompleteData();
            } else {
                $this->status = self::STATUS_CHUNKING;
            }
        } else {
            // DIRECT MODE
            if (move_uploaded_file($archiveNameTemp, $this->archivePath) === false) {
                throw new Exception(DUP_PRO_U::esc_html__('Can\'t rename file part to file'));
            }
            $this->setCompleteData();
        }
    }

    /**
     * Download archive from remote URL
     *
     * @return void
     */
    protected function remoteDownload()
    {
        $startingRemoteURL = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL, array('options' => array('default' => false)));
        if ($startingRemoteURL == false) {
            throw new Exception('Remote URL must be a valid URL');
        }

        $this->remoteChunk = self::getRestoreChunkDownload();
        if (!($this->remoteChunk instanceof DynamicChunkRequests)) {
            $this->remoteChunk = new DynamicChunkRequests();
            $this->remoteChunk->setExtraData('retrials', 0);
            $this->remoteChunk->setExtraData('maxRetrials', 0); // Default value 0, but particular storage can change it
            $this->remoteChunk->setExtraData('startedDownload', false);
        }

        if (!$this->remoteChunk->getExtraData('startedDownload')) {
            $downloadData = array();
            try {
                $downloadData = apply_filters('duplicator_pro_remote_download_data', [
                    'url' => $startingRemoteURL,
                    'archiveName' => basename(SnapURL::parseUrl($startingRemoteURL, PHP_URL_PATH)),
                    'chunkTime' => DynamicChunkRequests::DEFAULT_CHUNK_TIME,
                    'maxRetrials' => 0
                ]);
            } catch (Exception $e) {
                $retrials = $this->remoteChunk->getExtraData('retrials');
                if ($retrials <= self::INIT_REMOTE_URL_DATA_RETRIALS) {
                    $this->remoteChunk->setExtraData('retrials', $retrials + 1);
                    return;
                }
                throw $e;
            }

            if (!preg_match(DUPLICATOR_PRO_ARCHIVE_REGEX_PATTERN, $downloadData['archiveName'])) {
                throw new Exception(__("Invalid archive file name. Please use the valid archive file!", 'duplicator-pro'));
            }

            $this->remoteChunk->setDownloadUrl($downloadData['url']);
            $this->remoteChunk->setChunkTime($downloadData['chunkTime']);
            $this->remoteChunk->setExtraData('archiveName', $downloadData['archiveName']);
            $this->remoteChunk->setExtraData('startingRemoteURL', $startingRemoteURL);
            $this->remoteChunk->setExtraData('retrials', 0); // Reset retrials
            $this->remoteChunk->setExtraData('maxRetrials', $downloadData['maxRetrials']);
            $this->remoteChunk->setExtraData('startedDownload', true);

            $this->archivePath = DUPLICATOR_PRO_PATH_IMPORTS . '/' . $downloadData['archiveName'];
            $archivePart       = $this->getArchivePart();
            if (file_exists($archivePart)) {
                unlink($archivePart);
            }
        } else {
            $this->archivePath = DUPLICATOR_PRO_PATH_IMPORTS . '/' . $this->remoteChunk->getExtraData('archiveName');
            $archivePart       = $this->getArchivePart();

            if (!file_exists($archivePart)) {
                throw new Exception('Can\t resume the download, archive part file don\'t exists');
            }

            if ($this->remoteChunk->getExtraData('startingRemoteURL') !== $startingRemoteURL) {
                throw new Exception('Input params not valid');
            }
        }

        $startTime = microtime(true);
        do {
            $tmpFile = tempnam(DUPLICATOR_PRO_PATH_IMPORTS, 'tmp_p2p_part_');

            $options = array();
            if (session_id() == "") {
                session_start();
            }
            if (isset($_SESSION["duplicator_pro_import_from_link_cookies"])) {
                $options['cookies'] = $_SESSION["duplicator_pro_import_from_link_cookies"];
            }
            $options['filename']   = $tmpFile;
            $options['verify']     = false;
            $options['verifyname'] = false;

            $response = $this->remoteChunk->request(
                array(),
                array(),
                Requests::GET,
                $options,
                false
            );

            if ($response->success == false) {
                $retrials    = $this->remoteChunk->getExtraData('retrials');
                $maxRetrials = $this->remoteChunk->getExtraData('maxRetrials');

                if ($retrials <= $maxRetrials) {
                    $this->remoteChunk->setExtraData('retrials', $retrials + 1);
                    break;
                } else {
                    throw new Exception("Remote URL request on " . $this->remoteChunk->getExtraData('startingRemoteURL') . " failed");
                }
            }

            SnapIO::appendFileToFile($tmpFile, $archivePart);
            // Preserve cookies for use in the next request
            $_SESSION["duplicator_pro_import_from_link_cookies"] = property_exists($response, 'cookies') ? $response->cookies : null;
            $this->remoteChunk->setExtraData('retrials', 0); // Reset retrials
            $deltaTime = microtime(true) - $startTime;
        } while (!$this->remoteChunk->isComplete() && $deltaTime < self::P2P_TIMEOUT);

        if ($this->remoteChunk->isComplete()) {
            if (SnapIO::rename($archivePart, $this->archivePath, true) === false) {
                throw new Exception('Can\'t rename file part to file');
            }
            $this->setCompleteData();
        } else {
            $this->status = self::STATUS_CHUNKING;
        }
    }

    /**
     * Return restore chunk download object
     *
     * @return false|DynamicChunkRequests false if restore isn't set
     */
    protected static function getRestoreChunkDownload()
    {
        try {
            $restoreDownload = (isset($_POST['restoreDownload']) ? SnapUtil::sanitizeNSCharsNewline($_POST['restoreDownload']) : '');
            $result          = false;
            if (strlen($restoreDownload) === 0) {
                return $result;
            }

            $restoreDownload = stripslashes($restoreDownload);
            $result          = JsonSerialize::unserializeToObj($restoreDownload, DynamicChunkRequests::class);
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * Get archvie part full path
     *
     * @return string
     */
    protected function getArchivePart()
    {
        return $this->archivePath . '.part';
    }

    /**
     * Set completa package upload data
     *
     * @return void
     */
    public function setCompleteData()
    {
        $this->status      = self::STATUS_COMPLETE;
        $this->remoteChunk = false;

        try {
            $importObj = new DUP_PRO_Package_Importer($this->archivePath);
            $importObj->cleanFolder();

            $this->isImportable      = $importObj->isImportable();
            $this->installerPageLink = $importObj->getInstallerPageLink();
            $this->htmlDetails       = $importObj->getHtmlDetails(false);
            $this->created           = $importObj->getCreated();
            if (($this->archiveSize = filesize($this->archivePath)) === false) {
                $this->archiveSize = -1;
            }
        } catch (Exception $e) {
            $this->isImportable      = false;
            $this->installerPageLink = '';
            $this->htmlDetails       = sprintf(DUP_PRO_U::esc_html__('Problem on import, message: %s'), $e->getMessage());
            $this->created           =  '';
            $this->invalidMessage    = $e->getMessage();
        }
    }
}
