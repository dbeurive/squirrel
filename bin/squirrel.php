<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
use dbeurive\Squirrel\Configuration;
use dbeurive\Squirrel\FileScanner;
use dbeurive\Squirrel\Destination;
use dbeurive\Squirrel\BackupStatusManager;
use dbeurive\Log\Logger;

define('DEFAULT_CONFIGURATION', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'squirrel.json');
define('ARG_TASK', 'task');
define('ARG_CONF', 'config');
define('ARG_VERBOSE', 'verbose');

// ---------------------------------------------
// Parse the command line.
// ---------------------------------------------

$arg_task = null;
$arg_conf = null;
$arg_verbose = false;

$climate = new League\CLImate\CLImate;
$climate->arguments->add(array(
    ARG_CONF => array(
        'prefix'       => 'c',
        'longPrefix'   => 'config',
        'description'  => 'Path to the configuration file.',
        'defaultValue' => DEFAULT_CONFIGURATION
    ),
    ARG_TASK => array(
        'prefix'       => 't',
        'longPrefix'   => 'task',
        'description'  => 'Task to perform.',
        'required'     => true
    ),
    ARG_VERBOSE => array(
        'prefix'       => 'v',
        'longPrefix'   => 'verbose',
        'description'  => 'Activate the verbose mode.',
        'noValue'      => true
    )
));

try {
    $climate->arguments->parse();
} catch (\Exception $e) {
    fatal_error($e->getMessage());
}

$arg_task = $climate->arguments->get(ARG_TASK);
$arg_conf = $climate->arguments->get(ARG_CONF);
$arg_verbose = $climate->arguments->get(ARG_VERBOSE);

// ---------------------------------------------
// Load the configuration.
// ---------------------------------------------

try {
    $config = new Configuration($arg_conf, realpath(dirname($arg_conf)));
} catch (\Exception $e) {
    fatal_error($e->getMessage());
}

// ---------------------------------------------
// Check the arguments.
// ---------------------------------------------

if (! $config->isTaskConfigured($arg_task)) {
    fatal_error(sprintf('Unknown task "%s".', $arg_task));
}

// ---------------------------------------------
// Create the LOG file.
// ---------------------------------------------

$timestamp = strftime('%Y%m%d');
$log = $config->getLog();
$log_path = null;

if ($log->fileTimestamped()) {
    $log_path = sprintf('%s%s%s-%s', $log->getDirectory(), DIRECTORY_SEPARATOR, $timestamp, $log->getName());
} else {
    $log_path = sprintf('%s%s%s', $log->getDirectory(), DIRECTORY_SEPARATOR, $log->getName());
}

$logger = new Logger($log_path, $config->getLog()->getLevel());

// ---------------------------------------------
// Get the task to execute.
// ---------------------------------------------

$task = $config->getTask($arg_task);

info(sprintf('Starting task "%s"', $task));
info(sprintf('LOG file is: %s', $log_path));
info(sprintf('File ID = "%s".', $task->getFileId()));
info(sprintf('Local input directory = "%s".', $task->getLocalInputDirectory()));
info(sprintf('Local done directory = "%s".', $task->getLocalDoneDirectory()));

// ---------------------------------------------
// Lists the files to backup.
// ---------------------------------------------

$files_to_backup = array();
$file_scanner = new FileScanner($task->getLocalInputDirectory(), $task->getFileId());

try {
    $files_to_backup = $file_scanner->getFiles();
    info(sprintf('Number of files to backup: %d', count($files_to_backup)));
    $logger->debug(sprintf('List of local files to backup: %s', implode(', ', $files_to_backup)));

} catch (\Exception $e) {
    $logger->fatal(sprintf('Cannot get the list of files to backup! %s', $e->getMessage()));
    fatal_error($e->getMessage());
}

$error_message = null;

// ---------------------------------------------
// Send files.
// ---------------------------------------------

$backup_status = new BackupStatusManager($files_to_backup);

