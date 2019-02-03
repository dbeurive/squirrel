<?php

/**
 * This file implements the configuration loader.
 */

namespace dbeurive\Squirrel;

use dbeurive\Log\Logger;

/**
 * Class Configuration
 *
 * This class represents the configuration, as loaded from a given configuration file.
 *
 * @package dbeurive\Squirrel
 */
class Configuration
{
    const KEY_DESTINATIONS = 'destinations';
    const KEY_TASKS = 'tasks';
    const KEY_LOG = 'log';
    const KEY_DIRECTORY = 'directory';
    const KEY_LEVEL = 'level';
    const KEY_NAME = 'name';
    const KEY_HOST = 'host';
    const KEY_PASSWORD = 'password';
    const KEY_USER = 'user';
    const KEY_FILE_ID = 'file-id';
    const KEY_KEEP_COUNT = 'keep-count';
    const KEY_PORT = 'port';
    const KEY_TIMEOUT = 'timeout';
    const KEY_LOCAL_INPUT_DIRECTORY = 'local-input-directory';
    const KEY_LOCAL_DONE_DIRECTORY = 'local-done-directory';
    const KEY_REMOTE_DIRECTORY = 'path';
    const KEY_RETRY = 'retry';
    const KEY_SLEEP = 'sleep';
    const KEY_ON_SUCCESS = 'on-success';
    const KEY_ON_ERROR = 'on-error';

    const VALUE_DEFAULT_PORT = 21;
    const VALUE_DEFAULT_TIMEOUT = 60;
    const VALUE_DEFAULT_RETRY = 3;
    const VALUE_DEFAULT_SLEEP = 10;
    const VALUE_DEFAULT_ON_ERROR = '';
    const VALUE_DEFAULT_ON_SUCCESS = '';
    const VALUE_DEFAULT_LOG_LEVEL = 'INFO';

    /** @var array Internal representation of the configuration. */
    private $__configuration;
    /** @var string Path to the configuration file. */
    private $__configurationFilePath;
    /** @var string Base path used as reference for relative paths. */
    private $__localBasePath;
    /** @var array List of destinations. */
    private $__destinations;
    /** @var array List of tasks. */
    private $__tasks;
    /** @var Log Specification fot he LOG file. */
    private $__log;

    /**
     * Configuration constructor.
     * @param string $in_configuration_path Path to the configuration file.
     * @param string $in_local_base_path Base path used as reference for relative paths.
     * @throws Exception
     * @throws \ReflectionException
     */
    public function __construct($in_configuration_path, $in_local_base_path)
    {
        $this->__configurationFilePath = $in_configuration_path;
        $this->__localBasePath = $in_local_base_path;

        // Load, check, complete (with default values) the configuration.
        $this->__configuration = $this->__load($in_configuration_path);
        $this->__configuration = $this->__checkAndComplete($this->__configuration);
        $this->__processDirectories();

        // Create the objects that build up the configuration.
        $this->__destinations = $this->__createDestinations();
        $this->__tasks = $this->__createTasks();
        $this->__log = $this->__createLog();

    }

    /**
     * Return the list of destinations.
     * @return array The list of destinations.
     */
    public function getDestinations() {
        return $this->__destinations;
    }

    /**
     * Return the names of the destinations.
     * @return Destination The names of the destination.
     */
    public function getDestinationsNames() {
        return array_keys($this->__destinations);
    }

    /**
     * Return the list of tasks
     * @return array The list of tasks
     */
    public function getTasks() {
        return $this->__tasks;
    }

    /**
     * Return the names of the tasks.
     * @return array The names of the tasks.
     */
    public function getTasksNames() {
        return array_keys($this->__tasks);
    }

    /**
     * Return a destination identified by its given name.
     * @param string $in_destination_name Name of the destination.
     * @return Destination The destination.
     */
    public function getDestination($in_destination_name) {
        return $this->__destinations[$in_destination_name];
    }

