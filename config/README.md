# Configuration

This directory contains the default configuration file.

The configuration file contains a JSON encoded data structure, which description is given below.

## Log file section

Structure:

    "log": {
        "directory": "/path/to/the/local/directory/that/contains/the/log/file",
        "name": <name_of_the_file>,
        "level": <log_level>,
        "file-timestamped" true|false
    }


* `directory`: path to the local directory used to store the LOG file.
* `name`: name of the LOG file.
* `level`: the LOG level. This value can be:
  * "FATAL": only messages tagged "FATAL" will be printed to the LOG file.
  * "ERROR": only messages tagged "FATAL" and "ERROR" will be printed to the LOG file.
  * "WARNING": only messages tagged "FATAL", "ERROR" and "WARNING" will be printed to the LOG file.
  * "SUCCESS": only messages tagged "FATAL", "ERROR", "WARNING" and "SUCCESS" will be printed to the LOG file.
  * "INFO": only messages tagged "FATAL", "ERROR", "WARNING", "SUCCESS" and "INFO" will be printed to the LOG file. **This is the default value**.
  * "DATA": only messages tagged "FATAL", "ERROR", "WARNING", "SUCCESS", "INFO" and "DATA" will be printed to the LOG file.
  * "DEBUG": all messages will be printed to the LOG file.
* `file-timestamped`: flag that indicates whether the name of the LOG file must be prefixed by the timestamp of the day or not.
  Possible values are:
  * `true`: the name of the file will be prefixed by the timestamp of the day "`YYMMDD`".
  * `false`: the name of the file will **NOT** be prefixed by the timestamp of the day.

When the LOG file is created, it is prefixed by the date of the day "`YYYYMMDD`". With:

* `YYYY`: four-digit representation for the year.
* `MM`: two-digit representation of the month (with leading zeros).
* `DD`: two-digit representation of the day of the month (with leading zeros).

> Please note that you can use the string "`${__DIR__}`" within the directory path to refer to the directory where the configuration file resides.

> Please note that the LOG file is opened and closed each time a message is added to the file.
> Relatively to performance, this strategy is not the best one.
> However, it makes the management of LOG files easier.
> If you've ever faced a _dereferenced file_, then you understand why.
> And, by the way, for a backup tool that uses FTP, logging performance is not an issue.

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

Structure:

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
          If the directory does not exist, then it is created.
* `timeout`: The connexion timeout.
* `retry`: the number of attempted retries for every FTP commands (including the connexion) before the script gives up.
* `sleep`: the number of seconds between to attempted retries.

## Task section

This section lists the backup tasks that can be performed.

Structure:

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



