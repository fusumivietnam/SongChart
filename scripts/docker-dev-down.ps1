$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $PSScriptRoot
& powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $Root 'scripts/songchart.ps1') dev down
exit $LASTEXITCODE