    /**
     * Return a task identified by its given name.
     * @param string $in_task_name Name of the task.
     * @return Task The task.
     */
    public function getTask($in_task_name) {
        return $this->__tasks[$in_task_name];
    }

    /**
     * Return the specification for the LOG file.
     * @return Log The specification for the LOG file.
     */
    public function getLog() {
        return $this->__log;
    }

    /**
     * Test whether a given task name is registered within the configuration.
     * @param string $in_task_name The name of the task.
     * @return bool If the task is registered, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function isTaskConfigured($in_task_name) {
        return array_key_exists($in_task_name, $this->__configuration[self::KEY_TASKS]);
    }

    /**
     * Test whether a given destination name is registered within the configuration.
     * @param string $in_destination_name The name of the destination.
     * @return bool If the destination is registered, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function isDestinationConfigured($in_destination_name) {
        return array_key_exists($in_destination_name, $this->__configuration[self::KEY_DESTINATIONS]);
    }

    /**
     * Return the host associated with a given destination.
     * @param string $in_destination_name Name of the destination.
     * @return string The host.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getDestinationHost($in_destination_name) {
        $this->__checkDestinationConfigured($in_destination_name);
        return $this->__configuration[self::KEY_DESTINATIONS][$in_destination_name][self::KEY_HOST];
    }

    /**
     * Return the user associated with a given destination.
     * @param string $in_destination_name Name of the destination.
     * @return string The user.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getDestinationUser($in_destination_name) {
        $this->__checkDestinationConfigured($in_destination_name);
        return $this->__configuration[self::KEY_DESTINATIONS][$in_destination_name][self::KEY_USER];
    }

    /**
     * Return the password associated with a given destination.
     * @param string $in_destination_name Name of the destination.
     * @return string The password.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getDestinationPassword($in_destination_name) {
        $this->__checkDestinationConfigured($in_destination_name);
        return $this->__configuration[self::KEY_DESTINATIONS][$in_destination_name][self::KEY_PASSWORD];
    }

    /**
     * Return the file ID associated with a given task.
     * @param string $in_task_name Mask that represents the file names
     * @return string The file ID.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getTaskFileId($in_task_name) {
        $this->__checkTaskConfigured($in_task_name);
        return $this->__configuration[self::KEY_TASKS][$in_task_name][self::KEY_FILE_ID];
    }

    /**
     * Return the count associated with a given task.
     * @param string $in_task_name Name of the task.
     * @return string The count.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getTaskKeepCount($in_task_name) {
        $this->__checkTaskConfigured($in_task_name);
        return $this->__configuration[self::KEY_TASKS][$in_task_name][self::KEY_KEEP_COUNT];
    }

    /**
     * Return the port associated with a given destination.
     * @param string $in_destination_name Name of the destination.
     * @return int The port number.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getDestinationPort($in_destination_name) {
        $this->__checkDestinationConfigured($in_destination_name);
        return $this->__configuration[self::KEY_DESTINATIONS][$in_destination_name][self::KEY_PORT];
    }

    /**
     * Return the remote directory associated with a given destination.
     * @param string $in_destination_name Name of the destination.
     * @return string The remote directory.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getDestinationPath($in_destination_name) {
        $this->__checkDestinationConfigured($in_destination_name);
        return $this->__configuration[self::KEY_DESTINATIONS][$in_destination_name][self::KEY_REMOTE_DIRECTORY];
    }

    /**
     * Return the timeout associated with a given destination.
     * @param string $in_destination_name Name of the destination.
     * @return int The timeout.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getDestinationTimeout($in_destination_name) {
        $this->__checkDestinationConfigured($in_destination_name);
        return $this->__configuration[self::KEY_DESTINATIONS][$in_destination_name][self::KEY_TIMEOUT];
    }

    /**
     * Return the number of retries associated with a given destination.
     * @param string $in_destination_name Name of the destination.
     * @return int The number of retries.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getDestinationRetry($in_destination_name) {
        $this->__checkDestinationConfigured($in_destination_name);
        return $this->__configuration[self::KEY_DESTINATIONS][$in_destination_name][self::KEY_RETRY];
    }

    /**
     * Return the number of seconds between two transfer attempts.
     * @param string $in_destination_name Name of the destination.
     * @return int The number of seconds between two transfer attempts..
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getDestinationSleep($in_destination_name) {
        $this->__checkDestinationConfigured($in_destination_name);
        return $this->__configuration[self::KEY_DESTINATIONS][$in_destination_name][self::KEY_SLEEP];
    }

    /**
     * Return the local input directory associated with a given task.
     * @param string $in_task_name Name of the task.
     * @return int The local input directory.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getTaskLocalInputDirectory($in_task_name) {
        $this->__checkTaskConfigured($in_task_name);
        return $this->__configuration[self::KEY_TASKS][$in_task_name][self::KEY_LOCAL_INPUT_DIRECTORY];
    }

    /**
     * Return the path to the local directory used to store backup up files, associated with a given task.
     * @param string $in_task_name Name of the task.
     * @return int The path to the local directory used to backup up files.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getTaskLocalDoneDirectory($in_task_name) {
        $this->__checkTaskConfigured($in_task_name);
        return $this->__configuration[self::KEY_TASKS][$in_task_name][self::KEY_LOCAL_DONE_DIRECTORY];
    }

    /**
     * Return the destinations associated with a given task.
     * @param string $in_task_name Name of the task.
     * @return string The destination.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getTaskDestinations($in_task_name) {
        $this->__checkTaskConfigured($in_task_name);
        return $this->__configuration[self::KEY_TASKS][$in_task_name][self::KEY_DESTINATIONS];
    }

    /**
     * Return the error callback associated with a given task.
     * @param string $in_task_name Name of the task.
     * @return string The error callback.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getTaskOnError($in_task_name) {
        $this->__checkTaskConfigured($in_task_name);
        return $this->__configuration[self::KEY_TASKS][$in_task_name][self::KEY_ON_ERROR];
    }

    /**
     * Return the success callback associated with a given task.
     * @param string $in_task_name Name of the task.
     * @return string The success callback.
     * @throws Exception
     * @note This method should be used within the unit tests only.
     */
    public function getTaskOnSuccess($in_task_name) {
        $this->__checkTaskConfigured($in_task_name);
        return $this->__configuration[self::KEY_TASKS][$in_task_name][self::KEY_ON_SUCCESS];
    }

