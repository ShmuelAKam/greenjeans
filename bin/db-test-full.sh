#!/bin/bash
date
umask 0002

TEST_SUITE="$1"

if [ -z "$TEST_SUITE" ]; then
    TEST_SUITE="primary"
fi

./vendor/bin/phpunit --configuration ./phpunit.persistence.xml  --display-deprecations --testsuite $TEST_SUITE

# --stop-on-failure
