<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
use League\CLImate\CLImate;
use dbeurive\Log\Logger;
use dbeurive\Squirrel\Configuration;

// Path to the default configuration file.
define('DEFAULT_CONFIGURATION', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'squirrel.json');

// Definition for the command line option that represents the path to the configuration file.
define('CLO_CONF', 'config');

// Definition for the command line option that represents the verbosity level.
define('CLO_VERBOSE', 'verbose');

// Common specification for the command line parser.
define('CLI_COMMON_CONFIGURATION', array(
    CLO_CONF => array(
        'prefix'       => 'c',
        'longPrefix'   => 'config',
        'description'  => 'Path to the configuration file.',
        'defaultValue' => DEFAULT_CONFIGURATION
    ),
    CLO_VERBOSE => array(
        'prefix'       => 'v',
        'longPrefix'   => 'verbose',
        'description'  => 'Activate the verbose mode.',
        'noValue'      => true
    )
));


/**
 * Class Environment
 *
 * This class represents the "outside" local environment of the script.
 * The script interacts with the local environment through this class.
 */
class Environment {

    /** @var CLImate */
    static private $__climate;
    /** @var bool */
    static private $__cloVerbose;
    /** @var string */
    static private $__cloConfiguration;
    /** @var string */
    static private $__logPath;
    /** @var Configuration */
    static private $__configuration;
    /** @var Logger */
    static private $__logger;

    /**
     * Initialise the environment container.
     * @param array $in_specific_args Climate specifications for the specific script.
     */
    static public function init(array $in_specific_args)
    {
        self::$__climate = new CLImate();
        try {
            self::$__climate->arguments->add(array_merge(CLI_COMMON_CONFIGURATION, $in_specific_args));
        } catch (\Exception $e) {
            printf("Unexpected error: %s\n", $e->getMessage());
            exit(1);
        }

        // Parse the command line.
        try {
            self::$__climate->arguments->parse();
        } catch (\Exception $e) {
            self::fatal_error($e->getMessage());
        }

        self::$__cloConfiguration = self::$__climate->arguments->get(CLO_CONF);
        self::$__cloVerbose = self::$__climate->arguments->get(CLO_VERBOSE);

        // Load the configuration. This action may throw an exception.
        try {
            self::$__configuration = new Configuration(self::$__cloConfiguration, realpath(dirname(self::$__cloConfiguration)));
        } catch (\Exception $e) {
            self::fatal_error($e->getMessage());
        }

        // Open the LOG file.
        $timestamp = strftime('%Y%m%d');
        $log = self::$__configuration->getLog();

        if ($log->fileTimestamped()) {
            self::$__logPath = sprintf('%s%s%s-%s', $log->getDirectory(), DIRECTORY_SEPARATOR, $timestamp, $log->getName());
        } else {
            self::$__logPath = sprintf('%s%s%s', $log->getDirectory(), DIRECTORY_SEPARATOR, $log->getName());
        }

        try {
            self::$__logger = new Logger(self::$__logPath, self::$__configuration->getLog()->getLevel());
        } catch (\Exception $e) {
            printf("Unexpected error: %s\n", $e->getMessage());
            exit(1);
        }
    }

    /**
     * Return the object that contains the loaded configuration.
     * @return Configuration The loaded configuration.
     */
    static public function getConfiguration() {
       return self::$__configuration;
    }

    /**
     * Test whether the verbosity mode is activated or not.
     * @return bool If the verbosity mode is activated, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    static public function getCloVerbose() {
        return self::$__cloVerbose;
    }

    /**
     * Return the path to the configuration file that has been loaded.
     * @return string The configuration file that has been loaded.
     */
    static public function getCloConfigurationPath() {
        return self::$__cloConfiguration;
    }

    /**
     * Return the value of a command line option identified by its name.
     * @param string $in_name The name of the command line option.
     * @return mixed the value of a command line option.
     */
    static public function getCloValue($in_name) {
        return self::$__climate->arguments->get($in_name);
    }

    /**
     * Return the value of a command line argument identified by its name.
     * @param string $in_name The name of the command line argument.
     * @return mixed the value of a command line argument.
     */
    static public function getClaValue($in_name) {
        return self::$__climate->arguments->get($in_name);
    }

    /**
     * Return the path to the LOG file.
     * @return string The path to the LOG file.
     */
    static public function getLogPath() {
       return self::$__logPath;
    }

    /**
     * Return the logger.
     * @return Logger The looger.
     */
    static public function getLogger() {
       return self::$__logger;
    }

    /**
     * Report a debug message.
     * @param string $in_message The information to report.
     * @throws Exception
     */
    static public function debug($in_message) {
        self::$__logger->debug($in_message);
        if (self::$__cloVerbose) {
            self::$__climate->lightGray($in_message);
        }
    }

    /**
     * Report an information.
     * @param string $in_message The information to report.
     * @throws Exception
     */
    static public function info($in_message) {
        self::$__logger->info($in_message);
        if (self::$__cloVerbose) {
            self::$__climate->out($in_message);
        }
    }

    /**
     * Report a successful operation.
     * @param string $in_message The information to report.
     * @throws Exception
     */
    static public function success($in_message) {
        self::$__logger->success($in_message);
        if (self::$__cloVerbose) {
            self::$__climate->green($in_message);
        }
    }

    /**
     * Report a warning.
     * @param string $in_message The information to report.
     * @throws Exception
     */
    static public function warning($in_message) {
        self::$__logger->warning($in_message);
        if (self::$__cloVerbose) {
            self::$__climate->yellow($in_message);
        }
    }


    /**
     * Report an error.
     * @param string $in_message Message that describes the error.
     * @throws Exception
     */
    static public function error($in_message) {
        self::$__logger->error($in_message);
        if (self::$__cloVerbose) {
            self::$__climate->red($in_message);
        }
    }

    /**
     * Report a fatal error.
     * @param string $in_message Message that describes the error.
     * @throws Exception
     */
    static public function fatal($in_message) {
        self::$__logger->fatal($in_message);
        if (self::$__cloVerbose) {
            self::$__climate->lightRed(sprintf('FATAL ERROR: %s', $in_message));
        }
        exit(1);
    }
}