    /**
     * Create the associations between the names of the destinations and their internal representations.
     * @return array The method returns an associative array.
     *         The keys are the names of names of the destinations.
     *         The values are instances of the class Destination.
     */
    private function __createDestinations() {
        $result = array();
        /**
         * @var string $_destination Name of the destination.
         * @var array $_conf Configuration for the destination.
         */
        foreach ($this->__configuration[self::KEY_DESTINATIONS] as $_destination => $_conf) {
            $result[$_destination] = new Destination(
                $_destination,
                $_conf[self::KEY_HOST],
                $_conf[self::KEY_USER],
                $_conf[self::KEY_PASSWORD],
                $_conf[self::KEY_PORT],
                $_conf[self::KEY_REMOTE_DIRECTORY],
                $_conf[self::KEY_TIMEOUT],
                $_conf[self::KEY_SLEEP],
                $_conf[self::KEY_RETRY]);
        }
        return $result;
    }

    /**
     * Create the associations between the names of the tasks and their internal representations.
     * @return array The method returns an associative array.
     *         The keys are the names of names of the tasks.
     *         The values are instances of the class Task.
     */
    private function __createTasks() {
        $result = array();
        /**
         * @var string $_task Name of the task.
         * @var array $_conf Configuration for this task.
         */
        foreach ($this->__configuration[self::KEY_TASKS] as $_task => $_conf) {

            $destinations = array();
            foreach ($_conf[self::KEY_DESTINATIONS] as $_destination_name) {
                $destinations[$_destination_name] = $this->__destinations[$_destination_name];
            }

            $result[$_task] = new Task(
                $_task,
                $_conf[self::KEY_LOCAL_INPUT_DIRECTORY],
                $_conf[self::KEY_LOCAL_DONE_DIRECTORY],
                $_conf[self::KEY_FILE_ID],
                $_conf[self::KEY_KEEP_COUNT],
                $destinations,
                $_conf[self::KEY_ON_SUCCESS],
                $_conf[self::KEY_ON_ERROR]);
        }
        return $result;
    }

