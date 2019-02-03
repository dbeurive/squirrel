<?php

/**
 * This file implements the representation of a file to backup.
 */

namespace dbeurive\Squirrel;

/**
 * Class File
 *
 * This class represents a file to backup.
 *
 * A file is represented by the following elements:
 *
 *     - A timestamp.
 *     - An ID.
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
 * Please not that the basename of the file is:
 *
 *     <timestamp>-<ID>
 *
 * For example, let's consider the following basename:
 *
 *     20190101120000-file.tar.gz
 *
 *     - The timestamp is "20190101120000".
 *     - The ID is "file.tar.gz".
 *
 * @package dbeurive\Squirrel
 */

class File
{
    const TIMESTAMP_LENGTH = 14;

    /** @var Timestamp */
    private $__timestamp;
    /** @var string */
    private $__id;

    /**
     * File constructor.
     * @param Timestamp $in_timestamp The file timestamp.
     * @param string $in_id The file ID.
     */
    public function __construct(Timestamp $in_timestamp, $in_id) {
        $this->__timestamp = $in_timestamp;
        $this->__id = $in_id;
    }

    /**
     * Return the file timestamp.
     * @return Timestamp The file timestamp.
     */
    public function getTimestamp() {
        return $this->__timestamp;
    }

    /**
     * Return the file ID
     * @return string The file ID.
     */
    public function getId() {
        return $this->__id;
    }

    /**
     * Return the basename of the file.
     * @return string The basename of the file.
     */
    public function getBasename() {
        return sprintf('%s-%s', $this->__timestamp, $this->__id);
    }

    /**
     * Test whether a given text represents a basename or not.
     * @param string $in_name The basename to test.
     * @return bool If the given text represents a basename, then the function returns the value true.
     *         Otherwise, it returns the value false.
     */
    static public function isBasename($in_name) {
        $mask = sprintf('/^\d{%d}\-.+$/', self::TIMESTAMP_LENGTH);
        return 1 === preg_match($mask, $in_name);
    }

    /**
     * Return the timestamp from a given basename.
     * @param string $in_basename The basename.
     * @return Timestamp The method returns the timestamp.
     * @throws Exception
     */
    static public function getTimestampFromBasename($in_basename) {
        if (! self::isBasename($in_basename)) {
            throw new Exception(sprintf('Invalid basename "%s".', $in_basename));
        }
        $t = (string)substr($in_basename, 0, self::TIMESTAMP_LENGTH);
        return new Timestamp($t);
    }

    /**
     * Extract the ID from a given basename.
     * @param string $in_basename The base.
     * @return string The method returns the ID.
     * @throws Exception
     */
    static public function getIdFromBasename($in_basename) {
        if (! self::isBasename($in_basename)) {
            throw new Exception(sprintf('Invalid basename "%s".', $in_basename));
        }
        return substr($in_basename, self::TIMESTAMP_LENGTH+1);
    }

    /**
     * Given a basename, the method returns an instance of the class File.
     * @param string $in_basename The basename.
     * @return File The method returns an instance of the class File.
     * @throws Exception
     */
    static public function basenameToFile($in_basename) {

        if (! self::isBasename($in_basename)) {
            throw new Exception(sprintf('Invalid basename "%s".', $in_basename));
        }

        $timestamp = self::getTimestampFromBasename($in_basename);
        $id = self::getIdFromBasename($in_basename);

        return new File(new Timestamp($timestamp), $id);
    }

    /**
     * Given a list of basenames, the method returns the corresponding list of instances of the class File.
     * @param array array $in_list list basenames.
     * @return array The method returns the corresponding list of instances of the class File.
     * @throws Exception
     */
    static public function basenamesToFiles(array $in_basenames) {
        $properties = array();
        /** @var string $_basename */
        foreach ($in_basenames as $_basename) {
            $properties[] = self::basenameToFile($_basename);
        }
        return $properties;
    }

    /**
     * Gather a given list of instances of the class File according to their timestamps.
     * @param File[] $in_files List of instances of the class File to gather.
     * @param int $in_opt_keep Number of instances of the class File to keep.
     * @param string[] $out_opt_expired_timestamps Reference to an array used to store the expired timestamps.
     * @return array The method returns an associative array the contains the gathered files.
     *         - Keys are timestamps. Ex: 20190201000002.
     *         - Values are lists of instances of the class File.
     */
    static public function gatherByTimestamp(array $in_files, $in_opt_keep=0, array &$out_opt_expired_timestamps=array()) {

        // Sort the instances of the class File according to the timestamps.

        usort($in_files,
            function (File $a, File $b) {
                return Timestamp::cmp($a->getTimestamp(), $b->getTimestamp());
            }
        );

        // Gather the instances of the class File according to their timestamps.

        $list = array();
        /** @var File $_file */
        foreach ($in_files as $_file) {
            $current_timestamp = $_file->getTimestamp()->get();
            if (! array_key_exists($current_timestamp, $list)) {
                $list[$current_timestamp] = array();
            }

            $list[$current_timestamp][] = $_file;
        }


        // Sort the instances of the class File according to their IDs.

        /**
         * @var string $_timestamp
         * @var array $_data
         */
        foreach ($list as $_timestamp => &$_file) {
            usort($_file, function(File $a, File $b) {
                return strcmp($a->getId(), $b->getId());
            });
        }

        // Find the expired instances of the class File.

        $out_opt_expired_timestamps = array();
        if ($in_opt_keep > 0) {
            $timestamps = array_keys($list);

            $count = count($timestamps);
            for ($i=0; $i<$count - $in_opt_keep; $i++) {
                $out_opt_expired_timestamps[] = $timestamps[$i];
            }
        }

        return $list;
    }
}