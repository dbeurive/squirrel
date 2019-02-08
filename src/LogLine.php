<?php

/**
 * This file implements the class that represents a line extracted from the application LOG file.
 */

namespace dbeurive\Squirrel;

use dbeurive\Log\Logger;

/**
 * Class LogLine
 *
 * This class represents a line extracted from the application LOG file.
 *
 * @package dbeurive\Squirrel
 */
class LogLine
{
    const FORMAT_RAW = 0;        // The message was not linearised.
    const FORMAT_LINEARIZED = 1; // The message was linearised.

    /** @var Timestamp */
    private $__timestamp;
    /** @var string */
    private $__session;
    /** @var int */
    private $__levelInt;
    /** @var string */
    private $__levelText;
    /** @var int */
    private $__formatCode;
    /** @var string */
    private $__formatText;
    /** @var string */
    private $__message;
    /** @var string */
    private $__rawMessage;

    /**
     * LogLine constructor.
     * @param string $in_timestamp The timestamp.
     * @param string $in_session The session ID.
     * @param string $in_level The LOG level.
     * @param string $in_format The LOG format.
     * @param string $in_message The message.
     * @throws Exception
     */
    public function __construct($in_timestamp, $in_session, $in_level, $in_format, $in_message)
    {
        $this->__timestamp = new Timestamp($in_timestamp);
        $this->__session = $in_session;

        $this->__levelText = $in_level;
        $this->__levelInt = Logger::getLevelFromName($in_level);

        $this->__formatText = $in_format;
        $this->__formatCode = self::__getFormatCode($in_format);

        $this->__rawMessage = $in_message;
        if (self::FORMAT_LINEARIZED == $this->__formatCode) {
            $this->__message = Logger::delinearize($in_message);
        } else {
            $this->__message = $in_message;
        }
    }

    /**
     * Get the message in its "human readable" format. If it was linearised, then it is "delinearized".
     * @return string The message.
     */
    public function getMessage() {
        return $this->__message;
    }

    /**
     * Get the raw message. If it was linearised, then it is not "delinearized".
     * @return string The raw message.
     */
    public function getRawMessage() {
        return $this->__rawMessage;
    }

    /**
     * Return the timestamp.
     * @return Timestamp The timestamp.
     */
    public function getTimestamp() {
        return $this->__timestamp;
    }

    /**
     * Return the session ID.
     * @return string The session ID.
     */
    public function getSessionId() {
        return $this->__session;
    }

    /**
     * Get the text that represents the message LOG level.
     * @return bool The text that represents the message LOG level.
     */
    public function getLevelAsText() {
        return $this>$this->__levelText;
    }

    /**
     * Get the integer value that represents the message LOG level.
     * @return bool|int The integer value that represents the message LOG level.
     */
    public function getLevelAsInt() {
        return $this->__levelInt;
    }

    /**
     * Test whether a message is linearized or not.
     * @return bool If the message is linearized, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function isLinearized() {
        return self::FORMAT_LINEARIZED == $this->__formatCode;
    }

    /**
     * Get the internal code that identifies the format (linearized or not) of the message that was extracted from the
     * line of LOG.
     * @param string $in_format The textual representation of the format ("R":raw or "L":linearised).
     * @return int The method returns one of the value listed below:
     *         - FORMAT_LINEARIZED: the message was linearised.
     *         - FORMAT_RAW: the message was not linearised.
     * @throws Exception
     */
    static private function __getFormatCode($in_format) {
        if ('L' == $in_format) {
            return self::FORMAT_LINEARIZED;
        } elseif ('R' == self::FORMAT_RAW) {
            return self::FORMAT_RAW;
        }
        throw new Exception(sprintf('Unexpected line format "%s".', $in_format));
    }


}