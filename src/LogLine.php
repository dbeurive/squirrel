<?php

namespace dbeurive\Squirrel;


use dbeurive\Log\Logger;

class LogLine
{
    const FORMAT_RAW = 0;
    const FORMAT_LINEARIZED = 1;
    const FORMAT_UNEXPECTED = 2;

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

    public function getMessage() {
        return $this->__message;
    }

    public function getRawMessage() {
        return $this->__rawMessage;
    }

    public function getTimestamp() {
        return $this->__timestamp;
    }

    public function getSessionId() {
        return $this->__session;
    }

    public function getLevelAsText() {
        return $this>$this->__levelText;
    }

    public function getLevelAsInt() {
        return $this->__levelInt;
    }

    public function isLinearized() {
        return self::FORMAT_LINEARIZED == $this->__formatCode;
    }

    /**
     * @param $in_format
     * @return int
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