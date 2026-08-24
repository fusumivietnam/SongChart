param(
    [switch]$NoBuild,
    [switch]$KeepServices
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$Root = Split-Path -Parent $PSScriptRoot
$Compose = Join-Path $Root 'compose.verify.yml'
$ProjectName = 'songchart-verify'

Push-Location $Root
try {
    if ($null -eq (Get-Command docker -ErrorAction SilentlyContinue)) {
        throw 'Docker CLI was not found. Install/start Docker Desktop with WSL2 integration, then rerun.'
    }

    & docker compose version
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker Compose v2 is required.'
    }

    Write-Host '[SongChart verify] Resetting ephemeral PostgreSQL/Redis services...' -ForegroundColor Cyan
    & docker compose -p $ProjectName -f $Compose down --remove-orphans
    if ($LASTEXITCODE -ne 0) {
        throw 'Unable to reset canonical verification services.'
    }

    if (-not $NoBuild) {
        Write-Host '[SongChart verify] Building PHP 8.5 verification image...' -ForegroundColor Cyan
        & docker compose -p $ProjectName -f $Compose build verify
        if ($LASTEXITCODE -ne 0) {
            throw 'Verification image build failed.'
        }
    }

    Write-Host '[SongChart verify] Running canonical verification...' -ForegroundColor Cyan
    & docker compose -p $ProjectName -f $Compose run --rm verify
    if ($LASTEXITCODE -ne 0) {
        throw 'Canonical verification failed.'
    }

    Write-Host '[SongChart verify] PASSED.' -ForegroundColor Green
} finally {
    if (-not $KeepServices) {
        & docker compose -p $ProjectName -f $Compose down --remove-orphans
    }

    Pop-Location
}
