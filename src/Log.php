<?php

/**
 * This file implements the class that represents the LOG file.
 */

namespace dbeurive\Squirrel;

/**
 * Class Log
 *
 * This class represents the LOG file.
 *
 * @package dbeurive\Squirrel
 */

class Log
{
    /** @var string Path to the directory used to store the LOG file. */
    private $__directory;
    /** @var string Name of the LOG file. */
    private $__name;
    /** @var int LOG level. */
    private $__level;
    /** @var bool Flag that indicates whether the name of the LOG file is timestamped or not. */
    private $__fileTimestamped;

    /**
     * Log constructor.
     * @param string $in_directory Path to the directory used to store the LOG files.
     * @param string $in_name Name of the LOG file.
     * @param int $in_level The LOG level.
     * @param bool $in_timestamped Flag that indicates whether the name of the LOG file is timestamped or not.
     */
    public function __construct($in_directory, $in_name, $in_level, $in_timestamped)
    {
        $this->__directory = $in_directory;
        $this->__name = $in_name;
        $this->__level = $in_level;
        $this->__fileTimestamped = $in_timestamped;
    }

    /**
     * Get the path to the directory used to store the LOG files.
     * @return string Path to the directory used to store the LOG files.
     */
    public function getDirectory() {
        return $this->__directory;
    }

    /**
     * Get the name of the LOG file.
     * @return string The name of the LOG file.
     */
    public function getName() {
        return $this->__name;
    }

    /**
     * Get the LOG level.
     * @return int The LOG level.
     */
    public function getLevel() {
        return $this->__level;
    }

    /**
     * Indicates whether the name of the LOG file must be timestamped or not.
     * @return bool If the method returns the value true, then the name of the LOG file must be timestamped.
     *         Otherwise, the name of the LOG file must not be timestamped.
     */
    public function fileTimestamped() {
        return $this->__fileTimestamped;
    }
}