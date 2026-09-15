#!/usr/bin/env bash
set -euo pipefail

cd /workspace

mode="${SONGCHART_VERIFICATION_MODE:-full}"
case "$mode" in
  full|close) ;;
  *)
    echo "[SongChart verify] Unsupported verification mode: $mode" >&2
    exit 2
    ;;
esac

echo "[SongChart verify] PHP: $(php -r 'echo PHP_VERSION;')"
echo "[SongChart verify] Composer: $(composer --version --no-ansi)"
echo "[SongChart verify] Node: $(node --version)"
echo "[SongChart verify] Mode: $mode"

echo "[SongChart verify] Verifying canonical PHP extensions..."
php scripts/verify-canonical-php-extensions.php

echo "[SongChart verify] Installing locked PHP dependencies into the verification vendor volume..."
composer install --no-interaction --prefer-dist --no-progress

if [[ "$mode" == 'close' ]]; then
  echo "[SongChart verify] Reusing exact-head CHECK evidence; running canonical-only closure gates..."
  php scripts/run-canonical-close.php
  echo "[SongChart verify] Canonical close PASSED."
  exit 0
fi

echo "[SongChart verify] Installing locked frontend dependencies into the verification node_modules volume..."
npm ci --no-audit --no-fund

echo "[SongChart verify] Refreshing exact-container repository authority graph..."
php scripts/compile-repository-contracts.php --refresh-check

echo "[SongChart verify] Normalizing source with the locked Pint version..."
composer quality:normalize

echo "[SongChart verify] Refreshing authority graph after normalization..."
php scripts/compile-repository-contracts.php --refresh-check

echo "[SongChart verify] Running the single canonical closure entrypoint..."
composer canonical:verify

echo "[SongChart verify] Canonical verification PASSED."
