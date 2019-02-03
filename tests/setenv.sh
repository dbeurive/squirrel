#!/usr/bin/env bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ] ; do SOURCE="$(readlink "$SOURCE")"; done
SCRIPT_DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

cp "${SCRIPT_DIR}"/data/global/* "${SCRIPT_DIR}/ftp-input/"

if [ ! -d "${SCRIPT_DIR}"/ftp-done ]; then
    echo "ERROR!!!"
    exit 1
fi

cd "${SCRIPT_DIR}"/ftp-done
if [ $? -ne 0 ]; then
    echo "ERROR!!!"
    exit 1
fi

rm -f *
