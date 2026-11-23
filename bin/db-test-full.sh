#!/bin/bash
date
umask 0002

TEST_SUITE="${1:-primary}"
DB_TYPE="${2:-SQLite}"

DB_TYPE="$DB_TYPE" ./vendor/bin/phpunit --configuration ./phpunit.persistence.xml  --display-deprecations --testsuite $TEST_SUITE
# --stop-on-failure
