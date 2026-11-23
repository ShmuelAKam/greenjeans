#!/bin/bash
date
umask 0002

TEST_SUITE="${1:-primary}"
RUN_TYPE="${2:-Normal}"
SILENT="${3:-SILENT}"

RUN_TYPE="$RUN_TYPE" SILENT="$SILENT" ./vendor/bin/phpunit --configuration ./phpunit.api.xml --display-deprecations --testsuite "$TEST_SUITE"

# --stop-on-failure
