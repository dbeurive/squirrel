<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use dbeurive\Squirrel\Configuration;


define('CLA_TYPE', 'type');

define('CLI_SPECIFIC_CONFIGURATION', array(
    CLA_TYPE => array(
        'description'  => 'Type of the element to list (task or destination).',
        'required'     => true
    ),
));

define('TYPE_TASK', 'task');
define('TYPE_DESTINATION', 'destination');

/**
 * Print the help.
 */
$help = function() {
    printf("php %s task|destination\n", basename(__FILE__));
};

// ---------------------------------------------
// Initialise the environment.
// ---------------------------------------------

Environment::init(CLI_SPECIFIC_CONFIGURATION, $help);
$cla_type = strtolower(Environment::getClaValue(CLA_TYPE));

if (! in_array($cla_type, array(TYPE_TASK, TYPE_DESTINATION))) {
    Environment::fatal(sprintf('Invalid argument value "%s" !', $cla_type));
}

$config = Environment::getConfiguration();

if (TYPE_TASK == $cla_type) {
    ls_task($config);
} else {
    ls_dest($config);
}



/**
 * Print the list of available tasks.
 * @param Configuration $in_config The main configuration.
 */
function ls_task(Configuration $in_config) {
    $tasks = $in_config->getTasks();
    Environment::out(implode("\n", array_keys($tasks)));
}

/**
 * Print the list of available destination.
 * @param Configuration $in_config The main configuration.
 */
function ls_dest(Configuration $in_config) {
    $destinations = $in_config->getDestinations();
    Environment::out(implode("\n", array_keys($destinations)));
}