    /**
     * Create the specification for the LOG file.
     * @return Log The specification for the LOG file.
     */
    public function __createLog() {
        return new Log($this->__configuration[self::KEY_LOG][self::KEY_DIRECTORY],
            $this->__configuration[self::KEY_LOG][self::KEY_NAME],
            $this->__configuration[self::KEY_LOG][self::KEY_LEVEL]);
    }

    /**
     * Check that a given destination is configured.
     * @param string $in_destination_name Name of the destination.
     * @throws Exception
     */
    private function __checkDestinationConfigured($in_destination_name) {
        if (! array_key_exists($in_destination_name, $this->__configuration[self::KEY_DESTINATIONS])) {
            throw new Exception(sprintf('The given destination "%s" is not configured!', $in_destination_name));
        }
    }

    /**
     * Check that a given task is configured.
     * @param string $in_task_name Name of the task.
     * @throws Exception
     */
    private function __checkTaskConfigured($in_task_name) {
        if (! array_key_exists($in_task_name, $this->__configuration[self::KEY_TASKS])) {
            throw new Exception(sprintf('The given task "%s" is not configured!', $in_task_name));
        }
    }

    /**
     * Load the configuration from the given configuration file.
     * @param string $in_configuration_path Path to the configuration file.
     * @return array The method returns an array that represents the configuration loaded from the given file.
     * @throws Exception
     */
    private function __load($in_configuration_path) {
        if (false === $json = @file_get_contents($in_configuration_path)) {
            $error = error_get_last();
            throw new Exception(sprintf('Cannot load the given configuration file "%s". %s',
                $in_configuration_path,
                $error['message']));
        }

        $conf = @json_decode($json, true);
        $error = json_last_error();

        if (JSON_ERROR_NONE !== $error) {
            $message = json_last_error_msg();

            throw new Exception(sprintf('Cannot load the given configuration file "%s". %s.',
                $in_configuration_path,
                $message));
        }
        return $conf;
    }

    /**
     * Check and complete the configuration loaded from the configuration file.
     * @param array $in_configuration General configuration, as loaded from the configuration file.
     * @throws Exception
     * @throws \ReflectionException
     * @return array The method returns the configuration.
     */
    private function __checkAndComplete(array $in_configuration) {

        // Check the presence of the first level tags.
        $mandatory_tags1 = array(self::KEY_DESTINATIONS, self::KEY_TASKS, self::KEY_LOG);
        /** @var string $_optional_destination_tag */
        foreach ($mandatory_tags1 as $_optional_destination_tag) {
            if (! array_key_exists($_optional_destination_tag, $in_configuration)) {
                throw new Exception(sprintf('The configuration file "%s" is not valid. The entry "/%s" is missing.',
                    $this->__configurationFilePath,
                    $_optional_destination_tag));
            }
        }

        $in_configuration = $this->__checkAnCompleteLog($in_configuration);
        $in_configuration = $this->__checkAndCompleteDestinations($in_configuration);
        $in_configuration = $this->__checkAndCompleteTasks($in_configuration);
        return $in_configuration;
    }

