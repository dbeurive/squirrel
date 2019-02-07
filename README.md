# Introduction

This repository contains a script that performs backups over FTP.

# Installation

## Prerequisite

* You need PHP 5.6 or greater.
* Composer.
* A git client (optional).

## Installation

Clone this repository.

    git clone https://github.com/dbeurive/squirrel.git
    
> You can also download the [ZIP archive](https://github.com/dbeurive/squirrel/archive/master.zip).

Go the the directory `squirrel`.

    cd squirrel 

Install the dependencies.

    composer install
    
You can delete all files related to unit tests.

    rm -rf tests phpunit.xml

# Configuration

The configuration file is described in this [this document](config/README.md).

# Usage

This section presents all the available commands.

* **`squirrel-exec.php`**: execute a task (send files to remote secure storage).
* **`squirrel-list.php`**: list available tasks or destinations.
* **`squirrel-log.php`**: print the LOG file in a easy to read presentation.
* **`squirrel-ls.php`**: list the backups stored on a given destination.

For all these commands the command line options listed below apply:

* `--config <path to the configuration file>` (or `-c <path to the configuration file>`): specify an alternative configuration file.
  The default LOG file is `config/squirrel.json`.
* `--verobse` (or `-v`): activate the "verbose mode".

## Execute a task

By executing a task you trigger the transfers of files to back up from the local file system to remote destinations
(through FTP).

    php squirrel-exec.php \
        [(--config|-c) /path/to/the/config/file] \
        [(--verbose|-v)] \
        <name of the task to execute>

## List the available tasks and destinations:
    
You can print all the tasks or destinations configured within the configuration file.
    
    php squirrel-list.php \
        [(--config|-c) /path/to/the/config/file] \
        [(--verbose|-v)] \
        (task|destination)
        
For example, to print the list of available tasks to execute:

    $ php squirrel-list.php task
    wiki
    database
    
To print the list of configured destinations:

    $ php squirrel-list.php destination
    ovh-secure-storage-1
    ovh-secure-storage-2

## Print the LOG file in an easy to read presentation 
    
You can print the LOG file in an easy to read presentation: 
        
    php squirrel-log.php \
        [-p|--problems] \
        [(--config|-c) /path/to/the/config/file] \
        [(--verbose|-v)]

Specific options:
    
* The option `--problem` (or `-p`) will filter the lines of LOG and keep only the lines tagged `WARNING`, `ERROR` or `FATAL`.

## List the backups stored on a given destination

You can list all the backups stored on a designated destination:

    php squirrel-ls.php \
        [(--config|-c) /path/to/the/config/file] \
        [(--verbose|-v)] \
        <name of the destination>        


