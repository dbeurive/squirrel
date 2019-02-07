<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
use dbeurive\Squirrel\Log;
use dbeurive\Log\Logger;

define('CLO_PROBLEM', 'problem');

define('CLI_SPECIFIC_CONFIGURATION', array(
    CLO_PROBLEM => array(
        'prefix'       => 'p',
        'longPrefix'   => 'problems',
        'description'  => 'Show only warnings and errors.',
        'noValue'      => true
    )
));

/**
 * Print the help.
 */
$help = function() {
    printf("php %s [-p|--problem]\n", basename(__FILE__));
};

Environment::init(CLI_SPECIFIC_CONFIGURATION, $help);

$clo_problem = Environment::getCloValue(CLO_PROBLEM);

$log = Environment::getLogPath();

Environment::outInfo(sprintf("# Path to the LOG file: %s", $log), true);
Environment::outInfo(sprintf("# Path to the configuration file: %s", Environment::getCloConfigurationPath()), true);


if (false === $fd = fopen($log, 'r')) {
    $error = error_get_last();
    Environment::fatal(sprintf('Cannot open the LOG file "%s": %s', $log, $error['message']));
}

while ((false !== $line = fgets($fd))) {

    if (preg_match('/^\s*$/', $line)) {
        continue;
    }

    $line = preg_replace('/\r?\n$/', '', $line);
    $log_line = Log::parse($line);

    if (! $clo_problem) {
        if ($log_line->getLevelAsInt() == Logger::LEVEL_INFO) {
            Environment::outInfo($line);
            continue;
        }

        if ($log_line->getLevelAsInt() == Logger::LEVEL_SUCCESS) {
            Environment::outSuccess($line);
            continue;
        }
    }

    if ($log_line->getLevelAsInt() == Logger::LEVEL_WARNING) {
        Environment::outWarning($line);
        continue;
    }

    if ($log_line->getLevelAsInt() == Logger::LEVEL_ERROR) {
        Environment::outError($line);
        continue;
    }

    if ($log_line->getLevelAsInt() == Logger::LEVEL_FATAL) {
        Environment::outFatal($line);
        continue;
    }
}

fclose($fd);