    /**
     * Check and complete the configuration for the LOG file.
     * @param array $in_configuration General configuration, as loaded from the configuration file.
     * @return array The method returns the configuration.
     * @throws Exception
     * @throws \ReflectionException
     */
    private function __checkAnCompleteLog(array $in_configuration)
    {
        $mandatory_log_tags = array(
            self::KEY_DIRECTORY => '__validatorIsString',
            self::KEY_NAME => '__validatorIsString'
        );
        $optional_log_tags = array(
            self::KEY_LEVEL => array('value' => self::VALUE_DEFAULT_LOG_LEVEL, 'validator' => '__validatorIsLogLevel'),
        );

        /**
         * @var string $_mandatory_log_tag
         * @var string $_validator_name
         */
        foreach ($mandatory_log_tags as $_mandatory_log_tag => $_validator_name) {

            // Check the mandatory parameters.

            if (!array_key_exists($_mandatory_log_tag, $in_configuration[self::KEY_LOG])) {
                throw new Exception(sprintf('The configuration file "%s" is not valid. The entry "/log/%s" is missing.',
                    $this->__configurationFilePath,
                    $_mandatory_log_tag));
            } else {
                $value = $in_configuration[self::KEY_LOG][$_mandatory_log_tag];
                if (false === self::__call_private_or_protected_method(self::class, $_validator_name, $this, $value)) {
                    throw new Exception(sprintf('The configuration file "%s" is not valid. The value of the entry "/log/%s" (%s) is not valid.',
                        $this->__configurationFilePath,
                        $_mandatory_log_tag,
                        $value));
                }
            }

            // Check the optional parameters.

            /**
             * @var string $_optional_log_tag
             * @var array $_default
             */
            foreach ($optional_log_tags as $_optional_log_tag => $_default) {
                if (! array_key_exists($_optional_log_tag, $in_configuration[self::KEY_LOG])) {
                    $in_configuration[self::KEY_LOG][$_optional_log_tag] = $_default['value'];
                } else {

                    $value = $in_configuration[self::KEY_LOG][$_optional_log_tag];
                    $validator = $_default['validator'];

                    if (false === self::__call_private_or_protected_method(self::class, $validator, $this, $value)) {
                        throw new Exception(sprintf('The configuration file "%s" is not valid. The value of the entry "/log/%s" (%s) is not valid.',
                            $this->__configurationFilePath,
                            $_optional_log_tag,
                            $value));
                    }
                }
            }
        }

        return $in_configuration;
    }

    /**
     * Check and complete the configuration for the destinations.
     * @param array $in_configuration General configuration, as loaded from the configuration file.
     * @throws Exception
     * @throws \ReflectionException
     * @return array The method returns the configuration.
     */
    private function __checkAndCompleteDestinations(array $in_configuration) {

        $mandatory_destination_tags = array(
            self::KEY_HOST              => '__validatorOk',
            self::KEY_USER              => '__validatorOk',
            self::KEY_PASSWORD          => '__validatorOk',
            self::KEY_REMOTE_DIRECTORY  => '__validatorIsString',

        );
        $optional_destination_tags = array(
            self::KEY_PORT    => array('value' => self::VALUE_DEFAULT_PORT,    'validator' => '__validatorIsInt'),
            self::KEY_TIMEOUT => array('value' => self::VALUE_DEFAULT_TIMEOUT, 'validator' => '__validatorIsInt'),
            self::KEY_RETRY   => array('value' => self::VALUE_DEFAULT_RETRY,   'validator' => '__validatorIsInt'),
            self::KEY_SLEEP   => array('value' => self::VALUE_DEFAULT_SLEEP,   'validator' => '__validatorIsInt'),
        );

        /**
         * @var string $_mandatory_destination_tag
         * @var string $_validator_name
         */
        foreach ($mandatory_destination_tags as $_mandatory_destination_tag => $_validator_name) {
            /**
             * @var string $_destination_name
             * @var array $_destination_conf
             */
            foreach ($in_configuration[self::KEY_DESTINATIONS] as $_destination_name => &$_destination_conf) {

                // Check the mandatory parameters.

                if (! array_key_exists($_mandatory_destination_tag, $_destination_conf)) {
                    throw new Exception(sprintf('The configuration file "%s" is not valid. The entry "/destinations/%s/%s" is missing.',
                        $this->__configurationFilePath,
                        $_destination_name,
                        $_mandatory_destination_tag));
                } else {
                    $value = $_destination_conf[$_mandatory_destination_tag];
                    if (false === self::__call_private_or_protected_method(self::class, $_validator_name, $this, $value)) {
                        throw new Exception(sprintf('The configuration file "%s" is not valid. The value of the entry "/destinations/%s/%s" (%s) is not valid.',
                            $this->__configurationFilePath,
                            $_destination_name,
                            $_mandatory_destination_tag,
                            $value));
                    }
                }

                // Check the optional parameters.

                /**
                 * @var string $_optional_destination_tag
                 * @var array $_default
                 */
                foreach ($optional_destination_tags as $_optional_destination_tag => $_default) {
                    if (! array_key_exists($_optional_destination_tag, $_destination_conf)) {
                        $_destination_conf[$_optional_destination_tag] = $_default['value'];
                    } else {

                        $value = $_destination_conf[$_optional_destination_tag];
                        $validator = $_default['validator'];

                        if (false === self::__call_private_or_protected_method(self::class, $validator, $this, $value)) {
                            throw new Exception(sprintf('The configuration file "%s" is not valid. The value of the entry "/destinations/%s/%s" (%s) is not valid.',
                                $this->__configurationFilePath,
                                $_destination_name,
                                $_optional_destination_tag,
                                $value));
                        }
                    }
                }
            }
        }
        return $in_configuration;
    }

