<?php

include __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

use dbeurive\Squirrel\FileScanner;
use dbeurive\Squirrel\Destination;
use dbeurive\Squirrel\BackupStatusManager;

define('CLA_TASK', 'task');
define('ARG_LIST_TASKS', 'list_tasks');
define('ARG_LIST_DESTINATIONS', 'list_destinations');

define('CLI_SPECIFIC_CONFIGURATION', array(
    CLA_TASK => array(
        'description'  => 'Name of the task to perform.',
        'required'     => true
    ),
));

/**
 * Print the help.
 */
$help = function() {
    printf("php %s <name of the task to execute>\n", basename(__FILE__));
};

// ---------------------------------------------
// Initialise the environment.
// ---------------------------------------------

Environment::init(CLI_SPECIFIC_CONFIGURATION, $help);
$cla_task = Environment::getClaValue(CLA_TASK);
$config = Environment::getConfiguration();

if (! $config->isTaskConfigured($cla_task)) {
    Environment::fatal(sprintf('Unknown task "%s".', $cla_task));
}

// ---------------------------------------------
// Get the task to execute.
// ---------------------------------------------

$task = $config->getTask($cla_task);

Environment::info(sprintf('Starting task "%s"', $task));
Environment::info(sprintf('LOG file is: %s', Environment::getLogPath()));
Environment::info(sprintf('File ID = "%s".', $task->getFileId()));
Environment::info(sprintf('Local input directory = "%s".', $task->getLocalInputDirectory()));
Environment::info(sprintf('Local done directory = "%s".', $task->getLocalDoneDirectory()));

// ---------------------------------------------
// Lists the files to backup.
// ---------------------------------------------

$files_to_backup = array();
$file_scanner = new FileScanner($task->getLocalInputDirectory(), $task->getFileId());

try {
    $files_to_backup = $file_scanner->getFiles();
    Environment::info(sprintf('Number of files to backup: %d', count($files_to_backup)));
    Environment::debug(sprintf('List of local files to backup: %s', implode(', ', $files_to_backup)));
} catch (\Exception $e) {
    Environment::fatal(sprintf('Cannot get the list of files to backup! %s', $e->getMessage()));
}

$error_message = null;

// ---------------------------------------------
// Send files.
// ---------------------------------------------

$backup_status = new BackupStatusManager($files_to_backup);

if (count($files_to_backup)) {

    Environment::info("Backup files.");
    /** @var Destination $_destination */
    foreach ($task->getDestinations() as $_destination) {

        Environment::info(sprintf('Open a connexion to "%s"', $_destination->getName()));
        if (false === $_destination->reach($error_message)) {
            Environment::error(sprintf('Fail to open a connexion to "%s": %s', $_destination, $error_message));
            $backup_status->setErrors($files_to_backup);
            continue;
        }

        if (false === $_destination->createPath($error_message)) {
            Environment::error(sprintf('Fail to to create the directory "%s" on the destination "%s": %s', $_destination->getPath(), $_destination, $error_message));
            $backup_status->setErrors($files_to_backup);
            continue;
        }

        foreach ($files_to_backup as $_local_file_path) {
            Environment::info(sprintf('Send "%s"', basename($_local_file_path)));
            if (false === $_destination->send($_local_file_path, $error_message)) {
                Environment::error(sprintf('Fail to transfer the file "%s" to "%s": %s', $_local_file_path, $_destination, $error_message));
                $backup_status->setError($_local_file_path);
                continue;
            }
            Environment::success(sprintf('File "%s" successfully transferred to "%s".', basename($_local_file_path), $_destination));
        }

        Environment::debug(sprintf('Disconnect from "%s".', $_destination->getName()));
        $_destination->leave();
    }

    Environment::info("Moving backed up files to the \"done\" directory.");
    foreach ($files_to_backup as $_local_file_path) {

        if ($backup_status->isError($_local_file_path)) {
            Environment::info(sprintf('Do not delete "%s".', basename($_local_file_path)));
            continue;
        }

        Environment::info(sprintf('Move "%s"', basename($_local_file_path)));
        if (false === $task->done($_local_file_path, $error_message)) {
            Environment::error(sprintf('Fail to move "%s" to "%s": %s', $_local_file_path, $task->getLocalDoneDirectory(), $error_message));
            continue;
        }
        Environment::success(sprintf('File "%s" successfully moved to "%s".', basename($_local_file_path), $task->getLocalDoneDirectory()));
    }
}

// ---------------------------------------------
// Remove expired files.
// ---------------------------------------------

/** @var Destination $_destination */
foreach ($task->getDestinations() as $_destination) {

    Environment::info(sprintf('Open a connexion to "%s"', $_destination->getName()));
    if (false === $_destination->reach($error_message)) {
        Environment::error(sprintf('Fail to open a connexion to "%s": %s', $_destination->getName(), $error_message));
        continue;
    }

    $expired = array();
    Environment::info(sprintf('Get the list of files to delete on "%s"', $_destination));
    if (false === $files = $_destination->inventory($task->getKeepCount(),$expired, $error_message)) {
        Environment::error(sprintf('Fail to get the list of remote files to delete on "%s": %s', $_destination, $error_message));
        continue;
    }

    Environment::info(sprintf('Number of remote files to delete on "%s": %d', $_destination, count($expired)));
    Environment::debug(sprintf('List of remote files to delete: %s', implode(', ', $expired)));

    if (count($expired) > 0) {
        foreach ($expired as $_file) {

            Environment::info(sprintf('Del "%s" from "%s"', $_file, $_destination->getName()));
            if (false === $_destination->remove($_file, $error_message)) {
                Environment::error(sprintf('Fail to remove "%s" from "%s": %s', $_file, $_destination, $error_message));
            } else {
                Environment::success(sprintf('File "%s" successfully deleted from "%s".', $_file, $_destination));
            }
        }
    } else {
        Environment::info(sprintf('No file to delete on "%s".', $_destination));
    }

    Environment::debug(sprintf('Disconnect from "%s".', $_destination->getHost()));
    $_destination->leave();
}

Environment::info('Done');


