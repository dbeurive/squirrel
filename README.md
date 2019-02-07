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

The configuration presents 3 main entries:

* An entry that defines some parameters that apply to the LOG file.
* An entry that defines the _destinations_.
* An entry that defines the _tasks_.

**Destinations** are (remote) locations where the files to back up are transferred.
A destination is represented by 3 main parameters: a (remote) host, an FTP user, and a path to a (remote) directory (on the specified host).
Files to back up are transferred to the (remote) host, through FTP, using the specified user account, and are stored into
the given (remote) directory.

**Tasks** define the (local) files to back up and where these files must be transferred.
A task is represented by 3 main parameters: a _file ID_, a local directory (where the files to back up are stored)
and a list of _destinations_.

The names of the files to back up must respect the convention below:

* The name of the file begins with a timestamp "`YYYYMMDDHHMMSS`".
* The (prefixed) timestamp is followed by an en dash ("`-`").
* Then the name of the file ends with the **file ID**. The _file ID_ can by any string.

Examples of valid names for files to back up:

* `20000101120002-file1.txt`: the _file ID_ is "`file1.txt`".
* `20000101120003-file1.txt`: the _file ID_ is "`file1.txt`".
* `20000101120003-db.tar.gz`: the _file ID_ is "`db.tar.gz`".

> In the examples above, we see two files to back up with the same _file ID_ ("`file1.txt`"), but different timestamps.

The configuration file is described in this [this document](config/README.md).

Example of configuration file: [config/squirrel.json](config/squirrel.json).

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