    /**
     * Check and complete the configuration for the tasks.
     * @param array $in_configuration General configuration, as loaded from the configuration file.
     * @throws Exception
     * @throws \ReflectionException
     * @return array The method returns the configuration.
     */
    private function __checkAndCompleteTasks(array $in_configuration) {

        $mandatory_task_tags = array(
            self::KEY_LOCAL_INPUT_DIRECTORY  => '__validatorIsString',
            self::KEY_LOCAL_DONE_DIRECTORY   => '__validatorIsString',
            self::KEY_DESTINATIONS           => '__validatorIsArrayOsStrings',
            self::KEY_FILE_ID                   => '__validatorIsRegExp',
            self::KEY_KEEP_COUNT             => '__validatorIsInt'
        );
        $optional_task_tags = array(
            self::KEY_ON_ERROR   => array('value' => self::VALUE_DEFAULT_ON_ERROR, 'validator' => '__validatorIsString'),
            self::KEY_ON_SUCCESS => array('value' => self::VALUE_DEFAULT_ON_SUCCESS, 'validator' => '__validatorIsString')
        );

        /**
         * @var string $_mandatory_task_tag
         * @var string $_validator_name
         */
        foreach ($mandatory_task_tags as $_mandatory_task_tag => $_validator_name) {
            /**
             * @var string $_task_name
             * @var array $_task_conf
             */
            foreach ($in_configuration[self::KEY_TASKS] as $_task_name => &$_task_conf) {

                // Check the mandatory parameters.

                if (! array_key_exists($_mandatory_task_tag, $_task_conf)) {
                    throw new Exception(sprintf('The configuration file "%s" is not valid. The entry "/tasks/%s/%s" is missing.',
                        $this->__configurationFilePath,
                        $_task_name,
                        $_mandatory_task_tag));
                } else {
                    $value = $_task_conf[$_mandatory_task_tag];
                    if (false === self::__call_private_or_protected_method(self::class, $_validator_name, $this, $value)) {
                        throw new Exception(sprintf('The configuration file "%s" is not valid. The value of the entry "/tasks/%s/%s" (%s) is not valid.',
                            $this->__configurationFilePath,
                            $_task_name,
                            $_mandatory_task_tag,
                            $value));
                    }
                }

                // Check the optional parameters.

                /**
                 * @var string $_optional_task_tag
                 * @var array $_default
                 */
                foreach ($optional_task_tags as $_optional_task_tag => $_default) {
                    if (! array_key_exists($_optional_task_tag, $_task_conf)) {
                        $_task_conf[$_optional_task_tag] = $_default['value'];
                    } else {

                        $value = $_task_conf[$_optional_task_tag];
                        $validator = $_default['validator'];

                        if (false === self::__call_private_or_protected_method(self::class, $validator, $this, $value)) {
                            throw new Exception(sprintf('The configuration file "%s" is not valid. The value of the entry "/tasks/%s/%s" (%s) is not valid.',
                                $this->__configurationFilePath,
                                $_task_name,
                                $_optional_task_tag,
                                $value));
                        }
                    }
                }
            }
        }
        return $in_configuration;
    }

