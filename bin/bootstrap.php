<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
use League\CLImate\CLImate;
use dbeurive\Log\Logger;
use dbeurive\Squirrel\Configuration;



if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    set_exception_handler(function (Throwable $e) {
        Environment::fatal($e->getMessage());
    });
} else {
    set_exception_handler(function (\Exception $e) {
        Environment::fatal($e->getMessage());
    });
}



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

    /** @var CLImate|false */
    static private $__climate=false;
    /** @var bool */
    static private $__cloVerbose;
    /** @var string */
    static private $__cloConfigurationPath;
    /** @var string */
    static private $__logPath;
    /** @var Configuration */
    static private $__configuration;
    /** @var Logger|false */
    static private $__logger=false;
    /** @var callable */
    static private $__help;

    /**
     * Initialise the environment container.
     * @param array $in_specific_args Climate specifications for the specific script.
     * @param callable $in_help_printer Function that prints the help.
     */
    static public function init(array $in_specific_args, callable $in_help_printer)
    {
        self::$__climate = new CLImate();
        try {
            self::$__climate->arguments->add(array_merge(CLI_COMMON_CONFIGURATION, $in_specific_args));
        } catch (\Exception $e) {
            printf("Unexpected error: %s\n", $e->getMessage());
            exit(1);
        }

        self::$__help = $in_help_printer;

        // Parse the command line.
        try {
            self::$__climate->arguments->parse();
        } catch (\Exception $e) {
            self::$__climate->backgroundLightRed()->white(sprintf("Invalid command line: %s", $e->getMessage()));
            self::$__climate->out("\nUsage:\n");
            self::$__climate->out(call_user_func(self::$__help));
            self::$__climate->out("Options: [-v|--verbose] [-c|--config <path to config file>]");
            self::$__climate->out("         [-c|--config <path to config file>]\n");
            exit(1);
        }

        $config_path = self::$__climate->arguments->get(CLO_CONF);
        if (false === self::$__cloConfigurationPath = realpath($config_path)) {
            self::fatal(sprintf('The configuration file "%s" does not exist or is not accessible.', $config_path));
        }

        self::$__cloVerbose = self::$__climate->arguments->get(CLO_VERBOSE);

        // Load the configuration. This action may throw an exception.
        try {
            self::$__configuration = new Configuration(self::$__cloConfigurationPath, realpath(dirname(self::$__cloConfigurationPath)));
        } catch (\Exception $e) {
            self::fatal(sprintf('Invalid configuration file "%s": %s', realpath(dirname(self::$__cloConfigurationPath)), $e->getMessage()));
        }

        // Open the LOG file.
        $timestamp = strftime('%Y%m%d');
        $log = self::$__configuration->getLog();
        $log_path = null;

        if ($log->fileTimestamped()) {
            $log_path = sprintf('%s%s%s-%s', $log->getDirectory(), DIRECTORY_SEPARATOR, $timestamp, $log->getName());
        } else {
            $log_path = sprintf('%s%s%s', $log->getDirectory(), DIRECTORY_SEPARATOR, $log->getName());
        }

        if (! file_exists($log_path)) {
            if (! touch($log_path)) {
                self::fatal(sprintf('Cannot create the LOG file "%s"!', $log_path));
            }
        }

        if (false === self::$__logPath = realpath($log_path)) {
            self::fatal(sprintf('Unexpected error: cannot get the real path of the LOG file "%s".', $log_path));
        }

        try {
            self::$__logger = new Logger(self::$__logPath, self::$__configuration->getLog()->getLevel());
        } catch (\Exception $e) {
            self::fatal(sprintf("Unexpected error: %s", $e->getMessage()));
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
        return self::$__cloConfigurationPath;
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
     * Return the CLImare object.
     * @return CLImate the CLImare object.
     */
    static function getClimate() {
        return self::$__climate;
    }

    // ------------------------------------------------------------------------------------
    // Methods intended to be used to print message to the console only.
    // ------------------------------------------------------------------------------------

    /**
     * Print a message.
     * @param string $in_message Message to print.
     * @note This method is intended to be used to print message to the console only.
     * @param bool $in_opt_verbose_dependant This flag determines whether the print of the given message depends on the
     *        verbosity level or not.
     *        * The value true indicates that the print of the given message depends on the verbosity level: if the
     *          verbosity level is not zero, then the message will be printed.
     *        * The value false indicates that the print of the given message does not depend on the verbosity level.
     *          The message will be printed.
     * @note This method is intended to be used to print message to the console only.
     */
    static public function out($in_message, $in_opt_verbose_dependant=false) {
        if ($in_opt_verbose_dependant && ! self::$__cloVerbose) {
            return;
        }
        self::$__climate->out($in_message);
    }

    /**
     * Print an information.
     * @param string $in_message Message to print.
     * @param bool $in_opt_verbose_dependant This flag determines whether the print of the given message depends on the
     *        verbosity level or not.
     *        * The value true indicates that the print of the given message depends on the verbosity level: if the
     *          verbosity level is not zero, then the message will be printed.
     *        * The value false indicates that the print of the given message does not depend on the verbosity level.
     *          The message will be printed.
     * @note This method is intended to be used to print message to the console only.
     */
    static public function outInfo($in_message, $in_opt_verbose_dependant=false) {
        if ($in_opt_verbose_dependant && ! self::$__cloVerbose) {
            return;
        }
        self::$__climate->out($in_message);
    }

    /**
     * Print a success message.
     * @param string $in_message Message to print.
     * @param bool $in_opt_verbose_dependant This flag determines whether the print of the given message depends on the
     *        verbosity level or not.
     *        * The value true indicates that the print of the given message depends on the verbosity level: if the
     *          verbosity level is not zero, then the message will be printed.
     *        * The value false indicates that the print of the given message does not depend on the verbosity level.
     *          The message will be printed.
     * @note This method is intended to be used to print message to the console only.
     */
    static public function outSuccess($in_message, $in_opt_verbose_dependant=false) {
        if ($in_opt_verbose_dependant && ! self::$__cloVerbose) {
            return;
        }
        self::$__climate->lightGreen($in_message);
    }

    /**
     * Print a warning message.
     * @param string $in_message Message to print.
     * @param bool $in_opt_verbose_dependant This flag determines whether the print of the given message depends on the
     *        verbosity level or not.
     *        * The value true indicates that the print of the given message depends on the verbosity level: if the
     *          verbosity level is not zero, then the message will be printed.
     *        * The value false indicates that the print of the given message does not depend on the verbosity level.
     *          The message will be printed.
     * @note This method is intended to be used to print message to the console only.
     */
    static public function outWarning($in_message, $in_opt_verbose_dependant=false) {
        if ($in_opt_verbose_dependant && ! self::$__cloVerbose) {
            return;
        }
        self::$__climate->yellow($in_message);
    }

    /**
     * Print an error message.
     * @param string $in_message Message to print.
     * @param bool $in_opt_verbose_dependant This flag determines whether the print of the given message depends on the
     *        verbosity level or not.
     *        * The value true indicates that the print of the given message depends on the verbosity level: if the
     *          verbosity level is not zero, then the message will be printed.
     *        * The value false indicates that the print of the given message does not depend on the verbosity level.
     *          The message will be printed.
     * @note This method is intended to be used to print message to the console only.
     */
    static public function outError($in_message, $in_opt_verbose_dependant=false) {
        if ($in_opt_verbose_dependant && ! self::$__cloVerbose) {
            return;
        }
        self::$__climate->backgroundLightYellow()->black($in_message);
    }

    /**
     * Print a fatal error message.
     * @param string $in_message Message to print.
     * @param bool $in_opt_verbose_dependant This flag determines whether the print of the given message depends on the
     *        verbosity level or not.
     *        * The value true indicates that the print of the given message depends on the verbosity level: if the
     *          verbosity level is not zero, then the message will be printed.
     *        * The value false indicates that the print of the given message does not depend on the verbosity level.
     *          The message will be printed.
     * @note This method is intended to be used to print message to the console only.
     */
    static public function outFatal($in_message, $in_opt_verbose_dependant=false) {
        if ($in_opt_verbose_dependant && ! self::$__cloVerbose) {
            return;
        }
        self::$__climate->backgroundLightRed()->white($in_message);
    }

    // ------------------------------------------------------------------------------------
    // Methods intended to be used to print message to the console and to the LOG file.
    // ------------------------------------------------------------------------------------

    /**
     * Report a debug message.
     * @param string $in_message The information to report.
     * @note This method is intended to be used to print message to the console and to the LOG file.
     */
    static public function debug($in_message) {
        try {
            self::$__logger->debug($in_message);
        } catch (\Exception $e) {
            fwrite(STDERR, sprintf("Unexpected error! Cannot write in my LOG file \"%s\"", self::$__logPath));
        }

        if (self::$__cloVerbose) {
            self::$__climate->lightGray($in_message);
        }
    }

    /**
     * Report an information.
     * @param string $in_message The information to report.
     * @note This method is intended to be used to print message to the console and to the LOG file.
     */
    static public function info($in_message) {
        try {
            self::$__logger->info($in_message);
        } catch (\Exception $e) {
            fwrite(STDERR, sprintf("Unexpected error! Cannot write in my LOG file \"%s\"", self::$__logPath));
        }

        if (self::$__cloVerbose) {
            self::$__climate->out($in_message);
        }
    }

    /**
     * Report a successful operation.
     * @param string $in_message The information to report.
     * @note This method is intended to be used to print message to the console and to the LOG file.
     */
    static public function success($in_message) {
        try {
            self::$__logger->success($in_message);
        } catch (\Exception $e) {
            fwrite(STDERR, sprintf("Unexpected error! Cannot write in my LOG file \"%s\"\n", self::$__logPath));
        }

        if (self::$__cloVerbose) {
            self::$__climate->green($in_message);
        }
    }

    /**
     * Report a warning.
     * @param string $in_message The information to report.
     * @note This method is intended to be used to print message to the console and to the LOG file.
     */
    static public function warning($in_message) {
        try {
            self::$__logger->warning($in_message);
        } catch (\Exception $e) {
            fwrite(STDERR, sprintf("Unexpected error! Cannot write in my LOG file \"%s\"\n", self::$__logPath));
        }

        if (self::$__cloVerbose) {
            self::$__climate->yellow($in_message);
        }
    }


    /**
     * Report an error.
     * @param string $in_message Message that describes the error.
     * @note This method is intended to be used to print message to the console and to the LOG file.
     */
    static public function error($in_message) {
        try {
            self::$__logger->error($in_message);
        } catch (\Exception $e) {
            fwrite(STDERR, sprintf("Unexpected error! Cannot write in my LOG file \"%s\"\n", self::$__logPath));
        }

        if (self::$__cloVerbose) {
            self::$__climate->backgroundLightYellow()->black($in_message);
        }
    }

    /**
     * Report a fatal error.
     * @param string $in_message Message that describes the error.
     * @note This method is intended to be used to print message to the console and to the LOG file.
     */
    static public function fatal($in_message) {
        if (false !== self::$__logger) {
            try {
                self::$__logger->fatal($in_message);
            } catch (\Exception $e) {
                // Nothing that can be done!
            }
        }
        if (false !== self::$__climate) {
            self::$__climate->backgroundLightRed()->white($in_message);
        } else {
            fwrite(STDERR, "${in_message}\n");
        }

        exit(1);
    }
}




