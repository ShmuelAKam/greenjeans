#!/bin/bash
date
umask 0002

FILE_NAME="$1"
TEST_NAME="$2"
CONFIG_FILE="phpunit.api.xml"

# Only require the file name
if [ -z "$FILE_NAME" ]; then
    echo "Usage: $0 <file_name> [test_name]"
    exit 1
fi

# Run all tests if no test name is provided, otherwise filter for the specific one
if [ -z "$TEST_NAME" ]; then
    ./vendor/bin/phpunit -c "$CONFIG_FILE"                       "$FILE_NAME"
else
    ./vendor/bin/phpunit -c "$CONFIG_FILE" --filter "$TEST_NAME" "$FILE_NAME"
fi

# --stop-on-failure
