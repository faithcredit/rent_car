<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Package\Storage;

use DUP_PRO_Log;
use DUP_PRO_U;
use Duplicator\Core\CapMng;
use Exception;

class LocalStorage extends AbstractStorage
{
    /**
     * @param mixed[] $inputData input data that's not filtered yet
     */
    public function __construct($inputData)
    {
        parent::__construct($inputData);
    }

    /**
     * @return bool Method for validating the cases that could cause failures for the file storage
     */
    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }
        $this->addStatusMessage(sprintf(__('Checking if directory exists "%1$s"', 'duplicator-pro'), $this->getStoragePath()));
        if (!is_dir($this->getStoragePath())) {
            $this->addStatusMessage(sprintf(__(
                'The storage path does not exists "%1$s"',
                'duplicator-pro'
            ), $this->getStoragePath()));
            $this->setMessage('The storage path does not exists');
            return false;
        }
        $this->addStatusMessage(sprintf(__('Checking if the temporary file exists "%1$s"', 'duplicator-pro'), $this->getTestFileName()));
        if (file_exists($this->getFullTestFilePath())) {
            $this->addStatusMessage(sprintf(__(
                'File with the temporary file name already exists, please try again "%1$s"',
                'duplicator-pro'
            ), $this->getTestFileName()));
            $this->setMessage('File with the temporary file name already exists, please try again');
            return false;
        }
        return true;
    }

    /**
     * @return bool method for creating a test file on the local storage
     */
    public function createTestFile()
    {
        $this->addStatusMessage(__('Attempting to create the temporary file', 'duplicator-pro'));
        $handle = fopen($this->getFullTestFilePath(), 'x+');
        if (!fclose($handle)) {
            $this->addStatusMessage(__('There was a problem when storing the temporary file', 'duplicator-pro'));
            return false;
        }
        return true;
    }

    /**
     * @return bool method for executing a test deletion of a test file
     */
    public function deleteTestFile()
    {
        $this->addStatusMessage(__('Attempting to delete the temporary file', 'duplicator-pro'));
        if (!unlink($this->getStoragePath() . '/' . $this->getTestFileName())) {
            $this->addStatusMessage(__(
                'There was a problem when deleting the temporary file',
                'duplicator-pro'
            ));
            return false;
        }
        return true;
    }

    /**
     * @return self method for executing a test of the storage
     */
    public function testStorage()
    {
        try {
            CapMng::can(CapMng::CAP_STORAGE);
            if (!$this->isValid()) {
                return $this;
            }
            if (!$this->createTestFile()) {
                $this->addStatusMessage(
                    __(
                        'There was a problem when storing the temporary file',
                        'duplicator-pro'
                    )
                );
                $this->setMessage('There was a problem storing the temporary file on local storage.');
                return $this;
            }
            if (!$this->deleteTestFile()) {
                $this->addStatusMessage(__('There was a problem when deleting the temporary', 'duplicator-pro'));
                $this->setMessage('There was a problem deleting the temporary file on local storage.');
                return $this;
            }
            $this->addStatusMessage(__('Successfully stored and deleted file', 'duplicator-pro'));
            $this->setMessage('Successfully stored and deleted file');
            $this->setSuccessStatus();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            DUP_PRO_Log::trace($errorMessage);
            $this->setMessage($errorMessage);
        }
        return $this;
    }
}
