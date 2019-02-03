# Configuration

This directory contains the default configuration file.

The configuration file contains a JSON encoded data structure, which description is given below.

## Log file section


    "log": {
        "directory": "/path/to/the/local/directory/that/contains/the/log/file",
        "name": "name"
    }


* `log.directory`: path to the local directory used to store the LOG file.
* `log.name`: name of the LOG file.

When the LOG file is created, it is prefixed by the date of the day "`YYYYMMDD`".

> Please note that you can use the string "`${__DIR__}`" within the directory path to refer to the directory where the configuration file resides.

## Destination section

This section lists the _logical_ destinations (as opposed to the _network_ destination) where the backed up files are
deposited. 

A logical destination is defined by:

* A remote host (identified by its name or its IP address).
* An FTP account (user, login and TCP port).
* A path to a directory (relatively to the FTP account) where to deposit the backed up files.
* Some data used to handle FTP failures.
  * The connexion timeout.
  * The number of attempted retries for every FTP commands (including the connexion) before the script gives up.
  * The number of seconds between to attempted retries.



    "destinations": {
       "logical_name_of_the_destination": {
          "host": "network_host",
          "user": "ftp_user",
          "password": "ftp_password",
          "port": <tcp_port>,
          "path": "/path/to/backed/up/files",
          "timeout": <connexion_timeout>,
          "retry": <number_of_attempted_retries>,
          "sleep": <number_of_seconds_between_to_attempted_retries>
    }

   
* `host`: a remote host (identified by its name or its IP address).
* `user`: an FTP user.
* `password`: an FTP password.
* `port`: the TCP port used by the FTP server.
* `path`: the path to a directory (relatively to the FTP account) where to deposit the backed up files.
* `timeout`: The connexion timeout.
* `retry`: the number of attempted retries for every FTP commands (including the connexion) before the script gives up.
* `sleep`: the number of seconds between to attempted retries.

## Task section

This section lists the backup tasks that can be performed.


    "tasks": {
        "name_of_the_task": {
          "local-input-directory": "/path/to/the/local/directory/that/contains/the/files/to/backup",
          "local-done-directory": "/path/to/the/local/directory/used/to/store/the/backed/up/files",
          "file-id": "file_id",
          "keep-count": number_of_backup_to_keep,
          "destinations": ["logical_destination_1", "logical_destination_2", ...],
          "on-error": "error_handler",
          "on-success": "success_handler"
        }


* `local-input-directory`: path to the local directory where to look for files to backup.
* `local-done-directory`: path to the local directory where to move a file after it has been successfully backed up.
* `file-id`: the file ID.
* `keep-count`: number of backups to keep on the destinations.
* `destinations`: list of destinations where to backup the files.
* `on-error`: error handler.
* `on-success`: success handler.

> Please note that you can use the string "`${__DIR__}`" within the local directory paths to refer to the directory where
the configuration file resides.



