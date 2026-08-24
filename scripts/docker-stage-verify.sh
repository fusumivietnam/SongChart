#!/usr/bin/env bash
set -euo pipefail
cd /workspace

echo "[SongChart Docker stage] PHP: $(php -r 'echo PHP_VERSION;')"
echo "[SongChart Docker stage] Installing locked PHP dependencies..."
composer install --no-interaction --prefer-dist --no-progress

echo "[SongChart Docker stage] Installing locked frontend dependencies..."
npm ci --no-audit --no-fund

echo "[SongChart Docker stage] Refreshing exact-container repository authority graph..."
php scripts/compile-repository-contracts.php --refresh-check

echo "[SongChart Docker stage] Normalizing with locked Pint..."
composer quality:normalize

echo "[SongChart Docker stage] Refreshing authority graph after normalization..."
php scripts/compile-repository-contracts.php --refresh-check

echo "[SongChart Docker stage] Running the single stage closure entrypoint..."
composer stage:verify

echo "[SongChart Docker stage] PASSED."
