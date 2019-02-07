<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use dbeurive\Squirrel\Destination;

define('CLA_DESTINATION', 'destination');

define('CLI_SPECIFIC_CONFIGURATION', array(
    CLA_DESTINATION => array(
        'description'  => 'Name of the destination.',
        'required'     => true
    )
));

/**
 * Print the help.
 */
$help = function() {
    printf("php %s <name of the destination>\n", basename(__FILE__));
};

// ---------------------------------------------
// Initialise the environment.
// ---------------------------------------------

Environment::init(CLI_SPECIFIC_CONFIGURATION, $help);
$cla_destination = strtolower(Environment::getClaValue(CLA_DESTINATION));

$config = Environment::getConfiguration();
if (! $config->isDestinationConfigured($cla_destination)) {
    Environment::fatal(sprintf('The destination "%s" is not configured in the configuration file "%s"', $cla_destination, Environment::getCloConfigurationPath()));
}

Environment::outInfo(sprintf("# Path to the LOG file: %s", Environment::getLogPath()), true);
Environment::outInfo(sprintf("# Path to the configuration file: %s", Environment::getCloConfigurationPath()), true);

/** @var Destination $destination */
$destination = $config->getDestination($cla_destination);

$error_message = null;
if (false === $destination->reach($error_message)) {
    Environment::fatal(sprintf('Cannot reach the destination "%s": %s', $destination, $error_message));
}

$to_keep = array(); // No use for it.
if (false === $backups = $destination->inventory(0, $to_keep, $error_message)) {
    Environment::fatal(sprintf('Cnnont inventory the remote backups of the destination "%s": %s', $destination, $error_message));
}

foreach ($backups as $_name) {
    Environment::out($_name);
}

