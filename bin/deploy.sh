#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
cd "$PROJECT_ROOT"

DRY_RUN=""
if [[ "${1:-}" == "--dry-run" || "${1:-}" == "-n" ]]; then
  DRY_RUN="-n"
  echo ">>> RUNNING IN DRY-RUN MODE (App only - tests/ untouched) <<<"
  echo
fi

rsync -rlvzc $DRY_RUN --delete --no-p --no-t \
  --chmod=D2775,F664 \
  --exclude="/vendor/***" \
  --exclude="/tests/***" \
  --exclude="/.settings/" \
  --exclude="/.project" \
  --exclude="/.buildpath" \
  --exclude="/.git/***" \
  --exclude="*.local.php" \
  --exclude="/SessionAuth.json" \
  --exclude="*.phar" \
  --exclude="/.phpunit.result.cache" \
  --exclude="/.php-cs-fixer.cache" \
  --exclude='CHEATSHEET.md' \
  ./ www-data@kam-motion.com:/var/www/html/greenjeans/

echo
echo "App upload time: $SECONDS seconds."
echo
date

