<?php

/**
 * The file implements a timestamp.
 */

namespace dbeurive\Squirrel;

/**
 * Class Timestamp
 *
 * This class represents a timestamp.
 *
 * @package dbeurive\Squirrel
 */

class Timestamp
{
    const TIMESTAMP_LENGTH = 14;

    /** @var string Year. */
    private $__year;
    /** @var string Month. */
    private $__month;
    /** @var string Day. */
    private $__day;
    /** @var string Hour. */
    private $__hour;
    /** @var string Minute. */
    private $__minute;
    /** @var string Second. */
    private $__second;
    /** @var string The text that represents the timestamp. */
    private $__text;

    /**
     * Timestamp constructor.
     * @param string $in_text The text that represents the timestamp.
     * @throws Exception
     */
    public function __construct($in_text)
    {
        $data = array();
        if (1 !== preg_match('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/', $in_text, $data)) {
            throw new Exception(sprintf('Invalid timestamp "%s".', $in_text));
        }
        $this->__year = (string)$data[1];
        $this->__month = (string)$data[2];
        $this->__day = (string)$data[3];
        $this->__hour = (string)$data[4];
        $this->__minute = (string)$data[5];
        $this->__second = (string)$data[6];
        $this->__text = (string)$in_text;
    }

    /**
     * Get the year.
     * @return string The year.
     */
    public function getYear() {
        return (string)$this->__year;
    }

    /**
     * Get the month.
     * @return string The month.
     */
    public function getMonth() {
        return (string)$this->__month;
    }

    /**
     * Get the day.
     * @return string The day.
     */
    public function getDay() {
        return (string)$this->__day;
    }

    /**
     * Get the hour.
     * @return string The hour.
     */
    public function getHour() {
        return (string)$this->__hour;
    }

    /**
     * Get the minute.
     * @return string The minute.
     */
    public function getMinute() {
        return (string)$this->__minute;
    }

    /**
     * Get the second.
     * @return string The second.
     */
    public function getSecond() {
        return (string)$this->__second;
    }

    /**
     * Get the textual representation of the timestamp.
     * @return string The textual representation of the timestamp.
     */
    public function get() {
        return (string)$this->__text;
    }

    public function __toString() {
        return (string)$this->__text;
    }

    /**
     * Compare 2 timestamps.
     * @param Timestamp $a One of the timestamps to compare.
     * @param Timestamp $b The other timestamps to compare.
     * @return int If the timestamps are identical, then the method returns the value 0.
     *         If $a < $b, then then the method returns the value -1.
     *         If $a > $b, then then the method returns the value +1.
     */
    static function cmp(Timestamp $a, Timestamp $b) {
        $list = array(
            'getYear',
            'getMonth',
            'getDay',
            'getHour',
            'getMinute',
            'getSecond'
        );
        foreach ($list as $_method) {
            $r = self::__intCmp(intval($a->$_method()), intval($b->$_method()));
            if (0 != $r) {
                return $r;
            }
        }
        return 0;
    }

    /**
     * Compare two integers.
     * @param int $a One of the integer to compare.
     * @param int $b The other integer to compare.
     * @return int If the timestamps are identical, then the method returns the value 0.
     *         If $a < $b, then then the method returns the value -1.
     *         If $a > $b, then then the method returns the value +1.
     */
    private static function __intCmp($a, $b) {
        if ($a > $b) {
            return 1;
        }
        if ($a < $b) {
            return -1;
        }
        return 0;
    }
}