<?php

namespace dbeurive\Squirrel;

/**
 * Class BackupStatus
 *
 * This class implements a service that keeps track of the status of backup operations.
 *
 * @package dbeurive\Squirrel
 */
class BackupStatusManager
{
    private $__status = array();

    /**
     * BackupStatus constructor.
     * @param array $in_files List of files scheduled for backup.
     */
    public function __construct(array $in_files) {
        foreach ($in_files as $_file) {
            $this->__status[basename($_file)] = true;
        }
    }

    /**
     * Declare that a file could not be backed up.
     * @param string $in_file Name of the file.
     */
    public function setError($in_file) {
        $this->__status[basename($in_file)] = false;
    }

    /**
     * Declare that a given list of files could not be backed up.
     * @param array $in_files List of files names.
     */
    public function setErrors(array $in_files) {
        foreach ($in_files as $_file) {
            $this->__status[basename($_file)] = false;
        }
    }

    /**
     * Test whether a file has been successfully backed up or not.
     * @param string $in_local_file Name of the file.
     * @return bool If the file has been successfully backed up, then the method returns the value true.
     *         Otherwise, it returns the value false.
     * @throws \Exception
     */
    public function isSuccess($in_local_file) {
        $in_local_file = basename($in_local_file);
        if (! array_key_exists($in_local_file, $this->__status)) {
            throw new \Exception(sprintf('Unexpected error: the file "%s" is unknown.', $in_local_file));
        }
        return $this->__status[$in_local_file];
    }

    /**
     * Test whether the backup of a file produced an error or not.
     * @param string $in_local_file Name of the file.
     * @return bool If the file has been successfully backed up, then the method returns the value false.
     *         Otherwise, it returns the value true.
     * @throws \Exception
     */
    public function isError($in_local_file) {
        return ! $this->isSuccess($in_local_file);
    }
}