    /**
     * Replace the special tag "${__DIR__}" within all the local paths.
     */
    private function __processDirectories() {
        foreach ($this->__configuration[self::KEY_TASKS] as $_task_name => &$_task_conf) {
            $_task_conf[self::KEY_LOCAL_INPUT_DIRECTORY] = preg_replace('/\$\{__DIR__\}/', $this->__localBasePath, $_task_conf[self::KEY_LOCAL_INPUT_DIRECTORY]);
            $_task_conf[self::KEY_LOCAL_DONE_DIRECTORY] = preg_replace('/\$\{__DIR__\}/', $this->__localBasePath, $_task_conf[self::KEY_LOCAL_DONE_DIRECTORY]);
        }
        $this->__configuration[self::KEY_LOG][self::KEY_DIRECTORY] = preg_replace('/\$\{__DIR__\}/', $this->__localBasePath, $this->__configuration[self::KEY_LOG][self::KEY_DIRECTORY]);
    }

    /**
     * Execute a private or a protected non-static method from a given class, within the context of a given object.
     * @param string $inClassName Name of the class.
     * @param string $inMethodName Name of the (private or protected) method to execute.
     * @param mixed $inObject Object that defines the execution's context.
     * @return mixed The method returns whatever the call method returns.
     * @throws \ReflectionException
     */
    static private function __call_private_or_protected_method($inClassName, $inMethodName, $inObject) {
        $reflection = new \ReflectionClass($inClassName);
        $method = $reflection->getMethod($inMethodName);
        $method->setAccessible(true);
        return $method->invokeArgs($inObject, array_slice(func_get_args(), 3));
    }

    /**
     * Validator: check that a value is an integer.
     * @param mixed $in_value The value to test.
     * @return bool If the given value is an integer, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    private function __validatorIsInt($in_value) {
        return is_int($in_value);
    }

    /**
     * Validator: always return true (OK).
     * @param mixed $in_value The value to test.
     * @return true The method always returns the value true.
     */
    private function __validatorOk($in_value) {
        return true;
    }

    /**
     * Validator: check that a value is a string.
     * @param mixed $in_value The value to test.
     * @return bool If the given value is a string, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    private function __validatorIsString($in_value) {
        return is_string($in_value);
    }

    /**
     * Validator: check that a value is a LOG level.
     * @param mixed $in_value The value to test.
     * @return bool If the given value is a LOG level, then the method returns the integer value that represents the level.
     *         Otherwise, it returns the value false.
     */

    private function __validatorIsLogLevel($in_value) {
        if (! is_string($in_value)) {
            return false;
        }
        return (false !== Logger::getLevelFromName($in_value));
    }

    /**
     * Validator: test whether a string represents a valid regular expression ot not.
     * @param string $in_value The string to test.
     * @return bool If the given value represents a valid regular expression, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    private function __validatorIsRegExp($in_value) {
        $in_value = preg_replace('/^\//', '', $in_value);
        $in_value = preg_replace('/\/$/', '', $in_value);
        $reg_exp = sprintf('/%s/', $in_value);
        @preg_match($reg_exp, '');
        return is_null(error_get_last());
    }

    /**
     * Validator: check that a value is an array of string.
     * @param mixed $in_value The value to test.
     * @return bool If the given value is an array string, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    private function __validatorIsArrayOsStrings($in_value) {
        if (! is_array($in_value)) {
            return false;
        }
        /** @var array $in_value */
        foreach ($in_value as $_index => $_value) {
            if (! is_string($_value)) {
                return false;
            }
        }

        return true;
    }
}