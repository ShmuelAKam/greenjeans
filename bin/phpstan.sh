#!/bin/bash

LEVEL="${1:-6}"

vendor/bin/phpstan analyse -l$LEVEL $2
