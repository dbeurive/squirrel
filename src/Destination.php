<?php

/**
 * This file implements the object that defines a destination.
 */

namespace dbeurive\Squirrel;

use dbeurive\Ftp\Ftp;
use dbeurive\Ftp\AbstractEntryManager;

/**
 * Class Destination
 *
 * This class implements the object that defines a destination.
 *
 * @package dbeurive\Squirrel
 */
class Destination
{
    /** @var string Logical name of the destination. */
    private $__name;
    /** @var string Name of the host. */
    private $__host;
    /** @var string ID of the FTP user. */
    private $__user;
    /** @var string Password for the FTP user. */
    private $__password;
    /** @var int FTP port number. */
    private $__port;
    /** @var string Path, on the host where to store the backups. */
    private $__path;
    /** @var int Timeout, in seconds, for every FTP commands. */
    private $__timeout;
    /** @var int Number of seconds between an FTP error and the test tentative. */
    private $__sleep;
    /** @var int Maximum number of tentative for an FTP action. */
    private $__retry;
    /** @var Ftp|false FTP handler. */
    private $__ftp = false;
    /** @var array Option used to establish the FTP connexion. */
    private $__options;

    /**
     * Destination constructor.
     * @param string $in_name Logical name of the destination.
     * @param string $in_host Name of the host.
     * @param string $in_user ID of the FTP user.
     * @param string $in_password Password for the FTP user.
     * @param int $in_port FTP port number.
     * @param string $in_path Path to the directory where the store the backups.
     * @param int $in_timeout Timeout, in seconds, for every FTP commands.
     * @param int $in_sleep Number of seconds between an FTP error and the test tentative.
     * @param int $in_count Maximum number of tentative for an FTP action.
     */
    public function __construct($in_name, $in_host, $in_user, $in_password, $in_port, $in_path, $in_timeout, $in_sleep, $in_count) {
        $this->__name = $in_name;
        $this->__host = $in_host;
        $this->__user = $in_user;
        $this->__password = $in_password;
        $this->__port = $in_port;
        $this->__path = $in_path;
        $this->__timeout = $in_timeout;
        $this->__sleep = $in_sleep;
        $this->__retry = $in_count;

        $this->__options = array(
            Ftp::OPTION_PORT => $this->__port,
            Ftp::OPTION_TIMEOUT => $this->__timeout
        );
    }

