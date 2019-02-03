# Introduction

This repository contains a script that performs backups over FTP.

# Installation

## Prerequisite

* You need PHP 5.6 or greater.
* Composer.
* A git client (optional).

## Installation

Clone this repository (or download the ZIP archive).

Go the the directory `squirrel`.

    cd squirrel 

Install the dependencies.

    composer install
    
You can delete all files related to unit tests.

    rm -rf tests phpunit.xml

# Usage

Setup the configuration (see [this link](config/README.md)).

Then you can run the script:

    php squirrel.php [(--config|-c) /path/to/the/config/file] \
        [(--task|-t) <name of the task to execute>] \
        [(--verbose|-v)] 
        
