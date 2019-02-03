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

    /**
     * Log constructor.
     * @param string $in_directory Path to the directory used to store the LOG files.
     * @param string $in_name Name of the LOG file.
     * @param int $in_level The LOG level.
     */
    public function __construct($in_directory, $in_name, $in_level)
    {
        $this->__directory = $in_directory;
        $this->__name = $in_name;
        $this->__level = $in_level;
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
}