    /**
     * Open a connexion to the destination.
     * @param string $out_message Reference to a string used to store an error message.
     * @return bool Upon successful completion, the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function reach(&$out_message) {
        for ($i=0; $i<$this->__retry; $i++) {
            $out_message = null;
            try {
                $this->__ftp = new Ftp($this->__host, $this->__options);
                $this->__ftp->connect();
                $this->__ftp->login($this->__user, $this->__password);
            } catch (\Exception $e) {
                $out_message = $e->getMessage();
                if ($i < $this->__retry) { sleep($this->__sleep); }
                continue;
            }
            break;
        }
        if (is_null($out_message)) {
            return true;
        }
        $this->__ftp = null;
        return false;
    }

    /**
     * Send a file to this destination.
     * @param string $in_local_path Path to the file to send.
     * @param string $out_message Reference to a string used to store an error message.
     * @return bool Upon successful completion, the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function send($in_local_path, &$out_message) {
        $remote_path = sprintf('%s/%s',
            $this->__path,
            basename($in_local_path));

        for ($i=0; $i<$this->__retry; $i++) {
            $out_message = null;
            try {
                $this->__ftp->put($in_local_path, $remote_path);
            } catch (\Exception $e) {
                $out_message = $e->getMessage();
                if ($i < $this->__retry) { sleep($this->__sleep); }
                continue;
            }
        }
        if (is_null($out_message)) {
            return true;
        }
        return false;
    }

    /**
     * Remove a file from this destination.
     * @param string $in_file_basename Basename of the file to remove.
     * @param string Reference to a string used to store an error message.
     * @return bool Upon successful completion, the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function remove($in_file_basename, &$out_message) {
        $remote_file = sprintf('%s/%s', $this->__path, $in_file_basename);

        for ($i=0; $i<$this->__retry; $i++) {
            $out_message = null;
            try {
                $this->__ftp->deleteIfExists($remote_file);
            } catch (\Exception $e) {
                $out_message = $e->getMessage();
                if ($i < $this->__retry) { sleep($this->__sleep); }
                continue;
            }
        }
        if (is_null($out_message)) {
            return true;
        }
        return false;
    }

    /**
     * Inventory the files stored on the destination and lists the expired ones.
     * @param int $in_keep_count Number of backups to keep on this destination.
     *        Set the value to 0 if you don't care about expired files.
     * @param array $out_expired Reference to an array used to store the expired backups.
     *        If the value of "$in_keep_count" is 0, then this parameter is of no use.
     * @param string Reference to a string used to store an error message.
     * @return string[]|bool Upon successful completion, the method returns the list of backups found on this destination.
     *         backups are designated by their basenames.
     *         Otherwise, the method returns the value false.
     * @throws Exception
     */
    public function inventory($in_keep_count, array &$out_expired, &$out_message) {

        $out_expired = array();

        /** @var array $files */
        $files = null;
        for ($i=0; $i<$this->__retry; $i++) {
            $out_message = null;
            try {
                $files = $this->__ftp->ls($this->__path, true);
            } catch (\Exception $e) {
                $out_message = $e->getMessage();
                if ($i < $this->__retry) { sleep($this->__sleep); }
                continue;
            }
        }

        if (! is_null($out_message)) {
            return false;
        }

        $basenames = array_map(function (AbstractEntryManager $e) { return basename($e->getPath()); }, $files);
        $files = File::basenamesToFiles($basenames);

        /** @var string[] $expired_timestamps */
        $expired_timestamps = array();
        $files = File::gatherByTimestamp($files, $in_keep_count, $expired_timestamps);

        /** @var string $_timestamp */
        foreach ($expired_timestamps as $_timestamp) {
            $out_expired = array_merge($out_expired,
                array_map(function (File $e) { return $e->getBasename() ; }, $files[$_timestamp]));
        }

        return $basenames;
    }

    /**
     * Create the directory (on the remote host) where the backups are stored.
     * If the directory already exists, it is not created.
     * @param string $out_message Reference to a string used to store an error message.
     * @return bool Upon successful completion, the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function createPath(&$out_message) {

        for ($i=0; $i<$this->__retry; $i++) {
            $out_message = null;
            try {
                $this->__ftp->mkdirRecursiveIfNotExist($this->__path);
            } catch (\Exception $e) {
                $out_message = $e->getMessage();
                if ($i < $this->__retry) { sleep($this->__sleep); }
                continue;
            }
        }

        if (is_null($out_message)) {
            return true;
        }
        return false;
    }

    /**
     * Close the connexion to this destination.
     * @throws \dbeurive\Ftp\Exception
     */
    public function leave() {
        $this->__ftp->disconnect();
    }

    /**
     * Get the logical name of the destination.
     * @return string The logical name of the destination.
     */
    public function getName() {
        return $this->__name;
    }

    /**
     * Get the name of the network host.
     * @return string The name of the network host.
     */
    public function getHost() {
        return $this->__host;
    }

    /**
     * Get the ID of the FTP user.
     * @return string The ID of the FTP user.
     */
    public function getUser() {
        return $this->__user;
    }

    /**
     * Get the password for the FTP user.
     * @return string The password for the FTP user.
     */
    public function getPassword() {
        return $this->__password;
    }

    /**
     * Get the FTP port number.
     * @return int The FTP port number.
     */
    public function getPort() {
        return $this->__port;
    }

    /**
     * Get the path to the directory used to store the backups.
     * @return string The path to the directory used to store the backups.
     */
    public function getPath() {
        return $this->__path;
    }

    /**
     * Get the timeout, in seconds, for every FTP commands.
     * @return int The FTP timeout.
     */
    public function getTimeout() {
        return $this->__timeout;
    }

    /**
     * Get the number of seconds between an FTP error and the test tentative.
     * @return int The number of seconds between an FTP error and the test tentative.
     */
    public function getSleep() {
        return $this->__sleep;
    }

    /**
     * Get the maximum number of tentative for an FTP action.
     * @return int The maximum number of tentative for an FTP action.
     */
    public function getRetry() {
        return $this->__retry;
    }

    public function __toString() {
        return $this->__name;
    }
}