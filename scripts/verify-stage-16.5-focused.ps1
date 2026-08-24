param(
    [Parameter(Mandatory=$false)]
    [string[]]$Tests = @(
        'tests/Architecture/PrivilegedAuditGovernanceTest.php',
        'tests/Feature/PrivilegedAuditTest.php'
    )
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$Root = Split-Path -Parent $PSScriptRoot
Push-Location $Root
try {
    if ($null -eq (Get-Command docker -ErrorAction SilentlyContinue)) {
        throw 'Docker is required for canonical database-backed focused tests.'
    }

    Write-Host '[SongChart focused test] Starting canonical PostgreSQL/Redis services...' -ForegroundColor Cyan
    & docker compose -f compose.verify.yml up -d postgres redis
    if ($LASTEXITCODE -ne 0) {
        throw 'Canonical PostgreSQL/Redis services failed to start.'
    }

    Write-Host '[SongChart focused test] Synchronizing locked Composer dependencies in verification volume...' -ForegroundColor Cyan
    & docker compose -f compose.verify.yml run --rm verify composer install --no-interaction --prefer-dist --no-progress
    if ($LASTEXITCODE -ne 0) {
        throw 'Canonical verification Composer install failed.'
    }

    Write-Host '[SongChart focused test] Running focused tests through PostgreSQL test authority...' -ForegroundColor Cyan
    $arguments = @('compose', '-f', 'compose.verify.yml', 'run', '--rm', 'verify', 'php', 'scripts/run-database-tests.php', 'postgres', '--prepare-schema')
    $arguments += $Tests
    & docker @arguments
    if ($LASTEXITCODE -ne 0) {
        throw 'Canonical database-backed focused tests failed.'
    }

    Write-Host '[SongChart focused test] PASSED.' -ForegroundColor Green
} finally {
    Pop-Location
}
