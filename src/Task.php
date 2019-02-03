<?php

/**
 * This file implements a task.
 */

namespace dbeurive\Squirrel;

/**
 * Class Task
 *
 * This class represents a task.
 *
 * @package dbeurive\Squirrel
 */

class Task
{
    /** @var string Name of the task. */
    private $__name;
    /** @var string Path to the local directory where the files to backup are stored. */
    private $__localInputDirectory;
    /** @var string Path to the local directory where to move the backed up files. */
    private $__localDoneDirectory;
    /** @var string ID of the file to backup. */
    private $__id;
    /** @var int Number of backups to keep. */
    private $__keepCount;
    /** @var array Associative array that associates destination names to destination objects.
             Keys are destination names.
             Values are destination objects.
     */
    private $__destinations;
    /** @var string Name of the handler to execute when the backup process is successful. */
    private $__onError;
    /** @var string Name of the handler to execute when the backup process failed. */
    private $__onSuccess;

    /**
     * Task constructor.
     * @param string $in_name Name of the task.
     * @param string $in_local_input_path Path to the local directory where the files to backup are stored.
     * @param string $in_local_done_path Path to the local directory where to move the backed up files.
     * @param string $in_file_id ID of the files to back up.
     * @param int $in_max_count Maximum backups to keep.
     * @param array $in_destinations List of destinations for this task.
     * @param string $in_on_success Name of the handler to execute when the backup process is successful.
     * @param string $in_on_error Name of the handler to execute when the backup process failed.
     */
    public function __construct($in_name, $in_local_input_path, $in_local_done_path, $in_file_id, $in_max_count, array $in_destinations, $in_on_success, $in_on_error)
    {
        $this->__name = $in_name;
        $this->__localInputDirectory = $in_local_input_path;
        $this->__localDoneDirectory = $in_local_done_path;
        $this->__id = $in_file_id;
        $this->__keepCount = $in_max_count;
        $this->__destinations = $in_destinations;
        $this->__onSuccess = $in_on_success;
        $this->__onError = $in_on_error;
    }

    /**
     * Move a file to the local directory where to move the backed up files.
     * @param string $in_file_name Name of the file to move.
     * @param string $out_message Reference to a string used to store an error message.
     * @return bool Upon successful completion, the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function done($in_file_name, &$out_message) {
        $done_file = sprintf('%s%s%s',
            $this->__localDoneDirectory,
            DIRECTORY_SEPARATOR,
            basename($in_file_name)
        );

        if (false === @rename($in_file_name, $done_file)) {
            $error = error_get_last();
            $out_message = sprintf('Cannot move "%s" to "%s". %s', $in_file_name, $done_file, $error['message']);
            return false;
        }
        return true;
    }

    /**
     * Return the local input directory.
     * @return string The local input directory.
     */
    public function getLocalInputDirectory() {
        return $this->__localInputDirectory;
    }

    /**
     * Return the local directory used to store files when they are successfully transferred to the remote host.
     * @return string The local directory used to store files when they are successfully transferred to the remote host.
     */
    public function getLocalDoneDirectory() {
        return $this->__localDoneDirectory;
    }

    /**
     * Return the ID of the file to backup.
     * @return String The ID of the file to backup.
     */
    public function getFileId() {
        return $this->__id;
    }

    /**
     * Return the number of backup files to keep on the remote host.
     * @return int The number of backup files to keep on the remote host.
     */
    public function getKeepCount() {
        return $this->__keepCount;
    }

    /**
     * Return the list of the destinations where to store the backup files.
     * @return array The list of the destinations where to store the backup files.
     *         Each element of the returned array is an instance of the class Destination.
     */
    public function getDestinations() {
        return $this->__destinations;
    }

    /**
     * Return the list of destinations names.
     * @return array The method returns the names of the destinations.
     */
    public function getDestinationsNames() {
        return array_keys($this->__destinations);
    }

    /**
     * Return the command to execute upon a successful transfer.
     * @return string The command to execute upon a successful transfer.
     */
    public function getOnError() {
        return $this->__onError;
    }

    /**
     * Return the command to execute upon a erroneous transfer.
     * @return string The command to execute upon a erroneous transfer.
     */
    public function getOnSuccess() {
        return $this->__onSuccess;
    }

    /**
     * Convert the current instance into a string.
     * @return mixed
     */
    public function __toString() {
        return $this->__name;
    }
}