if (count($files_to_backup)) {

    info("Backup files.");
    /** @var Destination $_destination */
    foreach ($task->getDestinations() as $_destination) {

        info(sprintf('Open a connexion to "%s"', $_destination->getName()));
        if (false === $_destination->reach($error_message)) {
            error(sprintf('Fail to open a connexion to "%s": %s', $_destination, $error_message));
            $backup_status->setErrors($files_to_backup);
            continue;
        }

        if (false === $_destination->createPath($error_message)) {
            error(sprintf('Fail to to create the directory "%s" on the destination "%s": %s', $_destination->getPath(), $_destination, $error_message));
            $backup_status->setErrors($files_to_backup);
            continue;
        }

        foreach ($files_to_backup as $_local_file_path) {
            info(sprintf('Send "%s"', basename($_local_file_path)));
            if (false === $_destination->send($_local_file_path, $error_message)) {
                error(sprintf('Fail to transfer the file "%s" to "%s": %s', $_local_file_path, $_destination, $error_message));
                $backup_status->setError($_local_file_path);
                continue;
            }
            $logger->success(sprintf('File "%s" successfully transferred to "%s".', basename($_local_file_path), $_destination));
        }

        $logger->debug(sprintf('Disconnect from "%s".', $_destination->getName()));
        $_destination->leave();
    }

    info("Moving backed up files to the \"done\" directory.");
    foreach ($files_to_backup as $_local_file_path) {

        if ($backup_status->isError($_local_file_path)) {
            info(sprintf('Do not delete "%s".', basename($_local_file_path)));
            continue;
        }

        info(sprintf('Move "%s"', basename($_local_file_path)));
        if (false === $task->done($_local_file_path, $error_message)) {
            error(sprintf('Fail to move "%s" to "%s": %s', $_local_file_path, $task->getLocalDoneDirectory(), $error_message));
            continue;
        }
        $logger->success(sprintf('File "%s" successfully moved to "%s".', basename($_local_file_path), $task->getLocalDoneDirectory()));
    }
}

// ---------------------------------------------
// Remove expired files.
// ---------------------------------------------

/** @var Destination $_destination */
foreach ($task->getDestinations() as $_destination) {

    info(sprintf('Open a connexion to "%s"', $_destination->getName()));
    if (false === $_destination->reach($error_message)) {
        error(sprintf('Fail to open a connexion to "%s": %s', $_destination->getName(), $error_message));
        continue;
    }

    $expired = array();
    info(sprintf('Get the list of files to delete on "%s"', $_destination));
    if (false === $files = $_destination->inventory($task->getKeepCount(),$expired, $error_message)) {
        error(sprintf('Fail to get the list of remote files to delete on "%s": %s', $_destination, $error_message));
        continue;
    }

    info(sprintf('Number of remote files to delete on "%s": %d', $_destination, count($expired)));
    $logger->debug(sprintf('List of remote files to delete: ', implode(', ', $expired)));

    if (count($expired) > 0) {
        foreach ($expired as $_file) {

            info(sprintf('Del "%s" from "%s"', $_file, $_destination->getName()));
            if (false === $_destination->remove($_file, $error_message)) {
                $logger->error(sprintf('Fail to remove "%s" from "%s": %s', $_file, $_destination, $error_message));
            } else {
                $logger->success(sprintf('File "%s" successfully deleted from "%s".', $_file, $_destination));
            }
        }
    } else {
        info(sprintf('No file to delete on "%s".', $_destination));
    }

    $logger->debug('Disconnect from "%s".', $_destination->getHost());
    $_destination->leave();
}

info('Done');

/**
 * Report an information.
 * @param string $in_message The information to report..
 * @throws Exception
 */
function info($in_message) {
    global $climate;
    global $arg_verbose;
    global $logger;

    $logger->info($in_message);
    if ($arg_verbose) {
        $climate->out($in_message);
    }
}

/**
 * Report an error.
 * @param string $in_message Message that describes the error.
 * @throws Exception
 */
function error($in_message) {
    global $climate;
    global $arg_verbose;
    global $logger;

    $logger->error($in_message);
    if ($arg_verbose) {
        $climate->red($in_message);
    }
}

/**
 * Report a fatal error.
 * @param string $in_message Message that describes the error.
 */
function fatal_error($in_message) {
    global $climate;
    global $arg_verbose;

    if ($arg_verbose) {
        $climate->error(sprintf("ERROR: %s\n", $in_message));
    }

    exit(1);
}


