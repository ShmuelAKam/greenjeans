#!/bin/bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
cd "$PROJECT_ROOT"

DRY_RUN=""
if [[ "${1:-}" == "--dry-run" || "${1:-}" == "-n" ]]; then
  DRY_RUN="-n"
  echo ">>> RUNNING IN DRY-RUN MODE (Tests & test configs only) <<<"
  echo
fi

rsync -rlvz $DRY_RUN --delete --no-p \
  --chmod=D2775,F664 \
  --filter="P /tests/Api/fixture-prev/***" \
  --filter="P /tests/Api/fixtures-prev/***" \
  --filter="P /tests/Api/fixtures-last/***" \
  --include="/tests/***" \
  --include="/phpunit*.xml" \
  --include="/phpstan.neon" \
  --exclude="*" \
  ./ www-data@kam-motion.com:/var/www/html/greenjeans/

echo
echo "Tests upload time: $SECONDS seconds."
echo
date

