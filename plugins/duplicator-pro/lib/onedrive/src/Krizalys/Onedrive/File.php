<?php

namespace DuplicatorPro\Krizalys\Onedrive;
defined("ABSPATH") or die("");
/**
 * @class File
 *
 * A File instance is a DriveItem instance referencing a OneDrive file. It may
 * have content but may not contain other OneDrive drive items.
 */
class File extends DriveItem
{
    public $sha1 = null;

    /**
     * Constructor.
     *
     * @param Client       $client  The Client instance owning this DriveItem
     *                              instance.
     * @param null|string  $id      The unique ID of the OneDrive drive item
     *                              referenced by this DriveItem instance.
     * @param array|object $options An array/object with one or more of the
     *                              following keys/properties:
     *                              - 'parent_id' (string) The unique ID of the
     *                              parent OneDrive folder of this drive item.
     *                              - 'name' (string) The name of this drive
     *                              item.
     *                              - 'description'  (string) The description of
     *                              this drive item. May be empty.
     *                              - 'size' (int) The size of this drive item,
     *                              in bytes.
     *                              - 'created_time' (string) The creation time,
     *                              as a RFC date/time.
     *                              - 'updated_time' (string) The last
     *                              modification time, as a RFC date/time.
     */
    public function __construct(Client $client, $id, $options = [])
    {
        parent::__construct($client, $id, $options);

        if(property_exists($options,'file') && property_exists($options->file,'hashes')){
            if(property_exists($options->file->hashes,'sha1Hash')){
                $this->sha1 = strtolower($options->file->hashes->sha1Hash);
            }else if(is_array($options->file->hashes) && array_key_exists('sha1Hash', $options->file->hashes)){
                $this->sha1 = strtolower($options->file->hashes['sha1Hash']);
            }
        }

    }

    /**
     * Fetches the content of the OneDrive file referenced by this File
     * instance.
     *
     * @param array $options Extra cURL options to apply.
     *
     * @return string The content of the OneDrive file referenced by this File
     *                instance.
     *
     * @todo Should somewhat return the content-type as well; this information
     *       is not disclosed by OneDrive.
     */
    public function fetchContent($options = [])
    {
        return $this->_client->apiGet("drive/items/".$this->_id . '/content', $options);
    }

    public function sha1CheckSum($file)
    {
        if($this->sha1){
            $local_sha1 = sha1_file($file);
            \DUP_PRO_Log::trace("{$local_sha1} <=> {$this->sha1}");

            // SnapCreek Custom code
            // sha1_file($file) may return false on failure
            // return $local_sha1 == $this->sha1;
            if (false !== $local_sha1) {
                return $local_sha1 == $this->sha1;
            } else {
                return true;
            }
        } elseif (is_null($this->sha1)) {
            return true;
        } else {
            throw new \Exception("Couldn't get the SHA1 hash of the uploaded file.",404);
        }
    }
}
