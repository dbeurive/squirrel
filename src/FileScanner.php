<?php

/**
 * This file implements the service that selects the files to backup on the local filesystem.
 */

namespace dbeurive\Squirrel;

/**
 * Class FileScanner
 *
 * This class implements the service that selects the files to backup on the local filesystem.
 *
 * The names of the files to backup are made of two parts:
 *
 *     - A timestamp.
 *     - An ID.
 *
 * The basename is the file to backup is:
 *
 *     <timestamp>-<ID>
 *
 * For example:
 *
 *     20190101120000-file.tar.gz
 *
 * The timestamp format is "YYYYMMDDHHMMSS":
 *
 *     - YYYY: four digit representation for the year.
 *     - MM: two digit representation of the month (with leading zeros).
 *     - DD: two-digit day of the month (with leading zeros).
 *     - HH: two digit representation of the hour in 24-hour format (with leading zeros).
 *     - MM: two digit representation of the minute (with leading zeros).
 *     - SS: two digit representation of the second (with leading zeros).
 *
 * The ID is a string that represents the right-hand part of the file basename.
 *
 * @package dbeurive\Squirrel
 */

class FileScanner
{
    /** @var string The file ID.
     *  @see The the description of the class.
     */
    private $__fileId;
    /** @var string The local directory to scan for files to backup. */
    private $__localDirectory;

    /**
     * Filer constructor.
     * @param string $in_local_directory Path to the local directory to scan.
     * @param string $in_file_id ID of the file.
     */
    public function __construct($in_local_directory, $in_file_id) {
        $this->__localDirectory = $in_local_directory;
        $this->__fileId = $in_file_id;
    }

    /**
     * Return the list of files which names match the provided mask in the provided local directory.
     * @return array The method returns an array that contains the absolute paths to the files.
     * @throws Exception
     */
    public function getFiles() {
        if (false === $handle = @opendir($this->__localDirectory)) {
            $error = error_get_last();
            throw new Exception(sprintf('Cannot open the input directory "%s". %s', $this->__localDirectory, $error['message']));
        }

        $files = array();
        while (false !== ($entry = readdir($handle))) {

            if (is_dir($entry)) {
                continue;
            } elseif (1 !== preg_match('/^\d{14}\-/', $entry)) {
                continue;
            }

            $id = substr($entry, 15);
            if (0 === strcmp($id, $this->__fileId)) {
                $files[] = realpath(sprintf('%s%s%s', $this->__localDirectory, DIRECTORY_SEPARATOR, $entry));
            }
        }

        closedir($handle);
        return $files;
    }
}